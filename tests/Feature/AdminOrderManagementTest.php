<?php

namespace Tests\Feature;

use App\Mail\OrderStatusMail;
use App\Models\Ad;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_management_is_restricted_to_administrators(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.orders.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.orders.index'))
            ->assertOk();
    }

    public function test_administrator_can_list_filter_search_and_open_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['name' => 'Cliente Sergipano']);
        $store = $this->createStore(User::factory()->create(), 'Loja Central');
        $pending = $this->createOrder($buyer, $store, 'PED-ADMIN-PENDENTE');
        $completed = $this->createOrder($buyer, $store, 'PED-ADMIN-CONCLUIDO', 'completed');

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($pending->public_id)
            ->assertSee($completed->public_id)
            ->assertSee('Cliente Sergipano')
            ->assertSee('Loja Central');

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pending->public_id)
            ->assertDontSee($completed->public_id);

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['q' => 'CONCLUIDO']))
            ->assertOk()
            ->assertSee($completed->public_id)
            ->assertDontSee($pending->public_id);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $pending))
            ->assertOk()
            ->assertSee($pending->public_id)
            ->assertSee('Produto administrativo')
            ->assertSee(route('admin.orders.status', $pending), false);
    }

    public function test_administrator_status_change_updates_stock_and_notifies_both_sides(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $buyer = User::factory()->create();
        $store = $this->createStore($owner);
        $product = $this->createProduct($store, 5);
        $order = $this->createOrder($buyer, $store, 'PED-ADMIN-ESTOQUE', 'pending', $product, 2);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->stock_deducted_at);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $buyer->id,
            'kind' => 'order_status',
        ]);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'order_admin_status',
        ]);
        Mail::assertSent(OrderStatusMail::class, 1);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->stock_restored_at);
        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_administrator_cannot_skip_order_workflow_steps(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createOrder(
            User::factory()->create(),
            $this->createStore(User::factory()->create()),
            'PED-ADMIN-INVALIDO'
        );

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
        Mail::assertNothingSent();
    }

    private function createStore(User $owner, string $name = 'Loja Pedido Admin'): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => $name,
            'slug' => 'loja-pedido-admin-'.uniqid(),
            'description' => 'Loja criada para validar a gestão administrativa de pedidos.',
            'category' => 'Artigos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createProduct(Store $store, int $stock): Ad
    {
        return Ad::create([
            'user_id' => $store->user_id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Artigos',
            'title' => 'Produto administrativo',
            'slug' => 'produto-administrativo-'.uniqid(),
            'description' => 'Produto com estoque controlado.',
            'price' => 100,
            'stock_quantity' => $stock,
            'track_stock' => true,
            'allow_backorders' => false,
            'city' => 'Aracaju',
            'state' => 'SE',
            'status' => 'active',
        ]);
    }

    private function createOrder(
        User $buyer,
        Store $store,
        string $publicId,
        string $status = 'pending',
        ?Ad $product = null,
        int $quantity = 1
    ): Order {
        $order = Order::create([
            'public_id' => $publicId,
            'user_id' => $buyer->id,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'status' => $status,
            'fulfillment_method' => 'pickup',
            'customer_name' => $buyer->name,
            'customer_phone' => '79999999999',
            'customer_email' => $buyer->email,
            'subtotal' => 100 * $quantity,
            'total' => 100 * $quantity,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'ad_id' => $product?->id,
            'product_title' => 'Produto administrativo',
            'unit_price' => 100,
            'quantity' => $quantity,
            'line_total' => 100 * $quantity,
        ]);

        return $order;
    }
}
