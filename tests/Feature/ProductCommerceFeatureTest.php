<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Order;
use App\Models\ProductAddon;
use App\Models\ProductVariation;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCommerceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_out_of_stock_and_quantity_above_stock_are_rejected(): void
    {
        $product = $this->createProduct($this->createStore(), [
            'track_stock' => true,
            'stock_quantity' => 2,
        ]);

        $this->post(route('cart.add', $product), ['quantity' => 3])
            ->assertSessionHasErrors('cart');

        $product->update(['stock_quantity' => 0]);
        $this->post(route('cart.add', $product), ['quantity' => 1])
            ->assertSessionHasErrors('cart');
    }

    public function test_variation_addon_price_and_stock_lifecycle_are_preserved_in_order(): void
    {
        $owner = User::factory()->create();
        $buyer = User::factory()->create();
        $store = $this->createStore($owner);
        $product = $this->createProduct($store);
        $variation = ProductVariation::create([
            'ad_id' => $product->id,
            'name' => 'Grande',
            'sku' => 'PIZZA-G',
            'price_adjustment' => 20,
            'stock_quantity' => 5,
            'track_stock' => true,
            'active' => true,
        ]);
        $addon = ProductAddon::create([
            'ad_id' => $product->id,
            'name' => 'Bacon extra',
            'price' => 5,
            'active' => true,
        ]);

        $this->actingAs($buyer)
            ->post(route('cart.add', $product), [
                'variation_id' => $variation->id,
                'addon_ids' => [$addon->id],
                'quantity' => 2,
                'note' => 'Sem cebola',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($buyer)
            ->post(route('checkout.place'), [
                'customer_name' => $buyer->name,
                'customer_phone' => '79999999999',
                'customer_email' => $buyer->email,
                'fulfillment_method' => 'pickup',
            ])
            ->assertSessionHasNoErrors();

        $order = Order::with('items')->firstOrFail();
        $item = $order->items->first();
        $this->assertSame('250.00', $order->subtotal);
        $this->assertSame($variation->id, $item->product_variation_id);
        $this->assertSame('Grande', $item->variation_name);
        $this->assertSame('PIZZA-G', $item->sku);
        $this->assertSame('Sem cebola', $item->customer_note);
        $this->assertSame('Bacon extra', $item->addons[0]['name']);

        $this->actingAs($owner)
            ->patch(route('seller.orders.status', [$store, $order]), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors();
        $this->assertSame(3, $variation->fresh()->stock_quantity);

        $this->actingAs($owner)
            ->patch(route('seller.orders.status', [$store, $order]), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();
        $this->assertSame(5, $variation->fresh()->stock_quantity);
    }

    public function test_delivery_rules_and_fee_are_applied_by_backend(): void
    {
        $buyer = User::factory()->create();
        $store = $this->createStore(null, [
            'pickup_available' => false,
            'delivery_available' => true,
            'delivery_cities' => ['Aracaju'],
            'delivery_neighborhoods' => ['Atalaia'],
            'delivery_fee' => 12.50,
            'delivery_region_fees' => [['region' => 'Atalaia', 'fee' => 7]],
            'minimum_order' => 50,
        ]);
        $product = $this->createProduct($store);

        $this->actingAs($buyer)->post(route('cart.add', $product));
        $this->actingAs($buyer)
            ->post(route('checkout.place'), [
                'customer_name' => $buyer->name,
                'customer_phone' => '79999999999',
                'fulfillment_method' => 'delivery',
                'delivery_address' => 'Rua A, 10',
                'delivery_city' => 'Aracaju',
                'delivery_neighborhood' => 'Atalaia',
                'delivery_state' => 'SE',
            ])
            ->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame('7.00', $order->delivery_fee);
        $this->assertSame('107.00', $order->total);
    }

    public function test_customer_can_favorite_ask_and_receive_store_answer(): void
    {
        $owner = User::factory()->create();
        $customer = User::factory()->create();
        $store = $this->createStore($owner);
        $product = $this->createProduct($store);

        $this->actingAs($customer)
            ->post(route('products.favorite.toggle', $product))
            ->assertSessionHas('success');
        $this->assertTrue($customer->favorites()->whereKey($product->id)->exists());

        $this->actingAs($customer)
            ->post(route('products.questions.store', $product), [
                'question' => 'Este produto possui garantia?',
            ])
            ->assertSessionHasNoErrors();
        $question = $product->questions()->firstOrFail();

        $this->actingAs($owner)
            ->post(route('products.questions.answer', $question), [
                'answer' => 'Sim, possui garantia de doze meses.',
            ])
            ->assertSessionHasNoErrors();

        $this->get(route('store.products.show', [$store, $product]))
            ->assertOk()
            ->assertSee('Este produto possui garantia?')
            ->assertSee('Sim, possui garantia de doze meses.');
    }

    public function test_technical_specs_video_and_variation_image_are_rendered(): void
    {
        $store = $this->createStore();
        $product = $this->createProduct($store, [
            'technical_specs' => ['Material' => 'Algodão', 'Peso' => '500 g'],
            'video_url' => 'https://youtu.be/abcdefghijk',
        ]);
        ProductVariation::create([
            'ad_id' => $product->id,
            'name' => 'Azul / M',
            'price_adjustment' => 0,
            'stock_quantity' => 3,
            'track_stock' => true,
            'image' => 'uploads/variations/azul.webp',
            'active' => true,
        ]);

        $this->get(route('store.products.show', [$store, $product]))
            ->assertOk()
            ->assertSee('Ficha técnica')
            ->assertSee('Algodão')
            ->assertSee('youtube-nocookie.com/embed/abcdefghijk', false)
            ->assertSee('uploads/variations/azul.webp', false);
    }

    public function test_product_page_counts_only_confirmed_sales_and_has_breadcrumb_schema(): void
    {
        $store = $this->createStore();
        $product = $this->createProduct($store);
        $this->createOrderItem($product, 'completed', 3);
        $this->createOrderItem($product, 'pending', 7);
        $this->createOrderItem($product, 'cancelled', 5);

        $this->get(route('store.products.show', [$store, $product]))
            ->assertOk()
            ->assertSee('3 unidades vendidas')
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"position":3', false);
    }

    public function test_cart_rejects_addon_from_another_product(): void
    {
        $store = $this->createStore();
        $product = $this->createProduct($store);
        $otherProduct = $this->createProduct($store, ['slug' => 'outro-produto']);
        $foreignAddon = ProductAddon::create([
            'ad_id' => $otherProduct->id,
            'name' => 'Adicional inválido',
            'price' => 500,
            'active' => true,
        ]);

        $this->post(route('cart.add', $product), ['addon_ids' => [$foreignAddon->id]])
            ->assertSessionHasErrors('addon_ids');
    }

    public function test_cart_rejects_variation_from_another_product(): void
    {
        $store = $this->createStore();
        $product = $this->createProduct($store);
        $otherProduct = $this->createProduct($store, ['slug' => 'produto-com-variacao']);
        $foreignVariation = ProductVariation::create([
            'ad_id' => $otherProduct->id,
            'name' => 'Variação externa',
            'price_adjustment' => 0,
            'stock_quantity' => 10,
            'track_stock' => true,
            'active' => true,
        ]);

        $this->post(route('cart.add', $product), ['variation_id' => $foreignVariation->id])
            ->assertSessionHasErrors('variation_id');
    }

    public function test_inactive_store_product_cannot_be_favorited_or_receive_question(): void
    {
        $customer = User::factory()->create();
        $store = $this->createStore(null, ['active' => false]);
        $product = $this->createProduct($store);

        $this->actingAs($customer)
            ->post(route('products.favorite.toggle', $product))
            ->assertNotFound();
        $this->actingAs($customer)
            ->post(route('products.questions.store', $product), ['question' => 'Pergunta válida?'])
            ->assertNotFound();
    }

    public function test_catalog_modal_history_api_and_sitemap_only_show_active_products(): void
    {
        $store = $this->createStore(null, ['product_display_mode' => 'catalog']);
        $active = $this->createProduct($store, ['slug' => 'produto-ativo']);
        $inactive = $this->createProduct($store, ['slug' => 'produto-inativo', 'status' => 'inactive']);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('data-quick-product="quick-product-'.$active->id.'"', false)
            ->assertSee('history.pushState', false)
            ->assertSee('Ver página completa');

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('store.products.show', [$store, $active]), false)
            ->assertDontSee(route('store.products.show', [$store, $inactive]), false);
    }

    public function test_catalog_modal_shows_gallery_review_summary_and_variation_stock_data(): void
    {
        $store = $this->createStore(null, [
            'product_display_mode' => 'catalog',
            'delivery_fee' => 8.50,
            'delivery_min_minutes' => 30,
            'delivery_max_minutes' => 50,
            'free_delivery_threshold' => 80,
            'minimum_order' => 25,
            'pickup_address' => 'Rua da Loja, 10',
        ]);
        $product = $this->createProduct($store);
        AdImage::create([
            'ad_id' => $product->id,
            'image_path' => 'uploads/products/principal.webp',
            'is_main' => true,
        ]);
        AdImage::create([
            'ad_id' => $product->id,
            'image_path' => 'uploads/products/detalhe.webp',
            'is_main' => false,
        ]);
        ProductVariation::create([
            'ad_id' => $product->id,
            'name' => 'Tamanho M',
            'price_adjustment' => 10,
            'stock_quantity' => 3,
            'track_stock' => true,
            'active' => true,
        ]);
        Review::create([
            'ad_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'Produto excelente.',
            'content_hash' => hash('sha256', 'produto-excelente-'.$product->id),
            'status' => 'approved',
        ]);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('aria-labelledby="quick-product-title-'.$product->id.'"', false)
            ->assertSee('data-quick-thumbnail', false)
            ->assertSee('uploads/products/detalhe.webp', false)
            ->assertSee('5,0')
            ->assertSee('(1 avaliação)')
            ->assertSee('data-stock="3"', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('unidades disponíveis')
            ->assertSee('Entrega disponível')
            ->assertSee('a partir de R$ 8,50')
            ->assertSee('Prazo de 30 a 50 minutos')
            ->assertSee('Entrega grátis acima de R$ 80,00')
            ->assertSee('Retirada disponível em Rua da Loja, 10')
            ->assertSee('Pedido mínimo: R$ 25,00');
    }

    public function test_individual_product_page_exposes_reactive_variation_stock(): void
    {
        $store = $this->createStore(null, ['product_display_mode' => 'individual']);
        $product = $this->createProduct($store, [
            'display_mode' => 'individual',
            'low_stock_threshold' => 4,
        ]);
        ProductVariation::create([
            'ad_id' => $product->id,
            'name' => 'Últimas unidades',
            'price_adjustment' => 12,
            'stock_quantity' => 2,
            'track_stock' => true,
            'active' => true,
        ]);

        $this->get(route('store.products.show', [$store, $product]))
            ->assertOk()
            ->assertSee('data-product-stock-status', false)
            ->assertSee('aria-live="polite"', false)
            ->assertSee('data-low-stock-threshold="4"', false)
            ->assertSee('data-stock="2"', false)
            ->assertSee('data-track-stock="1"', false)
            ->assertSee('Selecione uma opção para consultar o estoque.')
            ->assertSee('quantity.max = Math.max(1, stock)', false)
            ->assertSee('Últimas ${stock} unidades', false);
    }

    public function test_store_management_has_operational_product_summary(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $product = $this->createProduct($store, [
            'sku' => 'CAM-001',
            'track_stock' => true,
            'stock_quantity' => 2,
            'low_stock_threshold' => 3,
        ]);
        ProductVariation::create([
            'ad_id' => $product->id,
            'name' => 'Azul',
            'price_adjustment' => 0,
            'stock_quantity' => 2,
            'track_stock' => true,
            'active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('store.manage', $store))
            ->assertOk()
            ->assertSee('Produtos da loja')
            ->assertSee('CAM-001')
            ->assertSee('1 variação')
            ->assertSee('Estoque baixo')
            ->assertSee(route('ad.edit', $product), false);
    }

    private function createStore(?User $owner = null, array $overrides = []): Store
    {
        $owner ??= User::factory()->create();

        return Store::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Loja Comercial',
            'slug' => 'loja-comercial-'.uniqid(),
            'description' => 'Loja para testar produtos.',
            'category' => 'Alimentação',
            'product_display_mode' => 'catalog',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
            'pickup_available' => true,
            'delivery_available' => true,
        ], $overrides));
    }

    private function createProduct(Store $store, array $overrides = []): Ad
    {
        return Ad::create(array_merge([
            'user_id' => $store->user_id,
            'store_id' => $store->id,
            'module' => 'products',
            'display_mode' => 'default',
            'advertiser_type' => 'Alimentação',
            'title' => 'Produto comercial',
            'slug' => 'produto-comercial-'.uniqid(),
            'description' => 'Produto usado nos testes comerciais.',
            'price' => 100,
            'stock_quantity' => 0,
            'track_stock' => false,
            'minimum_quantity' => 1,
            'city' => 'Aracaju',
            'state' => 'Sergipe',
            'status' => 'active',
        ], $overrides));
    }

    private function createOrderItem(Ad $product, string $status, int $quantity): void
    {
        $buyer = User::factory()->create();
        $order = Order::create([
            'public_id' => 'PED-'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $buyer->id,
            'store_id' => $product->store_id,
            'store_name' => $product->store->name,
            'status' => $status,
            'fulfillment_method' => 'pickup',
            'customer_name' => $buyer->name,
            'customer_phone' => '79999999999',
            'subtotal' => $product->effective_price * $quantity,
            'total' => $product->effective_price * $quantity,
            'placed_at' => now(),
        ]);
        $order->items()->create([
            'ad_id' => $product->id,
            'product_title' => $product->title,
            'unit_price' => $product->effective_price,
            'quantity' => $quantity,
            'line_total' => $product->effective_price * $quantity,
        ]);
    }
}
