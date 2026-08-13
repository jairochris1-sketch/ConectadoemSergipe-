<?php

namespace App\Http\Controllers;

use App\Exceptions\AsaasIntegrationException;
use App\Models\Ad;
use App\Models\ServiceClientSubscription;
use App\Models\ServicePaymentSetting;
use App\Models\ServiceSubscriptionPayment;
use App\Models\ServiceSubscriptionPlan;
use App\Services\AsaasClient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceSubscriptionController extends Controller
{
    public function store(Request $request, Ad $ad, ServiceSubscriptionPlan $plan, AsaasClient $asaas)
    {
        abort_unless($plan->ad_id === $ad->id && $plan->active && $ad->status === 'active', 404);
        $setting = ServicePaymentSetting::where('user_id', $ad->user_id)->first();
        abort_unless($setting?->isReadyForSubscriptions(), 404);

        $data = $request->validate([
            'billing_type' => ['required', Rule::in(['PIX', 'BOLETO', 'CREDIT_CARD'])],
            'cpf_cnpj' => ['required', 'string', 'max:20'],
            'accept_terms' => ['accepted'],
        ]);
        $document = preg_replace('/\D+/', '', $data['cpf_cnpj']);
        if (! in_array(strlen($document), [11, 14], true)) {
            throw ValidationException::withMessages(['cpf_cnpj' => 'Informe um CPF ou CNPJ válido para a cobrança.']);
        }

        $existing = ServiceClientSubscription::query()
            ->where('service_subscription_plan_id', $plan->id)
            ->where('customer_user_id', $request->user()->id)
            ->whereIn('status', ['creating', 'pending_payment', 'active'])
            ->latest('id')
            ->first();
        if ($existing) {
            if ($existing->status === 'active') {
                return back()->with('success', 'Você já possui este plano ativo.');
            }
            if ($invoiceUrl = $this->safeInvoiceUrl($existing->latestInvoiceUrl())) {
                return redirect()->away($invoiceUrl);
            }
            if ($existing->asaas_subscription_id) {
                try {
                    $payments = $asaas->subscriptionPayments($setting, $existing->asaas_subscription_id);
                    $invoiceUrl = $this->recordInitialPayment($existing, $payments['data'][0] ?? null);
                    if ($safeUrl = $this->safeInvoiceUrl($invoiceUrl)) {
                        return redirect()->away($safeUrl);
                    }
                } catch (AsaasIntegrationException) {
                    // A assinatura remota já existe; o webhook ainda poderá sincronizar a cobrança.
                }
            }

            return back()->with('success', 'Sua assinatura está aguardando a geração ou confirmação da cobrança.');
        }

        $subscription = ServiceClientSubscription::create([
            'service_subscription_plan_id' => $plan->id,
            'ad_id' => $ad->id,
            'payment_setting_id' => $setting->id,
            'customer_user_id' => $request->user()->id,
            'status' => 'creating',
            'billing_type' => $data['billing_type'],
            'terms_snapshot' => $plan->terms ?: 'Renovação mensal. O cancelamento impede novas cobranças e mantém benefícios já pagos até o fim do ciclo.',
            'consented_at' => now(),
        ]);

        try {
            $customerId = $this->customerId($setting, $request, $document, $asaas);
            $subscription->update(['asaas_customer_id' => $customerId]);
            $remote = $asaas->createSubscription($setting, [
                'customer' => $customerId,
                'billingType' => $data['billing_type'],
                'value' => (float) $plan->price,
                'nextDueDate' => now('America/Fortaleza')->toDateString(),
                'cycle' => 'MONTHLY',
                'description' => $plan->name.' - '.$ad->title,
                'externalReference' => 'service-subscription:'.$subscription->public_id,
            ]);
            $remoteId = (string) ($remote['id'] ?? '');
            if ($remoteId === '') {
                throw new AsaasIntegrationException('O Asaas não retornou a identificação da assinatura.');
            }

            $subscription->update([
                'asaas_subscription_id' => $remoteId,
                'status' => 'pending_payment',
            ]);
        } catch (AsaasIntegrationException $exception) {
            $subscription->update(['status' => 'failed']);

            return back()->withErrors(['asaas' => $exception->getMessage()]);
        }

        try {
            $payments = $asaas->subscriptionPayments($setting, $remoteId);
            $invoiceUrl = $this->recordInitialPayment($subscription, $payments['data'][0] ?? null);
        } catch (AsaasIntegrationException) {
            $invoiceUrl = null;
        }

        if ($safeUrl = $this->safeInvoiceUrl($invoiceUrl)) {
            return redirect()->away($safeUrl);
        }

        return back()->with('success', 'Assinatura criada. A cobrança será disponibilizada assim que o Asaas concluir o processamento.');
    }

    public function cancel(Request $request, ServiceClientSubscription $subscription, AsaasClient $asaas)
    {
        abort_unless($subscription->customer_user_id === $request->user()->id, 403);
        if ($subscription->status === 'cancelled') {
            return back()->with('success', 'Esta assinatura já está cancelada.');
        }

        if ($subscription->asaas_subscription_id) {
            try {
                $asaas->cancelSubscription($subscription->paymentSetting, $subscription->asaas_subscription_id);
            } catch (AsaasIntegrationException $exception) {
                return back()->withErrors(['asaas' => $exception->getMessage()]);
            }
        }

        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return back()->with('success', 'Assinatura cancelada. Nenhuma nova cobrança será gerada.');
    }

    private function customerId(ServicePaymentSetting $setting, Request $request, string $document, AsaasClient $asaas): string
    {
        $known = ServiceClientSubscription::query()
            ->where('payment_setting_id', $setting->id)
            ->where('customer_user_id', $request->user()->id)
            ->whereNotNull('asaas_customer_id')
            ->latest('id')
            ->value('asaas_customer_id');
        if ($known) {
            return $known;
        }

        $remote = $asaas->createCustomer($setting, [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'mobilePhone' => preg_replace('/\D+/', '', (string) $request->user()->phone),
            'cpfCnpj' => $document,
            'externalReference' => 'service-customer:'.$request->user()->id,
        ]);
        $id = (string) ($remote['id'] ?? '');
        if ($id === '') {
            throw new AsaasIntegrationException('O Asaas não retornou a identificação do cliente.');
        }

        return $id;
    }

    private function recordInitialPayment(ServiceClientSubscription $subscription, ?array $payment): ?string
    {
        if (! $payment || empty($payment['id'])) {
            return null;
        }

        ServiceSubscriptionPayment::updateOrCreate(
            [
                'payment_setting_id' => $subscription->payment_setting_id,
                'asaas_payment_id' => $payment['id'],
            ],
            [
                'service_client_subscription_id' => $subscription->id,
                'status' => strtolower((string) ($payment['status'] ?? 'pending')),
                'billing_type' => $payment['billingType'] ?? $subscription->billing_type,
                'value' => $payment['value'] ?? $subscription->plan->price,
                'net_value' => $payment['netValue'] ?? null,
                'due_date' => $payment['dueDate'] ?? null,
                'invoice_url' => $payment['invoiceUrl'] ?? null,
            ]
        );

        return $payment['invoiceUrl'] ?? null;
    }

    private function safeInvoiceUrl(?string $url): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL) || strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host !== 'asaas.com' && ! str_ends_with($host, '.asaas.com')) {
            return null;
        }

        return $url;
    }
}
