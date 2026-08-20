<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaInstallationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_exposes_an_installable_pwa(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $response
            ->assertSee('rel="manifest"', false)
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('data-pwa-install-prompt', false)
            ->assertSee('js/pwa-install.js', false)
            ->assertSee('Instale o Conectado em Sergipe');

        $manifestPath = public_path('manifest.webmanifest');
        $serviceWorkerPath = public_path('sw.js');
        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertFileExists($serviceWorkerPath);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertContains('192x192', array_column($manifest['icons'], 'sizes'));
        $this->assertContains('512x512', array_column($manifest['icons'], 'sizes'));
        $this->assertFileExists(public_path('pwa/icon-192.png'));
        $this->assertFileExists(public_path('pwa/icon-512.png'));
        $this->assertFileExists(public_path('pwa/icon-maskable-512.png'));
        $this->assertFileExists(public_path('offline.html'));
    }
}
