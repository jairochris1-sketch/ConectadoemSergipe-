<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPanelAdDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_an_ad_with_json_without_a_redirect(): void
    {
        $user = User::factory()->create();
        $ad = $this->createAd($user, 'anuncio-exclusao-json');

        $this->actingAs($user)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->post(route('ad.destroy', $ad->id), ['_method' => 'DELETE'])
            ->assertOk()
            ->assertJson([
                'message' => 'Anúncio removido com sucesso!',
                'ad_id' => $ad->id,
            ]);

        $this->assertDatabaseMissing('ads', ['id' => $ad->id]);
    }

    public function test_panel_contains_the_progressive_async_delete_controls(): void
    {
        $user = User::factory()->create();
        $ad = $this->createAd($user, 'anuncio-painel-assincrono');

        $this->actingAs($user)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('data-user-ads-panel', false)
            ->assertSee('data-user-ads-count', false)
            ->assertSee('data-user-ad-row="'.$ad->id.'"', false)
            ->assertSee('data-user-ad-delete-form', false)
            ->assertSee("'Accept': 'application/json'", false);
    }

    public function test_regular_delete_keeps_the_redirect_fallback(): void
    {
        $user = User::factory()->create();
        $ad = $this->createAd($user, 'anuncio-exclusao-fallback');

        $this->actingAs($user)
            ->delete(route('ad.destroy', $ad->id))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHas('success', 'Anúncio removido com sucesso!');

        $this->assertDatabaseMissing('ads', ['id' => $ad->id]);
    }

    private function createAd(User $user, string $slug): Ad
    {
        return Ad::create([
            'user_id' => $user->id,
            'module' => 'products',
            'title' => 'Anúncio para exclusão',
            'slug' => $slug,
            'description' => 'Anúncio usado para validar a exclusão assíncrona no painel.',
            'price' => 100,
            'city' => 'Aracaju',
            'state' => 'SE',
            'status' => 'active',
        ]);
    }
}
