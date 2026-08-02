<?php

namespace Tests\Feature;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusMail;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Store;
use App\Models\StorePromotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CartCheckoutOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_build_a_single_store_cart_and_product_without_price_is_rejected(): void
    {
        $store = $this->createStore(User::factory()->create());
        $product = $this->createProduct($store);
        $withoutPrice = $this->createProduct($store, [
            'title' => 'Produto sem preço',
            'slug' => 'produto-sem-preco',
            'price' => null,
        ]);

        $this->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success');

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('300,00');

        $this->post(route('cart.add', $withoutPrice), ['quantity' => 1])
            ->assertSessionHasErrors('cart');
    }

    public function test_cart_rejects_products_from_another_store(): void
    {
        $first = $this->createProduct($this->createStore(User::factory()->create()));
        $second = $this->createProduct(
            $this->createStore(User::factory()->create()),
            ['title' => 'Produto de outra loja', 'slug' => 'produto-outra-loja']
        );

        $this->post(route('cart.add', $first));
        $this->post(route('cart.add', $second))
            ->assertSessionHasErrors('cart');

        $this->get(route('cart.index'))
            ->assertSee($first->title)
            ->assertDontSee($second->title);
    }

    public function test_authenticated_customer_places_order_with_price_snapshot_and_cart_is_cleared(): void
    {
        Mail::fake();
        $buyer = User::factory()->create([
            'phone' => '79999999999',
            'city' => 'Aracaju',
        ]);
        $store = $this->createStore(User::factory()->create());
        $product = $this->createProduct($store);

        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $this->actingAs($buyer)
            ->post(route('checkout.place'), [
                'customer_name' => 'Cliente Teste',
                'customer_phone' => '(79) 99999-9999',
                'customer_email' => 'cliente@example.com',
                'fulfillment_method' => 'delivery',
                'delivery_address' => 'Rua A, 100',
                'delivery_city' => 'Aracaju',
                'delivery_state' => 'se',
                'delivery_zipcode' => '49000-000',
                'notes' => 'Entregar à tarde.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertSame($buyer->id, $order->user_id);
        $this->assertSame($store->id, $order->store_id);
        $this->assertSame('pending', $order->status);
        $this->assertSame('300.00', $order->total);
        $this->assertSame('150.00', $order->items->first()->unit_price);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame('SE', $order->delivery_state);

        $product->update(['price' => 199]);
        $this->assertSame('150.00', $order->fresh()->items->first()->unit_price);
        $this->assertEmpty(session('store_cart', []));

        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->public_id)
            ->assertSee('Cliente Teste');
        Mail::assertSent(OrderPlacedMail::class, 2);
    }

    public function test_active_coupon_is_applied_and_snapshotted_in_order(): void
    {
        Mail::fake();
        $buyer = User::factory()->create();
        $store = $this->createStore(User::factory()->create());
        $product = $this->createProduct($store);
        $promotion = StorePromotion::create([
            'store_id' => $store->id,
            'title' => 'Dez por cento',
            'coupon_code' => 'SERGIPE10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'ends_at' => now()->addDay(),
            'active' => true,
        ]);

        $this->post(route('cart.add', $product));
        $this->post(route('checkout.coupon.apply'), ['coupon_code' => 'sergipe10'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->actingAs($buyer)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('SERGIPE10')
            ->assertSee('135,00');

        $this->post(route('checkout.place'), [
            'customer_name' => $buyer->name,
            'customer_phone' => '79999999999',
            'customer_email' => $buyer->email,
            'fulfillment_method' => 'pickup',
        ])->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame($promotion->id, $order->store_promotion_id);
        $this->assertSame('SERGIPE10', $order->coupon_code);
        $this->assertSame('15.00', $order->discount_total);
        $this->assertSame('135.00', $order->total);
    }

    public function test_invalid_or_expired_coupon_is_rejected(): void
    {
        $store = $this->createStore(User::factory()->create());
        $product = $this->createProduct($store);
        StorePromotion::create([
            'store_id' => $store->id,
            'title' => 'Cupom encerrado',
            'coupon_code' => 'EXPIRADO',
            'discount_type' => 'fixed',
            'discount_value' => 20,
            'ends_at' => now()->subMinute(),
            'active' => true,
        ]);

        $this->post(route('cart.add', $product));
        $this->post(route('checkout.coupon.apply'), ['coupon_code' => 'EXPIRADO'])
            ->assertSessionHasErrors('coupon_code');
    }

    public function test_store_owner_can_process_order_and_other_user_cannot_access_it(): void
    {
        Mail::fake();
        $owner = User::factory()->create();
        $buyer = User::factory()->create();
        $store = $this->createStore($owner);
        $order = $this->createOrder($buyer, $store);

        $this->actingAs(User::factory()->create())
            ->get(route('seller.orders.show', [$store, $order]))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('seller.orders.index', $store))
            ->assertOk()
            ->assertSee($order->public_id);

        foreach (['confirmed', 'preparing', 'ready', 'completed'] as $status) {
            $this->actingAs($owner)
                ->patch(route('seller.orders.status', [$store, $order]), ['status' => $status])
                ->assertSessionHasNoErrors();
            $this->assertSame($status, $order->fresh()->status);
        }
        Mail::assertSent(OrderStatusMail::class, 4);
    }

    public function test_buyer_can_cancel_only_a_pending_order(): void
    {
        $buyer = User::factory()->create();
        $order = $this->createOrder($buyer, $this->createStore(User::factory()->create()));

        $this->actingAs($buyer)
            ->patch(route('orders.cancel', $order))
            ->assertSessionHas('success');
        $this->assertSame('cancelled', $order->fresh()->status);

        $this->actingAs($buyer)
            ->patch(route('orders.cancel', $order))
            ->assertSessionHasErrors('order');
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Pedido',
            'slug' => 'loja-pedido-'.uniqid(),
            'description' => 'Loja criada para validar pedidos.',
            'category' => 'Artigos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createProduct(Store $store, array $overrides = []): Ad
    {
        return Ad::create(array_merge([
            'user_id' => $store->user_id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Artigos',
            'title' => 'Produto do carrinho',
            'slug' => 'produto-carrinho-'.uniqid(),
            'description' => 'Produto disponível para compra.',
            'price' => 150,
            'city' => 'Aracaju',
            'state' => 'SE',
            'status' => 'active',
        ], $overrides));
    }

    private function createOrder(User $buyer, Store $store): Order
    {
        $order = Order::create([
            'public_id' => 'PED-TESTE-'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $buyer->id,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'status' => 'pending',
            'fulfillment_method' => 'pickup',
            'customer_name' => $buyer->name,
            'customer_phone' => '79999999999',
            'customer_email' => $buyer->email,
            'subtotal' => 150,
            'total' => 150,
            'placed_at' => now(),
        ]);
        $order->items()->create([
            'product_title' => 'Produto preservado',
            'unit_price' => 150,
            'quantity' => 1,
            'line_total' => 150,
        ]);

        return $order;
    }
}
