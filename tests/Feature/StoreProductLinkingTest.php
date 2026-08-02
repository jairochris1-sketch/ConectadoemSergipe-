<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreProductLinkingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_is_linked_automatically_when_user_has_one_active_store(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($user);

        $this->actingAs($user)
            ->post(route('ad.store'), $this->validProductData([
                'include_in_store' => '1',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product = Ad::where('title', 'Produto da loja')->firstOrFail();

        $this->assertSame($store->id, $product->store_id);
        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee($product->title);
        $this->actingAs($user)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee($store->name);
    }

    public function test_user_can_publish_product_without_including_it_in_store(): void
    {
        $user = User::factory()->create();
        $this->createStore($user);

        $this->actingAs($user)
            ->post(route('ad.store'), $this->validProductData([
                'include_in_store' => '0',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Produto da loja',
            'store_id' => null,
        ]);
    }

    public function test_user_cannot_link_product_to_another_users_store(): void
    {
        $user = User::factory()->create();
        $otherStore = $this->createStore(User::factory()->create());

        $this->actingAs($user)
            ->from(route('ad.create'))
            ->post(route('ad.store'), $this->validProductData([
                'include_in_store' => '1',
                'store_id' => $otherStore->id,
            ]))
            ->assertRedirect(route('ad.create'))
            ->assertSessionHasErrors('store_id');

        $this->assertDatabaseMissing('ads', ['title' => 'Produto da loja']);
    }

    public function test_owner_can_remove_product_from_store_while_editing(): void
    {
        $user = User::factory()->create();
        $store = $this->createStore($user);
        $product = Ad::create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Informática',
            'title' => 'Produto editável',
            'slug' => 'produto-editavel',
            'description' => 'Produto criado para validar a remoção da loja.',
            'price' => 100,
            'city' => 'Aracaju',
            'state' => 'Sergipe',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->put(route('ad.update', $product->id), [
                'title' => $product->title,
                'category_name' => 'Informática',
                'price' => '100,00',
                'city' => $product->city,
                'description' => $product->description,
                'include_in_store' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('ad.show', $product->slug));

        $this->assertNull($product->fresh()->store_id);
    }

    public function test_non_product_modules_ignore_store_link_fields(): void
    {
        $user = User::factory()->create();
        $otherStore = $this->createStore(User::factory()->create());

        $data = $this->validProductData([
            'module' => 'vehicles',
            'title' => 'Veículo sem loja',
            'include_in_store' => '1',
            'store_id' => $otherStore->id,
        ]);

        $this->actingAs($user)
            ->post(route('ad.store'), $data)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Veículo sem loja',
            'store_id' => null,
        ]);
    }

    private function validProductData(array $overrides = []): array
    {
        return array_merge([
            'module' => 'products',
            'category_name' => 'Informática',
            'title' => 'Produto da loja',
            'price' => '150,00',
            'city' => 'Aracaju',
            'description' => 'Produto usado para validar a integração com a loja.',
            'whatsapp' => '79999999999',
        ], $overrides);
    }

    private function createStore(User $user): Store
    {
        return Store::create([
            'user_id' => $user->id,
            'name' => 'Loja de Teste',
            'slug' => 'loja-de-teste-' . uniqid(),
            'description' => 'Loja usada nos testes de vínculo de produtos.',
            'category' => 'Eletrônicos',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
        ]);
    }
}
