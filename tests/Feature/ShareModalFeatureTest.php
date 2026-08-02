<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareModalFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_contains_accessible_social_share_modal(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Conectado em Sergipe | Serviços, lojas e oportunidades locais">', false)
            ->assertSee('<meta property="og:url" content="'.route('home').'">', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('id="social-share-modal"', false)
            ->assertSee('aria-labelledby="social-share-title"', false)
            ->assertSee('Facebook')
            ->assertSee('WhatsApp')
            ->assertSee('Instagram')
            ->assertSee('X (Twitter)')
            ->assertSee('Mais opções')
            ->assertSee('ou copie o link')
            ->assertSee('https://www.facebook.com/sharer/sharer.php', false)
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('https://twitter.com/intent/tweet', false)
            ->assertSee('navigator.share', false)
            ->assertSee('navigator.clipboard.writeText', false)
            ->assertSee('html[data-theme="dark"] .social-share-modal', false)
            ->assertSee('width: min(520px, calc(100% - 32px))', false);
    }
}
