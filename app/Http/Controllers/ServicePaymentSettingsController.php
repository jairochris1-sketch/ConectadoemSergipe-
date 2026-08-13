<?php

namespace App\Http\Controllers;

use App\Exceptions\AsaasIntegrationException;
use App\Models\Ad;
use App\Models\ServicePaymentSetting;
use App\Models\ServiceSubscriptionPlan;
use App\Services\AsaasClient;
use App\Support\ServiceBookingCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServicePaymentSettingsController extends Controller
{
    public function update(Request $request, Ad $ad)
    {
        $this->authorizeManagement($request, $ad);
        $data = $request->validate([
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
            'api_key' => ['nullable', 'string', 'min:20', 'max:500'],
            'online_payments_enabled' => ['nullable', 'boolean'],
            'subscriptions_enabled' => ['nullable', 'boolean'],
        ]);

        $setting = ServicePaymentSetting::firstOrNew(['user_id' => $ad->user_id]);
        $newKey = trim((string) ($data['api_key'] ?? ''));
        $environmentChanged = $setting->exists && $setting->environment !== $data['environment'];
        if ($environmentChanged && $newKey === '') {
            throw ValidationException::withMessages(['api_key' => 'Informe a chave correspondente ao novo ambiente.']);
        }
        $credentialsChanged = $environmentChanged || $newKey !== '';

        $setting->provider = 'asaas';
        $setting->environment = $data['environment'];
        if ($newKey !== '') {
            $setting->api_key = $newKey;
            $setting->api_key_hint = Str::mask($newKey, '*', 4, max(strlen($newKey) - 8, 4));
        }

        if (! $setting->api_key) {
            throw ValidationException::withMessages(['api_key' => 'Informe uma chave da API Asaas.']);
        }

        if ($credentialsChanged) {
            $setting->account_status = null;
            $setting->verified_at = null;
            $setting->webhook_id = null;
            $setting->webhook_token = null;
            $setting->webhook_registered_at = null;
            $setting->online_payments_enabled = false;
            $setting->subscriptions_enabled = false;
        } else {
            $wantsOnline = $request->boolean('online_payments_enabled');
            $wantsSubscriptions = $request->boolean('subscriptions_enabled');
            if (($wantsOnline || $wantsSubscriptions) && (! $setting->verified_at || ! $setting->webhook_registered_at)) {
                throw ValidationException::withMessages([
                    'online_payments_enabled' => 'Teste a conexão e registre o webhook antes de publicar pagamentos.',
                ]);
            }
            $setting->online_payments_enabled = $wantsOnline;
            $setting->subscriptions_enabled = $wantsOnline && $wantsSubscriptions;
        }

        $setting->save();

        return back()->with('success', $credentialsChanged
            ? 'Credencial salva. Agora teste a conexão com o Asaas.'
            : 'Preferências de pagamento atualizadas.');
    }

    public function verify(Request $request, Ad $ad, AsaasClient $asaas)
    {
        $this->authorizeManagement($request, $ad);
        $setting = ServicePaymentSetting::where('user_id', $ad->user_id)->firstOrFail();

        try {
            $status = $asaas->verifyAccount($setting);
        } catch (AsaasIntegrationException $exception) {
            return back()->withErrors(['asaas' => $exception->getMessage()]);
        }

        $general = strtoupper((string) ($status['general'] ?? 'CONNECTED'));
        $setting->update(['account_status' => $general, 'verified_at' => now()]);

        return back()->with('success', 'Conexão com o Asaas verificada no ambiente '.($setting->environment === 'sandbox' ? 'Sandbox' : 'Produção').'.');
    }

    public function registerWebhook(Request $request, Ad $ad, AsaasClient $asaas)
    {
        $this->authorizeManagement($request, $ad);
        $setting = ServicePaymentSetting::where('user_id', $ad->user_id)->firstOrFail();
        abort_unless($setting->verified_at, 422, 'Teste a conexão com o Asaas primeiro.');

        $baseUrl = rtrim((string) config('services.asaas.webhook_base_url'), '/');
        $url = $baseUrl.'/webhooks/asaas/'.$setting->public_id;
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (! str_starts_with($url, 'https://') || in_array($host, ['localhost', '127.0.0.1'], true)) {
            throw ValidationException::withMessages([
                'webhook' => 'Configure ASAAS_WEBHOOK_BASE_URL com a URL HTTPS pública do site antes de registrar o webhook.',
            ]);
        }

        $token = $setting->webhook_token ?: Str::random(64);
        try {
            $webhook = $asaas->registerWebhook($setting, $url, $ad->user->email, $token);
        } catch (AsaasIntegrationException $exception) {
            return back()->withErrors(['asaas' => $exception->getMessage()]);
        }

        $setting->update([
            'webhook_id' => $webhook['id'] ?? $setting->webhook_id,
            'webhook_token' => $token,
            'webhook_registered_at' => now(),
        ]);

        return back()->with('success', 'Webhook seguro registrado no Asaas. Agora você pode ativar os pagamentos.');
    }

    public function storePlan(Request $request, Ad $ad)
    {
        $this->authorizeManagement($request, $ad);
        [$data, $procedures] = $this->validatedPlan($request, $ad);
        $plan = $ad->serviceSubscriptionPlans()->create($data);
        $plan->procedures()->sync($procedures);

        return back()->with('success', 'Plano mensal criado'.($plan->active ? ' e publicado.' : ' como rascunho.'));
    }

    public function updatePlan(Request $request, Ad $ad, ServiceSubscriptionPlan $plan)
    {
        $this->authorizeManagement($request, $ad);
        abort_unless($plan->ad_id === $ad->id, 404);
        [$data, $procedures] = $this->validatedPlan($request, $ad);
        if ($plan->subscriptions()->whereNotIn('status', ['failed', 'cancelled'])->exists()) {
            $currentProcedures = $plan->procedures()
                ->get()
                ->mapWithKeys(fn ($procedure) => [
                    (int) $procedure->id => $procedure->pivot->included_uses === null
                        ? null
                        : (int) $procedure->pivot->included_uses,
                ])
                ->sortKeys()
                ->all();
            $newProcedures = collect($procedures)
                ->map(fn ($pivot) => $pivot['included_uses'] === null ? null : (int) $pivot['included_uses'])
                ->sortKeys()
                ->all();
            if ((float) $plan->price !== (float) $data['price'] || $currentProcedures !== $newProcedures) {
                throw ValidationException::withMessages([
                    'plan' => 'Este plano já possui assinantes. Para mudar preço ou benefícios, desative-o e crie uma nova versão.',
                ]);
            }
        }
        $plan->update($data);
        $plan->procedures()->sync($procedures);

        return back()->with('success', 'Plano mensal atualizado.');
    }

    private function validatedPlan(Request $request, Ad $ad): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'terms' => ['nullable', 'string', 'max:3000'],
            'active' => ['nullable', 'boolean'],
            'procedures' => ['required', 'array'],
            'procedures.*.enabled' => ['nullable', 'boolean'],
            'procedures.*.included_uses' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $allowedIds = $ad->serviceProcedures()->where('active', true)->pluck('id')->map(fn ($id) => (string) $id);
        $procedures = collect($data['procedures'])
            ->filter(fn ($item, $id) => ! empty($item['enabled']) && $allowedIds->contains((string) $id))
            ->mapWithKeys(fn ($item, $id) => [(int) $id => ['included_uses' => $item['included_uses'] ?? null]])
            ->all();

        if ($procedures === []) {
            throw ValidationException::withMessages(['procedures' => 'Selecione pelo menos um procedimento para o plano.']);
        }

        $active = $request->boolean('active');
        $setting = ServicePaymentSetting::where('user_id', $ad->user_id)->first();
        if ($active && ! $setting?->isReadyForSubscriptions()) {
            throw ValidationException::withMessages(['active' => 'Conecte o Asaas, registre o webhook e ative as assinaturas antes de publicar o plano.']);
        }

        return [[
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'cycle' => 'MONTHLY',
            'terms' => $data['terms'] ?? null,
            'active' => $active,
        ], $procedures];
    }

    private function authorizeManagement(Request $request, Ad $ad): void
    {
        abort_unless($request->user() && ($request->user()->role === 'admin' || $ad->user_id === $request->user()->id), 403);
        abort_unless(ServiceBookingCatalog::eligible($ad), 404);
    }
}
