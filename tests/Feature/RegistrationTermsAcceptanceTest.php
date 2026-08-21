<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class RegistrationTermsAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_keeps_submit_available_and_explains_required_terms(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="terms_accepted"', false)
            ->assertSee('name="terms_accepted"', false)
            ->assertSee('required', false)
            ->assertSee('href="'.route('page.terms').'"', false)
            ->assertSee('Termos de Uso')
            ->assertSee('data-register-submit', false)
            ->assertSee('id="terms-client-error"', false)
            ->assertSee('Marque a opção acima para aceitar os Termos de Uso')
            ->assertDontSee('submitButton.disabled = !termsCheckbox.checked', false);
    }

    public function test_registration_is_rejected_without_terms_acceptance(): void
    {
        $this->from(route('register'))
            ->post(route('register'), $this->validRegistrationData())
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('terms_accepted');

        $this->assertDatabaseMissing('users', ['email' => 'novo.usuario@example.com']);
    }

    public function test_registration_succeeds_after_terms_acceptance(): void
    {
        $this->post(route('register'), [
            ...$this->validRegistrationData(),
            'terms_accepted' => '1',
        ])->assertRedirect(route('register.success'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.usuario@example.com',
            'username' => 'novo.usuario',
        ]);
        $this->assertGuest();
    }

    public function test_registration_success_page_animates_and_redirects_to_login(): void
    {
        $this->get(route('register.success'))
            ->assertOk()
            ->assertSee('id="registration-success"', false)
            ->assertSee('data-animation-src="'.asset('animations/account-created.json').'"', false)
            ->assertSee('data-login-url="'.route('login').'"', false)
            ->assertSee('Sua conta foi criada!')
            ->assertDontSee('id="marketplaceHeader"', false)
            ->assertSee('assets/registration-success-', false);
    }

    public function test_expired_registration_token_returns_to_the_form_safely(): void
    {
        $session = app('session')->driver();
        $request = Request::create('/cadastro', 'POST', [
            'name' => 'Pessoa com sessão expirada',
            'username' => 'pessoa.expirada',
            'email' => 'pessoa.expirada@example.com',
            'phone' => '79999999998',
            'city' => 'Aracaju',
            'password' => 'segredo-temporario',
            'password_confirmation' => 'segredo-temporario',
            '_token' => 'token-expirado',
        ]);
        $request->setLaravelSession($session);

        $response = app(ExceptionHandler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('register'), $response->headers->get('Location'));
        $this->assertSame('Pessoa com sessão expirada', $session->getOldInput('name'));
        $this->assertNull($session->getOldInput('password'));
        $this->assertTrue($session->get('errors')->has('session'));
    }

    public function test_registration_generates_username_suggestions_when_username_is_already_taken(): void
    {
        User::factory()->create([
            'username' => 'jairo.santos',
            'email' => 'jairo.existente@example.com',
            'phone' => '79988888888',
        ]);

        $response = $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Jairo Santos',
                'username' => 'jairo.santos',
                'email' => 'outro.jairo@example.com',
                'phone' => '79977777777',
                'city' => 'Aracaju',
                'password' => 'senha-segura-123',
                'password_confirmation' => 'senha-segura-123',
                'terms_accepted' => '1',
            ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('username');
        $response->assertSessionHas('username_suggestions');

        $suggestions = session('username_suggestions');
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertNotContains('jairo.santos', $suggestions);
    }

    public function test_suggest_usernames_endpoint_returns_valid_available_options(): void
    {
        User::factory()->create([
            'username' => 'mariasilva',
            'email' => 'maria@example.com',
            'phone' => '79966666666',
        ]);

        $response = $this->getJson(route('register.suggest-usernames', [
            'name' => 'Maria Silva',
            'username' => 'mariasilva',
        ]));

        $response->assertOk()
            ->assertJsonStructure(['suggestions']);

        $suggestions = $response->json('suggestions');
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertNotContains('mariasilva', $suggestions);
    }

    public function test_registration_page_contains_password_security_warning(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Atenção! Memorize ou guarde sua senha em um local seguro.');
    }

    private function validRegistrationData(): array
    {
        return [
            'name' => 'Novo Usuário',
            'username' => 'novo.usuario',
            'email' => 'novo.usuario@example.com',
            'phone' => '79999999999',
            'city' => 'Aracaju',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ];
    }
}
