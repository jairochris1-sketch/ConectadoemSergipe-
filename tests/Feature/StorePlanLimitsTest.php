<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePlanLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
    }

    public function test_free_plan_blocks_creating_a_store_in_the_backend(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'free']);

        $this->actingAs($user)
            ->get(route('store.create'))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHas('store_limit');

        $this->actingAs($user)
            ->post(route('store.store'), $this->validStoreData(1))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHasErrors('store');

        $this->assertSame(0, $user->stores()->count());
    }

    public function test_pro_plan_allows_one_store_and_blocks_the_second(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'pro']);
        $this->createStore($user, 1);

        $this->actingAs($user)
            ->get(route('store.create'))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHas('store_limit');

        $this->actingAs($user)
            ->post(route('store.store'), $this->validStoreData(2))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHasErrors('store');

        $this->assertSame(1, $user->stores()->count());
    }

    public function test_gold_plan_allows_three_stores_and_blocks_the_fourth(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'gold']);

        foreach (range(1, 3) as $number) {
            $this->actingAs($user)
                ->post(route('store.store'), $this->validStoreData($number))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(3, $user->stores()->count());

        $this->actingAs($user)
            ->post(route('store.store'), $this->validStoreData(4))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHasErrors('store');

        $this->assertSame(3, $user->stores()->count());
    }

    public function test_free_store_blocks_the_fourth_product_but_allows_it_outside_the_store(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'free']);
        $store = $this->createStore($user, 1);

        foreach (range(1, 3) as $number) {
            $this->createProduct($user, $store, $number);
        }

        $this->actingAs($user)
            ->post(route('ad.store'), $this->validProductData([
                'title' => 'Quarto produto bloqueado',
                'include_in_store' => '1',
                'store_id' => $store->id,
            ]))
            ->assertSessionHasErrors('store_id');

        $this->assertDatabaseMissing('ads', ['title' => 'Quarto produto bloqueado']);

        $this->actingAs($user)
            ->post(route('ad.store'), $this->validProductData([
                'title' => 'Produto publicado fora da loja',
                'include_in_store' => '0',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Produto publicado fora da loja',
            'store_id' => null,
        ]);
    }

    public function test_pro_store_accepts_products_without_a_numeric_limit(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($user, 1);

        foreach (range(1, 5) as $number) {
            $this->createProduct($user, $store, $number);
        }

        $this->actingAs($user)
            ->post(route('ad.store'), $this->validProductData([
                'title' => 'Sexto produto PRO',
                'include_in_store' => '1',
                'store_id' => $store->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Sexto produto PRO',
            'store_id' => $store->id,
        ]);
    }

    public function test_user_cannot_manage_another_users_store_by_the_specific_route(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $store = $this->createStore($owner, 1);

        $this->actingAs($intruder)
            ->get(route('store.manage', $store))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->put(route('store.update_specific', $store), $this->validStoreData(2))
            ->assertForbidden();
    }

    public function test_panel_shows_plan_usage_and_the_correct_store_action(): void
    {
        $freeUser = User::factory()->create(['subscription_plan' => 'free']);

        $this->actingAs($freeUser)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Plano Gratuito')
            ->assertSee('0 de 0')
            ->assertSee('Ver planos');

        $proUser = User::factory()->create(['subscription_plan' => 'pro']);
        $this->createStore($proUser, 1);

        $this->actingAs($proUser)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Plano PRO')
            ->assertSee('1 de 1')
            ->assertSee('Ver planos');

        $goldUser = User::factory()->create(['subscription_plan' => 'gold']);
        $this->createStore($goldUser, 1);

        $this->actingAs($goldUser)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Plano Premium')
            ->assertSee('Criar outra loja');
    }

    private function createStore(User $user, int $number): Store
    {
        return Store::create([
            'user_id' => $user->id,
            'name' => "Loja {$number}",
            'slug' => "loja-{$user->id}-{$number}",
            'description' => 'Loja usada para validar os limites dos planos.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ]);
    }

    private function createProduct(User $user, Store $store, int $number): Ad
    {
        return Ad::create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'module' => 'products',
            'advertiser_type' => 'Informática',
            'title' => "Produto {$number}",
            'slug' => "produto-{$user->id}-{$number}",
            'description' => 'Produto usado para preencher o limite da loja.',
            'price' => 100,
            'city' => 'Aracaju',
            'state' => 'Sergipe',
            'status' => 'active',
        ]);
    }

    private function validStoreData(int $number): array
    {
        return [
            'name' => "Loja cadastrada {$number}",
            'description' => 'Loja criada pelo fluxo real do controlador.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'phone' => '7933333333',
            'whatsapp' => '79999999999',
            'instagram' => '@lojateste',
            'website' => 'https://example.com',
        ];
    }

    private function validProductData(array $overrides = []): array
    {
        return array_merge([
            'module' => 'products',
            'category_name' => 'Informática',
            'title' => 'Produto do limite',
            'price' => '150,00',
            'city' => 'Aracaju',
            'description' => 'Produto usado para validar o limite comercial.',
            'whatsapp' => '79999999999',
        ], $overrides);
    }
}
