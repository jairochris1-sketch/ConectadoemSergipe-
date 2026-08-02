<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_has_global_light_dark_and_system_theme_control(): void
    {
        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="themeToggleBtn"', false)
            ->assertSee('data-theme-value="light"', false)
            ->assertSee('data-theme-value="dark"', false)
            ->assertSee('data-theme-value="system"', false)
            ->assertSee('class="theme-toggle-btn"', false)
            ->assertSee('class="theme-dropdown"', false)
            ->assertSee('Aparência clara')
            ->assertSee('Aparência escura')
            ->assertSee('Seguir dispositivo')
            ->assertSee(asset('css/theme-toggle.css'), false)
            ->assertSee(asset('js/main.js'), false)
            ->assertSee("localStorage.getItem('theme')", false)
            ->assertSee('data-bs-theme', false);

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, asset('css/style.css')),
            strpos($html, "localStorage.getItem('theme')")
        );
        $this->assertLessThan(
            strpos($html, asset('css/theme-toggle.css')),
            strpos($html, asset('css/style.css'))
        );
        $this->assertLessThan(strrpos($html, '</body>'), strpos($html, 'class="theme-toggle-container"'));
    }

    public function test_theme_control_is_also_available_on_authentication_and_admin_pages(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="themeToggleBtn"', false)
            ->assertSee(asset('js/main.js'), false);

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('id="themeToggleBtn"', false)
            ->assertSee(asset('css/theme-toggle.css'), false)
            ->assertSee(asset('js/main.js'), false);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="themeToggleBtn"', false)
            ->assertSee(asset('css/theme-toggle.css'), false)
            ->assertSee(asset('js/main.js'), false);
    }

    public function test_theme_css_connects_legacy_and_bootstrap_components_to_global_variables(): void
    {
        $css = file_get_contents(public_path('css/theme-toggle.css'));

        $this->assertStringContainsString('--color-card-bg: var(--card);', $css);
        $this->assertStringContainsString('--bs-body-bg: var(--background);', $css);
        $this->assertStringContainsString('.admin-topbar {', $css);
        $this->assertStringContainsString('background: var(--card) !important;', $css);
        $this->assertStringContainsString('.bg-white {', $css);
        $this->assertStringContainsString('.text-dark {', $css);
        $this->assertStringContainsString('border-color: var(--border);', $css);
    }
}
