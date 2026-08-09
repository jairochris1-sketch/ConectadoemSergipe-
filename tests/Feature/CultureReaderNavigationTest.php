<?php

namespace Tests\Feature;

use App\Models\CultureWork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CultureReaderNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cordel_reader_has_functional_page_navigation_controls(): void
    {
        $author = User::factory()->create();
        $work = CultureWork::create([
            'user_id' => $author->id,
            'title' => 'Cordel de teste',
            'slug' => 'cordel-de-teste',
            'content' => str_repeat("Primeira estrofe do cordel.\n", 100),
            'category' => 'cordel',
            'status' => 'published',
        ]);

        $this->get(route('culture.show', $work->slug))
            ->assertOk()
            ->assertSee('data-cordel-direction="previous"', false)
            ->assertSee('data-cordel-direction="next"', false)
            ->assertSee('data-cordel-page="0"', false)
            ->assertSee('data-cordel-page="1"', false)
            ->assertSee('showCordelPage(currentCordelPage + 1)', false)
            ->assertSee('Página ${currentCordelPage + 1} de ${cordelPages.length}', false)
            ->assertDontSee('scrollBy(', false);
    }
}
