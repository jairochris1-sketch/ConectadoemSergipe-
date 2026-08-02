<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\StoreEvent;
use App\Models\User;
use App\Services\StoreAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_view_is_recorded_once_per_visitor_each_day(): void
    {
        $store = $this->createStore(User::factory()->create());
        $server = [
            'REMOTE_ADDR' => '10.20.30.40',
            'HTTP_USER_AGENT' => 'Analytics browser',
        ];

        $this->withServerVariables($server)
            ->postJson(route('store.events.store', $store), ['event_type' => 'page_view'])
            ->assertNoContent();
        $this->withServerVariables($server)
            ->postJson(route('store.events.store', $store), ['event_type' => 'page_view'])
            ->assertNoContent();

        $this->assertDatabaseCount('store_events', 1);
        $this->assertDatabaseHas('store_events', [
            'store_id' => $store->id,
            'event_type' => 'page_view',
        ]);
    }

    public function test_store_owner_access_is_not_counted(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->postJson(route('store.events.store', $store), ['event_type' => 'page_view'])
            ->assertNoContent();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->postJson(route('store.events.store', $store), ['event_type' => 'page_view'])
            ->assertNoContent();

        $this->assertDatabaseCount('store_events', 0);
    }

    public function test_product_click_must_belong_to_the_public_store(): void
    {
        $store = $this->createStore(User::factory()->create());
        $product = $this->createProduct($store);
        $otherStore = $this->createStore(User::factory()->create());
        $otherProduct = $this->createProduct($otherStore);

        $this->postJson(route('store.events.store', $store), [
            'event_type' => 'product_click',
            'ad_id' => $product->id,
        ])->assertNoContent();

        $this->postJson(route('store.events.store', $store), [
            'event_type' => 'product_click',
            'ad_id' => $otherProduct->id,
        ])->assertStatus(422);

        $this->assertDatabaseCount('store_events', 1);
    }

    public function test_analytics_service_returns_summary_conversion_and_top_products(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'start']);
        $store = $this->createStore($owner);
        $product = $this->createProduct($store);

        $this->createEvent($store, 'page_view', null, 'visitor-a');
        $this->createEvent($store, 'page_view', null, 'visitor-b');
        $this->createEvent($store, 'whatsapp_click', null, 'visitor-a');
        $this->createEvent($store, 'product_click', $product, 'visitor-b');
        $this->createEvent($store, 'share_click', null, 'visitor-c');

        $analytics = app(StoreAnalyticsService::class)->forStore($store, $owner);

        $this->assertSame(30, $analytics['days']);
        $this->assertSame(2, $analytics['summary']['views']['value']);
        $this->assertSame(1, $analytics['summary']['contacts']['value']);
        $this->assertSame(1, $analytics['summary']['product_clicks']['value']);
        $this->assertSame(1, $analytics['summary']['shares']['value']);
        $this->assertSame(50.0, $analytics['conversion_rate']);
        $this->assertSame($product->title, $analytics['top_products']->first()['title']);
    }

    public function test_owner_management_page_displays_analytics_and_public_page_has_tracking_hooks(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $product = $this->createProduct($store);
        $this->createEvent($store, 'page_view', null, 'visitor-a');

        $this->actingAs($owner)
            ->get(route('store.manage', $store))
            ->assertOk()
            ->assertSee('Desempenho da sua vitrine')
            ->assertSee('Visualizações por dia')
            ->assertSee('Canais de contato');

        auth()->logout();

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('const eventEndpoint', false)
            ->assertSee('data-store-event="product_click"', false)
            ->assertSee('data-store-event="whatsapp_click"', false)
            ->assertSee((string) $product->id, false);
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Analytics',
            'slug' => 'loja-analytics-' . uniqid(),
            'description' => 'Loja criada para testar estatísticas.',
            'category' => 'Eletrônicos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'phone' => '7933333333',
            'whatsapp' => '79999999999',
            'instagram' => '@lojaanalytics',
            'website' => 'https://example.com',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createProduct(Store $store): Ad
    {
        return Ad::create([
            'user_id' => $store->user_id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Eletrônicos',
            'title' => 'Produto mais acessado',
            'slug' => 'produto-analytics-' . uniqid(),
            'description' => 'Produto de teste.',
            'price' => 199.90,
            'city' => 'Aracaju',
            'state' => 'Sergipe',
            'status' => 'active',
        ]);
    }

    private function createEvent(
        Store $store,
        string $type,
        ?Ad $ad,
        string $visitor
    ): StoreEvent {
        return StoreEvent::create([
            'store_id' => $store->id,
            'ad_id' => $ad?->id,
            'event_type' => $type,
            'visitor_hash' => hash('sha256', $visitor . $type . ($ad?->id ?? '')),
            'occurred_on' => now()->toDateString(),
        ]);
    }
}
