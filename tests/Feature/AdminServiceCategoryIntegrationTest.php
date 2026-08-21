<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceCategoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_is_connected_to_the_selected_service_profile_kind(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Farmacêutico',
                'module' => 'services',
                'profile_kind' => 'liberal_professional',
                'icon' => 'fa-prescription-bottle-medical',
                'color' => '#0d6efd',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Farmacêutico',
            'module' => 'services',
            'profile_kind' => 'liberal_professional',
            'active' => true,
        ]);

        $professional = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($professional)
            ->get(route('ad.create', [
                'module' => 'services',
                'profile_kind' => 'liberal_professional',
            ]))
            ->assertOk()
            ->assertSee('databaseServiceCategoriesByProfileKind', false)
            ->assertSee('Farmac\\u00eautico', false);

        $this->actingAs($professional)
            ->post(route('ad.store'), [
                'module' => 'services',
                'profile_kind' => 'liberal_professional',
                'category_name' => 'Farmacêutico',
                'title' => 'Farmacêutico Clínico em Aracaju',
                'city' => 'Aracaju',
                'description' => 'Orientação farmacêutica e acompanhamento profissional especializado.',
                'whatsapp' => '79999999999',
                'phone' => '79999999999',
                'liberal_credential' => 'CRF/SE 1234',
                'liberal_credential_issuer' => 'Conselho Regional de Farmácia de Sergipe',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Farmacêutico Clínico em Aracaju',
            'profile_kind' => 'liberal_professional',
            'advertiser_type' => 'Farmacêutico',
        ]);

        $this->actingAs($professional)
            ->get(route('module.services', ['profile_kind' => 'liberal_professional']))
            ->assertOk()
            ->assertSee('<option value="Farmacêutico"', false);

        $this->actingAs($professional)
            ->get(route('module.services'))
            ->assertOk()
            ->assertDontSee('<option value="Farmacêutico"', false);
    }

    public function test_service_category_requires_a_profile_kind_and_can_be_reassigned(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Corretora',
                'module' => 'services',
                'icon' => 'fa-house',
                'color' => '#0d6efd',
            ])
            ->assertSessionHasErrors('profile_kind');

        $category = Category::create([
            'name' => 'Corretora',
            'slug' => 'corretora',
            'module' => 'services',
            'profile_kind' => 'real_estate_agency',
            'icon' => 'fa-house',
            'color' => '#0d6efd',
            'sort_order' => 1,
            'active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), [
                'name' => 'Corretora',
                'module' => 'services',
                'profile_kind' => 'liberal_professional',
                'icon' => 'fa-house',
                'color' => '#0d6efd',
                'sort_order' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('liberal_professional', $category->fresh()->profile_kind);

        $this->actingAs($admin)
            ->get(route('admin.categories', ['q' => 'Corretora']))
            ->assertOk()
            ->assertSee('Profissional liberal');
    }
}
