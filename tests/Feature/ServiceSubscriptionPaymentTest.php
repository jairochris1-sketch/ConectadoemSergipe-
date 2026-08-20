<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\ServicePaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ServiceSubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_connects_asaas_and_plans_stay_hidden_until_every_switch_is_enabled(): void
    {
        $owner = User::factory()->create(['email' => 'profissional@example.com']);
        [$ad, $procedure] = $this->provider($owner);
        $key = '$aact_hmlg_'.str_repeat('a', 40);

        $this->actingAs($owner)->put(route('service-payments.settings.update', $ad), [
            'environment' => 'sandbox',
            'api_key' => $key,
            'online_payments_enabled' => 1,
            'subscriptions_enabled' => 1,
        ])->assertRedirect();

        $setting = ServicePaymentSetting::where('user_id', $owner->id)->firstOrFail();
        $this->assertSame($key, $setting->api_key);
        $this->assertNotSame($key, DB::table('service_payment_settings')->where('id', $setting->id)->value('api_key'));
        $this->assertFalse($setting->online_payments_enabled);
        $this->assertFalse($setting->subscriptions_enabled);

        Http::fake([
            'https://api-sandbox.asaas.com/v3/myAccount/status/' => Http::response(['general' => 'APPROVED']),
            'https://api-sandbox.asaas.com/v3/webhooks' => Http::response(['id' => 'wbh_123']),
        ]);
        config([
            'app.url' => 'https://agenda.example.com',
            'services.asaas.webhook_base_url' => 'https://agenda.example.com',
        ]);
        URL::forceRootUrl('https://agenda.example.com');
        URL::forceScheme('https');

        $this->actingAs($owner)->post(route('service-payments.settings.verify', $ad))->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($owner)
            ->post(route('service-payments.settings.webhook', $ad))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($owner)->put(route('service-payments.settings.update', $ad), [
            'environment' => 'sandbox',
            'online_payments_enabled' => 1,
            'subscriptions_enabled' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($owner)->post(route('service-subscription-plans.store', $ad), [
            'name' => '3 unhas por mês',
            'price' => 90,
            'description' => 'Três sessões mensais.',
            'active' => 1,
            'procedures' => [
                $procedure->id => ['enabled' => 1, 'included_uses' => 3],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertTrue($setting->fresh()->isReadyForSubscriptions());
        $this->assertDatabaseHas('service_subscription_plans', ['ad_id' => $ad->id, 'name' => '3 unhas por mês', 'active' => 1]);
        auth()->logout();

        $this->get(route('service-booking.book', $ad))
            ->assertOk()
            ->assertSee('3 unhas por mês')
            ->assertSee('R$ 90,00')
            ->assertSee('Entrar para assinar');
    }

    public function test_customer_subscription_is_only_activated_by_authenticated_idempotent_webhook(): void
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create([
            'name' => 'Cliente Teste',
            'email' => 'cliente@example.com',
            'phone' => '79999999999',
        ]);
        [$ad, $procedure] = $this->provider($owner);
        $setting = ServicePaymentSetting::create([
            'user_id' => $owner->id,
            'environment' => 'sandbox',
            'api_key' => '$aact_hmlg_'.str_repeat('b', 40),
            'verified_at' => now(),
            'account_status' => 'APPROVED',
            'webhook_id' => 'wbh_123',
            'webhook_token' => str_repeat('t', 64),
            'webhook_registered_at' => now(),
            'online_payments_enabled' => true,
            'subscriptions_enabled' => true,
        ]);
        $plan = $ad->serviceSubscriptionPlans()->create([
            'name' => 'Clube da manicure',
            'price' => 75,
            'cycle' => 'MONTHLY',
            'active' => true,
        ]);
        $plan->procedures()->attach($procedure, ['included_uses' => 3]);

        Http::fake([
            'https://api-sandbox.asaas.com/v3/customers' => Http::response(['id' => 'cus_123']),
            'https://api-sandbox.asaas.com/v3/subscriptions' => Http::response(['id' => 'sub_123']),
            'https://api-sandbox.asaas.com/v3/subscriptions/sub_123' => Http::response([]),
            'https://api-sandbox.asaas.com/v3/subscriptions/sub_123/payments*' => Http::response([
                'data' => [[
                    'id' => 'pay_123',
                    'status' => 'PENDING',
                    'billingType' => 'PIX',
                    'value' => 75,
                    'dueDate' => now()->toDateString(),
                    'invoiceUrl' => 'https://sandbox.asaas.com/i/pay_123',
                ]],
            ]),
        ]);

        $this->actingAs($customer)->post(route('service-subscriptions.store', [$ad, $plan]), [
            'billing_type' => 'PIX',
            'cpf_cnpj' => '123.456.789-01',
            'accept_terms' => 1,
        ])->assertRedirect('https://sandbox.asaas.com/i/pay_123');

        $subscription = $plan->subscriptions()->firstOrFail();
        $this->assertSame('pending_payment', $subscription->status);
        $this->assertNotNull($subscription->consented_at);

        $payload = [
            'id' => 'evt_123',
            'event' => 'PAYMENT_RECEIVED',
            'payment' => [
                'id' => 'pay_123',
                'subscription' => 'sub_123',
                'status' => 'RECEIVED',
                'billingType' => 'PIX',
                'value' => 75,
                'netValue' => 73.50,
                'dueDate' => now()->toDateString(),
                'paymentDate' => now()->toDateString(),
                'invoiceUrl' => 'https://sandbox.asaas.com/i/pay_123',
            ],
        ];

        $this->postJson(route('service-payments.asaas-webhook', $setting), $payload)
            ->assertUnauthorized();
        $this->withHeader('asaas-access-token', str_repeat('t', 64))
            ->postJson(route('service-payments.asaas-webhook', $setting), $payload)
            ->assertOk();
        $this->withHeader('asaas-access-token', str_repeat('t', 64))
            ->postJson(route('service-payments.asaas-webhook', $setting), $payload)
            ->assertOk();

        $this->assertSame('active', $subscription->fresh()->status);
        $this->assertDatabaseCount('service_payment_webhook_events', 1);
        $this->assertDatabaseCount('service_subscription_payments', 1);
        $this->assertDatabaseHas('service_subscription_payments', [
            'asaas_payment_id' => 'pay_123',
            'status' => 'received',
        ]);
        $this->actingAs($owner)->get(route('service-booking.manage', $ad))
            ->assertOk()
            ->assertSee('Assinantes e pagamentos')
            ->assertSee('Cliente Teste')
            ->assertSee('Recebido')
            ->assertSee('R$ 75,00');

        $staff = $ad->serviceStaff()->create(['name' => 'Ana Manicure', 'active' => true]);
        $staff->procedures()->attach($procedure);
        $date = now('America/Fortaleza')->addDays(2);
        $staff->availabilities()->create([
            'day_of_week' => $date->dayOfWeek,
            'starts_at' => '09:00',
            'ends_at' => '18:00',
        ]);

        $this->actingAs($customer)->post(route('service-booking.store', $ad), [
            'procedure_id' => $procedure->id,
            'staff_id' => $staff->id,
            'starts_at' => $date->toDateString().' 09:40',
        ])->assertRedirect();

        $appointment = $ad->serviceAppointments()->firstOrFail();
        $this->assertSame('0.00', $appointment->service_price);
        $this->assertSame($subscription->id, $appointment->service_client_subscription_id);
        $this->assertDatabaseHas('service_subscription_usages', [
            'service_appointment_id' => $appointment->id,
            'status' => 'reserved',
        ]);

        $this->actingAs($owner)->patch(route('service-booking.appointments.update', [$ad, $appointment]), [
            'status' => 'cancelled',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_subscription_usages', [
            'service_appointment_id' => $appointment->id,
            'status' => 'released',
        ]);

        $this->actingAs($customer)->delete(route('service-subscriptions.cancel', $subscription))->assertRedirect();
        $this->assertSame('cancelled', $subscription->fresh()->status);

        $this->actingAs($customer)->post(route('service-booking.store', $ad), [
            'procedure_id' => $procedure->id,
            'staff_id' => $staff->id,
            'starts_at' => $date->toDateString().' 10:50',
        ])->assertRedirect();
        $this->assertSame('0.00', $ad->serviceAppointments()->latest('id')->firstOrFail()->service_price);
    }

    private function provider(User $owner): array
    {
        $ad = Ad::create([
            'user_id' => $owner->id,
            'module' => 'services',
            'advertiser_type' => 'Manicure e Pedicure',
            'title' => 'Espaço da manicure',
            'slug' => 'espaco-manicure-'.uniqid(),
            'description' => 'Perfil para testar planos e pagamentos.',
            'city' => 'Aracaju',
            'status' => 'active',
            'booking_enabled' => true,
        ]);
        $procedure = $ad->serviceProcedures()->create([
            'name' => 'Manicure simples',
            'price' => 35,
            'duration_minutes' => 60,
            'active' => true,
        ]);

        return [$ad, $procedure];
    }
}
