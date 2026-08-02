<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_store_displays_store_specific_report_form(): void
    {
        $store = $this->createStore(User::factory()->create());

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Reportar esta loja')
            ->assertSee('Loja não existe ou encerrou as atividades')
            ->assertSee('Informações falsas sobre a loja')
            ->assertDontSee('Já foi vendido')
            ->assertSee('class="modal-dialog modal-dialog-scrollable modal-lg"', false);
    }

    public function test_guest_can_submit_store_report_and_return_to_store(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $response = $this->post(route('store.reports.store', $store), [
            'reason' => 'wrong_location',
            'severity' => 'misleading',
            'details' => 'A loja informa uma cidade diferente nos dados de contato.',
            'wants_notification' => 0,
            'truth_confirmation' => 1,
        ]);

        $report = Report::firstOrFail();

        $response->assertRedirect(route('reports.thank_you', $report->public_id));
        $this->assertSame('store', $report->subject_type);
        $this->assertSame($store->id, $report->store_id);
        $this->assertSame($owner->id, $report->advertiser_id);
        $this->assertNull($report->ad_id);
        $this->assertNull($report->reporter_user_id);

        $this->get(route('reports.thank_you', $report->public_id))
            ->assertOk()
            ->assertSee('Voltar à loja')
            ->assertSee(route('store.show', $store->slug), false);
    }

    public function test_store_rejects_ad_only_reason(): void
    {
        $store = $this->createStore(User::factory()->create());

        $this->post(route('store.reports.store', $store), [
            'reason' => 'sold',
            'severity' => 'error',
            'truth_confirmation' => 1,
        ])->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_owner_cannot_report_own_store(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->post(route('store.reports.store', $store), [
                'reason' => 'spam',
                'severity' => 'error',
                'truth_confirmation' => 1,
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_registered_user_cannot_repeat_open_store_report_within_twenty_four_hours(): void
    {
        $reporter = User::factory()->create();
        $store = $this->createStore(User::factory()->create());
        $payload = [
            'reason' => 'spam',
            'severity' => 'error',
            'truth_confirmation' => 1,
        ];

        $this->actingAs($reporter)->post(route('store.reports.store', $store), $payload);
        $this->actingAs($reporter)
            ->post(route('store.reports.store', $store), $payload)
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('reports', 1);
    }

    public function test_admin_can_hide_reported_store_and_notify_reporter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $reporter = User::factory()->create();
        $store = $this->createStore($owner);
        $report = $this->createReport($store, $reporter);

        $this->actingAs($admin)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee($report->reference)
            ->assertSee('Loja');

        $this->actingAs($admin)
            ->get(route('admin.reports.show', $report))
            ->assertOk()
            ->assertSee('Bloquear loja')
            ->assertDontSee('Corrigir categoria');

        $this->actingAs($admin)
            ->post(route('admin.reports.action', $report), [
                'action' => 'hide',
                'resolution_note' => 'Loja ocultada durante a análise.',
            ])
            ->assertRedirect(route('admin.reports.show', $report));

        $this->assertFalse($store->fresh()->active);
        $this->assertSame('resolved', $report->fresh()->status);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $reporter->id,
            'report_id' => $report->id,
            'kind' => 'report_result',
        ]);
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Sergipana de Teste',
            'slug' => 'loja-sergipana-' . fake()->unique()->numerify('####'),
            'description' => 'Loja criada para validar o fluxo de denúncias.',
            'category' => 'Artigos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createReport(Store $store, User $reporter): Report
    {
        return Report::create([
            'public_id' => fake()->uuid(),
            'store_id' => $store->id,
            'advertiser_id' => $store->user_id,
            'reporter_user_id' => $reporter->id,
            'subject_type' => 'store',
            'ad_title_snapshot' => $store->name,
            'ad_module_snapshot' => 'stores',
            'reason' => 'scam',
            'severity' => 'critical',
            'details' => 'Possível tentativa de golpe associada à loja.',
            'wants_notification' => true,
            'status' => 'open',
        ]);
    }
}
