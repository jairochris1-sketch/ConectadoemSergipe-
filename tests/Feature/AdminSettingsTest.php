<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_require_an_administrator(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_administrator_can_replace_a_home_banner(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        );

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_name' => 'Conectado em Sergipe',
            'contact_email' => 'contato@example.com',
            'whatsapp_number' => '5579999999999',
            'instagram_url' => 'https://instagram.com/conectado',
            'home_banner_1' => UploadedFile::fake()
                ->createWithContent('banner.png', $png)
                ->mimeType('image/png'),
            'services_banner_1' => UploadedFile::fake()
                ->createWithContent('prestadores.png', $png)
                ->mimeType('image/png'),
        ]);

        $response->assertRedirect();

        $bannerPath = Setting::get('home_banner_1');
        $servicesBannerPath = Setting::get('services_banner_1');
        $socialPreviewPath = Setting::get('home_social_preview');

        $this->assertNotNull($bannerPath);
        $this->assertNotNull($servicesBannerPath);
        $this->assertStringStartsWith('uploads/home_banner_1_', $bannerPath);
        $this->assertStringStartsWith('uploads/services_banner_1_', $servicesBannerPath);
        $this->assertFileExists(public_path($bannerPath));
        $this->assertFileExists(public_path($servicesBannerPath));

        if (function_exists('imagejpeg')) {
            $this->assertStringStartsWith('uploads/home_social_', $socialPreviewPath);
            $this->assertStringEndsWith('.jpg', $socialPreviewPath);
            $this->assertFileExists(public_path($socialPreviewPath));
            $this->assertSame('image/jpeg', mime_content_type(public_path($socialPreviewPath)));
        } else {
            $this->assertNull($socialPreviewPath);
        }

        $homeResponse = $this->get(route('home'))
            ->assertOk()
            ->assertSee(asset($bannerPath), false)
            ->assertDontSee(asset($servicesBannerPath), false);
        if ($socialPreviewPath) {
            $homeResponse->assertSee('<meta property="og:image" content="'.asset($socialPreviewPath).'">', false);
        }

        $this->get(route('module.services'))
            ->assertOk()
            ->assertSee(asset($servicesBannerPath), false)
            ->assertDontSee(asset($bannerPath), false);

        @unlink(public_path($bannerPath));
        @unlink(public_path($servicesBannerPath));
        if ($socialPreviewPath) {
            @unlink(public_path($socialPreviewPath));
        }
    }

    public function test_administrator_can_save_integrations_and_authentication_balloon_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'site_name' => 'Conectado em Sergipe',
                'contact_email' => 'contato@example.com',
                'image_moderation_enabled' => '1',
                'google_vision_api_key' => 'vision-secret',
                'google_login_enabled' => '1',
                'google_client_id' => 'google-client-id',
                'google_client_secret' => 'google-client-secret',
                'auth_balloon_enabled' => '1',
                'auth_balloon_msg1' => 'Mensagem personalizada principal',
                'auth_balloon_msg2' => 'Segunda mensagem personalizada',
                'auth_balloon_msg3' => 'Terceira mensagem personalizada',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('1', Setting::get('image_moderation_enabled'));
        $this->assertSame('vision-secret', Setting::get('google_vision_api_key'));
        $this->assertSame('1', Setting::get('google_login_enabled'));
        $this->assertSame('google-client-id', Setting::get('google_client_id'));
        $this->assertSame('google-client-secret', Setting::get('google_client_secret'));
        $this->assertSame('1', Setting::get('auth_balloon_enabled'));
        $this->assertSame('Mensagem personalizada principal', Setting::get('auth_balloon_msg1'));
        $this->assertSame('Segunda mensagem personalizada', Setting::get('auth_balloon_msg2'));
        $this->assertSame('Terceira mensagem personalizada', Setting::get('auth_balloon_msg3'));

        $this->get(route('admin.settings'))
            ->assertOk()
            ->assertDontSee('vision-secret', false)
            ->assertDontSee('google-client-secret', false)
            ->assertSee('deixe em branco para manter');

        $this->app['auth']->logout();

        foreach (['login', 'register', 'password.request'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('id="auth-floating-balloon"', false)
                ->assertSee('data-auth-balloon-close', false)
                ->assertSee('aria-label="Fechar balão"', false)
                ->assertSee('balloon.hidden = true', false)
                ->assertSee('class="auth-message-balloon', false)
                ->assertSee('auth-message-balloon-header', false)
                ->assertSee('auth-message-balloon-label', false)
                ->assertSee('auth-message-balloon-dots', false)
                ->assertSee('data-balloon-current', false)
                ->assertSee('data-balloon-previous', false)
                ->assertSee('data-balloon-next', false)
                ->assertSee('data-balloon-pause', false)
                ->assertSee('aria-label="Pausar mensagens"', false)
                ->assertSee('height: 315px;', false)
                ->assertSee('max-height: 145px;', false)
                ->assertSee('font-size: clamp(.98rem, 1.7vw, 1.12rem);', false)
                ->assertSee('line-height: 1.45;', false)
                ->assertSee('}, 7000);', false)
                ->assertSee('isPaused = !isPaused', false)
                ->assertSee('dots.forEach', false)
                ->assertSee('Conectado em Sergipe')
                ->assertSee('Descubra novas possibilidades')
                ->assertSee('Mensagem personalizada principal')
                ->assertSee('Segunda mensagem personalizada')
                ->assertSee('Terceira mensagem personalizada');
        }

        foreach (['login', 'register'] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('href="'.route('auth.google').'"', false)
                ->assertSee('class="auth-google-button"', false)
                ->assertSee('class="auth-google-icon"', false)
                ->assertSee('fill="#4285F4"', false)
                ->assertSee('fill="#34A853"', false)
                ->assertSee('fill="#FBBC05"', false)
                ->assertSee('fill="#EA4335"', false)
                ->assertSee('Continuar com Google');
        }

        $this->actingAs($admin)
            ->post(route('admin.settings.update'), [
                'site_name' => 'Conectado em Sergipe',
                'contact_email' => 'contato@example.com',
                'google_vision_api_key' => '',
                'google_client_secret' => '',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('0', Setting::get('image_moderation_enabled'));
        $this->assertSame('0', Setting::get('google_login_enabled'));
        $this->assertSame('0', Setting::get('auth_balloon_enabled'));
        $this->assertSame('vision-secret', Setting::get('google_vision_api_key'));
        $this->assertSame('google-client-secret', Setting::get('google_client_secret'));
        $this->assertSame(
            'disabled',
            app(ImageModerationService::class)->inspect('unused-while-disabled.jpg')['details']['status']
        );

        $this->app['auth']->logout();

        foreach (['login', 'register', 'password.request'] as $routeName) {
            $response = $this->get(route($routeName))->assertOk();
            $response->assertDontSee('id="auth-floating-balloon"', false);

            if ($routeName !== 'password.request') {
                $response->assertDontSee('href="'.route('auth.google').'"', false);
            }
        }
    }

    public function test_administrator_can_activate_only_publish_page_designs_four_or_five(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $advertiser = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.settings.publish_design'), [
                'publish_page_design' => 'design5',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('design5', Setting::get('publish_page_design'));

        $this->actingAs($advertiser)
            ->get(route('ad.create'))
            ->assertOk()
            ->assertSee('publish-design-design5', false)
            ->assertDontSee('module-card-services', false);

        $this->actingAs($admin)
            ->post(route('admin.settings.publish_design'), [
                'publish_page_design' => 'design3',
            ])
            ->assertSessionHasErrors('publish_page_design');

        $this->assertSame('design5', Setting::get('publish_page_design'));
    }

    public function test_home_showcase_does_not_leak_into_jobs_page(): void
    {
        $providerUser = User::factory()->create();
        Ad::create([
            'user_id' => $providerUser->id,
            'module' => 'services',
            'title' => 'Profissional da home',
            'slug' => 'profissional-da-home',
            'description' => 'Prestador usado para testar o isolamento das páginas.',
            'price' => 0,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('swiper-hero', false)
            ->assertSee('Prestadores de Serviços');

        $this->get(route('module.jobs'))
            ->assertOk()
            ->assertDontSee('swiper-hero', false)
            ->assertDontSee('Prestadores de Serviços');
    }

    public function test_only_owner_or_administrator_can_manage_an_ad(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = Ad::create([
            'user_id' => $owner->id,
            'module' => 'products',
            'title' => 'Produto protegido',
            'slug' => 'produto-protegido',
            'description' => 'Anúncio usado no teste de autorização.',
            'price' => 100,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $this->get(route('ad.edit', $ad->id))
            ->assertRedirect(route('login'));

        $this->actingAs($otherUser)
            ->get(route('ad.edit', $ad->id))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('ad.edit', $ad->id))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('ad.edit', $ad->id))
            ->assertOk();
    }
}
