<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickProfileRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_profile_page_is_separate_and_lists_all_supported_profile_types(): void
    {
        $this->get(route('quick-profile.create'))
            ->assertOk()
            ->assertSee('data-quick-profile', false)
            ->assertSee('Prestador de Serviços')
            ->assertSee('Profissional Liberal')
            ->assertSee('Lojas')
            ->assertSee('Empresas de Serviços')
            ->assertSee('Artistas')
            ->assertSee('Empresa Contratante')
            ->assertSee('Imobiliária')
            ->assertSee('Produtor Rural')
            ->assertSee('data-liberal-fields hidden', false)
            ->assertSee('Seu perfil profissional é gratuito.')
            ->assertSee(route('page.plans'))
            ->assertSee(asset('images/logo.png'))
            ->assertSee(route('quick-profile.store'));

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="registration-form"', false)
            ->assertDontSee('data-quick-profile', false);
    }

    public function test_home_professional_profile_button_opens_the_quick_registration(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cadastrar Meu Perfil Profissional')
            ->assertSee('href="'.route('quick-profile.create').'"', false);
    }

    public function test_guest_can_create_an_account_and_professional_profile_in_the_quick_flow(): void
    {
        $category = config('marketplace.service_categories_by_profile_kind.professional.0');

        $response = $this->post(route('quick-profile.store'), $this->validPayload([
            'category' => $category,
            'services' => [$category],
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirectContains('/prestador/');

        $user = User::where('email', 'cadastro.rapido@example.com')->firstOrFail();
        $ad = Ad::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect(route('provider.show', $ad->slug));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('professional', $ad->profile_kind);
        $this->assertSame($category, $ad->advertiser_type);
        $this->assertSame('Aracaju', $ad->city);
        $this->assertTrue($ad->is_claimed);
        $this->assertSame([$category], data_get($ad->technical_specs, 'quick_profile.services'));
    }

    public function test_authenticated_user_can_create_a_quick_profile_without_creating_another_account(): void
    {
        $user = User::factory()->create(['phone' => '79999999999']);
        $category = config('marketplace.service_categories_by_profile_kind.service_company.0');

        $response = $this->actingAs($user)->post(route('quick-profile.store'), [
            ...$this->validPayload([
                'profile_kind' => 'service_company',
                'category' => $category,
                'services' => [$category],
            ]),
            'account_name' => null,
            'email' => null,
            'phone' => null,
            'password' => null,
            'password_confirmation' => null,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirectContains('/prestador/');

        $ad = Ad::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect(route('provider.show', $ad->slug));
        $this->assertSame(1, User::count());
        $this->assertSame('service_company', $ad->profile_kind);
    }

    public function test_quick_flow_rejects_an_unknown_profile_type_and_requires_credentials_for_liberal_professionals(): void
    {
        $this->from(route('quick-profile.create'))
            ->post(route('quick-profile.store'), $this->validPayload(['profile_kind' => 'desconhecido']))
            ->assertRedirect(route('quick-profile.create'))
            ->assertSessionHasErrors('profile_kind');

        $category = config('marketplace.service_categories_by_profile_kind.liberal_professional.0');

        $this->from(route('quick-profile.create'))
            ->post(route('quick-profile.store'), $this->validPayload([
                'profile_kind' => 'liberal_professional',
                'category' => $category,
                'services' => [$category],
            ]))
            ->assertRedirect(route('quick-profile.create'))
            ->assertSessionHasErrors(['liberal_credential', 'liberal_credential_issuer']);
    }

    private function validPayload(array $overrides = []): array
    {
        $category = config('marketplace.service_categories_by_profile_kind.professional.0');

        return array_replace([
            'profile_kind' => 'professional',
            'account_name' => 'Pessoa Cadastro Rápido',
            'email' => 'cadastro.rapido@example.com',
            'phone' => '79999999998',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
            'name' => 'Profissional Cadastro Rápido',
            'category' => $category,
            'main_city' => 'Aracaju',
            'whole_state' => '0',
            'cities' => ['São Cristóvão'],
            'neighborhood' => 'Centro',
            'services' => [$category],
            'description' => 'Atendimento profissional rápido, cuidadoso e feito com experiência.',
            'terms' => '1',
        ], $overrides);
    }
}
