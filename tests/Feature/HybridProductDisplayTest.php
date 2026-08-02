<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use App\Services\ProductDisplayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HybridProductDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_suggestion_and_product_override_determine_effective_mode(): void
    {
        $service = app(ProductDisplayService::class);

        $this->assertSame('catalog', $service->suggestForCategory('Alimentação e bebidas'));
        $this->assertSame('individual', $service->suggestForCategory('Eletrônicos'));

        $store = $this->createStore(['product_display_mode' => 'catalog']);
        $product = $this->createProduct($store);

        $this->assertSame('catalog', $product->effectiveDisplayMode());

        $product->update(['display_mode' => 'individual']);

        $this->assertSame('individual', $product->fresh()->effectiveDisplayMode());
    }

    public function test_public_product_page_has_canonical_metadata_and_structured_data(): void
    {
        $store = $this->createStore();
        $product = $this->createProduct($store, [
            'title' => 'Notebook Sergipano',
            'slug' => 'notebook-sergipano',
        ]);
        $url = route('store.products.show', [$store, $product]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Notebook Sergipano')
            ->assertSee('<link rel="canonical" href="'.$url.'">', false)
            ->assertSee('<meta property="og:url" content="'.$url.'">', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta property="og:image:alt" content="Notebook Sergipano', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('Adicionar ao carrinho')
            ->assertSee('Comprar agora')
            ->assertSee('data-social-share', false);
    }

    public function test_nested_product_binding_rejects_product_from_another_store(): void
    {
        $store = $this->createStore();
        $otherStore = $this->createStore(['slug' => 'outra-loja']);
        $product = $this->createProduct($otherStore);

        $this->get(route('store.products.show', [$store, $product]))
            ->assertNotFound();
    }

    public function test_legacy_product_url_redirects_to_canonical_store_url(): void
    {
        $store = $this->createStore();
        $product = $this->createProduct($store);

        $this->get(route('ad.show', $product->slug))
            ->assertRedirect(route('store.products.show', [$store, $product]))
            ->assertStatus(301);
    }

    public function test_buy_now_adds_product_and_goes_directly_to_checkout(): void
    {
        $product = $this->createProduct($this->createStore());

        $this->post(route('cart.add', $product), [
            'quantity' => 2,
            'buy_now' => 1,
        ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas(
                'store_cart.items',
                fn ($items) => collect($items)->contains(
                    fn ($item) => ($item['product_id'] ?? null) === $product->id
                        && ($item['quantity'] ?? null) === 2
                )
            );
    }

    private function createStore(array $overrides = []): Store
    {
        $owner = User::factory()->create();

        return Store::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Loja Híbrida',
            'slug' => 'loja-hibrida-'.uniqid(),
            'description' => 'Loja criada para testar a apresentação híbrida de produtos.',
            'category' => 'Eletrônicos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
            'product_display_mode' => 'individual',
        ], $overrides));
    }

    private function createProduct(Store $store, array $overrides = []): Ad
    {
        return Ad::create(array_merge([
            'user_id' => $store->user_id,
            'store_id' => $store->id,
            'module' => 'products',
            'display_mode' => 'default',
            'advertiser_type' => 'Eletrônicos',
            'title' => 'Produto híbrido',
            'slug' => 'produto-hibrido-'.uniqid(),
            'description' => 'Produto publicado para validar sua página própria.',
            'price' => 150,
            'city' => 'Aracaju',
            'state' => 'Sergipe',
            'status' => 'active',
        ], $overrides));
    }
}
