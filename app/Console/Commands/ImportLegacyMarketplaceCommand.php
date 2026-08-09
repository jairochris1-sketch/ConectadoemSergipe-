<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreBusinessHour;
use App\Models\StoreMedia;
use App\Models\User;
use App\Services\LegacySqlDumpParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ImportLegacyMarketplaceCommand extends Command
{
    protected $signature = 'legacy:import
        {dump : Caminho do dump SQL antigo}
        {--admin-email= : Administrador que será dono dos perfis ainda não reivindicados}
        {--media-root= : Pasta que contém uploads/ads da plataforma antiga}
        {--commit : Grava a importação; sem esta opção executa apenas uma prévia}
        {--enable-claims : Ativa a reivindicação nos perfis importados}';

    protected $description = 'Importa dados da plataforma PHP antiga para o marketplace Laravel';

    public function handle(LegacySqlDumpParser $parser): int
    {
        try {
            $dumpPath = $this->absolutePath((string) $this->argument('dump'));
            $tables = $parser->parse($dumpPath);
            $this->assertRequiredTables($tables);
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $admin = $this->resolveAdmin();
        if (! $admin) {
            $this->components->error('Nenhum administrador foi encontrado para receber os perfis importados.');

            return self::FAILURE;
        }

        $clients = collect($tables['clientes'])->keyBy('id');
        $categories = collect($tables['categorias'])->keyBy('id');
        $hours = collect($tables['horarios'] ?? [])->groupBy('anuncio_id');
        $images = collect($tables['imagens'] ?? [])->groupBy('anuncio_id');
        $ads = collect($tables['anuncios']);
        $mediaRoot = $this->option('media-root')
            ? $this->absolutePath((string) $this->option('media-root'))
            : null;

        $serviceAds = $ads->where('tipo', '!=', 'loja')->values();
        $storeAds = $ads->where('tipo', 'loja')->values();
        $referencedMedia = $this->referencedMedia($ads, collect($tables['imagens'] ?? []));
        $availableMedia = $referencedMedia->filter(
            fn (string $path): bool => $this->legacyMediaExists($mediaRoot, $path)
        );
        $existingServices = Ad::query()->whereIn('slug', $serviceAds->pluck('slug'))->count();
        $existingStores = Store::query()->whereIn('slug', $storeAds->pluck('slug'))->count();

        $this->components->info($this->option('commit') ? 'Importação da plataforma antiga' : 'Prévia da importação');
        $this->table(['Origem', 'Registros'], [
            ['Clientes', $clients->count()],
            ['Categorias', $categories->count()],
            ['Prestadores', $serviceAds->count()],
            ['Lojas', $storeAds->count()],
            ['Prestadores já existentes', $existingServices],
            ['Lojas já existentes', $existingStores],
            ['Imagens de galeria', collect($tables['imagens'] ?? [])->count()],
            ['Horários', collect($tables['horarios'] ?? [])->count()],
            ['Arquivos de mídia encontrados', $availableMedia->count().' de '.$referencedMedia->count()],
        ]);
        $this->line("Administrador responsável: {$admin->email}");

        if (! $this->option('commit')) {
            $this->components->warn('Prévia concluída. Nenhum registro foi alterado. Use --commit para gravar.');

            return self::SUCCESS;
        }

        $statistics = DB::transaction(function () use (
            $admin,
            $categories,
            $clients,
            $hours,
            $images,
            $serviceAds,
            $storeAds,
            $mediaRoot,
        ): array {
            User::query()->where('role', '!=', 'admin')->update(['subscription_plan' => 'free']);

            $categoryIds = $this->importCategories($categories);
            $stats = [
                'services_created' => 0,
                'services_updated' => 0,
                'stores_created' => 0,
                'stores_updated' => 0,
                'media_imported' => 0,
                'media_missing' => 0,
            ];

            foreach ($serviceAds as $legacyAd) {
                $client = $clients->get($legacyAd['cliente_id']);
                $category = $categories->get($legacyAd['categoria_id']);
                $ad = Ad::query()->firstOrNew(['slug' => $legacyAd['slug']]);
                $wasRecentlyCreated = ! $ad->exists;

                $ad->forceFill([
                    'user_id' => $admin->id,
                    'category_id' => $categoryIds[$legacyAd['categoria_id']] ?? null,
                    'module' => 'services',
                    'advertiser_type' => $category['nome'] ?? 'Prestador de Serviço',
                    'title' => $legacyAd['titulo'],
                    'description' => $legacyAd['descricao'],
                    'price' => null,
                    'cnpj' => $legacyAd['cnpj'] ?: ($client['cnpj'] ?? null),
                    'city' => $legacyAd['cidade'] ?: ($client['cidade'] ?? 'Aracaju'),
                    'state' => $this->normalizeState($legacyAd['estado'] ?: ($client['estado'] ?? null)),
                    'region' => $legacyAd['regiao'] ?: ($client['regiao'] ?? null),
                    'business_hours' => $this->serviceHours($hours->get($legacyAd['id'], collect())),
                    'instagram' => $legacyAd['instagram'] ?: null,
                    'facebook' => $legacyAd['facebook'] ?: null,
                    'logo' => $this->availableMediaPath($mediaRoot, $legacyAd['imagem_principal']),
                    'banner' => $this->availableMediaPath($mediaRoot, $legacyAd['imagem_banner']),
                    'status' => $legacyAd['status'] === 'ativo' ? 'active' : 'inactive',
                    'views' => (int) ($legacyAd['visualizacoes'] ?? 0),
                    'is_claimed' => false,
                    'claiming_enabled' => (bool) $this->option('enable-claims'),
                    'claimed_at' => null,
                    'contact_phone' => $legacyAd['telefone'] ?: ($client['telefone'] ?? null),
                    'contact_whatsapp' => $legacyAd['whatsapp'] ?: ($client['whatsapp'] ?? null),
                    'created_at' => $legacyAd['created_at'] ?: now(),
                    'updated_at' => $legacyAd['updated_at'] ?: $legacyAd['created_at'] ?: now(),
                ])->save();

                $stats[$wasRecentlyCreated ? 'services_created' : 'services_updated']++;
                $stats = $this->syncAdImages(
                    $ad,
                    $images->get($legacyAd['id'], collect()),
                    $mediaRoot,
                    $stats,
                );
            }

            foreach ($storeAds as $legacyAd) {
                $client = $clients->get($legacyAd['cliente_id']);
                $category = $categories->get($legacyAd['categoria_id']);
                $store = Store::query()->firstOrNew(['slug' => $legacyAd['slug']]);
                $wasRecentlyCreated = ! $store->exists;

                $store->forceFill([
                    'user_id' => $admin->id,
                    'name' => $legacyAd['titulo'],
                    'description' => $legacyAd['descricao'],
                    'category' => $category['nome'] ?? 'Loja',
                    'city' => $legacyAd['cidade'] ?: ($client['cidade'] ?? null),
                    'state' => $this->normalizeState($legacyAd['estado'] ?: ($client['estado'] ?? null)),
                    'phone' => $legacyAd['telefone'] ?: ($client['telefone'] ?? null),
                    'whatsapp' => $legacyAd['whatsapp'] ?: ($client['whatsapp'] ?? null),
                    'instagram' => $legacyAd['instagram'] ?: null,
                    'logo' => $this->availableMediaPath($mediaRoot, $legacyAd['imagem_principal']),
                    'banner' => $this->availableMediaPath($mediaRoot, $legacyAd['imagem_banner']),
                    'active' => $legacyAd['status'] === 'ativo',
                    'moderation_status' => 'approved',
                    'moderated_by' => $admin->id,
                    'moderated_at' => now(),
                    'created_at' => $legacyAd['created_at'] ?: now(),
                    'updated_at' => $legacyAd['updated_at'] ?: $legacyAd['created_at'] ?: now(),
                ])->save();

                $stats[$wasRecentlyCreated ? 'stores_created' : 'stores_updated']++;
                $this->syncStoreHours($store, $hours->get($legacyAd['id'], collect()));
                $stats = $this->syncStoreMedia(
                    $store,
                    $images->get($legacyAd['id'], collect()),
                    $mediaRoot,
                    $stats,
                );
            }

            return $stats;
        });

        $this->table(['Resultado', 'Quantidade'], [
            ['Prestadores criados', $statistics['services_created']],
            ['Prestadores atualizados', $statistics['services_updated']],
            ['Lojas criadas', $statistics['stores_created']],
            ['Lojas atualizadas', $statistics['stores_updated']],
            ['Mídias vinculadas', $statistics['media_imported']],
            ['Mídias pendentes', $statistics['media_missing']],
        ]);
        $this->components->success('Importação concluída. O comando pode ser executado novamente sem duplicar perfis.');

        return self::SUCCESS;
    }

    private function resolveAdmin(): ?User
    {
        $email = trim((string) $this->option('admin-email'));

        return User::query()
            ->where('role', 'admin')
            ->when($email !== '', fn ($query) => $query->where('email', $email))
            ->first();
    }

    /** @param array<string, array<int, array<string, mixed>>> $tables */
    private function assertRequiredTables(array $tables): void
    {
        $missing = array_values(array_diff(['clientes', 'categorias', 'anuncios'], array_keys($tables)));

        if ($missing !== []) {
            throw new RuntimeException('Tabelas obrigatórias ausentes no dump: '.implode(', ', $missing));
        }
    }

    private function absolutePath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\\\\\/]/', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    private function importCategories($categories): array
    {
        $ids = [];

        foreach ($categories as $legacyCategory) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $legacyCategory['slug']],
                [
                    'name' => $legacyCategory['nome'],
                    'icon' => $this->fontAwesomeIcon($legacyCategory['icone']),
                    'active' => true,
                ],
            );
            $ids[$legacyCategory['id']] = $category->id;
        }

        return $ids;
    }

    private function fontAwesomeIcon(?string $legacyIcon): string
    {
        $icon = trim((string) $legacyIcon);

        return $icon === '' ? 'fa-tag' : 'fa-'.Str::kebab($icon);
    }

    private function normalizeState(?string $state): string
    {
        $state = Str::upper(trim((string) $state));

        return in_array($state, ['SE', 'SERGIPE'], true) ? 'SE' : Str::limit($state ?: 'SE', 2, '');
    }

    private function serviceHours($rows): array
    {
        $days = [1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday', 7 => 'sunday'];
        $result = [];

        foreach ($rows as $row) {
            if ((int) $row['fechado'] === 1 || ! $row['abertura'] || ! $row['fechamento']) {
                continue;
            }

            $result[$days[(int) $row['dia_semana']] ?? (string) $row['dia_semana']] = [
                'open' => substr($row['abertura'], 0, 5),
                'close' => substr($row['fechamento'], 0, 5),
            ];
        }

        return $result;
    }

    private function syncStoreHours(Store $store, $rows): void
    {
        foreach ($rows as $row) {
            $day = (int) $row['dia_semana'] % 7;
            StoreBusinessHour::query()->updateOrCreate(
                ['store_id' => $store->id, 'day_of_week' => $day],
                [
                    'is_closed' => (bool) $row['fechado'],
                    'is_24_hours' => false,
                    'opens_at' => $row['abertura'],
                    'closes_at' => $row['fechamento'],
                ],
            );
        }
    }

    private function syncAdImages(Ad $ad, $images, ?string $mediaRoot, array $stats): array
    {
        foreach ($images as $legacyImage) {
            $path = $this->availableMediaPath($mediaRoot, $legacyImage['caminho']);
            if (! $path) {
                $stats['media_missing']++;
                continue;
            }

            AdImage::query()->updateOrCreate(
                ['ad_id' => $ad->id, 'image_path' => $path],
                ['is_main' => false],
            );
            $stats['media_imported']++;
        }

        return $stats;
    }

    private function syncStoreMedia(Store $store, $images, ?string $mediaRoot, array $stats): array
    {
        foreach ($images as $legacyImage) {
            $path = $this->availableMediaPath($mediaRoot, $legacyImage['caminho']);
            if (! $path) {
                $stats['media_missing']++;
                continue;
            }

            StoreMedia::query()->updateOrCreate(
                ['store_id' => $store->id, 'type' => 'gallery', 'path' => $path],
                ['sort_order' => (int) $legacyImage['ordem']],
            );
            $stats['media_imported']++;
        }

        return $stats;
    }

    private function referencedMedia($ads, $images)
    {
        return $ads
            ->flatMap(fn (array $ad): array => [$ad['imagem_principal'], $ad['imagem_banner']])
            ->merge($images->pluck('caminho'))
            ->filter()
            ->map(fn (string $path): string => $this->normalizeMediaPath($path))
            ->unique()
            ->values();
    }

    private function availableMediaPath(?string $mediaRoot, ?string $path): ?string
    {
        if (! $path || ! $this->legacyMediaExists($mediaRoot, $path)) {
            return null;
        }

        return $this->normalizeMediaPath($path);
    }

    private function legacyMediaExists(?string $mediaRoot, string $path): bool
    {
        if (! $mediaRoot || ! is_dir($mediaRoot)) {
            return false;
        }

        $relative = preg_replace('#^uploads/ads/#', '', $this->normalizeMediaPath($path));

        return is_file(rtrim($mediaRoot, '\\/').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative));
    }

    private function normalizeMediaPath(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }
}
