<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStoreModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrator_can_access_store_moderation_routes(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->get(route('admin.stores'))
            ->assertForbidden();
        $this->actingAs($owner)
            ->get(route('admin.stores.show', $store))
            ->assertForbidden();
        $this->actingAs($owner)
            ->post(route('admin.stores.action', $store), ['action' => 'deactivate'])
            ->assertForbidden();
    }

    public function test_admin_listing_exposes_filters_metrics_and_store_indicators(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['name' => 'Proprietário Sergipano']);
        $store = $this->createStore($owner, [
            'name' => 'Loja Eletrônica Aracaju',
            'category' => 'Eletrônicos',
            'city' => 'Aracaju',
        ]);
        $this->createProduct($owner, $store);
        Review::create([
            'store_id' => $store->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'Excelente loja para compras em Sergipe.',
            'content_hash' => hash('sha256', 'admin-store-review'),
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.stores', [
                'q' => 'Eletrônica',
                'status' => 'active',
                'moderation' => 'approved',
                'category' => 'Eletrônicos',
                'city' => 'Aracaju',
            ]))
            ->assertOk()
            ->assertSee('Moderação de lojas')
            ->assertSee('Loja Eletrônica Aracaju')
            ->assertSee('Proprietário Sergipano')
            ->assertSee('1 produtos')
            ->assertSee('5,0')
            ->assertSee(route('admin.stores.show', $store), false)
            ->assertSee('name="moderation"', false);
    }

    public function test_admin_can_open_detailed_store_analysis(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['name' => 'Dono da Vitrine']);
        $store = $this->createStore($owner);
        $product = $this->createProduct($owner, $store);

        $this->actingAs($admin)
            ->get(route('admin.stores.show', $store))
            ->assertOk()
            ->assertSee('Análise da loja')
            ->assertSee('Dono da Vitrine')
            ->assertSee($product->title)
            ->assertSee('name="action"', false)
            ->assertSee('Suspender pela moderação')
            ->assertSee(route('admin.stores.action', $store), false);
    }

    public function test_suspension_requires_note_hides_store_and_notifies_owner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $store), [
                'action' => 'suspend',
            ])
            ->assertSessionHasErrors('moderation_note');

        $this->assertSame('approved', $store->fresh()->moderation_status);

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $store), [
                'action' => 'suspend',
                'moderation_note' => 'Cadastro suspenso para conferência dos dados comerciais.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $store->refresh();
        $this->assertSame('suspended', $store->moderation_status);
        $this->assertFalse($store->active);
        $this->assertSame($admin->id, $store->moderated_by);
        $this->assertSame('Cadastro suspenso para conferência dos dados comerciais.', $store->moderation_note);
        $this->assertNotNull($store->moderated_at);

        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'store_moderation',
            'action_url' => route('store.edit', [], false),
        ]);

        auth()->logout();
        $this->get(route('store.show', $store->slug))->assertNotFound();
    }

    public function test_suspended_store_cannot_be_reactivated_by_owner(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner, [
            'active' => false,
            'moderation_status' => 'suspended',
            'moderation_note' => 'Aguardando correção cadastral.',
        ]);

        $this->actingAs($owner)
            ->post(route('store.toggle_status'))
            ->assertSessionHas('store_warning');

        $this->assertFalse($store->fresh()->active);
        $this->actingAs($owner)
            ->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('oculta pela moderação')
            ->assertSee('Aguardando correção cadastral.');
    }

    public function test_admin_can_approve_and_restore_suspended_store(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $store = $this->createStore($owner, [
            'active' => false,
            'moderation_status' => 'suspended',
            'moderation_note' => 'Dados em análise.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $store), [
                'action' => 'approve',
                'moderation_note' => 'Cadastro conferido pela equipe.',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $store->refresh();
        $this->assertSame('approved', $store->moderation_status);
        $this->assertTrue($store->active);
        $this->assertSame('Cadastro conferido pela equipe.', $store->moderation_note);

        auth()->logout();
        $this->get(route('store.show', $store->slug))->assertOk();
        $this->get(route('stores.index'))->assertSee($store->name);
    }

    public function test_suspended_store_cannot_be_activated_without_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $store = $this->createStore(User::factory()->create(), [
            'active' => false,
            'moderation_status' => 'suspended',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $store), [
                'action' => 'activate',
            ])
            ->assertSessionHas('error');

        $this->assertFalse($store->fresh()->active);
        $this->assertSame('suspended', $store->fresh()->moderation_status);
    }

    private function createStore(User $owner, array $overrides = []): Store
    {
        return Store::create(array_merge([
            'user_id' => $owner->id,
            'name' => 'Loja Administrada',
            'slug' => 'loja-administrada-' . uniqid(),
            'description' => 'Loja criada para validar o painel administrativo.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ], $overrides));
    }

    private function createProduct(User $owner, Store $store): Ad
    {
        return Ad::create([
            'user_id' => $owner->id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Moda',
            'title' => 'Produto da loja administrada',
            'slug' => 'produto-loja-administrada-' . uniqid(),
            'description' => 'Produto usado na análise administrativa.',
            'price' => 100,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
    }
}
