<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\CommunityHelpRequest;
use App\Models\CultureWork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCultureAndCommunityHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_culture_and_help_admin_pages_require_an_administrator(): void
    {
        foreach (['admin.culture.index', 'admin.community-help.index'] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('admin.login'));

            $this->actingAs(User::factory()->create(['role' => 'collaborator']))
                ->get(route($routeName))
                ->assertForbidden();

            $this->actingAs(User::factory()->create(['role' => 'admin']))
                ->get(route($routeName))
                ->assertOk();

            $this->app['auth']->logout();
        }
    }

    public function test_administrator_can_filter_hide_and_republish_cultural_works(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['name' => 'Autora Sergipana']);
        $published = $this->createCultureWork($author, 'Cordel da feira', 'published');
        $draft = $this->createCultureWork($author, 'Rascunho musical', 'draft', 'musica');

        $this->actingAs($admin)
            ->get(route('admin.culture.index', ['status' => 'published', 'q' => 'feira']))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertSee('Autora Sergipana');

        $this->actingAs($admin)
            ->post(route('admin.culture.action', $published), ['action' => 'hide'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('hidden', $published->fresh()->status);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $author->id,
            'kind' => 'culture_moderation',
        ]);

        $this->app['auth']->logout();
        $this->get(route('culture.show', $published->slug))->assertNotFound();
        $this->actingAs($author)->get(route('culture.show', $published->slug))->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.culture.action', $published), ['action' => 'publish'])
            ->assertSessionHas('success');

        $this->assertSame('published', $published->fresh()->status);
        $this->app['auth']->logout();
        $this->get(route('culture.show', $published->slug))->assertOk();
    }

    public function test_author_cannot_republish_a_work_hidden_by_administration(): void
    {
        $author = User::factory()->create();
        Ad::create([
            'user_id' => $author->id,
            'module' => 'services',
            'profile_kind' => 'cultural_artist',
            'advertiser_type' => 'Cordelista',
            'title' => 'Perfil cultural da autora',
            'slug' => 'perfil-cultural-autora',
            'description' => 'Perfil artístico usado para gerenciar obras culturais.',
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
        $work = $this->createCultureWork($author, 'Obra sob análise', 'hidden');

        $this->actingAs($author)
            ->put(route('culture.update', $work->id), [
                'title' => 'Obra revisada pelo autor',
                'category' => 'cordel',
                'summary' => 'Resumo revisado.',
                'content' => 'Conteúdo revisado.',
                'theme' => 'cultura local',
                'status' => 'published',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('hidden', $work->fresh()->status);
        $this->assertSame('Obra revisada pelo autor', $work->fresh()->title);
    }

    public function test_administrator_can_filter_and_open_help_requests_with_reported_responses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['name' => 'Morador de Aracaju']);
        $helper = User::factory()->create();
        $reporter = User::factory()->create();
        $pending = $this->createHelpRequest($owner, 'Preciso de apoio no bairro', 'pending');
        $open = $this->createHelpRequest($owner, 'Procuro transporte local', 'open');
        $response = $open->responses()->create([
            'user_id' => $helper->id,
            'message' => 'Consigo ajudar, mas esta mensagem precisa ser analisada.',
            'status' => 'published',
        ]);
        $response->reports()->create([
            'reporter_user_id' => $reporter->id,
            'reason' => 'scam',
            'details' => 'Solicitou pagamento antecipado.',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.community-help.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pending->title)
            ->assertDontSee($open->title);

        $this->actingAs($admin)
            ->get(route('admin.community-help.index', ['reported' => 1]))
            ->assertOk()
            ->assertSee($open->title)
            ->assertDontSee($pending->title);

        $this->actingAs($admin)
            ->get(route('admin.community-help.show', $open))
            ->assertOk()
            ->assertSee($open->title)
            ->assertSee('Possível golpe')
            ->assertSee('Solicitou pagamento antecipado.')
            ->assertSee(route('community-help.responses.moderate', [$open, $response]), false);
    }

    public function test_administrator_can_hide_and_restore_a_public_help_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $helpRequest = $this->createHelpRequest($owner, 'Pedido público moderável', 'open');

        $this->actingAs($admin)
            ->patch(route('community-help.moderate', $helpRequest), [
                'action' => 'hide',
                'moderation_reason' => 'Contém informação que precisa ser revisada.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('hidden', $helpRequest->fresh()->status);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'community_request_hidden',
        ]);

        $this->app['auth']->logout();
        $this->get(route('community-help.show', $helpRequest))->assertNotFound();

        $this->actingAs($admin)
            ->patch(route('community-help.moderate', $helpRequest), ['action' => 'restore'])
            ->assertSessionHas('success');

        $helpRequest->refresh();
        $this->assertSame('open', $helpRequest->status);
        $this->assertNotNull($helpRequest->expires_at);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'community_request_restored',
        ]);
    }

    private function createCultureWork(
        User $author,
        string $title,
        string $status,
        string $category = 'cordel'
    ): CultureWork {
        return CultureWork::create([
            'user_id' => $author->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'summary' => 'Obra usada para validar o painel cultural.',
            'content' => 'Conteúdo cultural sergipano.',
            'category' => $category,
            'theme' => 'cultura local',
            'status' => $status,
        ]);
    }

    private function createHelpRequest(User $owner, string $title, string $status): CommunityHelpRequest
    {
        return CommunityHelpRequest::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'category' => 'information',
            'title' => $title,
            'description' => 'Pedido comunitário criado para validar a administração e a moderação local.',
            'city' => 'Aracaju',
            'neighborhood' => 'Centro',
            'urgency' => 'normal',
            'status' => $status,
            'duration_days' => 7,
            'published_at' => $status === 'pending' ? null : now(),
            'expires_at' => $status === 'pending' ? null : now()->addDays(7),
        ]);
    }
}
