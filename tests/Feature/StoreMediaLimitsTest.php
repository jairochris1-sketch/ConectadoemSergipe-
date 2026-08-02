<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StoreMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StoreMediaLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
    }

    public function test_media_limits_follow_the_users_plan(): void
    {
        $free = User::factory()->create(['subscription_plan' => 'free']);
        $pro = User::factory()->create(['subscription_plan' => 'pro']);
        $gold = User::factory()->create(['subscription_plan' => 'gold']);

        $this->assertSame(0, $free->storeMediaLimit('banner'));
        $this->assertSame(0, $free->storeMediaLimit('gallery'));
        $this->assertSame(3, $pro->storeMediaLimit('banner'));
        $this->assertSame(12, $pro->storeMediaLimit('gallery'));
        $this->assertSame(6, $gold->storeMediaLimit('banner'));
        $this->assertSame(20, $gold->storeMediaLimit('gallery'));
    }

    public function test_free_plan_blocks_store_creation(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'free']);

        $this->actingAs($user)
            ->post(route('store.store'), [
                ...$this->validStoreData(),
                'banner' => $this->projectImageUpload('principal.png'),
            ])
            ->assertSessionHasErrors('store');

        $this->assertDatabaseCount('stores', 0);
        $this->assertDatabaseCount('store_media', 0);
    }

    public function test_pro_plan_can_save_three_banners_and_gallery_images(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'pro']);

        $this->actingAs($user)
            ->post(route('store.store'), [
                ...$this->validStoreData(),
                'banner' => $this->projectImageUpload('principal.png'),
                'additional_banners' => [
                    $this->projectImageUpload('adicional-1.png'),
                    $this->projectImageUpload('adicional-2.png'),
                ],
                'gallery_images' => [
                    $this->projectImageUpload('galeria-1.png'),
                    $this->projectImageUpload('galeria-2.png'),
                ],
            ])
            ->assertSessionHasNoErrors();

        $store = Store::with('media')->firstOrFail();
        $this->assertNotNull($store->banner);
        $this->assertSame(2, $store->media->where('type', 'banner')->count());
        $this->assertSame(2, $store->media->where('type', 'gallery')->count());

        collect([$store->banner])
            ->merge($store->media->pluck('path'))
            ->each(fn ($path) => File::delete(public_path($path)));
    }

    public function test_owner_can_remove_media_and_use_the_released_slot(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($user);
        File::ensureDirectoryExists(public_path('uploads'));
        $oldMedia = collect(range(1, 3))->map(function ($number) use ($store) {
            $path = "uploads/old-store-gallery-{$number}.webp";
            File::put(public_path($path), 'old');

            return StoreMedia::create([
                'store_id' => $store->id,
                'type' => 'gallery',
                'path' => $path,
            ]);
        });

        $this->actingAs($user)
            ->put(route('store.update_specific', $store), [
                ...$this->validStoreData(),
                'remove_media_ids' => [$oldMedia->first()->id],
                'gallery_images' => [
                    $this->projectImageUpload('substituta.png'),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('store_success');

        $this->assertDatabaseMissing('store_media', ['id' => $oldMedia->first()->id]);
        $this->assertSame(3, $store->media()->where('type', 'gallery')->count());
        $replacement = $store->media()
            ->where('type', 'gallery')
            ->whereNotIn('id', $oldMedia->pluck('id'))
            ->firstOrFail();
        $this->assertFileDoesNotExist(public_path($oldMedia->first()->path));
        $this->assertFileExists(public_path($replacement->path));

        $store->media()->pluck('path')->each(fn ($path) => File::delete(public_path($path)));
    }

    public function test_public_store_displays_gallery_and_multiple_banner_data(): void
    {
        $owner = User::factory()->create(['subscription_plan' => 'gold']);
        $store = $this->createStore($owner, ['banner' => 'images/hero_banner.jpg']);
        StoreMedia::create([
            'store_id' => $store->id,
            'type' => 'banner',
            'path' => 'images/logo.png',
        ]);
        StoreMedia::create([
            'store_id' => $store->id,
            'type' => 'gallery',
            'path' => 'images/logo.png',
        ]);

        $this->get(route('store.show', $store->slug))
            ->assertOk()
            ->assertSee('Conheça melhor a loja')
            ->assertSee('data-store-gallery-index="0"', false)
            ->assertSee('storefront-hero-background', false)
            ->assertSee('hero_banner.jpg', false)
            ->assertSee('logo.png', false);
    }

    private function validStoreData(): array
    {
        return [
            'name' => 'Loja com Mídia',
            'description' => 'Loja usada para validar banners e galeria.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'phone' => '7933333333',
            'whatsapp' => '79999999999',
            'instagram' => '@lojamidia',
            'website' => 'https://example.com',
        ];
    }

    private function createStore(User $user, array $overrides = []): Store
    {
        return Store::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Loja com Galeria',
            'slug' => 'loja-com-galeria-' . uniqid(),
            'description' => 'Descrição da loja com mídia.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
            'moderation_status' => 'approved',
        ], $overrides));
    }

    private function projectImageUpload(string $name): UploadedFile
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'store-media-upload-');
        copy(public_path('images/logo.png'), $temporaryPath);

        return new UploadedFile($temporaryPath, $name, 'image/png', null, true);
    }
}
