<?php

namespace Tests\Feature;

use App\Models\ReportNotification;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCommercialCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_followers_are_notified_when_active_promotion_is_created(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'pro']);
        $follower = User::factory()->create();
        $store = $this->createStore($owner);
        $follower->followedStores()->attach($store->id);

        $this->actingAs($owner)
            ->post(route('store.promotions.store', $store), [
                'title' => 'Semana da loja',
                'coupon_code' => 'LOJA20',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'active' => '1',
            ])
            ->assertSessionHasNoErrors();

        $notification = ReportNotification::where('user_id', $follower->id)
            ->where('kind', 'store_promotion')
            ->firstOrFail();
        $this->assertStringContainsString('LOJA20', $notification->message);
        $this->assertSame(route('store.show', $store->slug), $notification->action_url);
    }

    public function test_followers_are_notified_when_product_is_published_in_store(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'pro']);
        $follower = User::factory()->create();
        $store = $this->createStore($owner);
        $follower->followedStores()->attach($store->id);

        $this->actingAs($owner)
            ->post(route('ad.store'), [
                'module' => 'products',
                'category_name' => 'Artigos',
                'title' => 'Produto recém-publicado',
                'price' => '89,90',
                'city' => 'Aracaju',
                'description' => 'Produto criado para avisar seguidores da loja.',
                'whatsapp' => '79999999999',
                'include_in_store' => '1',
                'store_id' => $store->id,
            ])
            ->assertSessionHasNoErrors();

        $notification = ReportNotification::where('user_id', $follower->id)
            ->where('kind', 'store_product')
            ->firstOrFail();
        $this->assertStringContainsString('Produto recém-publicado', $notification->message);
    }

    public function test_user_with_notifications_disabled_does_not_receive_commercial_alert(): void
    {
        $owner = User::factory()->create();
        $follower = User::factory()->create(['notifications_enabled' => false]);
        $store = $this->createStore($owner);
        $follower->followedStores()->attach($store->id);

        $this->actingAs($owner)
            ->post(route('store.promotions.store', $store), [
                'title' => 'Oferta silenciosa',
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'active' => '1',
            ]);

        $this->assertDatabaseMissing('report_notifications', [
            'user_id' => $follower->id,
            'kind' => 'store_promotion',
        ]);
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Comercial',
            'slug' => 'loja-comercial-'.uniqid(),
            'description' => 'Loja usada para validar comunicação.',
            'category' => 'Artigos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }
}
