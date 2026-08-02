<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFeaturedFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
    }

    public function test_only_an_eligible_gold_store_can_receive_featured_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $freeOwner = User::factory()->create(['subscription_plan' => 'free']);
        $freeStore = $this->createStore($freeOwner, 'Loja Gratuita');

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $freeStore), [
                'action' => 'feature',
                'featured_days' => 30,
            ])
            ->assertSessionHas('error');

        $this->assertFalse($freeStore->fresh()->featured);

        $goldOwner = User::factory()->create(['subscription_plan' => 'gold']);
        $goldStore = $this->createStore($goldOwner, 'Loja Ouro');

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $goldStore), [
                'action' => 'feature',
                'featured_days' => 15,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $goldStore->refresh();
        $this->assertTrue($goldStore->featured);
        $this->assertTrue($goldStore->featured_until->isAfter(now()->addDays(14)));
        $this->assertTrue($goldStore->isCurrentlyFeatured());
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $goldOwner->id,
            'kind' => 'store_moderation',
        ]);
    }

    public function test_featured_store_appears_first_and_receives_a_public_badge(): void
    {
        $goldOwner = User::factory()->create(['subscription_plan' => 'gold']);
        $featuredStore = $this->createStore($goldOwner, 'Loja Ouro em Destaque', [
            'featured' => true,
            'featured_until' => now()->addDays(10),
            'created_at' => now()->subDay(),
        ]);
        $regularStore = $this->createStore(
            User::factory()->create(['subscription_plan' => 'gold']),
            'Loja Ouro Comum'
        );

        $this->get(route('stores.index'))
            ->assertOk()
            ->assertSeeInOrder([$featuredStore->name, $regularStore->name])
            ->assertSee('store-directory-card is-featured', false)
            ->assertSee('Em destaque');

        $this->get(route('store.show', $featuredStore->slug))
            ->assertOk()
            ->assertSee('Loja em destaque');
    }

    public function test_expired_featured_period_is_not_presented_as_active(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'gold']);
        $store = $this->createStore($owner, 'Loja com Destaque Expirado', [
            'featured' => true,
            'featured_until' => now()->subMinute(),
        ]);

        $this->assertFalse($store->fresh()->isCurrentlyFeatured());

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertDontSee('Loja em destaque');
    }

    public function test_suspending_a_store_also_removes_its_featured_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['subscription_plan' => 'gold']);
        $store = $this->createStore($owner, 'Loja Destacada Suspensa', [
            'featured' => true,
            'featured_until' => now()->addMonth(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stores.action', $store), [
                'action' => 'suspend',
                'moderation_note' => 'Suspensão usada para validar a remoção do destaque.',
            ])
            ->assertSessionHasNoErrors();

        $store->refresh();
        $this->assertFalse($store->active);
        $this->assertFalse($store->featured);
        $this->assertNull($store->featured_until);
    }

    public function test_admin_can_filter_featured_stores(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['subscription_plan' => 'gold']);
        $featuredStore = $this->createStore($owner, 'Loja Filtrada em Destaque', [
            'featured' => true,
            'featured_until' => now()->addWeek(),
        ]);
        $regularStore = $this->createStore($owner, 'Loja Fora do Filtro');

        $this->actingAs($admin)
            ->get(route('admin.stores', ['featured' => 'yes']))
            ->assertOk()
            ->assertSee($featuredStore->name)
            ->assertDontSee($regularStore->name)
            ->assertSee('name="featured"', false);
    }

    private function createStore(User $owner, string $name, array $overrides = []): Store
    {
        return Store::create(array_merge([
            'user_id' => $owner->id,
            'name' => $name,
            'slug' => str($name)->slug() . '-' . uniqid(),
            'description' => 'Loja criada para validar o recurso de destaque.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ], $overrides));
    }
}
