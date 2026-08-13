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
