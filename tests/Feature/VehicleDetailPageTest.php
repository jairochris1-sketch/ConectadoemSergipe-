<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_uses_exclusive_gallery_and_summary_layout(): void
    {
        $user = User::factory()->create([
            'name' => 'Anunciante de teste',
            'whatsapp' => '79999999999',
        ]);

        $vehicle = Ad::create([
            'user_id' => $user->id,
            'module' => 'vehicles',
            'advertiser_type' => 'Carros Usados/Seminovos',
            'title' => 'Toyota Corolla de teste',
            'slug' => 'toyota-corolla-de-teste',
            'description' => 'Veículo conservado com documentação em dia.',
            'price' => 75900,
            'city' => 'Aracaju',
            'status' => 'active',
            'views' => 0,
        ]);

        AdImage::create([
            'ad_id' => $vehicle->id,
            'image_path' => 'images/veiculo-principal.webp',
            'is_main' => true,
        ]);
        AdImage::create([
            'ad_id' => $vehicle->id,
            'image_path' => 'images/veiculo-interior.webp',
            'is_main' => false,
        ]);

        $this->get(route('ad.show', $vehicle->slug))
            ->assertOk()
            ->assertViewIs('ads.show-vehicle')
            ->assertSee('vehicle-gallery', false)
            ->assertSee('vehicle-main-photo', false)
            ->assertSee('vehicle-thumbnail-grid', false)
            ->assertSee('vehicle-summary-card', false)
            ->assertSee('vehicle-detail-layout', false)
            ->assertSee('max-width: 1580px', false)
            ->assertSee('minmax(0, 1175px) minmax(340px, 370px)', false)
            ->assertSee('aspect-ratio: 2.02 / 1', false)
            ->assertSee('vehicleGalleryModal', false)
            ->assertSee('openVehicleLightbox', false)
            ->assertSee('changeVehicleModalImage', false)
            ->assertSee('Compartilhar anúncio')
            ->assertSee('data-social-share', false)
            ->assertDontSee('shareVehicleAd', false)
            ->assertSee('<meta property="og:title" content="Toyota Corolla de teste - Veículos - Conectado em Sergipe">', false)
            ->assertSee('<meta property="og:url" content="'.route('ad.show', $vehicle->slug).'">', false)
            ->assertSee('<meta property="og:image" content="'.asset('images/veiculo-principal.webp').'">', false)
            ->assertSee('Sobre este veículo')
            ->assertSee('R$ 75.900,00')
            ->assertSee('Chamar no WhatsApp')
            ->assertSee('1 / 2')
            ->assertDontSee('Atendimento rápido e seguro');
    }

    public function test_non_vehicle_keeps_regular_ad_layout(): void
    {
        $user = User::factory()->create();
        $product = Ad::create([
            'user_id' => $user->id,
            'module' => 'products',
            'advertiser_type' => 'Informática',
            'title' => 'Produto de teste',
            'slug' => 'produto-layout-regular',
            'description' => 'Descrição do produto.',
            'price' => 100,
            'city' => 'Aracaju',
            'status' => 'active',
            'views' => 0,
        ]);

        $this->get(route('ad.show', $product->slug))
            ->assertOk()
            ->assertViewIs('ads.show')
            ->assertSee('<meta property="og:title" content="Produto de teste - Conectado em Sergipe">', false)
            ->assertSee('<meta property="og:url" content="'.route('ad.show', $product->slug).'">', false)
            ->assertSee('<meta property="og:image" content="'.asset('images/logo-hero.png').'">', false)
            ->assertDontSee('vehicle-summary-card', false);
    }
}
