<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationPreferenceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_preference_can_be_enabled_and_disabled(): void
    {
        $this->postJson(route('location.store'), [
            'city' => 'Nossa Senhora da Glória',
        ])
            ->assertOk()
            ->assertJson([
                'active' => true,
                'city' => 'Nossa Senhora da Glória',
            ])
            ->assertSessionHas('location_filter', [
                'enabled' => true,
                'city' => 'Nossa Senhora da Glória',
            ]);

        $this->deleteJson(route('location.destroy'))
            ->assertOk()
            ->assertJson(['active' => false])
            ->assertSessionMissing('location_filter');
    }

    public function test_active_location_filters_home_and_module_results(): void
    {
        $owner = User::factory()->create();
        $this->createAd($owner, 'products', 'Produto de Glória', 'produto-gloria', 'Nossa Senhora da Glória');
        $this->createAd($owner, 'products', 'Produto de Aracaju', 'produto-aracaju', 'Aracaju');
        $this->createAd($owner, 'services', 'Serviço de Glória', 'servico-gloria', 'Nossa Senhora da Glória');
        $this->createAd($owner, 'services', 'Serviço de Aracaju', 'servico-aracaju', 'Aracaju');
        $this->createStore($owner, 'Loja de Glória', 'home-loja-gloria', 'Nossa Senhora da Glória');
        $this->createStore($owner, 'Loja de Aracaju', 'home-loja-aracaju', 'Aracaju');

        $session = [
            'location_filter' => [
                'enabled' => true,
                'city' => 'Nossa Senhora da Glória',
            ],
        ];

        $this->withSession($session)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Produto de Glória')
            ->assertSee('Serviço de Glória')
            ->assertSee('Loja de Glória')
            ->assertDontSee('Produto de Aracaju')
            ->assertDontSee('Serviço de Aracaju')
            ->assertDontSee('Loja de Aracaju')
            ->assertDontSee('Resultados da Pesquisa')
            ->assertSee('Desativar localização')
            ->assertSee('Nossa Senhora da Glória');

        $this->withSession($session)
            ->get(route('module.products'))
            ->assertOk()
            ->assertSee('Produto de Glória')
            ->assertDontSee('Produto de Aracaju');

        $this->withSession($session)
            ->get(route('module.services'))
            ->assertOk()
            ->assertSee('Serviço de Glória')
            ->assertDontSee('Serviço de Aracaju');
    }

    public function test_active_location_filters_store_directory_and_its_counts(): void
    {
        $gloriaOwner = User::factory()->create(['city' => 'Nossa Senhora da Glória']);
        $aracajuOwner = User::factory()->create(['city' => 'Aracaju']);
        $gloriaStore = $this->createStore($gloriaOwner, 'Loja de Glória', 'loja-gloria', 'Nossa Senhora da Glória');
        $aracajuStore = $this->createStore($aracajuOwner, 'Loja de Aracaju', 'loja-aracaju', 'Aracaju');
        $this->createAd($gloriaOwner, 'products', 'Item de Glória', 'item-gloria', 'Nossa Senhora da Glória', $gloriaStore);
        $this->createAd($aracajuOwner, 'products', 'Item de Aracaju', 'item-aracaju', 'Aracaju', $aracajuStore);

        $this->withSession([
            'location_filter' => [
                'enabled' => true,
                'city' => 'Nossa Senhora da Glória',
            ],
        ])
            ->get(route('stores.index'))
            ->assertOk()
            ->assertSee('Loja de Glória')
            ->assertDontSee('Loja de Aracaju')
            ->assertSee('1 loja')
            ->assertSee('1 produto');
    }

    public function test_manual_city_choice_replaces_the_active_location_filter(): void
    {
        $owner = User::factory()->create();
        $this->createAd($owner, 'products', 'Produto de Aracaju', 'manual-aracaju', 'Aracaju');
        $this->createAd($owner, 'products', 'Produto de Glória', 'manual-gloria', 'Nossa Senhora da Glória');

        $this->withSession([
            'location_filter' => [
                'enabled' => true,
                'city' => 'Nossa Senhora da Glória',
            ],
        ])
            ->get(route('module.products', ['city' => 'Aracaju']))
            ->assertOk()
            ->assertSee('Produto de Aracaju')
            ->assertDontSee('Produto de Glória')
            ->assertSessionMissing('location_filter');
    }

    public function test_disabled_location_restores_unfiltered_results(): void
    {
        $owner = User::factory()->create();
        $this->createAd($owner, 'products', 'Produto de Aracaju', 'normal-aracaju', 'Aracaju');
        $this->createAd($owner, 'products', 'Produto de Glória', 'normal-gloria', 'Nossa Senhora da Glória');

        $this->withSession([
            'location_filter' => [
                'enabled' => true,
                'city' => 'Nossa Senhora da Glória',
            ],
        ])->deleteJson(route('location.destroy'))->assertOk();

        $this->get(route('module.products'))
            ->assertOk()
            ->assertSee('Produto de Aracaju')
            ->assertSee('Produto de Glória');
    }

    private function createAd(
        User $owner,
        string $module,
        string $title,
        string $slug,
        string $city,
        ?Store $store = null
    ): Ad {
        return Ad::create([
            'user_id' => $owner->id,
            'store_id' => $store?->id,
            'module' => $module,
            'title' => $title,
            'slug' => $slug,
            'description' => $title.' em Sergipe.',
            'advertiser_type' => $module === 'services' ? 'Eletricista' : 'Produtos',
            'price' => 100,
            'city' => $city,
            'status' => 'active',
        ]);
    }

    private function createStore(User $owner, string $name, string $slug, string $city): Store
    {
        return Store::create([
            'user_id' => $owner->id,
            'name' => $name,
            'slug' => $slug,
            'city' => $city,
            'active' => true,
        ]);
    }
}
