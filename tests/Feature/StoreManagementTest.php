<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StoreManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
    }

    public function test_guest_cannot_open_store_management(): void
    {
        $this->get(route('store.create'))->assertRedirect(route('login'));
        $this->post(route('store.store'), [])->assertRedirect(route('login'));
    }

    public function test_restaurant_onboarding_prefills_the_food_category(): void
    {
        $user = User::factory()->create(['subscription_plan' => 'pro']);

        $this->actingAs($user)
            ->get(route('store.create', ['category' => 'Alimentação']))
            ->assertOk()
            ->assertSee('Cadastre seu restaurante')
            ->assertSee('value="Alimentação" selected', false);
    }

    public function test_user_can_create_only_one_store_with_logo_and_banner(): void
    {
        $user = User::factory()->create([
            'subscription_plan' => 'pro',
            'city' => 'Aracaju',
            'whatsapp' => '79999999999',
        ]);

        $this->actingAs($user)
            ->post(route('store.store'), [
                ...$this->validStoreData(),
                'logo' => $this->projectImageUpload('store-logo.png'),
                'banner' => $this->projectImageUpload('store-banner.png'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('store.edit'));

        $store = Store::firstOrFail();
        $this->assertSame($user->id, $store->user_id);
        $this->assertSame('Moda', $store->category);
        $this->assertSame('Aracaju', $store->city);
        $this->assertTrue($store->active);
        $this->assertNotNull($store->logo);
        $this->assertNotNull($store->banner);
        $this->assertFileExists(public_path($store->logo));
        $this->assertFileExists(public_path($store->banner));

        $this->actingAs($user)
            ->post(route('store.store'), $this->validStoreData(['name' => 'Segunda loja']))
            ->assertSessionHasErrors('store');

        $this->assertDatabaseCount('stores', 1);

        File::delete(public_path($store->logo));
        File::delete(public_path($store->banner));
    }

    public function test_owner_can_update_remove_images_and_toggle_store_status(): void
    {
        $user = User::factory()->create();
        $store = $this->createStore($user, [
            'logo' => 'uploads/store-test-logo.webp',
            'banner' => 'uploads/store-test-banner.webp',
        ]);
        File::ensureDirectoryExists(public_path('uploads'));
        File::put(public_path($store->logo), 'logo');
        File::put(public_path($store->banner), 'banner');

        $this->actingAs($user)
            ->put(route('store.update'), [
                ...$this->validStoreData([
                    'name' => 'Loja Atualizada',
                    'category' => 'Beleza',
                    'city' => 'Itabaiana',
                ]),
                'remove_logo' => '1',
                'remove_banner' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('store_success');

        $store->refresh();
        $this->assertSame('Loja Atualizada', $store->name);
        $this->assertSame('Beleza', $store->category);
        $this->assertSame('Itabaiana', $store->city);
        $this->assertNull($store->logo);
        $this->assertNull($store->banner);
        $this->assertFileDoesNotExist(public_path('uploads/store-test-logo.webp'));
        $this->assertFileDoesNotExist(public_path('uploads/store-test-banner.webp'));

        $this->actingAs($user)
            ->post(route('store.toggle_status'))
            ->assertSessionHas('store_success');
        $this->assertFalse($store->fresh()->active);

        $this->actingAs($user)
            ->post(route('store.toggle_status'))
            ->assertSessionHas('store_success');
        $this->assertTrue($store->fresh()->active);
    }

    public function test_deleting_store_preserves_ads_without_store_link(): void
    {
        $user = User::factory()->create();
        $store = $this->createStore($user);
        $ad = Ad::create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'module' => 'products',
            'title' => 'Produto preservado',
            'slug' => 'produto-preservado-loja',
            'description' => 'Este produto deve continuar publicado após a exclusão da loja.',
            'price' => 120,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->delete(route('store.destroy'))
            ->assertRedirect(route('user.panel'))
            ->assertSessionHas('store_success');

        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
        $this->assertDatabaseHas('ads', [
            'id' => $ad->id,
            'store_id' => null,
            'status' => 'active',
        ]);
    }

    public function test_panel_exposes_contextual_store_management_action(): void
    {
        $userWithoutStore = User::factory()->create(['subscription_plan' => 'pro']);

        $this->actingAs($userWithoutStore)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('Criar minha loja')
            ->assertSee(route('store.create'), false);

        $owner = User::factory()->create(['subscription_plan' => 'pro']);
        $store = $this->createStore($owner);

        $this->actingAs($owner)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee($store->name)
            ->assertSee('Gerenciar')
            ->assertSee(route('store.edit'), false);
    }

    private function validStoreData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Loja Modelo Sergipe',
            'description' => 'Loja usada para validar o gerenciamento completo.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'phone' => '(79) 3333-3333',
            'whatsapp' => '(79) 99999-9999',
            'instagram' => '@lojamodelo',
            'website' => 'https://example.com',
        ], $overrides);
    }

    private function createStore(User $user, array $overrides = []): Store
    {
        return Store::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Loja do Usuário',
            'slug' => 'loja-do-usuario-' . uniqid(),
            'description' => 'Descrição da loja.',
            'category' => 'Moda',
            'city' => 'Aracaju',
            'state' => 'SE',
            'whatsapp' => '79999999999',
            'active' => true,
        ], $overrides));
    }

    private function projectImageUpload(string $name): UploadedFile
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'store-upload-');
        copy(public_path('images/logo.png'), $temporaryPath);

        return new UploadedFile($temporaryPath, $name, 'image/png', null, true);
    }
}
