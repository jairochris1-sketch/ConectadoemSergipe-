<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
    }

    public function test_store_directory_displays_active_stores_and_their_product_count(): void
    {
        $owner = User::factory()->create([
            'city' => 'Itabaiana',
        ]);
        $store = Store::create([
            'user_id' => $owner->id,
            'name' => 'Casa Sergipana',
            'slug' => 'casa-sergipana',
            'description' => 'Produtos para deixar sua casa mais bonita.',
            'active' => true,
        ]);
        Ad::create([
            'user_id' => $owner->id,
            'store_id' => $store->id,
            'module' => 'products',
            'title' => 'Mesa artesanal',
            'slug' => 'mesa-artesanal-loja',
            'description' => 'Mesa produzida por comércio local.',
            'advertiser_type' => 'Casa e Decoração',
            'price' => 850,
            'city' => 'Itabaiana',
            'status' => 'active',
        ]);

        $this->get(route('stores.index'))
            ->assertOk()
            ->assertSee('Lojas <span>on-line</span>', false)
            ->assertSee('Casa Sergipana')
            ->assertSee('1 produto')
            ->assertSee('data-social-share', false)
            ->assertSee(route('store.show', $store->slug), false);
    }

    public function test_store_directory_filters_by_city_and_category(): void
    {
        $matchingOwner = User::factory()->create([
            'city' => 'Aracaju',
        ]);
        $otherOwner = User::factory()->create([
            'city' => 'Lagarto',
        ]);

        $matchingStore = Store::create([
            'user_id' => $matchingOwner->id,
            'name' => 'Moda Aracaju',
            'slug' => 'moda-aracaju',
            'active' => true,
        ]);
        $otherStore = Store::create([
            'user_id' => $otherOwner->id,
            'name' => 'Beleza Lagarto',
            'slug' => 'beleza-lagarto',
            'active' => true,
        ]);
        Ad::create([
            'user_id' => $matchingOwner->id,
            'store_id' => $matchingStore->id,
            'module' => 'products',
            'advertiser_type' => 'Moda',
            'title' => 'Camisa sergipana',
            'slug' => 'camisa-sergipana-loja',
            'description' => 'Moda produzida em Sergipe.',
            'price' => 90,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
        Ad::create([
            'user_id' => $otherOwner->id,
            'store_id' => $otherStore->id,
            'module' => 'products',
            'advertiser_type' => 'Beleza',
            'title' => 'Produto de beleza',
            'slug' => 'produto-beleza-loja',
            'description' => 'Produto de beleza em Lagarto.',
            'price' => 45,
            'city' => 'Lagarto',
            'status' => 'active',
        ]);

        $this->get(route('stores.index', ['city' => 'Aracaju', 'category' => 'Moda']))
            ->assertOk()
            ->assertSee('Moda Aracaju')
            ->assertDontSee('Beleza Lagarto');
    }

    public function test_inactive_stores_are_not_exposed_in_the_directory(): void
    {
        $owner = User::factory()->create();
        Store::create([
            'user_id' => $owner->id,
            'name' => 'Loja Oculta',
            'slug' => 'loja-oculta',
            'active' => false,
        ]);

        $this->get(route('stores.index'))
            ->assertOk()
            ->assertDontSee('Loja Oculta');
    }
}
