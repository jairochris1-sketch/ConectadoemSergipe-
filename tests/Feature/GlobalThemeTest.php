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

    public function test_login_uses_theme_specific_branding_and_simple_registration_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(asset('images/2mapa-sergipe-conectado-azul.png'), false)
            ->assertSee(asset('images/1mapa-sergipe-conectado.png'), false)
            ->assertSee('auth-login-brand-logo-light', false)
            ->assertSee('auth-login-brand-logo-dark', false)
            ->assertSee('Não tem uma conta?')
            ->assertSee('>Criar conta</a>', false)
            ->assertSee('auth-login-benefits', false)
            ->assertSee('Vantagens do Conectado em Sergipe')
            ->assertSee('Seguro')
            ->assertSee('Prático')
            ->assertSee('Rápido')
            ->assertSee('Seus dados')
            ->assertSee('Tudo que você')
            ->assertSee('Acesso fácil')
            ->assertSee('class="mb-4 text-center"', false)
            ->assertSee('class="mb-3 text-center"', false)
            ->assertSee('class="form-control auth-login-field"', false)
            ->assertSee('placeholder="Número de celular, nome de usuário ou email"', false)
            ->assertSee('placeholder="Senha"', false)
            ->assertSee('data-access-copy-field', false)
            ->assertSee('data-access-copy', false)
            ->assertSee('aria-label="Copiar acesso"', false)
            ->assertSee('data-password-toggle', false)
            ->assertSee('data-password-copy', false)
            ->assertSee('aria-label="Mostrar senha"', false)
            ->assertSee('aria-label="Copiar senha"', false)
            ->assertSee('5 * 60 * 1000', false)
            ->assertSee("actions.hidden = true", false)
            ->assertSee("control.classList.add('auth-actions-expired')", false)
            ->assertDontSee('navigator.clipboard.readText()', false)
            ->assertSee('class="btn btn-primary auth-login-submit', false)
            ->assertSee('Esqueceu a senha?')
            ->assertDontSee('class="input-group"', false)
            ->assertDontSee('name="remember"', false)
            ->assertDontSee('Criar conta e publicar')
            ->assertDontSee('btn btn-primary btn-sm rounded-pill w-100', false)
            ->assertDontSee('Criar conta gratuita agora')
            ->assertDontSee('btn btn-success btn-sm rounded-pill w-100', false);
    }

    public function test_registration_and_password_recovery_use_the_theme_specific_branding(): void
    {
        foreach (['register', 'password.request'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee(asset('images/2mapa-sergipe-conectado-azul.png'), false)
                ->assertSee(asset('images/1mapa-sergipe-conectado.png'), false)
                ->assertSee('auth-theme-brand-logo-light', false)
                ->assertSee('auth-theme-brand-logo-dark', false)
                ->assertSee('class="mb-4 text-center"', false)
                ->assertDontSee(asset('images/logo.png'), false);
        }

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('auth-register-submit', false)
            ->assertSee('class="btn btn-primary auth-register-submit', false)
            ->assertSee('fa-user-check', false)
            ->assertSee('Criar agora')
            ->assertSee('auth-register-select-clean', false)
            ->assertSee('background-image: none !important;', false)
            ->assertDontSee('class="input-group"', false)
            ->assertDontSee('input-group-text', false)
            ->assertDontSee('data-password-toggle', false)
            ->assertDontSee('data-password-copy', false)
            ->assertDontSee('auth-password-control', false)
            ->assertDontSee('Criar Conta & Publicar', false)
            ->assertDontSee('fa-user-plus', false);

        $this->get(route('password.request'))
            ->assertOk()
            ->assertDontSee('auth-register-submit', false);
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
