<?php

namespace Tests\Feature;

use App\Models\ReportNotification;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreReviewsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_can_review_a_store_once_and_owner_is_notified(): void
    {
        $owner = User::factory()->create(['name' => 'Dono da Loja']);
        $reviewer = User::factory()->create([
            'name' => 'Cliente da Loja',
            'city' => 'Aracaju',
        ]);
        $store = $this->createStore($owner);

        $this->actingAs($reviewer)
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.8', 'HTTP_USER_AGENT' => 'Store review browser'])
            ->post(route('store.reviews.store', $store), [
                'rating' => 5,
                'comment' => 'Loja organizada, atendimento excelente e entrega cuidadosa.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $review = Review::firstOrFail();
        $this->assertNull($review->ad_id);
        $this->assertSame($store->id, $review->store_id);
        $this->assertSame('10.0.0.8', $review->ip_address);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'review_received',
            'action_url' => route('store.show', $store->slug, false) . '#avaliacao-' . $review->id,
        ]);

        $this->actingAs($reviewer)
            ->post(route('store.reviews.store', $store), [
                'rating' => 4,
                'comment' => 'Tentativa de avaliar a mesma loja uma segunda vez.',
            ])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_store_page_displays_reviews_and_owner_can_reply(): void
    {
        $owner = User::factory()->create(['name' => 'Bella Studio']);
        $reviewer = User::factory()->create(['name' => 'Maria Cliente']);
        $store = $this->createStore($owner);
        $review = Review::create([
            'store_id' => $store->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'comment' => 'Atendimento muito atencioso e produtos de ótima qualidade.',
            'content_hash' => hash('sha256', 'avaliacao-loja'),
            'status' => 'approved',
        ]);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Avaliações dos usuários')
            ->assertSee('Maria Cliente')
            ->assertSee('Atendimento muito atencioso e produtos de ótima qualidade.');

        $this->actingAs($owner)
            ->post(route('reviews.reply.store', $review), [
                'reply' => 'Agradecemos sua avaliação e esperamos receber você novamente.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('review_success');

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Resposta da loja')
            ->assertSee('Agradecemos sua avaliação e esperamos receber você novamente.');

        $notification = ReportNotification::where('user_id', $reviewer->id)
            ->where('kind', 'review_replied')
            ->firstOrFail();
        $this->assertSame(
            route('store.show', $store->slug, false) . '#resposta-avaliacao-' . $review->id,
            $notification->action_url
        );
    }

    public function test_store_owner_cannot_review_own_store(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->post(route('store.reviews.store', $store), [
                'rating' => 5,
                'comment' => 'O proprietário não deve avaliar a própria loja.',
            ])
            ->assertSessionHasErrors('review');

        $this->assertDatabaseCount('reviews', 0);
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Sergipana',
            'slug' => 'loja-sergipana-' . uniqid(),
            'description' => 'Comércio local de Sergipe.',
            'active' => true,
        ]);
    }
}
