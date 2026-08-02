<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StoreBusinessHour;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreBusinessHoursFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_weekly_hours_and_they_appear_on_storefront(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $hours = $this->weeklyPayload([
            1 => ['is_closed' => 0, 'opens_at' => '08:00', 'closes_at' => '18:00'],
            2 => ['is_closed' => 0, 'is_24_hours' => 1],
        ]);

        $this->actingAs($owner)
            ->put(route('store.business_hours.update', $store), ['hours' => $hours])
            ->assertSessionHas('store_success');

        $this->assertDatabaseCount('store_business_hours', 7);
        $this->assertDatabaseHas('store_business_hours', [
            'store_id' => $store->id,
            'day_of_week' => 1,
            'opens_at' => '08:00',
            'closes_at' => '18:00',
            'is_closed' => false,
        ]);
        $this->assertDatabaseHas('store_business_hours', [
            'store_id' => $store->id,
            'day_of_week' => 2,
            'is_24_hours' => true,
            'opens_at' => null,
            'closes_at' => null,
        ]);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Horário da loja')
            ->assertSee('Segunda-feira')
            ->assertSee('08:00 às 18:00')
            ->assertSee('Aberto 24 horas');
    }

    public function test_management_page_displays_all_weekdays(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->get(route('store.manage', $store))
            ->assertOk()
            ->assertSee('Horário de funcionamento')
            ->assertSeeInOrder([
                'Segunda-feira',
                'Terça-feira',
                'Quarta-feira',
                'Quinta-feira',
                'Sexta-feira',
                'Sábado',
                'Domingo',
            ]);
    }

    public function test_another_user_cannot_update_store_hours(): void
    {
        $store = $this->createStore(User::factory()->create());
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->put(route('store.business_hours.update', $store), [
                'hours' => $this->weeklyPayload(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('store_business_hours', 0);
    }

    public function test_equal_opening_and_closing_times_are_rejected(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $hours = $this->weeklyPayload([
            1 => ['is_closed' => 0, 'opens_at' => '08:00', 'closes_at' => '08:00'],
        ]);

        $this->actingAs($owner)
            ->put(route('store.business_hours.update', $store), ['hours' => $hours])
            ->assertSessionHasErrors('hours.0.closes_at');

        $this->assertDatabaseCount('store_business_hours', 0);
    }

    public function test_daytime_schedule_reports_open_and_closed_status(): void
    {
        $store = $this->createStore(User::factory()->create());
        $this->createHour($store, 1, '08:00', '18:00');

        $openStatus = $store->businessStatus(
            CarbonImmutable::create(2026, 7, 27, 10, 0, 0, 'America/Fortaleza')
        );
        $closedStatus = $store->businessStatus(
            CarbonImmutable::create(2026, 7, 27, 20, 0, 0, 'America/Fortaleza')
        );

        $this->assertTrue($openStatus['is_open']);
        $this->assertSame('Aberto agora', $openStatus['label']);
        $this->assertSame('Fecha às 18:00', $openStatus['detail']);
        $this->assertFalse($closedStatus['is_open']);
        $this->assertSame('Fechado agora', $closedStatus['label']);
    }

    public function test_overnight_schedule_remains_open_after_midnight(): void
    {
        $store = $this->createStore(User::factory()->create());
        $this->createHour($store, 1, '18:00', '02:00');

        $mondayNight = $store->businessStatus(
            CarbonImmutable::create(2026, 7, 27, 23, 0, 0, 'America/Fortaleza')
        );
        $tuesdayMorning = $store->businessStatus(
            CarbonImmutable::create(2026, 7, 28, 1, 0, 0, 'America/Fortaleza')
        );
        $tuesdayAfterClosing = $store->businessStatus(
            CarbonImmutable::create(2026, 7, 28, 3, 0, 0, 'America/Fortaleza')
        );

        $this->assertTrue($mondayNight['is_open']);
        $this->assertSame('Fecha amanhã às 02:00', $mondayNight['detail']);
        $this->assertTrue($tuesdayMorning['is_open']);
        $this->assertSame('Fecha às 02:00', $tuesdayMorning['detail']);
        $this->assertFalse($tuesdayAfterClosing['is_open']);
    }

    public function test_store_without_hours_has_unknown_status(): void
    {
        $store = $this->createStore(User::factory()->create());

        $status = $store->businessStatus(
            CarbonImmutable::create(2026, 7, 27, 10, 0, 0, 'America/Fortaleza')
        );

        $this->assertNull($status['is_open']);
        $this->assertSame('unknown', $status['state']);
        $this->assertSame('Horário não informado', $status['label']);
    }

    private function weeklyPayload(array $overrides = []): array
    {
        $hours = [];

        foreach (array_keys(StoreBusinessHour::WEEKDAYS) as $day) {
            $hours[] = array_merge([
                'day_of_week' => $day,
                'opens_at' => '08:00',
                'closes_at' => '18:00',
                'is_closed' => 1,
                'is_24_hours' => 0,
            ], $overrides[$day] ?? []);
        }

        return $hours;
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja com Horários',
            'slug' => 'loja-horarios-' . fake()->unique()->numerify('####'),
            'description' => 'Loja criada para testar horários.',
            'category' => 'Artigos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createHour(Store $store, int $day, string $opensAt, string $closesAt): StoreBusinessHour
    {
        return $store->businessHours()->create([
            'day_of_week' => $day,
            'opens_at' => $opensAt,
            'closes_at' => $closesAt,
            'is_closed' => false,
            'is_24_hours' => false,
        ]);
    }
}
