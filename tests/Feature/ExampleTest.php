<?php

namespace Tests\Feature;

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
            ->assertSee("const swiperFeatured = new Swiper('.swiper-featured-ads'", false)
            ->assertSee('speed: 650', false)
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
            ->assertSee('data-close-home-plans', false)
            ->assertSee('.card-premium > button[aria-label="Favoritar"]', false)
            ->assertDontSee('+ 50 mil usuários');
    }
}
