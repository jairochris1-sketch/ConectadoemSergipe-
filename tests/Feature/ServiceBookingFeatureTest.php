<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\ServiceAppointment;
use App\Models\User;
use App\Support\ServiceBookingCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceBookingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_is_limited_to_common_appointment_categories(): void
    {
        $owner = User::factory()->create();
        foreach (ServiceBookingCatalog::ELIGIBLE_CATEGORIES as $category) {
            $this->assertTrue(ServiceBookingCatalog::eligible($this->provider($owner, $category)));
        }
        $manicure = $this->provider($owner, 'Manicure e Pedicure');
        $electrician = $this->provider($owner, 'Eletricista');

        $this->assertFalse(ServiceBookingCatalog::eligible($electrician));

        $this->actingAs($owner)->get(route('service-booking.manage', $manicure))->assertOk();
        $this->actingAs($owner)->get(route('service-booking.manage', $electrician))->assertNotFound();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->get(route('service-booking.manage', $manicure))->assertForbidden();
    }

    public function test_owner_can_configure_procedures_staff_hours_and_enable_booking(): void
    {
        $owner = User::factory()->create();
        $ad = $this->provider($owner, 'Manicure e Pedicure');

        $this->actingAs($owner)->post(route('service-booking.procedures.store', $ad), [
            'name' => 'Manicure simples',
            'price' => 35,
            'duration_minutes' => 60,
        ])->assertRedirect();
        $procedure = $ad->serviceProcedures()->firstOrFail();

        $this->actingAs($owner)->post(route('service-booking.staff.store', $ad), [
            'name' => 'Ana Manicure',
        ])->assertRedirect();
        $staff = $ad->serviceStaff()->firstOrFail();

        $this->actingAs($owner)->put(route('service-booking.staff.update', [$ad, $staff]), [
            'name' => 'Ana Manicure',
            'procedure_ids' => [$procedure->id],
            'hours' => [1 => ['enabled' => 1, 'starts_at' => '09:00', 'ends_at' => '17:00']],
        ])->assertRedirect();

        $this->actingAs($owner)->patch(route('service-booking.toggle', $ad), ['booking_enabled' => 1])->assertRedirect();

        $this->assertDatabaseHas('service_procedures', ['ad_id' => $ad->id, 'name' => 'Manicure simples', 'duration_minutes' => 60]);
        $this->assertDatabaseHas('service_staff', ['ad_id' => $ad->id, 'name' => 'Ana Manicure']);
        $this->assertDatabaseHas('service_availabilities', ['service_staff_id' => $staff->id, 'day_of_week' => 1]);
        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'booking_enabled' => 1]);
    }

    public function test_customer_books_an_available_slot_and_duplicate_slot_is_blocked(): void
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create(['name' => 'Cliente Teste', 'phone' => '79999999999']);
        [$ad, $procedure, $staff, $date] = $this->configuredProvider($owner);
        $start = $date.' 09:40';

        $this->actingAs($customer)->get(route('service-booking.book', $ad).'?procedure='.$procedure->id.'&staff='.$staff->id.'&date='.$date)
            ->assertOk()
            ->assertSee('09:40')
            ->assertSee('Manicure simples')
            ->assertSee('Ana Manicure');

        $this->actingAs($customer)->post(route('service-booking.store', $ad), [
            'procedure_id' => $procedure->id,
            'staff_id' => $staff->id,
            'starts_at' => $start,
            'phone' => '79999999999',
        ])->assertRedirect();

        $this->assertDatabaseHas('service_appointments', [
            'ad_id' => $ad->id,
            'customer_name' => 'Cliente Teste',
            'service_price' => 35,
            'status' => 'pending',
        ]);

        $secondCustomer = User::factory()->create();
        $this->actingAs($secondCustomer)->post(route('service-booking.store', $ad), [
            'procedure_id' => $procedure->id,
            'staff_id' => $staff->id,
            'starts_at' => $start,
        ])->assertStatus(422);
    }

    public function test_completed_services_and_manual_costs_feed_the_financial_summary(): void
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create();
        [$ad, $procedure, $staff] = $this->configuredProvider($owner);
        $appointment = ServiceAppointment::create([
            'ad_id' => $ad->id,
            'service_procedure_id' => $procedure->id,
            'service_staff_id' => $staff->id,
            'customer_user_id' => $customer->id,
            'customer_name' => $customer->name,
            'starts_at' => now()->startOfMonth()->addDay()->setTime(10, 0),
            'ends_at' => now()->startOfMonth()->addDay()->setTime(11, 0),
            'service_price' => 35,
            'status' => 'completed',
        ]);

        $this->actingAs($owner)->post(route('service-booking.financial.store', $ad), [
            'type' => 'expense',
            'category' => 'Material',
            'description' => 'Esmaltes e algodão',
            'amount' => 10,
            'occurred_on' => now()->toDateString(),
        ])->assertRedirect();

        $this->actingAs($owner)->get(route('service-booking.manage', $ad))
            ->assertOk()
            ->assertSee('Cliente, procedimento, profissional, valor e data ficam registrados.')
            ->assertSee('R$ 35,00')
            ->assertSee('R$ 10,00')
            ->assertSee('R$ 25,00')
            ->assertSee('Desenvolvimento financeiro')
            ->assertSee('Vitrine usada para vender produtos')
            ->assertSee('Este resumo é gerencial e não substitui contabilidade');

        $this->assertDatabaseHas('service_financial_entries', ['ad_id' => $ad->id, 'type' => 'expense', 'amount' => 10]);
        $this->assertSame('Concluído', $appointment->status_label);
    }

    public function test_provider_profile_and_user_panel_show_the_booking_actions(): void
    {
        $owner = User::factory()->create();
        [$ad] = $this->configuredProvider($owner);

        $this->get(route('provider.show', $ad->slug))->assertOk()->assertSee('Agendar horário');
        $this->actingAs($owner)->get(route('user.panel'))->assertOk()->assertSee('Agenda e financeiro');
    }

    private function provider(User $owner, string $category): Ad
    {
        return Ad::create([
            'user_id' => $owner->id,
            'module' => 'services',
            'advertiser_type' => $category,
            'title' => $category.' da cidade',
            'slug' => str($category)->slug().'-'.uniqid(),
            'description' => 'Perfil para testar o agendamento de serviços.',
            'city' => 'Aracaju',
            'status' => 'active',
            'booking_enabled' => false,
        ]);
    }

    private function configuredProvider(User $owner): array
    {
        $ad = $this->provider($owner, 'Manicure e Pedicure');
        $ad->update(['booking_enabled' => true]);
        $procedure = $ad->serviceProcedures()->create(['name' => 'Manicure simples', 'price' => 35, 'duration_minutes' => 60, 'active' => true]);
        $staff = $ad->serviceStaff()->create(['name' => 'Ana Manicure', 'active' => true]);
        $staff->procedures()->attach($procedure);
        $date = now('America/Fortaleza')->addDays(2);
        $staff->availabilities()->create(['day_of_week' => $date->dayOfWeek, 'starts_at' => '09:00', 'ends_at' => '18:00']);

        return [$ad, $procedure, $staff, $date->toDateString()];
    }
}
