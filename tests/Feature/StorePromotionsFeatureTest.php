<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StorePromotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePromotionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_promotion_and_public_store_displays_coupon(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->post(route('store.promotions.store', $store), [
                'title' => 'Semana da beleza',
                'coupon_code' => 'sergipe10',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'description' => 'Desconto em itens selecionados.',
                'terms' => 'Válido para compras acima de R$ 100.',
                'ends_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'active' => 1,
            ])
            ->assertSessionHas('store_success');

        $promotion = StorePromotion::firstOrFail();
        $this->assertSame('SERGIPE10', $promotion->coupon_code);
        $this->assertTrue($promotion->active);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Cupons e promoções')
            ->assertSee('Semana da beleza')
            ->assertSee('10% OFF')
            ->assertSee('SERGIPE10')
            ->assertSee('data-coupon-copy="SERGIPE10"', false);
    }

    public function test_public_store_hides_paused_future_and_expired_promotions(): void
    {
        $store = $this->createStore(User::factory()->create());
        $this->createPromotion($store, ['title' => 'Promoção atual']);
        $this->createPromotion($store, ['title' => 'Promoção pausada', 'active' => false]);
        $this->createPromotion($store, [
            'title' => 'Promoção futura',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);
        $this->createPromotion($store, [
            'title' => 'Promoção encerrada',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Promoção atual')
            ->assertDontSee('Promoção pausada')
            ->assertDontSee('Promoção futura')
            ->assertDontSee('Promoção encerrada');
    }

    public function test_free_plan_cannot_exceed_active_promotion_limit(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'free']);
        $store = $this->createStore($owner);
        $this->createPromotion($store);

        $this->actingAs($owner)
            ->post(route('store.promotions.store', $store), [
                'title' => 'Segunda promoção',
                'discount_type' => 'fixed',
                'discount_value' => 20,
                'ends_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'active' => 1,
            ])
            ->assertSessionHasErrors('active');

        $this->assertDatabaseCount('store_promotions', 1);
    }

    public function test_owner_can_create_paused_promotion_after_reaching_active_limit(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'free']);
        $store = $this->createStore($owner);
        $this->createPromotion($store);

        $this->actingAs($owner)
            ->post(route('store.promotions.store', $store), [
                'title' => 'Oferta preparada',
                'discount_type' => 'fixed',
                'discount_value' => 20,
                'ends_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'active' => 0,
            ])
            ->assertSessionHas('store_success');

        $this->assertDatabaseHas('store_promotions', [
            'store_id' => $store->id,
            'title' => 'Oferta preparada',
            'active' => false,
        ]);
    }

    public function test_user_cannot_manage_another_owners_promotions(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $store = $this->createStore($owner);
        $promotion = $this->createPromotion($store);

        $this->actingAs($otherUser)
            ->put(route('store.promotions.update', [$store, $promotion]), [
                'title' => 'Alteração indevida',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'active' => 1,
            ])
            ->assertForbidden();

        $this->assertSame('Oferta de teste', $promotion->fresh()->title);
    }

    public function test_percentage_and_period_are_validated(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->post(route('store.promotions.store', $store), [
                'title' => 'Desconto inválido',
                'discount_type' => 'percentage',
                'discount_value' => 150,
                'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'active' => 1,
            ])
            ->assertSessionHasErrors(['discount_value', 'ends_at']);

        $this->assertDatabaseCount('store_promotions', 0);
    }

    public function test_owner_can_update_pause_and_delete_promotion(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($owner);
        $promotion = $this->createPromotion($store);

        $this->actingAs($owner)
            ->put(route('store.promotions.update', [$store, $promotion]), [
                'title' => 'Oferta atualizada',
                'coupon_code' => 'nova20',
                'discount_type' => 'fixed',
                'discount_value' => 20,
                'ends_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'active' => 1,
            ])
            ->assertSessionHas('store_success');

        $this->assertDatabaseHas('store_promotions', [
            'id' => $promotion->id,
            'title' => 'Oferta atualizada',
            'coupon_code' => 'NOVA20',
        ]);

        $this->actingAs($owner)
            ->post(route('store.promotions.toggle', [$store, $promotion]))
            ->assertSessionHas('store_success');
        $this->assertFalse($promotion->fresh()->active);

        $this->actingAs($owner)
            ->delete(route('store.promotions.destroy', [$store, $promotion]))
            ->assertSessionHas('store_success');
        $this->assertDatabaseMissing('store_promotions', ['id' => $promotion->id]);
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja com Ofertas',
            'slug' => 'loja-ofertas-' . fake()->unique()->numerify('####'),
            'description' => 'Loja criada para testar promoções.',
            'category' => 'Artigos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createPromotion(Store $store, array $overrides = []): StorePromotion
    {
        return $store->promotions()->create(array_merge([
            'title' => 'Oferta de teste',
            'coupon_code' => null,
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'description' => 'Descrição da promoção.',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(5),
            'active' => true,
        ], $overrides));
    }
}
