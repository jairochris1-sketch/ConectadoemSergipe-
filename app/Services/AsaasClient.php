<?php

namespace App\Services;

use App\Exceptions\AsaasIntegrationException;
use App\Models\ServicePaymentSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AsaasClient
{
    public function verifyAccount(ServicePaymentSetting $setting): array
    {
        return $this->send($setting, 'get', '/myAccount/status/');
    }

    public function registerWebhook(ServicePaymentSetting $setting, string $url, string $email, string $authToken): array
    {
        $payload = [
            'name' => 'Conectado em Sergipe - pagamentos',
            'url' => $url,
            'email' => $email,
            'enabled' => true,
            'interrupted' => false,
            'apiVersion' => 3,
            'authToken' => $authToken,
            'sendType' => 'SEQUENTIALLY',
            'events' => [
                'PAYMENT_CREATED',
                'PAYMENT_UPDATED',
                'PAYMENT_CONFIRMED',
                'PAYMENT_RECEIVED',
                'PAYMENT_OVERDUE',
                'PAYMENT_DELETED',
                'PAYMENT_REFUNDED',
                'PAYMENT_RESTORED',
                'PAYMENT_CHARGEBACK_REQUESTED',
                'PAYMENT_CHARGEBACK_DISPUTE',
            ],
        ];

        return $setting->webhook_id
            ? $this->send($setting, 'put', '/webhooks/'.$setting->webhook_id, $payload)
            : $this->send($setting, 'post', '/webhooks', $payload);
    }

    public function createCustomer(ServicePaymentSetting $setting, array $customer): array
    {
        return $this->send($setting, 'post', '/customers', $customer);
    }

    public function createSubscription(ServicePaymentSetting $setting, array $subscription): array
    {
        return $this->send($setting, 'post', '/subscriptions', $subscription);
    }

    public function subscriptionPayments(ServicePaymentSetting $setting, string $subscriptionId): array
    {
        return $this->send($setting, 'get', '/subscriptions/'.$subscriptionId.'/payments', ['limit' => 10]);
    }

    public function cancelSubscription(ServicePaymentSetting $setting, string $subscriptionId): void
    {
        $this->send($setting, 'delete', '/subscriptions/'.$subscriptionId);
    }

    private function request(ServicePaymentSetting $setting): PendingRequest
    {
        if (! $setting->api_key) {
            throw new AsaasIntegrationException('A chave da API Asaas ainda não foi configurada.');
        }

        $baseUrl = $setting->environment === 'production'
            ? config('services.asaas.production_url')
            : config('services.asaas.sandbox_url');

        return Http::baseUrl(rtrim((string) $baseUrl, '/').'/v3')
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'access_token' => $setting->api_key,
                'User-Agent' => config('services.asaas.user_agent'),
            ])
            ->connectTimeout(5)
            ->timeout(15);
    }

    private function send(ServicePaymentSetting $setting, string $method, string $uri, array $data = []): array
    {
        try {
            $request = $this->request($setting);
            $response = match ($method) {
                'get' => $request->get($uri, $data),
                'post' => $request->post($uri, $data),
                'put' => $request->put($uri, $data),
                'delete' => $request->delete($uri, $data),
                default => throw new AsaasIntegrationException('Operação Asaas não suportada.'),
            };
        } catch (ConnectionException) {
            throw new AsaasIntegrationException('Não foi possível conectar ao Asaas agora. Tente novamente em alguns instantes.');
        }

        return $this->json($response);
    }

    private function json(Response $response): array
    {
        if ($response->successful()) {
            return $response->json() ?: [];
        }

        $message = collect($response->json('errors', []))
            ->pluck('description')
            ->filter()
            ->first();

        throw new AsaasIntegrationException(
            $message ?: 'O Asaas não conseguiu concluir a operação. Confira a configuração e tente novamente.'
        );
    }
}
