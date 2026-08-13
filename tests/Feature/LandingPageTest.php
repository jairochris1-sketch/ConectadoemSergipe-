<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_is_the_root_and_marketplace_has_its_own_route(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('class="landing-canvas"', false)
            ->assertSee('Conectado em')
            ->assertSee('Sergipe')
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('2mapa-sergipe-conectado-azul.png', false)
            ->assertSee('data-landing-typewriter', false)
            ->assertSee('href="#conhecer-plataforma"', false)
            ->assertSee('id="conhecer-plataforma"', false)
            ->assertSee('data-city-ring', false)
            ->assertSee('data-city-toggle', false)
            ->assertSee('landingCityOrbit', false)
            ->assertSee('www.youtube-nocookie.com/embed/LS0ObEgTwZk', false)
            ->assertSee('https://pt.wikipedia.org/wiki/Aracaju', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="home-hero-plans-card"', false)
            ->assertDontSee('class="landing-canvas"', false);
    }

    public function test_admin_can_customize_landing_text_and_images(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        );

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'site_name' => 'Conectado em Sergipe',
                'contact_email' => 'contato@example.com',
                'landing_enabled' => '1',
                'landing_eyebrow' => 'Sergipe conectado',
                'landing_title' => 'Encontre tudo em',
                'landing_highlight' => 'um lugar',
                'landing_description' => 'Conteúdo personalizado da nova landing page.',
                'landing_supporting_text' => 'Apoio personalizado aos negócios sergipanos.',
                'landing_about_eyebrow' => 'Conheça nosso estado',
                'landing_about_title' => 'Sergipe inteiro mais perto',
                'landing_about_description' => 'Uma apresentação personalizada da plataforma.',
                'landing_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'landing_primary_label' => 'Acessar plataforma',
                'landing_secondary_label' => 'Saiba mais',
                'landing_image_1' => UploadedFile::fake()
                    ->createWithContent('prestador.png', $png)
                    ->mimeType('image/png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $imagePath = Setting::get('landing_image_1');
        $this->assertSame('1', Setting::get('landing_enabled'));
        $this->assertStringStartsWith('uploads/landing_image_1_', $imagePath);
        $this->assertFileExists(public_path($imagePath));

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Sergipe conectado')
            ->assertSee('Encontre tudo em')
            ->assertSee('um lugar')
            ->assertSee('Acessar plataforma')
            ->assertSee('Apoio personalizado aos negócios sergipanos.')
            ->assertSee('Conheça nosso estado')
            ->assertSee('Sergipe inteiro mais perto')
            ->assertSee('Uma apresentação personalizada da plataforma.')
            ->assertSee('www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee(asset($imagePath), false);

        @unlink(public_path($imagePath));
    }

    public function test_disabled_landing_redirects_visitors_but_admin_can_preview_it(): void
    {
        Setting::set('landing_enabled', '0');

        $this->get(route('landing'))
            ->assertRedirect(route('home'));

        $this->get(route('landing', ['preview' => 1]))
            ->assertRedirect(route('home'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('landing', ['preview' => 1]))
            ->assertOk()
            ->assertSee('Prévia administrativa');
    }

    public function test_landing_controls_are_available_in_admin_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('name="landing_enabled"', false)
            ->assertSee('name="landing_description"', false)
            ->assertSee('name="landing_about_title"', false)
            ->assertSee('name="landing_about_description"', false)
            ->assertSee('name="landing_video_url"', false)
            ->assertSee('name="landing_image_1"', false)
            ->assertSee('name="landing_image_7"', false)
            ->assertSee(route('landing', ['preview' => 1]), false);
    }
}
