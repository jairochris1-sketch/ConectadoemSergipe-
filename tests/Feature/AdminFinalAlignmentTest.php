<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\FeedPost;
use App\Models\ProviderClaim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFinalAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_users_assign_collaborator_and_control_account_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['name' => 'Pessoa da Comunidade', 'role' => 'user']);
        $other = User::factory()->create(['name' => 'Outro cadastro', 'role' => 'user']);

        $this->actingAs($admin)
            ->get(route('admin.users', ['q' => 'Comunidade', 'role' => 'user']))
            ->assertOk()
            ->assertSee($member->name)
            ->assertDontSee($other->name)
            ->assertSee(route('admin.users.status', $member), false)
            ->assertSee('value="collaborator"', false);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle_role', $member), ['role' => 'collaborator'])
            ->assertSessionHas('success');
        $this->assertSame('collaborator', $member->fresh()->role);

        $this->actingAs($admin)
            ->post(route('admin.users.status', $member), ['action' => 'suspend'])
            ->assertSessionHas('success');
        $this->assertNotNull($member->fresh()->suspended_at);

        $this->actingAs($admin)
            ->get(route('admin.users', ['account_status' => 'suspended']))
            ->assertOk()
            ->assertSee($member->name)
            ->assertDontSee($other->name);

        $this->actingAs($admin)
            ->post(route('admin.users.status', $member), ['action' => 'restore'])
            ->assertSessionHas('success');
        $this->assertNull($member->fresh()->suspended_at);
    }

    public function test_administrator_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.users.status', $admin), ['action' => 'suspend'])
            ->assertSessionHas('error');

        $this->assertNull($admin->fresh()->suspended_at);
    }

    public function test_administrator_can_filter_ads_by_moderation_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pending = $this->createAd($admin, 'Anúncio aguardando análise', 'pending');
        $active = $this->createAd($admin, 'Anúncio já publicado', 'active');

        $this->actingAs($admin)
            ->get(route('admin.ads', ['status' => 'pending']))
            ->assertOk()
            ->assertSee($pending->title)
            ->assertDontSee($active->title);
    }

    public function test_dashboard_attention_links_open_the_exact_pending_queues(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claimant = User::factory()->create();
        $reporter = User::factory()->create();
        $suspended = User::factory()->create(['suspended_at' => now()]);
        $ad = $this->createAd($admin, 'Perfil profissional pendente', 'pending');
        ProviderClaim::create([
            'ad_id' => $ad->id,
            'claimant_user_id' => $claimant->id,
            'relationship' => 'owner',
            'status' => ProviderClaim::STATUS_PENDING,
        ]);
        $pendingPost = FeedPost::create([
            'user_id' => $admin->id,
            'body' => 'Publicação aguardando análise administrativa.',
            'content_hash' => hash('sha256', 'post-pendente'),
            'status' => 'pending',
        ]);
        $reportedPost = FeedPost::create([
            'user_id' => $admin->id,
            'body' => 'Publicação denunciada pela comunidade.',
            'content_hash' => hash('sha256', 'post-denunciado'),
            'status' => 'published',
            'published_at' => now(),
        ]);
        $reportedPost->reports()->create([
            'reporter_user_id' => $reporter->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.ads', ['status' => 'pending']), false)
            ->assertSee(route('admin.provider_claims.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.feed.index', ['status' => 'pending']), false)
            ->assertSee(route('admin.feed.index', ['status' => 'reported']), false)
            ->assertSee(route('admin.users', ['account_status' => 'suspended']), false);

        $this->actingAs($admin)
            ->get(route('admin.feed.index', ['status' => 'reported']))
            ->assertOk()
            ->assertSee($reportedPost->body)
            ->assertDontSee($pendingPost->body);
    }

    private function createAd(User $owner, string $title, string $status): Ad
    {
        return Ad::create([
            'user_id' => $owner->id,
            'module' => 'services',
            'title' => $title,
            'slug' => str($title)->slug().'-'.uniqid(),
            'description' => 'Cadastro usado para auditar a fila administrativa.',
            'city' => 'Aracaju',
            'state' => 'SE',
            'status' => $status,
        ]);
    }
}
