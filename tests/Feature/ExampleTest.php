<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_carousels_have_working_navigation_configuration(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Arte & Cultura')
            ->assertSee('href="'.route('culture.index').'"', false)
            ->assertSee('Assine nossos planos e saia na frente.')
            ->assertSee('Conhecer planos')
            ->assertSee('aria-label="Ver banner anterior"', false)
            ->assertSee('aria-label="Ver próximo banner"', false)
            ->assertSee("navigation: { nextEl: '.home-hero-next', prevEl: '.home-hero-prev' }", false)
            ->assertSee("const swiperFeatured = new Swiper('.swiper-featured-ads'", false)
            ->assertSee('swiper-marquee-esteira', false)
            ->assertSee("const compact = el.classList.contains('swiper-category-compact')", false)
            ->assertSee('992: { slidesPerView: compact ? 3 : 4, spaceBetween: 14 }', false)
            ->assertSee('1200: { slidesPerView: compact ? 3 : 5, spaceBetween: 14 }', false);
    }

    public function test_home_has_the_dismissible_plans_card_and_fixed_save_buttons(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="home-hero-plans-card"', false)
            ->assertSee('Assine agora o Conectado em Sergipe')
            ->assertSee('Apareça em destaque, personalize seu perfil profissional e muito mais.')
            ->assertSee('Conheça nossos planos')
            ->assertSee('data-close-home-plans', false)
            ->assertDontSee('id="liveSupportLauncher"', false)
            ->assertDontSee('+ 50 mil usuários');
    }

    public function test_live_support_widget_does_not_appear_on_home_even_when_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="liveSupportLauncher"', false);
    }

    public function test_authenticated_home_includes_the_mobile_experience(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('home-authenticated', false)
            ->assertSee('aria-label="Navegação principal mobile"', false)
            ->assertSee('home-auth-mobile-providers', false)
            ->assertSee('Consultar');

        $this->app['auth']->logout();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('aria-label="Navegação principal mobile"', false)
            ->assertDontSee('home-provider-guest-mobile', false);
    }

    public function test_home_combines_custom_and_city_folder_banners(): void
    {
        Setting::set('home_banner_1', 'uploads/custom-home-banner.webp');

        $response = $this->get(route('home'))->assertOk();
        $heroBanners = $response->viewData('heroBanners');

        $this->assertContains('uploads/custom-home-banner.webp', $heroBanners);
        $this->assertContains('Cidades/Aracaju.webp', $heroBanners);
    }

    public function test_authentication_pages_use_the_city_folder_slideshow(): void
    {
        foreach (['login', 'register', 'password.request'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('class="auth-city-slideshow"', false)
                ->assertSee('Cidades/Aracaju.webp', false);
        }
    }
}
