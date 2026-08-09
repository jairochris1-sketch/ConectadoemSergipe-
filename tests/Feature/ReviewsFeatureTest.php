<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\ReportNotification;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_are_displayed_below_ads_and_guests_must_log_in_to_review(): void
    {
        $ad = $this->createAd(User::factory()->create(), 'services', 'prestador-com-avaliacoes');

        $this->get(route('provider.show', $ad->slug))
            ->assertOk()
            ->assertSee('Avaliações dos usuários')
            ->assertSee('<div class="reviews-average">0,0</div>', false)
            ->assertSee('Entre para avaliar')
            ->assertDontSee('Cliente verificado');

        $this->post(route('reviews.store', $ad), [
            'rating' => 5,
            'comment' => 'Atendimento excelente e serviço muito bem executado.',
        ])->assertRedirect(route('login'));
    }

    public function test_registered_user_can_review_once_and_request_metadata_is_recorded(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create([
            'name' => 'Maria Avaliadora',
            'city' => 'Nossa Senhora da Glória',
        ]);
        $ad = $this->createAd($owner, 'services', 'servico-avaliado-uma-vez');

        $this->actingAs($reviewer)
            ->withServerVariables(['REMOTE_ADDR' => '10.20.30.40', 'HTTP_USER_AGENT' => 'Review test browser'])
            ->post(route('reviews.store', $ad), [
                'rating' => 5,
                'comment' => 'Profissional pontual, cuidadoso e muito competente.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $review = Review::firstOrFail();
        $this->assertSame('approved', $review->status);
        $this->assertSame('10.20.30.40', $review->ip_address);
        $this->assertSame('Review test browser', $review->user_agent);
        $this->assertNotEmpty($review->abuse_fingerprint);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'review_received',
            'action_url' => route('provider.show', $ad->slug, false) . '#avaliacao-' . $review->id,
        ]);

        $notification = \App\Models\ReportNotification::where('user_id', $owner->id)
            ->where('kind', 'review_received')
            ->firstOrFail();

        $this->actingAs($owner)
            ->get(route('user.notifications.open', $notification))
            ->assertRedirect(route('provider.show', $ad->slug, false) . '#avaliacao-' . $review->id);

        $this->actingAs($reviewer)
            ->post(route('reviews.store', $ad), [
                'rating' => 4,
                'comment' => 'Tentativa de publicar uma segunda avaliação.',
            ])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 1);

        $this->get(route('provider.show', $ad->slug))
            ->assertOk()
            ->assertSee('Maria Avaliadora')
            ->assertSee('em Nossa Senhora da Glória')
            ->assertSee(now()->format('d/m/Y'))
            ->assertSee('Profissional pontual, cuidadoso e muito competente.')
            ->assertSee('review-author-name', false)
            ->assertSee('id="avaliacao-' . $review->id . '"', false)
            ->assertDontSee('Usuário cadastrado')
            ->assertSee('5,0');
    }

    public function test_owner_cannot_review_own_profile_or_delete_another_users_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $ad = $this->createAd($owner, 'services', 'perfil-sem-autoavaliacao');

        $this->actingAs($owner)
            ->post(route('reviews.store', $ad), [
                'rating' => 5,
                'comment' => 'Tentativa indevida de avaliar o próprio perfil.',
            ])
            ->assertSessionHasErrors('review');

        $review = $this->createReview($ad, $reviewer, 'Avaliação legítima feita por outra pessoa.');

        $this->actingAs($owner)
            ->delete(route('reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_author_can_edit_and_delete_own_review(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $ad = $this->createAd($owner, 'services', 'servico-avaliacao-editavel');
        $review = $this->createReview($ad, $reviewer, 'Comentário inicial sobre este atendimento.');

        $this->actingAs($reviewer)
            ->put(route('reviews.update', $review), [
                'rating' => 4,
                'comment' => 'Comentário atualizado pelo próprio usuário avaliador.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $review->refresh();
        $this->assertSame(4, $review->rating);
        $this->assertSame('Comentário atualizado pelo próprio usuário avaliador.', $review->comment);
        $this->assertNotNull($review->edited_at);

        $this->actingAs($reviewer)
            ->delete(route('reviews.destroy', $review))
            ->assertSessionHas('review_success');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_repeated_text_and_review_bursts_are_blocked(): void
    {
        $user = User::factory()->create();
        $firstAd = $this->createAd(User::factory()->create(), 'services', 'servico-texto-original');
        $secondAd = $this->createAd(User::factory()->create(), 'services', 'servico-texto-repetido');

        $this->actingAs($user)->post(route('reviews.store', $firstAd), [
            'rating' => 5,
            'comment' => 'O atendimento foi rápido, cuidadoso e muito profissional.',
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('reviews.store', $secondAd), [
            'rating' => 4,
            'comment' => '  O ATENDIMENTO foi rápido, cuidadoso e muito profissional. ',
        ])->assertSessionHasErrors('comment');

        $burstUser = User::factory()->create();
        foreach (range(1, 3) as $index) {
            $ad = $this->createAd(User::factory()->create(), 'services', 'servico-rapido-' . $index);
            $this->createReview($ad, $burstUser, 'Comentário diferente para o teste de limite ' . $index . '.');
        }

        $fourthAd = $this->createAd(User::factory()->create(), 'services', 'servico-limite-avaliacoes');
        $this->actingAs($burstUser)->post(route('reviews.store', $fourthAd), [
            'rating' => 3,
            'comment' => 'Quarta avaliação enviada dentro do intervalo controlado.',
        ])->assertSessionHasErrors('review');
    }

    public function test_owner_can_report_but_only_admin_can_hide_a_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $ad = $this->createAd($owner, 'services', 'prestador-avaliacao-denunciada');
        $review = $this->createReview($ad, $reviewer, 'Texto denunciado que permanece visível até a análise.');

        $this->actingAs($owner)
            ->post(route('reviews.report', $review), [
                'reason' => 'false',
                'details' => 'O relato não corresponde ao serviço prestado.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $report = ReviewReport::firstOrFail();
        $this->assertSame('pending', $report->status);
        $this->assertSame('approved', $review->fresh()->status);

        $this->get(route('provider.show', $ad->slug))
            ->assertSee('Texto denunciado que permanece visível até a análise.');

        $this->actingAs($admin)
            ->get(route('admin.reviews'))
            ->assertOk()
            ->assertSee('Texto denunciado que permanece visível até a análise.');

        $this->actingAs($admin)
            ->post(route('admin.reviews.action', $review), ['action' => 'hide'])
            ->assertSessionHas('success');

        $this->assertSame('hidden', $review->fresh()->status);
        $this->assertSame('actioned', $report->fresh()->status);
        $this->get(route('provider.show', $ad->slug))
            ->assertDontSee('Texto denunciado que permanece visível até a análise.');

        $this->actingAs($admin)
            ->post(route('admin.reviews.action', $review), ['action' => 'approve'])
            ->assertSessionHas('success');

        $this->assertSame('approved', $review->fresh()->status);
        $this->get(route('provider.show', $ad->slug))
            ->assertSee('Texto denunciado que permanece visível até a análise.');
    }

    public function test_all_approved_reviews_are_visible_and_hidden_reviews_are_not(): void
    {
        $ad = $this->createAd(User::factory()->create(), 'services', 'prestador-todas-avaliacoes');

        $this->createReview($ad, User::factory()->create(), 'Primeira avaliação aprovada e exibida integralmente.');
        $this->createReview($ad, User::factory()->create(), 'Segunda avaliação aprovada e exibida integralmente.');
        $hidden = $this->createReview($ad, User::factory()->create(), 'Avaliação ocultada pelo administrador.');
        $hidden->update(['status' => 'hidden']);

        $this->get(route('provider.show', $ad->slug))
            ->assertOk()
            ->assertSee('Primeira avaliação aprovada e exibida integralmente.')
            ->assertSee('Segunda avaliação aprovada e exibida integralmente.')
            ->assertDontSee('Avaliação ocultada pelo administrador.')
            ->assertSee('2 avaliações');
    }

    public function test_reviews_are_not_available_in_non_service_modules(): void
    {
        $reviewer = User::factory()->create();

        foreach (['real_estate', 'vehicles', 'products', 'jobs', 'agro'] as $module) {
            $ad = $this->createAd(User::factory()->create(), $module, 'sem-avaliacoes-' . $module);

            $this->get(route('ad.show', $ad->slug))
                ->assertOk()
                ->assertDontSee('Avaliações dos usuários')
                ->assertDontSee('Escrever uma avaliação')
                ->assertDontSee('Índice de confiança');

            $this->actingAs($reviewer)
                ->post(route('reviews.store', $ad), [
                    'rating' => 5,
                    'comment' => 'Esta avaliação não deve ser aceita neste módulo.',
                ])
                ->assertNotFound();
        }

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_five_approved_reviews_show_platform_count_and_search_metadata(): void
    {
        $ad = $this->createAd(User::factory()->create(), 'services', 'prestador-cinco-avaliacoes');

        foreach (range(1, 5) as $index) {
            $this->createReview(
                $ad,
                User::factory()->create(['city' => 'Aracaju']),
                'Avaliação aprovada número ' . $index . ' para validar o destaque.'
            );
        }

        $this->get(route('provider.show', $ad->slug))
            ->assertOk()
            ->assertSee('5 Avaliações no Conectado em Sergipe')
            ->assertSee('"@type":"ProfessionalService"', false)
            ->assertSee('"reviewCount":5', false)
            ->assertSee('"ratingValue":5', false);
    }

    public function test_profile_owner_can_reply_edit_delete_and_notify_the_reviewer(): void
    {
        $owner = User::factory()->create(['name' => 'Profissional Responsável']);
        $reviewer = User::factory()->create(['name' => 'Cliente Avaliador']);
        $outsider = User::factory()->create();
        $ad = $this->createAd($owner, 'services', 'prestador-com-resposta');
        $review = $this->createReview(
            $ad,
            $reviewer,
            'Avaliação que receberá uma resposta pública do profissional.'
        );

        $this->actingAs($outsider)
            ->post(route('reviews.reply.store', $review), [
                'reply' => 'Uma pessoa sem autorização não pode responder.',
            ])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('reviews.reply.store', $review), [
                'reply' => 'Obrigado pela avaliação e pela confiança em nosso trabalho.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $review->refresh();
        $this->assertSame('Obrigado pela avaliação e pela confiança em nosso trabalho.', $review->professional_reply);
        $this->assertSame($owner->id, $review->professional_reply_user_id);
        $this->assertNotNull($review->professional_replied_at);

        $notification = ReportNotification::query()
            ->where('user_id', $reviewer->id)
            ->where('kind', 'review_replied')
            ->firstOrFail();
        $replyDestination = route('provider.show', $ad->slug, false)
            . '#resposta-avaliacao-' . $review->id;

        $this->assertSame($replyDestination, $notification->action_url);

        $this->actingAs($reviewer)
            ->get(route('user.notifications.open', $notification))
            ->assertRedirect($replyDestination);
        $this->assertNotNull($notification->fresh()->read_at);

        $this->get(route('provider.show', $ad->slug))
            ->assertOk()
            ->assertSee('Resposta do profissional')
            ->assertSee('Profissional Responsável')
            ->assertSee('Obrigado pela avaliação e pela confiança em nosso trabalho.')
            ->assertSee('id="resposta-avaliacao-' . $review->id . '"', false);

        $this->actingAs($owner)
            ->put(route('reviews.reply.update', $review), [
                'reply' => 'Resposta corrigida pelo responsável pelo perfil.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $review->refresh();
        $this->assertSame('Resposta corrigida pelo responsável pelo perfil.', $review->professional_reply);
        $this->assertNotNull($review->professional_reply_edited_at);

        $this->actingAs($owner)
            ->delete(route('reviews.reply.destroy', $review))
            ->assertSessionHas('review_success');

        $review->refresh();
        $this->assertNull($review->professional_reply);
        $this->assertNull($review->professional_reply_user_id);
        $this->assertNull($review->professional_replied_at);
        $this->assertNull($review->professional_reply_edited_at);
    }

    private function createAd(User $owner, string $module, string $slug): Ad
    {
        return Ad::create([
            'user_id' => $owner->id,
            'module' => $module,
            'title' => 'Conteúdo avaliado ' . $slug,
            'slug' => $slug,
            'description' => 'Descrição completa usada no teste automatizado do sistema de avaliações.',
            'price' => 1000,
            'city' => 'Aracaju',
            'state' => 'SE',
            'status' => 'active',
        ]);
    }

    private function createReview(Ad $ad, User $user, string $comment): Review
    {
        return Review::create([
            'ad_id' => $ad->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => $comment,
            'content_hash' => hash('sha256', mb_strtolower(trim($comment))),
            'status' => 'approved',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'abuse_fingerprint' => hash('sha256', '127.0.0.1|Feature test'),
        ]);
    }
}
