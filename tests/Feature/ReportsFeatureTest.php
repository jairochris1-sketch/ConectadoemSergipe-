<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Report;
use App\Models\ReportNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_ad_and_service_use_their_own_report_fields(): void
    {
        $user = User::factory()->create(['phone' => '79999999999']);
        $ad = $this->createAd($user, 'vehicles', 'veiculo-denuncia');
        $service = $this->createAd($user, 'services', 'servico-denuncia');

        $this->get(route('ad.show', $ad->slug))
            ->assertOk()
            ->assertSee('Reportar este anúncio')
            ->assertSeeInOrder(['data-bs-target="#reportModal', '</main>', 'class="modal fade report-modal"'], false)
            ->assertSee('class="modal-dialog modal-dialog-scrollable modal-lg"', false)
            ->assertSee('class="modal-content border-0 rounded-4 shadow"', false)
            ->assertSee('Já foi vendido')
            ->assertDontSee('Serviço não está mais disponível')
            ->assertDontSee('Índice de confiança');

        $this->get(route('provider.show', $service->slug))
            ->assertOk()
            ->assertSee('Reportar este serviço')
            ->assertSeeInOrder(['data-bs-target="#reportModal', '</main>', 'class="modal fade report-modal"'], false)
            ->assertSee('class="modal-dialog modal-dialog-scrollable modal-lg"', false)
            ->assertSee('class="modal-content border-0 rounded-4 shadow"', false)
            ->assertSee('Serviço não está mais disponível')
            ->assertDontSee('Já foi vendido')
            ->assertDontSee('Índice de confiança');
    }

    public function test_guest_can_submit_a_valid_report_and_receives_a_public_reference(): void
    {
        $ad = $this->createAd(User::factory()->create(), 'products', 'produto-reportado');

        $response = $this->post(route('reports.store', $ad), [
            'reason' => 'suspicious_price',
            'severity' => 'misleading',
            'details' => 'O valor parece incompatível com o produto apresentado.',
            'wants_notification' => 0,
            'truth_confirmation' => 1,
        ]);

        $report = Report::firstOrFail();

        $response->assertRedirect(route('reports.thank_you', $report->public_id));
        $this->assertSame('ad', $report->subject_type);
        $this->assertNull($report->reporter_user_id);
        $this->assertFalse($report->wants_notification);

        $this->get(route('reports.thank_you', $report->public_id))
            ->assertOk()
            ->assertSee($report->reference)
            ->assertSee('Sua denúncia foi registrada');
    }

    public function test_service_rejects_an_ad_only_reason(): void
    {
        $service = $this->createAd(User::factory()->create(), 'services', 'servico-motivo-invalido');

        $this->post(route('reports.store', $service), [
            'reason' => 'sold',
            'severity' => 'error',
            'truth_confirmation' => 1,
        ])->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_guest_can_submit_a_valid_service_report(): void
    {
        $service = $this->createAd(User::factory()->create(), 'services', 'servico-reportado');

        $response = $this->post(route('reports.store', $service), [
            'reason' => 'unavailable_service',
            'severity' => 'error',
            'details' => 'O profissional informou que este serviço não está mais disponível.',
            'wants_notification' => 0,
            'truth_confirmation' => 1,
        ]);

        $report = Report::firstOrFail();

        $response->assertRedirect(route('reports.thank_you', $report->public_id));
        $this->assertSame('service', $report->subject_type);
        $this->assertSame('unavailable_service', $report->reason);
    }

    public function test_admin_can_hide_reported_content_and_reporter_is_notified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advertiser = User::factory()->create();
        $reporter = User::factory()->create();
        $ad = $this->createAd($advertiser, 'real_estate', 'imovel-denunciado');
        $report = $this->createReport($ad, $reporter);

        $this->actingAs($admin)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee($report->reference)
            ->assertSee('Alta prioridade');

        $this->actingAs($admin)
            ->post(route('admin.reports.action', $report), [
                'action' => 'hide',
                'resolution_note' => 'Ocultado durante a verificação.',
            ])
            ->assertRedirect(route('admin.reports.show', $report));

        $this->assertSame('inactive', $ad->fresh()->status);
        $this->assertSame('resolved', $report->fresh()->status);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $reporter->id,
            'report_id' => $report->id,
            'kind' => 'report_result',
            'action_url' => route('reports.thank_you', $report->public_id, false),
        ]);
    }

    public function test_suspended_user_cannot_access_authenticated_routes(): void
    {
        $user = User::factory()->create(['suspended_at' => now()]);

        $this->actingAs($user)
            ->get(route('user.panel'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function createAd(User $user, string $module, string $slug): Ad
    {
        return Ad::create([
            'user_id' => $user->id,
            'module' => $module,
            'title' => 'Conteúdo para teste ' . $slug,
            'slug' => $slug,
            'description' => 'Descrição completa usada para testar o fluxo de denúncias da plataforma.',
            'price' => 1000,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
    }

    private function createReport(Ad $ad, User $reporter): Report
    {
        return Report::create([
            'public_id' => fake()->uuid(),
            'ad_id' => $ad->id,
            'advertiser_id' => $ad->user_id,
            'reporter_user_id' => $reporter->id,
            'subject_type' => 'ad',
            'ad_title_snapshot' => $ad->title,
            'ad_module_snapshot' => $ad->module,
            'reason' => 'scam',
            'severity' => 'critical',
            'details' => 'Possível tentativa de golpe.',
            'wants_notification' => true,
            'status' => 'open',
        ]);
    }
}
