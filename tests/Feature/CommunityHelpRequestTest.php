<?php

namespace Tests\Feature;

use App\Models\CommunityHelpRequest;
use App\Models\ReportNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunityHelpRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_browse_requests_but_must_sign_in_to_create_one(): void
    {
        $this->get(route('community-help.index'))
            ->assertOk()
            ->assertSee('O que você precisa resolver perto de você?');

        $this->get(route('community-help.create'))
            ->assertRedirect(route('login'));
    }

    public function test_member_submission_is_private_until_admin_approves_it(): void
    {
        $member = User::factory()->create(['city' => 'Aracaju']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($member)
            ->post(route('community-help.store'), $this->validPayload())
            ->assertRedirect();

        $helpRequest = CommunityHelpRequest::sole();
        $this->assertSame('pending', $helpRequest->status);
        $this->assertNull($helpRequest->published_at);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $admin->id,
            'kind' => 'community_request_moderation',
        ]);

        $this->app['auth']->logout();
        $this->get(route('community-help.show', $helpRequest))->assertNotFound();
        $this->actingAs($member)
            ->get(route('community-help.show', $helpRequest))
            ->assertOk()
            ->assertSee('Este pedido está em análise');

        $this->actingAs($admin)
            ->patch(route('community-help.moderate', $helpRequest), ['action' => 'approve'])
            ->assertSessionHas('success');

        $helpRequest->refresh();
        $this->assertSame('open', $helpRequest->status);
        $this->assertNotNull($helpRequest->published_at);

        $this->get(route('community-help.show', $helpRequest))
            ->assertOk()
            ->assertSee($helpRequest->title);
        $this->get(route('community-help.index'))
            ->assertOk()
            ->assertSee($helpRequest->title);
    }

    public function test_collaborator_cannot_moderate_member_requests(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create(['role' => 'collaborator']);
        $helpRequest = $this->makeHelpRequest($owner, ['status' => 'pending', 'published_at' => null]);

        $this->actingAs($collaborator)
            ->patch(route('community-help.moderate', $helpRequest), ['action' => 'approve'])
            ->assertForbidden();

        $this->assertSame('pending', $helpRequest->fresh()->status);
    }

    public function test_another_member_can_respond_and_owner_can_mark_request_resolved(): void
    {
        $owner = User::factory()->create();
        $helper = User::factory()->create();
        $helpRequest = $this->makeHelpRequest($owner);

        $this->actingAs($owner)
            ->post(route('community-help.respond', $helpRequest), ['message' => 'Eu mesmo vou responder.'])
            ->assertForbidden();

        $this->actingAs($helper)
            ->post(route('community-help.respond', $helpRequest), [
                'message' => 'Conheço um profissional da região e posso orientar pelo chat.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('community_help_responses', [
            'community_help_request_id' => $helpRequest->id,
            'user_id' => $helper->id,
        ]);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $owner->id,
            'kind' => 'community_request_response',
        ]);

        $this->actingAs($owner)
            ->patch(route('community-help.responses.select', [$helpRequest, $helpRequest->responses()->sole()]))
            ->assertSessionHas('success');

        $this->assertSame('resolved', $helpRequest->fresh()->status);
        $this->assertNotNull($helpRequest->fresh()->resolved_at);
        $this->assertTrue($helpRequest->responses()->sole()->is_selected);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $helper->id,
            'kind' => 'community_help_selected',
        ]);
        $this->get(route('community-help.index'))->assertDontSee($helpRequest->title);
        $this->get(route('community-help.index', ['status' => 'resolved']))->assertSee($helpRequest->title);
    }

    public function test_request_owner_cannot_change_a_pending_request_status(): void
    {
        $owner = User::factory()->create();
        $helpRequest = $this->makeHelpRequest($owner, ['status' => 'pending', 'published_at' => null]);

        $this->actingAs($owner)
            ->patch(route('community-help.status', $helpRequest), ['status' => 'resolved'])
            ->assertUnprocessable();

        $this->assertSame('pending', $helpRequest->fresh()->status);
    }

    public function test_owner_can_correct_a_rejected_request_and_send_it_back_to_moderation(): void
    {
        $owner = User::factory()->create(['city' => 'Aracaju']);
        $otherMember = User::factory()->create();
        $helpRequest = $this->makeHelpRequest($owner, [
            'status' => 'rejected',
            'published_at' => null,
            'moderation_reason' => 'Informe melhor o bairro.',
        ]);

        $this->actingAs($otherMember)
            ->get(route('community-help.edit', $helpRequest))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('community-help.edit', $helpRequest))
            ->assertOk()
            ->assertSee('Corrija e reenvie seu pedido');

        $payload = $this->validPayload();
        $payload['neighborhood'] = 'Atalaia';

        $this->actingAs($owner)
            ->put(route('community-help.update', $helpRequest), $payload)
            ->assertRedirect(route('community-help.show', $helpRequest));

        $helpRequest->refresh();
        $this->assertSame('pending', $helpRequest->status);
        $this->assertSame('Atalaia', $helpRequest->neighborhood);
        $this->assertNull($helpRequest->moderation_reason);
    }

    public function test_expired_requests_are_not_publicly_visible(): void
    {
        $owner = User::factory()->create();
        $helpRequest = $this->makeHelpRequest($owner, ['expires_at' => now()->subMinute()]);

        $this->get(route('community-help.index'))->assertDontSee($helpRequest->title);
        $this->get(route('community-help.show', $helpRequest))->assertNotFound();
        $this->actingAs($owner)->get(route('community-help.show', $helpRequest))->assertOk();
    }

    public function test_reported_response_can_be_moderated_and_a_selected_hidden_response_reopens_request(): void
    {
        $owner = User::factory()->create();
        $helper = User::factory()->create();
        $reporter = User::factory()->create();
        $collaborator = User::factory()->create(['role' => 'collaborator']);
        $admin = User::factory()->create(['role' => 'admin']);
        $helpRequest = $this->makeHelpRequest($owner, [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
        $response = $helpRequest->responses()->create([
            'user_id' => $helper->id,
            'message' => 'Resposta denunciável exclusiva para o teste.',
            'is_selected' => true,
        ]);

        $this->actingAs($helper)
            ->post(route('community-help.responses.report', [$helpRequest, $response]), ['reason' => 'spam'])
            ->assertForbidden();

        $this->actingAs($reporter)
            ->post(route('community-help.responses.report', [$helpRequest, $response]), [
                'reason' => 'scam',
                'details' => 'A resposta solicita um pagamento antecipado.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($reporter)
            ->post(route('community-help.responses.report', [$helpRequest, $response]), [
                'reason' => 'personal_data',
                'details' => 'Também contém informações pessoais.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('community_help_response_reports', 1);
        $this->assertDatabaseHas('community_help_response_reports', [
            'community_help_response_id' => $response->id,
            'reporter_user_id' => $reporter->id,
            'reason' => 'personal_data',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $admin->id,
            'kind' => 'community_response_report',
        ]);

        $this->actingAs($reporter)
            ->get(route('community-help.index', ['scope' => 'reported']))
            ->assertForbidden();
        $this->actingAs($admin)
            ->get(route('community-help.index', ['scope' => 'reported']))
            ->assertOk()
            ->assertSee($helpRequest->title);

        $this->actingAs($collaborator)
            ->patch(route('community-help.responses.moderate', [$helpRequest, $response]), ['action' => 'hide'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('community-help.responses.moderate', [$helpRequest, $response]), [
                'action' => 'hide',
                'moderation_reason' => 'Possível golpe.',
            ])
            ->assertSessionHas('success');

        $this->assertSame('hidden', $response->fresh()->status);
        $this->assertFalse($response->fresh()->is_selected);
        $this->assertSame('open', $helpRequest->fresh()->status);
        $this->assertNull($helpRequest->fresh()->resolved_at);
        $this->assertDatabaseHas('community_help_response_reports', [
            'community_help_response_id' => $response->id,
            'status' => 'actioned',
        ]);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $helper->id,
            'kind' => 'community_response_hidden',
        ]);

        $this->app['auth']->logout();
        $this->get(route('community-help.show', $helpRequest))
            ->assertOk()
            ->assertDontSee('Resposta denunciável exclusiva para o teste.');

        $this->actingAs($admin)
            ->patch(route('community-help.responses.moderate', [$helpRequest, $response]), ['action' => 'restore'])
            ->assertSessionHas('success');
        $this->assertSame('published', $response->fresh()->status);
    }

    private function validPayload(): array
    {
        return [
            'category' => 'service',
            'title' => 'Preciso de eletricista hoje',
            'description' => 'Uma tomada parou de funcionar e procuro orientação de um profissional da região.',
            'city' => 'Aracaju',
            'neighborhood' => 'Farolândia',
            'urgency' => 'today',
            'duration_days' => 7,
            'safety_acknowledged' => '1',
        ];
    }

    private function makeHelpRequest(User $owner, array $attributes = []): CommunityHelpRequest
    {
        return CommunityHelpRequest::create(array_merge([
            'public_id' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'category' => 'service',
            'title' => 'Ajuda local para instalação elétrica',
            'description' => 'Procuro uma pessoa da região que possa orientar sobre uma instalação elétrica simples.',
            'city' => 'Aracaju',
            'neighborhood' => 'Farolândia',
            'urgency' => 'normal',
            'status' => 'open',
            'expires_at' => now()->addDays(7),
            'published_at' => now(),
        ], $attributes));
    }
}
