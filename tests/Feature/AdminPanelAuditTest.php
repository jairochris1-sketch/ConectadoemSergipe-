<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_has_a_working_mobile_menu_control(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('admin-menu-toggle', false)
            ->assertSee('admin-sidebar-overlay', false)
            ->assertSee('aria-label="Abrir menu administrativo"', false);
    }

    public function test_all_admin_get_pages_open_for_an_administrator(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            'admin.dashboard',
            'admin.users',
            'admin.ads',
            'admin.reports',
            'admin.reviews',
            'admin.categories',
            'admin.stores',
            'admin.settings',
        ] as $routeName) {
            $this->actingAs($admin)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_admin_can_only_assign_a_supported_ad_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = $this->createAd($admin);

        $this->actingAs($admin)
            ->post(route('admin.ads.toggle_status', $ad), ['status' => 'invalid'])
            ->assertSessionHasErrors('status');

        $this->assertSame('active', $ad->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.ads.toggle_status', $ad), ['status' => 'pending'])
            ->assertSessionHasNoErrors();

        $this->assertSame('pending', $ad->fresh()->status);
    }

    public function test_ads_page_exposes_the_moderation_status_control(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ad = $this->createAd($admin);

        $this->actingAs($admin)
            ->get(route('admin.ads'))
            ->assertOk()
            ->assertSee(route('admin.ads.toggle_status', $ad), false)
            ->assertSee('name="status"', false)
            ->assertSee('Bloqueado');
    }

    public function test_administrator_cannot_remove_their_own_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle_role', $admin))
            ->assertSessionHas('error');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_administrator_can_assign_the_collaborator_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle_role', $user), ['role' => 'collaborator'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'collaborator']);
    }

    public function test_duplicate_category_slug_is_reported_as_a_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Category::create([
            'name' => 'Veículos',
            'slug' => 'veiculos',
            'icon' => 'fa-car',
            'color' => '#ff0000',
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'VEÍCULOS',
                'icon' => 'fa-car',
                'color' => '#00ff00',
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Category::count());
    }

    public function test_public_desktop_header_displays_the_site_name_next_to_the_logo(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="navbar-mobile-brand-title"', false)
            ->assertSee('Conectado em Sergipe');
    }

    public function test_authenticated_header_uses_the_profile_photo_when_available(): void
    {
        $user = User::factory()->create([
            'avatar' => 'uploads/avatar/foto-cabecalho.webp',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('class="marketplace-account-avatar"', false)
            ->assertSee('uploads/avatar/foto-cabecalho.webp');
    }

    private function createAd(User $user): Ad
    {
        return Ad::create([
            'user_id' => $user->id,
            'module' => 'vehicles',
            'title' => 'Veículo do teste administrativo',
            'slug' => 'veiculo-teste-admin-' . uniqid(),
            'description' => 'Anúncio usado para validar a moderação administrativa.',
            'price' => 80000,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
    }
}
