<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VlibrasWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_loads_the_official_vlibras_widget(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('vw-access-button', false)
            ->assertSee('vw-plugin-wrapper', false)
            ->assertSee('https://vlibras.gov.br/app/vlibras-plugin.js', false)
            ->assertSee("new window.VLibras.Widget('https://vlibras.gov.br/app')", false);
    }
}
