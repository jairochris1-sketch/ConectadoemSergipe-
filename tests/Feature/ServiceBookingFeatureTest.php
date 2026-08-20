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
            'description' => 'Limpeza, lixamento e pintura tradicional, sem decoração.',
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

        $this->assertDatabaseHas('service_procedures', ['ad_id' => $ad->id, 'name' => 'Manicure simples', 'description' => 'Limpeza, lixamento e pintura tradicional, sem decoração.', 'duration_minutes' => 60]);
        $this->assertDatabaseHas('service_staff', ['ad_id' => $ad->id, 'name' => 'Ana Manicure']);
        $this->assertDatabaseHas('service_availabilities', ['service_staff_id' => $staff->id, 'day_of_week' => 1]);
        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'booking_enabled' => 1]);

        $this->actingAs($owner)->put(route('service-booking.procedures.update', [$ad, $procedure]), [
            'name' => 'Mão tradicional',
            'description' => 'Limpeza, lixamento e pintura tradicional, sem decoração ou alongamento.',
            'price' => 40,
            'duration_minutes' => 75,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_procedures', [
            'id' => $procedure->id,
            'name' => 'Mão tradicional',
            'description' => 'Limpeza, lixamento e pintura tradicional, sem decoração ou alongamento.',
            'price' => 40,
            'duration_minutes' => 75,
        ]);
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
            ->assertSee('Limpeza, lixamento e pintura tradicional, sem decoração.')
            ->assertSee('Ana Manicure');

        $response = $this->actingAs($customer)->post(route('service-booking.store', $ad), [
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
        $appointment = $ad->serviceAppointments()->firstOrFail();
        $response->assertRedirect(route('service-booking.whatsapp', [$ad, $appointment, 'requested']));
        $this->actingAs($customer)->get(route('service-booking.whatsapp', [$ad, $appointment, 'requested']))
            ->assertOk()
            ->assertSee('Seu horário está quase confirmado')
            ->assertSee('Enviar agendamento pelo WhatsApp')
            ->assertSee('data-whatsapp-seconds', false)
            ->assertSee('https://wa.me/557999999999', false);

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

    public function test_owner_can_block_hours_and_register_an_unregistered_customer_manually(): void
    {
        $owner = User::factory()->create();
        [$ad, $procedure, $staff, $date] = $this->configuredProvider($owner);

        $this->actingAs($owner)->post(route('service-booking.blocks.store', $ad), [
            'service_staff_id' => $staff->id,
            'starts_at' => $date.' 09:30',
            'ends_at' => $date.' 11:00',
            'reason' => 'Almoço antecipado',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs(User::factory()->create())
            ->get(route('service-booking.book', $ad).'?procedure='.$procedure->id.'&staff='.$staff->id.'&date='.$date)
            ->assertOk()
            ->assertDontSee('value="'.$date.' 09:40"', false)
            ->assertSee('value="'.$date.' 11:10"', false);

        $this->actingAs($owner)->post(route('service-booking.appointments.manual', $ad), [
            'procedure_id' => $procedure->id,
            'staff_id' => $staff->id,
            'starts_at' => $date.' 11:10',
            'customer_name' => 'Cliente do WhatsApp',
            'customer_phone' => '(79) 99999-0000',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_schedule_blocks', ['ad_id' => $ad->id, 'reason' => 'Almoço antecipado']);
        $this->assertDatabaseHas('service_appointments', [
            'ad_id' => $ad->id,
            'customer_user_id' => null,
            'customer_name' => 'Cliente do WhatsApp',
            'customer_phone' => '79999990000',
            'status' => 'confirmed',
        ]);
    }

    public function test_customer_can_reschedule_and_cancel_only_their_own_future_appointment(): void
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create(['name' => 'Cliente da agenda']);
        $otherCustomer = User::factory()->create();
        [$ad, $procedure, $staff, $date] = $this->configuredProvider($owner);

        $this->actingAs($customer)->post(route('service-booking.store', $ad), [
            'procedure_id' => $procedure->id,
            'staff_id' => $staff->id,
            'starts_at' => $date.' 09:40',
        ])->assertRedirect();
        $appointment = $ad->serviceAppointments()->firstOrFail();

        $this->actingAs($otherCustomer)
            ->patch(route('service-booking.customer.cancel', [$ad, $appointment]))
            ->assertForbidden();

        $this->actingAs($customer)->get(route('service-booking.book', $ad))
            ->assertOk()
            ->assertSee('Seus próximos horários')
            ->assertSee('Remarcar')
            ->assertSee('Cancelar horário');

        $this->actingAs($customer)->patch(route('service-booking.customer.reschedule', [$ad, $appointment]), [
            'starts_at' => $date.' 10:50',
        ])->assertRedirect(route('service-booking.whatsapp', [$ad, $appointment, 'rescheduled']))->assertSessionHasNoErrors();
        $this->assertSame('10:50', $appointment->fresh()->starts_at->format('H:i'));
        $this->assertSame('pending', $appointment->fresh()->status);

        $this->actingAs($customer)
            ->patch(route('service-booking.customer.cancel', [$ad, $appointment]))
            ->assertRedirect(route('service-booking.whatsapp', [$ad, $appointment, 'cancelled']))
            ->assertSessionHasNoErrors();
        $this->assertSame('cancelled', $appointment->fresh()->status);
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
        $ad->update(['booking_enabled' => true, 'contact_whatsapp' => '7999999999']);
        $procedure = $ad->serviceProcedures()->create(['name' => 'Manicure simples', 'description' => 'Limpeza, lixamento e pintura tradicional, sem decoração.', 'price' => 35, 'duration_minutes' => 60, 'active' => true]);
        $staff = $ad->serviceStaff()->create(['name' => 'Ana Manicure', 'active' => true]);
        $staff->procedures()->attach($procedure);
        $date = now('America/Fortaleza')->addDays(2);
        $staff->availabilities()->create(['day_of_week' => $date->dayOfWeek, 'starts_at' => '09:00', 'ends_at' => '18:00']);

        return [$ad, $procedure, $staff, $date->toDateString()];
    }
}
