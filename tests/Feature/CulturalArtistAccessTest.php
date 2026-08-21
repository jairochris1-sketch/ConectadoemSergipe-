<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalArtistAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_works_management_is_hidden_and_forbidden_without_cultural_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('culture.index'))
            ->assertOk()
            ->assertDontSee('Minhas Obras &amp; Rascunhos', false)
            ->assertSee('Criar perfil artístico');

        $this->actingAs($user)
            ->get(route('culture.my-works'))
            ->assertForbidden();
    }

    public function test_cultural_artist_can_see_and_open_works_management(): void
    {
        $artist = User::factory()->create();
        $this->createCulturalProfile($artist);

        $this->actingAs($artist)
            ->get(route('culture.index'))
            ->assertOk()
            ->assertSee('Minhas Obras &amp; Rascunhos', false);

        $this->actingAs($artist)
            ->get(route('culture.my-works'))
            ->assertOk()
            ->assertSee('Minhas Obras &amp; Rascunhos', false);
    }

    private function createCulturalProfile(User $user): Ad
    {
        return Ad::create([
            'user_id' => $user->id,
            'module' => 'services',
            'profile_kind' => 'cultural_artist',
            'advertiser_type' => 'Cordelista',
            'title' => 'Poeta de Sergipe',
            'slug' => 'poeta-de-sergipe-'.$user->id,
            'description' => 'Perfil artístico para publicar obras, cordéis e rascunhos.',
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
    }
}
