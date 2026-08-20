<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFollowFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_sign_in_to_follow_a_store(): void
    {
        $store = $this->createStore(User::factory()->create());

        $this->post(route('store.follow.toggle', $store))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('store_follows', 0);
    }

    public function test_user_can_follow_and_unfollow_a_public_store(): void
    {
        $store = $this->createStore(User::factory()->create());
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->postJson(route('store.follow.toggle', $store))
            ->assertOk()
            ->assertJson([
                'following' => true,
                'followers_count' => 1,
            ]);

        $this->assertDatabaseHas('store_follows', [
            'user_id' => $visitor->id,
            'store_id' => $store->id,
        ]);

        $this->actingAs($visitor)
            ->postJson(route('store.follow.toggle', $store))
            ->assertOk()
            ->assertJson([
                'following' => false,
                'followers_count' => 0,
            ]);

        $this->assertDatabaseCount('store_follows', 0);
    }

    public function test_owner_cannot_follow_own_store_and_hidden_store_cannot_be_followed(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->postJson(route('store.follow.toggle', $store))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Você não pode seguir a própria loja.');

        $store->update(['active' => false]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('store.follow.toggle', $store))
            ->assertNotFound();

        $this->assertDatabaseCount('store_follows', 0);
    }

    public function test_follow_state_appears_in_directory_store_page_and_user_panel(): void
    {
        $owner = User::factory()->create();
        $store = $this->createStore($owner);
        $visitor = User::factory()->create();
        $visitor->followedStores()->attach($store->id);

        $this->actingAs($visitor)
            ->get(route('stores.index'))
            ->assertOk()
            ->assertSee('data-store-follow', false)
            ->assertSee('aria-pressed="true"', false);

        $this->actingAs($visitor)
            ->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Seguindo')
            ->assertSee('data-store-follow-count', false);

        $this->actingAs($visitor)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Lojas seguidas')
            ->assertSee($store->name)
            ->assertSee('Deixar de seguir');
    }

    public function test_empty_followed_stores_panel_uses_store_icon(): void
    {
        $visitor = User::factory()->create();

        $this->actingAs($visitor)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Você ainda não segue nenhuma loja')
            ->assertSee('fa-store user-followed-stores-empty-icon', false)
            ->assertDontSee('fa-heart user-followed-stores-empty-icon', false);
    }

    private function createStore(User $owner): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Favorita',
            'slug' => 'loja-favorita-' . uniqid(),
            'description' => 'Loja pública para acompanhar.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }
}
