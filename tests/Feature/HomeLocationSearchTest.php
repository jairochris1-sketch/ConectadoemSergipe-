<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeLocationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_location_builds_a_clean_city_only_search(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('placeholder="O que você procura em Sergipe?"', false)
            ->assertSee("queryInput.placeholder = `O que você procura em \${selectedCity || 'Sergipe'}?`", false)
            ->assertSee('updateSearchPlaceholder()', false)
            ->assertSee("categoryFilter.value = ''", false)
            ->assertSee("moduleValue.value = ''", false)
            ->assertSee("serviceCategoryValue.value = ''", false)
            ->assertSee('localStorage.removeItem(storageKey)', false)
            ->assertSee("method: 'POST'", false)
            ->assertSee('body: JSON.stringify({ city })', false)
            ->assertSee("destination.search = ''", false)
            ->assertSee('window.location.assign(destination.toString())', false)
            ->assertDontSee("destination.searchParams.set('city', city)", false)
            ->assertSee('if (searchTerm.length < 2)', false)
            ->assertSee("'not-allowed': 'Permita o acesso ao microfone", false)
            ->assertSee('recognition.addEventListener(\'error\'', false)
            ->assertSee('navigator.mediaDevices.getUserMedia({ audio: true })', false)
            ->assertSee('id="home-voice-status"', false)
            ->assertSee('findSpokenServiceCategory(transcript)', false)
            ->assertSee('Categoria reconhecida:', false)
            ->assertSee('const automaticSearchDelay = 2000', false)
            ->assertDontSee('scheduleAutomaticSearch(nearestMunicipality.name)', false);
    }

    public function test_city_only_request_does_not_force_a_service_category(): void
    {
        $this->get(route('home', ['city' => 'Nossa Senhora da Glória']))
            ->assertOk()
            ->assertSee('placeholder="O que você procura em Nossa Senhora da Glória?"', false)
            ->assertSee('value="Nossa Senhora da Glória" selected', false)
            ->assertSee('name="module" value=""', false)
            ->assertSee('name="category"', false)
            ->assertDontSee('name="category" value="Marcenaria"', false);
    }

    public function test_active_gps_location_uses_the_city_in_the_search_placeholder(): void
    {
        $this->withSession([
            'location_filter' => [
                'enabled' => true,
                'city' => 'Nossa Senhora da Glória',
            ],
        ])->get(route('home'))
            ->assertOk()
            ->assertSee('placeholder="O que você procura em Nossa Senhora da Glória?"', false);
    }

    public function test_desktop_hero_spacing_and_provider_heading_apply_to_guests_and_users(): void
    {
        $layoutAssertions = static function ($response): void {
            $response
                ->assertOk()
                ->assertSee('.hero-search-card-container {', false)
                ->assertSee('margin-top: -145px;', false)
                ->assertSee('.home-provider-heading-row {', false)
                ->assertSee('grid-template-columns: minmax(0, 1fr) auto;', false)
                ->assertSee('white-space: normal;', false)
                ->assertSee('home-provider-heading-row', false);
        };

        $guestResponse = $this->get(route('home'));
        $layoutAssertions($guestResponse);
        $guestResponse->assertSee('home-guest', false);

        $userResponse = $this->actingAs(User::factory()->create())->get(route('home'));
        $layoutAssertions($userResponse);
        $userResponse->assertSee('home-authenticated', false);
    }

    public function test_text_search_also_matches_professional_type(): void
    {
        $owner = User::factory()->create();
        $provider = Ad::create([
            'user_id' => $owner->id,
            'module' => 'services',
            'advertiser_type' => 'Pedreiro',
            'title' => 'Reformas do João',
            'slug' => 'reformas-do-joao',
            'description' => 'Reformas residenciais em Sergipe.',
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $response = $this->get(route('home', ['q' => 'Pedreiro']))
            ->assertOk();

        $this->assertTrue(
            $response->viewData('searchResults')->contains('id', $provider->id)
        );
    }
}
