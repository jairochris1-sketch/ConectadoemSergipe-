<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\FavoriteFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdFavoriteFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creates_first_folder_and_saves_ad_from_home(): void
    {
        $user = User::factory()->create();
        $ad = $this->createAd();

        $this->actingAs($user)
            ->postJson(route('ads.favorite.store', $ad), ['folder_name' => 'Quero conhecer'])
            ->assertOk()
            ->assertJsonPath('favorite', true)
            ->assertJsonPath('folder.name', 'Quero conhecer');

        $folder = FavoriteFolder::where('user_id', $user->id)->sole();
        $this->assertSame('Quero conhecer', $folder->name);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'ad_id' => $ad->id,
            'folder_id' => $folder->id,
        ]);

        $this->actingAs($user)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Anúncios organizados por pasta')
            ->assertSee('Quero conhecer')
            ->assertSee($ad->title);
    }

    public function test_user_can_move_favorite_to_existing_folder_and_remove_it(): void
    {
        $user = User::factory()->create();
        $ad = $this->createAd();
        $firstFolder = $user->favoriteFolders()->create(['name' => 'Imóveis']);
        $secondFolder = $user->favoriteFolders()->create(['name' => 'Visitar depois']);
        $user->favorites()->attach($ad->id, [
            'folder_id' => $firstFolder->id,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('ads.favorite.store', $ad), ['folder_id' => $secondFolder->id])
            ->assertOk()
            ->assertJsonPath('folder.id', $secondFolder->id);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'ad_id' => $ad->id,
            'folder_id' => $secondFolder->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('ads.favorite.destroy', $ad))
            ->assertOk()
            ->assertJsonPath('favorite', false);

        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'ad_id' => $ad->id]);
    }

    public function test_user_cannot_save_in_another_users_folder(): void
    {
        $user = User::factory()->create();
        $otherFolder = User::factory()->create()->favoriteFolders()->create(['name' => 'Pasta privada']);
        $ad = $this->createAd();

        $this->actingAs($user)
            ->postJson(route('ads.favorite.store', $ad), ['folder_id' => $otherFolder->id])
            ->assertNotFound();

        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'ad_id' => $ad->id]);
    }

    public function test_home_renders_folder_dialog_and_current_favorite_state(): void
    {
        $user = User::factory()->create();
        $ad = $this->createAd();
        $folder = $user->favoriteFolders()->create(['name' => 'Comprar depois']);
        $user->favorites()->attach($ad->id, ['folder_id' => $folder->id, 'created_at' => now()]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('<button data-favorite-button', false);
    }

    public function test_folder_is_required_when_saving_favorite(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('ads.favorite.store', $this->createAd()), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('folder_name');
    }

    public function test_user_must_remove_one_favorite_before_saving_the_twenty_first(): void
    {
        $user = User::factory()->create();
        $folder = $user->favoriteFolders()->create(['name' => 'Minha lista']);
        $savedAds = collect(range(1, 20))->map(fn () => $this->createAd());

        foreach ($savedAds as $savedAd) {
            $user->favorites()->attach($savedAd->id, [
                'folder_id' => $folder->id,
                'created_at' => now(),
            ]);
        }

        $newAd = $this->createAd();
        $this->actingAs($user)
            ->postJson(route('ads.favorite.store', $newAd), ['folder_name' => 'Nova pasta cheia'])
            ->assertUnprocessable()
            ->assertJsonPath('limit', 20)
            ->assertJsonPath('message', 'Você atingiu o limite de 20 favoritos. Apague um favorito para salvar um novo.');

        $this->assertDatabaseCount('favorites', 20);
        $this->assertDatabaseMissing('favorite_folders', [
            'user_id' => $user->id,
            'name' => 'Nova pasta cheia',
        ]);

        $this->actingAs($user)
            ->deleteJson(route('ads.favorite.destroy', $savedAds->first()))
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('ads.favorite.store', $newAd), ['folder_id' => $folder->id])
            ->assertOk();

        $this->assertDatabaseCount('favorites', 20);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'ad_id' => $newAd->id,
        ]);
    }

    private function createAd(): Ad
    {
        $owner = User::factory()->create();

        return Ad::create([
            'user_id' => $owner->id,
            'module' => 'real_estate',
            'title' => 'Casa favorita '.uniqid(),
            'slug' => 'casa-favorita-'.uniqid(),
            'description' => 'Anúncio usado para testar as pastas de favoritos.',
            'price' => 150000,
            'city' => 'Aracaju',
            'status' => 'active',
            'views' => 999,
        ]);
    }
}
