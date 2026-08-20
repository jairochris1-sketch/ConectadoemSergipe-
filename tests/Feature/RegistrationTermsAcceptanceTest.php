<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTermsAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_requires_terms_before_enabling_submission(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="terms_accepted"', false)
            ->assertSee('name="terms_accepted"', false)
            ->assertSee('required', false)
            ->assertSee('href="'.route('page.terms').'"', false)
            ->assertSee('Termos de Uso')
            ->assertSee('data-register-submit', false)
            ->assertSee('submitButton.disabled = !termsCheckbox.checked', false);
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
        ])->assertRedirect(route('ad.create'));

        $this->assertDatabaseHas('users', [
            'email' => 'novo.usuario@example.com',
            'username' => 'novo.usuario',
        ]);
        $this->assertAuthenticatedAs(User::where('email', 'novo.usuario@example.com')->firstOrFail());
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
