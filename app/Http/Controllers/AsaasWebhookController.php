<?php

namespace App\Http\Controllers;

use App\Models\ServiceClientSubscription;
use App\Models\ServicePaymentSetting;
use App\Models\ServicePaymentWebhookEvent;
use App\Models\ServiceSubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, ServicePaymentSetting $paymentSetting): JsonResponse
    {
        $receivedToken = (string) $request->header('asaas-access-token');
        if (! $paymentSetting->webhook_token || ! hash_equals($paymentSetting->webhook_token, $receivedToken)) {
            return response()->json(['message' => 'Webhook não autorizado.'], 401);
        }

        $eventId = (string) $request->input('id');
        $eventType = strtoupper((string) $request->input('event'));
        if ($eventId === '' || $eventType === '') {
            return response()->json(['message' => 'Evento inválido.'], 422);
        }

        DB::transaction(function () use ($request, $paymentSetting, $eventId, $eventType): void {
            $event = ServicePaymentWebhookEvent::query()->firstOrCreate(
                ['payment_setting_id' => $paymentSetting->id, 'event_id' => $eventId],
                [
                    'event_type' => $eventType,
                    'resource_id' => $request->input('payment.id'),
                ]
            );
            $event->refresh();
            $event = ServicePaymentWebhookEvent::query()->lockForUpdate()->findOrFail($event->id);
            if ($event->processed_at) {
                return;
            }

            $this->processPayment($paymentSetting, $eventType, (array) $request->input('payment', []));
            $event->update(['processed_at' => now()]);
        }, 3);

        return response()->json(['received' => true]);
    }

    private function processPayment(ServicePaymentSetting $setting, string $eventType, array $payment): void
    {
        $remotePaymentId = (string) ($payment['id'] ?? '');
        if ($remotePaymentId === '') {
            return;
        }

        $remoteSubscription = $payment['subscription'] ?? null;
        $remoteSubscriptionId = is_array($remoteSubscription)
            ? (string) ($remoteSubscription['id'] ?? '')
            : (string) $remoteSubscription;

        $subscription = $remoteSubscriptionId !== ''
            ? ServiceClientSubscription::query()
                ->where('payment_setting_id', $setting->id)
                ->where('asaas_subscription_id', $remoteSubscriptionId)
                ->first()
            : null;

        if (! $subscription) {
            $reference = (string) ($payment['externalReference'] ?? '');
            if (str_starts_with($reference, 'service-subscription:')) {
                $subscription = ServiceClientSubscription::query()
                    ->where('payment_setting_id', $setting->id)
                    ->where('public_id', substr($reference, strlen('service-subscription:')))
                    ->first();
            }
        }

        if (! $subscription) {
            return;
        }

        $status = $this->paymentStatus($eventType, (string) ($payment['status'] ?? ''));
        $paidAt = in_array($status, ['confirmed', 'received'], true)
            ? ($payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? now())
            : null;

        ServiceSubscriptionPayment::updateOrCreate(
            ['payment_setting_id' => $setting->id, 'asaas_payment_id' => $remotePaymentId],
            [
                'service_client_subscription_id' => $subscription->id,
                'status' => $status,
                'billing_type' => $payment['billingType'] ?? $subscription->billing_type,
                'value' => $payment['value'] ?? $subscription->plan->price,
                'net_value' => $payment['netValue'] ?? null,
                'due_date' => $payment['dueDate'] ?? null,
                'paid_at' => $paidAt,
                'invoice_url' => $payment['invoiceUrl'] ?? null,
            ]
        );

        if (in_array($status, ['confirmed', 'received'], true)) {
            $start = Carbon::parse($payment['dueDate'] ?? now(), 'America/Fortaleza')->startOfDay();
            $end = $start->copy()->addMonthNoOverflow()->subDay();
            $subscription->update([
                'status' => $subscription->status === 'cancelled' ? 'cancelled' : 'active',
                'current_period_start' => $start->toDateString(),
                'current_period_end' => $end->toDateString(),
                'paid_through' => $end->toDateString(),
            ]);
        } elseif ($subscription->status === 'cancelled') {
            return;
        } elseif ($status === 'overdue' && (! $subscription->paid_through || $subscription->paid_through->lt(today('America/Fortaleza')))) {
            $subscription->update(['status' => 'past_due']);
        } elseif (in_array($status, ['refunded', 'chargeback', 'deleted'], true)) {
            $subscription->update(['status' => 'past_due']);
        }
    }

    private function paymentStatus(string $eventType, string $remoteStatus): string
    {
        return match ($eventType) {
            'PAYMENT_RECEIVED' => 'received',
            'PAYMENT_CONFIRMED' => 'confirmed',
            'PAYMENT_OVERDUE' => 'overdue',
            'PAYMENT_REFUNDED' => 'refunded',
            'PAYMENT_DELETED' => 'deleted',
            'PAYMENT_CHARGEBACK_REQUESTED', 'PAYMENT_CHARGEBACK_DISPUTE' => 'chargeback',
            default => strtolower($remoteStatus ?: 'pending'),
        };
    }
}
