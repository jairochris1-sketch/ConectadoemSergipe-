<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storefront_displays_identity_contacts_and_linked_products(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $product = $this->createProduct($owner, $store, [
            'title' => 'Notebook Sergipano',
            'advertiser_type' => 'Eletrônicos',
        ]);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Produtos da loja')
            ->assertSee('Conheça a loja')
            ->assertSee('Fale com a loja')
            ->assertSee('Compartilhar')
            ->assertSee('data-social-share', false)
            ->assertSee('<meta property="og:title" content="Loja Vitrine Sergipe - Loja no Conectado em Sergipe">', false)
            ->assertSee('<meta property="og:url" content="'.route('store.show', $store->slug).'">', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('Notebook Sergipano')
            ->assertSee('R$ 150,00')
            ->assertSee(route('store.products.show', [$store, $product]), false)
            ->assertSee('https://wa.me/5579999999999', false)
            ->assertSee('https://instagram.com/lojavitrine', false);
    }

    public function test_storefront_catalog_can_search_filter_and_sort_products(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $this->createProduct($owner, $store, [
            'title' => 'Notebook Profissional',
            'advertiser_type' => 'Eletrônicos',
            'price' => 3200,
        ]);
        $this->createProduct($owner, $store, [
            'title' => 'Camisa Regional',
            'slug' => 'camisa-regional',
            'advertiser_type' => 'Moda',
            'price' => 80,
        ]);
        $this->createProduct($owner, $store, [
            'title' => 'Celular Econômico',
            'slug' => 'celular-economico',
            'advertiser_type' => 'Eletrônicos',
            'price' => 900,
        ]);

        $this->get(route('store.show', [
            'slug' => $store->slug,
            'q' => 'Notebook',
        ]))
            ->assertOk()
            ->assertSee('Notebook Profissional')
            ->assertDontSee('Camisa Regional')
            ->assertDontSee('Celular Econômico');

        $response = $this->get(route('store.show', [
            'slug' => $store->slug,
            'category' => 'Eletrônicos',
            'sort' => 'price_asc',
        ]))
            ->assertOk()
            ->assertSee('Notebook Profissional')
            ->assertSee('Celular Econômico')
            ->assertDontSee('Camisa Regional');

        $this->assertLessThan(
            strpos($response->getContent(), 'Notebook Profissional'),
            strpos($response->getContent(), 'Celular Econômico')
        );
    }

    public function test_empty_and_filtered_empty_states_are_distinct(): void
    {
        $owner = User::factory()->create();
        $emptyStore = $this->createStore($owner, ['slug' => 'loja-vazia']);

        $this->get(route('store.show', $emptyStore->slug))
            ->assertOk()
            ->assertSee('Esta loja ainda não publicou produtos');

        $storeWithProduct = $this->createStore(User::factory()->create(), [
            'slug' => 'loja-com-produto',
        ]);
        $this->createProduct($storeWithProduct->user, $storeWithProduct);

        $this->get(route('store.show', [
            'slug' => $storeWithProduct->slug,
            'q' => 'produto inexistente',
        ]))
            ->assertOk()
            ->assertSee('Nenhum produto encontrado')
            ->assertSee('Ver todos os produtos');
    }

    public function test_inactive_store_is_hidden_from_public_but_visible_to_owner_and_admin(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner, ['active' => false]);

        $this->get(route('store.show', $store->slug))->assertNotFound();

        $this->actingAs($owner)
            ->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Esta loja está desativada');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Esta loja está desativada');
    }

    private function createStore(User $owner, array $overrides = []): Store
    {
        return Store::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Loja Vitrine Sergipe',
            'slug' => 'loja-vitrine-sergipe-'.uniqid(),
            'description' => 'Produtos selecionados e atendimento para todo o estado.',
            'category' => 'Eletrônicos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'phone' => '7933333333',
            'whatsapp' => '79999999999',
            'instagram' => '@lojavitrine',
            'website' => 'https://example.com',
            'active' => true,
        ], $overrides));
    }

    private function createProduct(User $owner, Store $store, array $overrides = []): Ad
    {
        return Ad::create(array_merge([
            'user_id' => $owner->id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Eletrônicos',
            'title' => 'Produto da vitrine',
            'slug' => 'produto-da-vitrine-'.uniqid(),
            'description' => 'Produto publicado na vitrine da loja.',
            'price' => 150,
            'city' => 'Aracaju',
            'state' => 'Sergipe',
            'status' => 'active',
        ], $overrides));
    }
}
