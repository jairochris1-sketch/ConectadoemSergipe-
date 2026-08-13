<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HomeCityGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_replaces_city_explorer_with_ordered_local_groups(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="home-city-groups-title"', false)
            ->assertDontSee('data-city-group-confirmation', false);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('id="home-city-groups-title"', false)
            ->assertSee('Nossos grupos em Sergipe por cidade')
            ->assertSee('Entrar no grupo')
            ->assertSee('data-city-group-confirmation', false)
            ->assertSee('Você realmente é')
            ->assertSee('data-group-gentilic="aracajuense"', false)
            ->assertSee('data-group-gentilic="tobiense"', false)
            ->assertSee('data-group-gentilic="itabaianense"', false)
            ->assertSee('data-group-gentilic="gloriense"', false)
            ->assertSee('Participar')
            ->assertSee('Fechar')
            ->assertDontSee('Explore anúncios por cidade');

        $content = $response->getContent();

        $this->assertSame(75, substr_count($content, 'data-city-group-slot='));
        $this->assertLessThan(strpos($content, 'data-city-group-slot="2"'), strpos($content, 'data-city-group-slot="1"'));
        $this->assertLessThan(strpos($content, 'data-city-group-slot="3"'), strpos($content, 'data-city-group-slot="2"'));
        $this->assertLessThan(strpos($content, 'data-city-group-slot="4"'), strpos($content, 'data-city-group-slot="3"'));
        $this->assertStringContainsString('data-city-group-slot="7" data-city-group-active="true"', $content);
        $this->assertStringContainsString('data-city-group-slot="8" data-city-group-active="false"', $content);
        $this->assertStringContainsString('Não ativo', $content);
        $this->assertStringContainsString('Indisponível', $content);
    }

    public function test_admin_can_change_group_cover_link_and_visibility(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        );

        $this->actingAs($admin)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('name="home_city_group_cover_1"', false)
            ->assertSee('name="home_city_group_link_1"', false)
            ->assertSee('name="home_city_group_enabled_1"', false);

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'site_name' => 'Conectado em Sergipe',
                'contact_email' => 'contato@example.com',
                'home_city_group_enabled_1' => '1',
                'home_city_group_link_1' => 'https://chat.whatsapp.com/grupo-aracaju',
                'home_city_group_cover_1' => UploadedFile::fake()
                    ->createWithContent('aracaju.png', $png)
                    ->mimeType('image/png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $coverPath = Setting::get('home_city_group_cover_1');
        $this->assertSame('1', Setting::get('home_city_group_enabled_1'));
        $this->assertSame('0', Setting::get('home_city_group_enabled_2'));
        $this->assertStringStartsWith('uploads/home_city_group_cover_1_', $coverPath);
        $this->assertFileExists(public_path($coverPath));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(asset($coverPath), false)
            ->assertSee('href="https://chat.whatsapp.com/grupo-aracaju"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('data-city-group-slot="1"', false)
            ->assertSee('data-city-group-slot="2" data-city-group-active="false"', false);

        @unlink(public_path($coverPath));
    }
}
