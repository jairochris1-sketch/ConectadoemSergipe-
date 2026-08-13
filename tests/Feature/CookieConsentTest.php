<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_exposes_cookie_banner_preferences_and_reset_control(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="cookie-consent-banner"', false)
            ->assertSee('id="cookie-preferences-dialog"', false)
            ->assertSee('data-cookie-settings', false)
            ->assertSee('data-cookie-reject', false)
            ->assertSee('data-cookie-accept', false)
            ->assertSee('data-cookie-category-input="preferences"', false)
            ->assertSee('data-cookie-category-input="analytics"', false)
            ->assertSee('data-cookie-category-input="marketing"', false)
            ->assertSee('Redefinir Cookies')
            ->assertSee(asset('css/cookie-consent.css'), false)
            ->assertSee(asset('js/cookie-consent.js'), false);
    }

    public function test_cookie_controls_are_available_to_authenticated_users_and_auth_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('id="cookie-consent-banner"', false)
            ->assertSee('Redefinir Cookies');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="cookie-consent-banner"', false)
            ->assertSee(asset('js/cookie-consent.js'), false);
    }

    public function test_privacy_page_explains_cookie_choices(): void
    {
        $this->get(route('page.privacy'))
            ->assertOk()
            ->assertSee('Cookies e tecnologias semelhantes')
            ->assertSee('cookies estritamente necessários')
            ->assertSee('Redefinir Cookies');
    }

    public function test_cookie_script_defaults_to_necessary_only_and_gates_optional_resources(): void
    {
        $script = file_get_contents(public_path('js/cookie-consent.js'));

        $this->assertStringContainsString("const cookieName = 'conectado_cookie_consent';", $script);
        $this->assertStringContainsString('necessary: true', $script);
        $this->assertStringContainsString('preferences: false', $script);
        $this->assertStringContainsString('analytics: false', $script);
        $this->assertStringContainsString('marketing: false', $script);
        $this->assertStringContainsString("'[data-cookie-category][data-cookie-src]'", $script);
        $this->assertStringContainsString("new CustomEvent('cookie-consent:updated'", $script);
        $this->assertStringContainsString('SameSite=Lax', $script);
    }
}
