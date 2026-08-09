<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use App\Support\CityImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_directory_lists_all_active_categories(): void
    {
        $owner = User::factory()->create();
        $category = Category::create([
            'name' => 'Técnico em Eletrônica',
            'slug' => 'tecnico-em-eletronica',
            'icon' => 'fa-plug',
            'active' => true,
        ]);
        $categoryWithoutProvider = Category::create([
            'name' => 'Categoria sem prestador',
            'slug' => 'categoria-sem-prestador',
            'icon' => 'fa-tag',
            'active' => true,
        ]);
        $inactiveCategory = Category::create([
            'name' => 'Categoria desativada',
            'slug' => 'categoria-desativada',
            'icon' => 'fa-tag',
            'active' => false,
        ]);

        Ad::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'module' => 'services',
            'advertiser_type' => $category->name,
            'title' => 'Conserto de eletrônicos',
            'slug' => 'conserto-de-eletronicos',
            'description' => 'Manutenção especializada.',
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $this->get(route('module.services'))
            ->assertOk()
            ->assertSee('<option value="Técnico em Eletrônica"', false)
            ->assertSee('<option value="Categoria sem prestador"', false)
            ->assertDontSee($inactiveCategory->name);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<option value="service:Técnico em Eletrônica">', false)
            ->assertSee('<option value="service:Categoria sem prestador">', false)
            ->assertDontSee($inactiveCategory->name);

        $this->get(route('module.services', ['category' => $category->name]))
            ->assertOk()
            ->assertSee('Conserto de eletrônicos');
    }

    public function test_service_provider_appears_in_directory_and_homepage(): void
    {
        $provider = $this->createAd('services', 'Eletricista de teste');

        $this->get('/servicos')
            ->assertOk()
            ->assertSee('Perfis profissionais')
            ->assertSee($provider->title)
            ->assertSee($provider->description)
            ->assertSee('services-results-layout', false)
            ->assertSee('services-directory-sidebar', false)
            ->assertSee('services-directory-grid', false)
            ->assertSee('services-directory-card', false)
            ->assertSee('services-directory-avatar', false)
            ->assertSee('services-directory-location', false)
            ->assertSee('Aplicar filtros')
            ->assertSee('WhatsApp')
            ->assertSee(route('provider.show', $provider->slug));

        $this->get('/')
            ->assertOk()
            ->assertSee('navbar-mobile-brand-title', false)
            ->assertSee('Conectado em Sergipe')
            ->assertSee('marketplace-header-guest', false)
            ->assertSee('marketplace-guest-login', false)
            ->assertSee('marketplace-mobile-announce', false)
            ->assertSee('Prestadores de Serviços')
            ->assertSee($provider->title)
            ->assertSee('Em destaque')
            ->assertSee('Eletricista')
            ->assertSee('d-lg-none', false)
            ->assertSee('col-6 col-md-6 col-xl-3', false)
            ->assertSee('provider-card-avatar', false)
            ->assertDontSee($provider->description)
            ->assertSee(route('provider.show', $provider->slug));
    }

    public function test_paid_provider_is_prioritized_in_home_highlights_and_free_profiles_fill_remaining_slots(): void
    {
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $paidOwner = User::factory()->create(['subscription_plan' => 'start']);
        $paidProvider = $this->createAdForUser($paidOwner, 'Prestador pago em destaque');
        $paidProvider->update(['created_at' => now()->subMonth()]);

        $mostViewedFreeProvider = null;
        foreach (range(1, 9) as $index) {
            $freeOwner = User::factory()->create(['subscription_plan' => 'free']);
            $freeProvider = $this->createAdForUser($freeOwner, "Prestador gratuito {$index}");
            $freeProvider->update([
                'created_at' => now()->subMinutes($index),
                'views' => $index === 1 ? 500 : $index,
            ]);
            $mostViewedFreeProvider ??= $freeProvider;
        }

        foreach (range(1, 8) as $index) {
            $generalAd = $this->createAd('products', "Produto procurado {$index}");
            $generalAd->update(['views' => 100 - $index]);
        }
        $adminOwner = User::factory()->create([
            'role' => 'admin',
            'subscription_plan' => 'free',
        ]);
        $adminProvider = $this->createAdForUser($adminOwner, 'Perfil criado pelo administrador');
        $adminProvider->update(['views' => 900]);

        $response = $this->get(route('home'))->assertOk();
        $providers = $response->viewData('serviceProviders');

        $this->assertCount(8, $providers);
        $this->assertSame($paidProvider->id, $providers->first()->id);
        $this->assertTrue((bool) $providers->first()->is_plan_featured);
        $response->assertSee('Destaque do plano pago');
        $featuredForYou = $response->viewData('featuredForYou');
        $this->assertTrue($featuredForYou->contains('id', $paidProvider->id));
        $this->assertTrue($featuredForYou->contains('id', $mostViewedFreeProvider->id));
        $this->assertLessThanOrEqual(3, $featuredForYou->where('module', 'services')->count());
        $this->assertFalse((bool) $featuredForYou->firstWhere('id', $adminProvider->id)->is_plan_featured);
        $response
            ->assertSee('Prestador pago')
            ->assertSee('Mais procurado')
            ->assertSee(route('provider.show', $paidProvider->slug));
        $this->assertDatabaseHas('plan_feature_values', [
            'plan_id' => \App\Models\Plan::where('slug', 'free')->value('id'),
            'plan_feature_id' => \App\Models\PlanFeature::where('key', 'provider_featured')->value('id'),
            'value' => '0',
        ]);
        $this->assertDatabaseHas('plan_feature_values', [
            'plan_id' => \App\Models\Plan::where('slug', 'start')->value('id'),
            'plan_feature_id' => \App\Models\PlanFeature::where('key', 'provider_featured')->value('id'),
            'value' => '1',
        ]);
    }

    public function test_service_provider_has_a_professional_page_and_old_url_redirects(): void
    {
        $provider = $this->createAd('services', 'Encanador de teste');
        $provider->update(['instagram' => '@encanador.teste']);
        $cityImage = CityImage::for($provider->city);

        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertSee('Perfil reivindicado')
            ->assertSee('provider-profile-header', false)
            ->assertSee('provider-profile-overview', false)
            ->assertSee('max-width: 1080px !important;', false)
            ->assertSee('Mensagens')
            ->assertSee('Conheça o trabalho.')
            ->assertSee('Horários não informados')
            ->assertSee('https://instagram.com/encanador.teste', false)
            ->assertSee('Instagram')
            ->assertSee('Compartilhar')
            ->assertSee('data-social-share', false)
            ->assertSee('<meta property="og:title" content="Encanador de teste - Prestador de Serviços em Sergipe">', false)
            ->assertSee('<meta property="og:url" content="'.route('provider.show', $provider->slug).'">', false)
            ->assertSee('<meta property="og:image" content="'.asset($cityImage).'">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee($cityImage, false)
            ->assertSee('background: var(--background);', false)
            ->assertSee('background: var(--card);', false)
            ->assertSee('grid-template-columns: minmax(180px, 199px)', false)
            ->assertSee('.provider-profile-lower .reviews-summary-card', false)
            ->assertDontSee('08:00')
            ->assertDontSee('R$');

        $this->get(route('ad.show', $provider->slug))
            ->assertRedirect(route('provider.show', $provider->slug));
    }

    public function test_related_professionals_are_shown_only_on_free_provider_profiles(): void
    {
        $this->seed(\Database\Seeders\PlansSeeder::class);

        $relatedOwner = User::factory()->create(['subscription_plan' => 'free']);
        $relatedProvider = $this->createAdForUser($relatedOwner, 'Profissional relacionado');

        $freeOwner = User::factory()->create(['subscription_plan' => 'free']);
        $freeProvider = $this->createAdForUser($freeOwner, 'Perfil gratuito');

        $this->get(route('provider.show', $freeProvider->slug))
            ->assertOk()
            ->assertSee('Outros profissionais em Nossa Senhora da Glória')
            ->assertSee($relatedProvider->title);

        $paidOwner = User::factory()->create(['subscription_plan' => 'start']);
        $paidProvider = $this->createAdForUser($paidOwner, 'Perfil do plano de vinte e cinco reais');
        $paidStore = Store::create([
            'user_id' => $paidOwner->id,
            'name' => 'Loja exclusiva do profissional',
            'slug' => 'loja-exclusiva-do-profissional',
            'description' => 'Produtos do mesmo titular do perfil profissional.',
            'category' => 'Comércio local',
            'city' => 'Nossa Senhora da Glória',
            'state' => 'SE',
            'active' => true,
            'moderation_status' => 'approved',
        ]);

        $this->get(route('provider.show', $paidProvider->slug))
            ->assertOk()
            ->assertDontSee('Outros profissionais em Nossa Senhora da Glória')
            ->assertDontSee($relatedProvider->title)
            ->assertSee('Vitrine do profissional')
            ->assertSee($paidStore->name)
            ->assertSee(route('store.show', $paidStore->slug), false);
    }

    public function test_service_provider_portfolio_uses_airbnb_gallery_without_duplicate_images(): void
    {
        $provider = $this->createAd('services', 'Marceneiro com galeria');

        foreach (range(1, 5) as $index) {
            AdImage::create([
                'ad_id' => $provider->id,
                'image_path' => "uploads/portfolio-{$index}.webp",
                'is_main' => $index === 1,
            ]);
        }

        $response = $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertSeeInOrder(['Conheça o trabalho.', 'Trabalhos e portfólio'])
            ->assertSee('provider-airbnb-gallery', false)
            ->assertSee('provider-airbnb-main', false)
            ->assertSee('provider-airbnb-thumbnails', false)
            ->assertSee('provider-profile-lower-frame', false)
            ->assertSee('data-gallery-thumb-index="0"', false)
            ->assertSee('Ver todas as fotos')
            ->assertSee('object-fit: contain;', false)
            ->assertSee('background-image: var(--portfolio-bg);', false)
            ->assertSee("mainButton.style.setProperty('--portfolio-bg'", false)
            ->assertDontSee('.provider-airbnb-main:hover img', false)
            ->assertSee('setInterval(() => setMain(currentIndex + 1), 8000)', false);

        foreach (range(1, 5) as $index) {
            $response->assertSee("uploads/portfolio-{$index}.webp", false);
        }

        $product = $this->createAd('products', 'Produto sem galeria Airbnb');
        $this->get(route('ad.show', $product->slug))
            ->assertOk()
            ->assertDontSee('provider-airbnb-gallery', false);
    }

    public function test_service_provider_card_clicks_profile_and_button_opens_whatsapp(): void
    {
        $provider = $this->createAd('services', 'Pintor de teste');
        $cityImage = CityImage::for($provider->city);

        $this->get('/servicos')
            ->assertOk()
            ->assertSee(route('provider.show', $provider->slug))
            ->assertSee('https://wa.me/5579999999999', false)
            ->assertSee('WhatsApp')
            ->assertSee($cityImage, false)
            ->assertDontSee('Ver perfil profissional');
    }

    public function test_service_directory_filters_and_displays_approved_review_summary(): void
    {
        $electrician = $this->createAd('services', 'Eletricista filtrado');
        $painter = $this->createAd('services', 'Pintor fora do resultado');
        $painter->update([
            'advertiser_type' => 'Pintor',
            'city' => 'Aracaju',
        ]);

        Review::create([
            'ad_id' => $electrician->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => 'Atendimento excelente para validar o resumo no card.',
            'content_hash' => hash('sha256', 'resumo-card-prestador'),
            'status' => 'approved',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'abuse_fingerprint' => hash('sha256', 'feature-test-directory'),
        ]);

        $this->get(route('module.services', [
            'category' => 'Eletricista',
            'city' => 'Nossa Senhora da Glória',
        ]))
            ->assertOk()
            ->assertSee($electrician->title)
            ->assertDontSee($painter->title)
            ->assertSee('5,0')
            ->assertSee('(1 avaliação)')
            ->assertSee('services-directory-filter', false);
    }

    public function test_service_directory_paginates_twenty_one_profiles_per_page(): void
    {
        foreach (range(1, 22) as $index) {
            $this->createAd('services', "Prestador paginado {$index}");
        }

        $this->get(route('module.services'))
            ->assertOk()
            ->assertViewHas('providers', function ($providers) {
                return $providers->perPage() === 21
                    && $providers->count() === 21
                    && $providers->total() === 22;
            })
            ->assertSee('Mostrando 1–21 de 22 resultado(s)')
            ->assertSee('services-directory-pagination', false);

        $this->get(route('module.services', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('providers', fn ($providers) => $providers->count() === 1)
            ->assertSee('Mostrando 22–22 de 22 resultado(s)');
    }

    public function test_professional_profile_call_to_action_changes_with_the_account_state(): void
    {
        $this->get(route('module.services'))
            ->assertOk()
            ->assertSee('Criar meu perfil profissional');

        $freeUser = User::factory()->create(['subscription_plan' => 'free']);
        $freeProfile = $this->createAdForUser($freeUser, 'Perfil gratuito existente');

        $this->actingAs($freeUser)
            ->get(route('module.services'))
            ->assertOk()
            ->assertSee('Gerenciar meu perfil')
            ->assertSee(route('ad.edit', $freeProfile->id))
            ->assertDontSee('Criar meu perfil profissional');

        $paidUser = User::factory()->create(['subscription_plan' => 'pro']);
        $this->createAdForUser($paidUser, 'Primeiro perfil do plano pago');

        $this->actingAs($paidUser)
            ->get(route('module.services'))
            ->assertOk()
            ->assertSee('Criar outro perfil')
            ->assertSee(route('ad.create', ['module' => 'services']));
    }

    public function test_free_user_cannot_create_a_second_professional_profile_by_url_or_post(): void
    {
        $user = User::factory()->create([
            'subscription_plan' => 'free',
            'whatsapp' => '79999999999',
        ]);
        $this->createAdForUser($user, 'Perfil profissional existente');

        $message = 'Você já possui 1 perfil profissional, que é o limite do plano gratuito. Assine um plano para cadastrar outro perfil.';

        $this->actingAs($user)
            ->get(route('ad.create', ['module' => 'services']))
            ->assertRedirect(route('module.services'))
            ->assertSessionHas('professional_profile_limit', $message);

        $this->actingAs($user)
            ->post(route('ad.store'), [
                'module' => 'services',
                'category_name' => 'Eletricista',
                'title' => 'Segundo perfil bloqueado',
                'city' => 'Aracaju',
                'description' => 'Este segundo perfil não deve ser salvo no plano gratuito.',
                'whatsapp' => '79999999999',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('module.services'))
            ->assertSessionHas('professional_profile_limit', $message);

        $this->assertSame(1, $user->professionalProfiles()->count());
        $this->assertDatabaseMissing('ads', ['title' => 'Segundo perfil bloqueado']);
    }

    public function test_create_service_provider_form_accepts_optional_cover_without_preview(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '79999999999',
        ]);

        $this->actingAs($user)
            ->get(route('ad.create', ['module' => 'services']))
            ->assertOk()
            ->assertSee('Capa do perfil profissional')
            ->assertSee('name="banner"', false)
            ->assertSee('name="public_address"', false)
            ->assertSee('Endereço público do local de atendimento')
            ->assertSee('Se não enviar, será usada uma imagem da cidade escolhida.')
            ->assertDontSee('cover-photo-preview-box');
    }

    public function test_city_cover_uses_the_exact_city_image_name(): void
    {
        $this->assertSame(
            'Cidades/Nossa Senhora da Glória.webp',
            CityImage::for('Nossa Senhora da Glória')
        );
    }

    public function test_service_provider_can_edit_cover_later(): void
    {
        $provider = $this->createAd('services', 'Gesseiro de teste');

        $this->actingAs($provider->user)
            ->get(route('ad.edit', $provider->id))
            ->assertOk()
            ->assertSee('Editar perfil profissional')
            ->assertSee('name="banner"', false)
            ->assertSee('name="instagram"', false)
            ->assertSee('name="facebook"', false)
            ->assertSee('Fotos do perfil')
            ->assertSee('Escolha outra capa para substituir a atual.');

        $this->actingAs($provider->user)
            ->put(route('ad.update', $provider->id), [
                'title' => $provider->title,
                'category_name' => $provider->display_category,
                'city' => $provider->city,
                'description' => $provider->description,
                'instagram' => 'https://instagram.com/gesseiro.teste',
                'facebook' => '@gesseiro.teste',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('provider.show', $provider->slug));

        $this->assertSame('https://instagram.com/gesseiro.teste', $provider->fresh()->instagram);
    }

    public function test_provider_can_publish_update_and_remove_a_public_service_address(): void
    {
        $provider = $this->createAd('services', 'Profissional com local fixo');

        $this->actingAs($provider->user)
            ->get(route('ad.edit', $provider->id))
            ->assertOk()
            ->assertSee('name="public_address"', false)
            ->assertSee('Endereço público do local de atendimento');

        $this->actingAs($provider->user)
            ->put(route('ad.update', $provider->id), [
                'title' => $provider->title,
                'category_name' => $provider->display_category,
                'city' => $provider->city,
                'description' => $provider->description,
                'public_address' => 'Avenida Principal, 45, Centro',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('provider.show', $provider->slug));

        $this->assertSame('Avenida Principal, 45, Centro', $provider->fresh()->public_address);
        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertSee('Local de atendimento')
            ->assertSee('Avenida Principal, 45, Centro')
            ->assertSee('Como chegar')
            ->assertSee('google.com/maps/dir', false);

        $this->actingAs($provider->user)
            ->put(route('ad.update', $provider->id), [
                'title' => $provider->title,
                'category_name' => $provider->display_category,
                'city' => $provider->city,
                'description' => $provider->description,
                'public_address' => '',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($provider->fresh()->public_address);
        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertDontSee('Local de atendimento')
            ->assertDontSee('Como chegar');
    }

    public function test_sales_ad_keeps_the_regular_ad_page(): void
    {
        $product = $this->createAd('products', 'Produto de teste');

        $this->get(route('ad.show', $product->slug))
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('R$');
    }

    private function createAd(string $module, string $title): Ad
    {
        $user = User::factory()->create([
            'phone' => '79999999999',
            'whatsapp' => '79999999999',
        ]);

        return Ad::create([
            'user_id' => $user->id,
            'module' => $module,
            'advertiser_type' => $module === 'services' ? 'Eletricista' : 'Produtos',
            'title' => $title,
            'slug' => str($title)->slug().'-'.$module,
            'description' => 'Descrição criada para verificar a exibição no site.',
            'price' => $module === 'services' ? 0 : 100,
            'city' => 'Nossa Senhora da Glória',
            'status' => 'active',
            'views' => 0,
        ]);
    }

    private function createAdForUser(User $user, string $title): Ad
    {
        return Ad::create([
            'user_id' => $user->id,
            'module' => 'services',
            'advertiser_type' => 'Eletricista',
            'title' => $title,
            'slug' => str($title)->slug().'-'.$user->id,
            'description' => 'Descrição criada para verificar as regras do perfil profissional.',
            'price' => 0,
            'city' => 'Nossa Senhora da Glória',
            'status' => 'active',
            'views' => 0,
        ]);
    }
}
