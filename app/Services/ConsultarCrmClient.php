<?php

namespace App\Services;

use App\Exceptions\CrmLookupException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ConsultarCrmClient
{
    public function lookup(string $credential, string $state, string $queryName): array
    {
        $number = preg_replace('/\D+/', '', $credential);
        $state = strtoupper(trim($state));
        $queryName = trim($queryName);

        if ($number === '' || ! preg_match('/^[A-Z]{2}$/', $state) || mb_strlen($queryName) < 3) {
            throw new CrmLookupException('Informe o nome completo, o número do CRM e uma UF válida.');
        }

        $url = trim((string) config('services.consultar_crm.url'));
        $key = trim((string) config('services.consultar_crm.key'));
        if ($url === '' || $key === '') {
            throw new CrmLookupException('A consulta ao CRM ainda não está configurada.', 'configuration');
        }

        return Cache::remember(
            'consultar-crm:'.hash('sha256', $state.':'.$number.':'.Str::lower(Str::ascii($queryName))),
            now()->addHours(12),
            fn () => $this->request($url, $key, $number, $state, $queryName)
        );
    }

    private function request(string $url, string $key, string $number, string $state, string $queryName): array
    {
        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.consultar_crm.timeout', 12))
                ->retry(2, 250, throw: false)
                ->get($url, [
                    'tipo' => 'crm',
                    'uf' => $state,
                    'q' => $queryName,
                    'chave' => $key,
                    'destino' => 'json',
                ]);
        } catch (ConnectionException) {
            throw new CrmLookupException('O serviço de consulta ao CRM está indisponível. Tente novamente mais tarde.', 'unavailable');
        }

        if ($response->status() === 429) {
            throw new CrmLookupException('O limite temporário de consultas ao CRM foi atingido. Tente novamente mais tarde.', 'limit');
        }
        if (! $response->successful()) {
            throw new CrmLookupException('Não foi possível consultar o CRM neste momento.', 'unavailable');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new CrmLookupException('A API de CRM retornou uma resposta inválida.', 'unavailable');
        }

        $items = $payload['item'] ?? [];
        if (is_array($items) && isset($items['numero'])) {
            $items = [$items];
        }
        if (! is_array($items)) {
            $items = [];
        }

        $match = collect($items)->first(function ($item) use ($number, $state) {
            return is_array($item)
                && preg_replace('/\D+/', '', (string) ($item['numero'] ?? '')) === $number
                && strtoupper((string) ($item['uf'] ?? '')) === $state;
        });

        if (! $match) {
            throw new CrmLookupException('Nenhum CRM correspondente ao número e à UF informados foi localizado.', 'not_found');
        }

        $situation = trim((string) ($match['situacao'] ?? ''));
        if (Str::lower(Str::ascii($situation)) !== 'ativo') {
            throw new CrmLookupException('O CRM foi localizado, mas a situação informada não está ativa.', 'inactive');
        }

        return [
            'name' => trim((string) ($match['nome'] ?? '')),
            'number' => $number,
            'state' => $state,
            'situation' => $situation,
            'profession' => trim((string) ($match['profissao'] ?? '')),
            'specialties' => $this->specialties($match),
            'source_url' => filter_var($match['link'] ?? null, FILTER_VALIDATE_URL) ?: null,
        ];
    }

    private function specialties(array $item): array
    {
        $value = $item['especialidades'] ?? $item['especialidade'] ?? null;
        if (is_array($value)) {
            return collect($value)->map(fn ($entry) => trim((string) $entry))->filter()->values()->all();
        }

        return collect(preg_split('/[,;|]+/', (string) $value))
            ->map(fn ($entry) => trim($entry))
            ->filter()
            ->values()
            ->all();
    }
}
