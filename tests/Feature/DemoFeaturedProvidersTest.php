<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use App\Services\DemoAdSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoFeaturedProvidersTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_featured_profiles_are_idempotent_and_use_a_paid_plan(): void
    {
        DemoAdSeeder::seedFeaturedServiceProfilesIfNeeded();
        DemoAdSeeder::seedFeaturedServiceProfilesIfNeeded();

        $users = User::where(function ($query) {
            $query->where('email', 'like', 'demo.destaque.%@example.invalid')
                ->orWhere('email', 'like', 'demo.liberal.%@example.invalid');
        })->get();
        $providers = Ad::where('slug', 'like', 'demo-%')->where('module', 'services')->get();

        $this->assertCount(12, $users);
        $this->assertCount(12, $providers);
        $this->assertTrue($users->every(fn (User $user) => $user->subscription_plan === 'start'));
        $this->assertTrue($providers->every(fn (Ad $provider) => $provider->status === 'active'));
        $this->assertCount(6, $providers->where('profile_kind', 'professional'));
        $this->assertCount(6, $providers->where('profile_kind', 'liberal_professional'));
        $this->assertTrue($providers->where('profile_kind', 'liberal_professional')->every(
            fn (Ad $provider) => filled(data_get($provider->technical_specs, 'liberal_profile.credential'))
                && count(data_get($provider->technical_specs, 'liberal_profile.specialties', [])) >= 2
        ));
        $this->assertTrue($providers->every(fn (Ad $provider) => str_contains($provider->description, 'Perfil demonstrativo')));
    }
}
