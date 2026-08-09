<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\ProviderClaim;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderClaimFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_unclaimed_profile_uses_its_own_public_contacts_and_offers_claim_action(): void
    {
        $curator = User::factory()->create([
            'name' => 'Conta administrativa',
            'role' => 'admin',
            'phone' => '79111111111',
            'whatsapp' => '79111111111',
        ]);
        $provider = $this->unclaimedProvider($curator, [
            'contact_phone' => '79222222222',
            'contact_whatsapp' => '79333333333',
        ]);

        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertSee('Perfil não reivindicado')
            ->assertSee('Reivindicar este perfil')
            ->assertSee(route('provider.claim.create', $provider))
            ->assertSee('https://wa.me/5579333333333', false)
            ->assertSee('tel:79222222222', false)
            ->assertDontSee('https://wa.me/5579111111111', false)
            ->assertDontSee(route('chat.index', ['with' => $curator->id]), false)
            ->assertDontSee($curator->name);
    }

    public function test_admin_can_create_an_unclaimed_service_profile_with_separate_contacts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $selectedUser = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.ads.store'), [
                'user_id' => $selectedUser->id,
                'module' => 'services',
                'title' => 'Pintor cadastrado pelo administrador',
                'city' => 'Aracaju',
                'description' => 'Perfil inicial para o profissional reivindicar depois.',
                'contact_phone' => '(79) 98888-7777',
                'contact_whatsapp' => '(79) 99999-6666',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('ads', [
            'title' => 'Pintor cadastrado pelo administrador',
            'user_id' => $admin->id,
            'module' => 'services',
            'is_claimed' => false,
            'claiming_enabled' => false,
            'contact_phone' => '(79) 98888-7777',
            'contact_whatsapp' => '(79) 99999-6666',
        ]);
    }

    public function test_admin_full_publish_flow_keeps_unclaimed_provider_contacts_off_admin_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone' => '79111111111',
            'whatsapp' => '79111111111',
        ]);

        $this->actingAs($admin)
            ->post(route('ad.store'), [
                'module' => 'services',
                'category_name' => 'Pintor',
                'title' => 'Pintor com perfil completo não reivindicado',
                'city' => 'Aracaju',
                'description' => 'Perfil criado pelo formulário completo do administrador.',
                'phone' => '79222222222',
                'whatsapp' => '79333333333',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ads', [
            'title' => 'Pintor com perfil completo não reivindicado',
            'user_id' => $admin->id,
            'is_claimed' => false,
            'claiming_enabled' => false,
            'contact_phone' => '79222222222',
            'contact_whatsapp' => '79333333333',
        ]);
        $this->assertSame('79111111111', $admin->fresh()->phone);
        $this->assertSame('79111111111', $admin->fresh()->whatsapp);
    }

    public function test_only_creator_admin_can_activate_and_disable_claiming_on_public_profile(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone' => '79911112222',
            'whatsapp' => '79933334444',
        ]);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $provider = $this->unclaimedProvider($admin, [
            'is_claimed' => true,
            'claiming_enabled' => false,
            'contact_phone' => null,
            'contact_whatsapp' => null,
        ]);

        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertDontSee('Reivindicar este perfil');

        $this->actingAs($otherAdmin)
            ->post(route('admin.ads.toggle_claiming', $provider), ['enabled' => 1])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.ads.toggle_claiming', $provider), ['enabled' => 1])
            ->assertSessionHasNoErrors()
            ->assertRedirect()
            ->assertSessionHas('success');

        $provider->refresh();
        $this->assertTrue($provider->claiming_enabled);
        $this->assertFalse($provider->is_claimed);
        $this->assertNull($provider->contact_phone);
        $this->assertNull($provider->contact_whatsapp);

        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertSee('Reivindicar este perfil');

        $this->actingAs($admin)
            ->post(route('admin.ads.toggle_claiming', $provider), ['enabled' => 0])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertFalse($provider->fresh()->claiming_enabled);
        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertDontSee('Reivindicar este perfil');

        $this->actingAs(User::factory()->create())
            ->get(route('provider.claim.create', $provider))
            ->assertNotFound();
    }

    public function test_guest_is_sent_to_login_and_authenticated_user_can_request_a_profile(): void
    {
        $provider = $this->unclaimedProvider(User::factory()->create(['role' => 'admin']));

        $this->get(route('provider.claim.create', $provider))
            ->assertRedirect(route('login'));

        $claimant = User::factory()->create([
            'phone' => '79999999999',
        ]);

        $this->actingAs($claimant)
            ->get(route('provider.claim.create', $provider))
            ->assertOk()
            ->assertSee('Assumir o perfil de '.$provider->title);

        $this->actingAs($claimant)
            ->post(route('provider.claim.store', $provider), [
                'relationship' => 'professional',
                'verification_phone' => '(79) 99999-9999',
                'verification_email' => $claimant->email,
                'explanation' => 'Posso confirmar pelo WhatsApp publicado.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('provider.show', $provider->slug))
            ->assertSessionHas('claim_success');

        $this->assertDatabaseHas('provider_claims', [
            'ad_id' => $provider->id,
            'claimant_user_id' => $claimant->id,
            'relationship' => 'professional',
            'verification_phone' => '79999999999',
            'status' => ProviderClaim::STATUS_PENDING,
        ]);
    }

    public function test_same_user_cannot_create_two_pending_claims_for_the_same_profile(): void
    {
        $provider = $this->unclaimedProvider(User::factory()->create(['role' => 'admin']));
        $claimant = User::factory()->create();

        ProviderClaim::create([
            'ad_id' => $provider->id,
            'claimant_user_id' => $claimant->id,
            'relationship' => 'professional',
            'verification_email' => $claimant->email,
            'status' => ProviderClaim::STATUS_PENDING,
        ]);

        $this->actingAs($claimant)
            ->post(route('provider.claim.store', $provider), [
                'relationship' => 'owner',
                'verification_email' => $claimant->email,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('provider.claim.create', $provider))
            ->assertSessionHas('info');

        $this->assertSame(1, ProviderClaim::where('ad_id', $provider->id)->count());
    }

    public function test_admin_approval_transfers_existing_profile_and_closes_competing_claims(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claimant = User::factory()->create(['subscription_plan' => 'free']);
        $otherClaimant = User::factory()->create();
        $provider = $this->unclaimedProvider($admin);

        $review = Review::create([
            'ad_id' => $provider->id,
            'user_id' => $otherClaimant->id,
            'rating' => 5,
            'comment' => 'Avaliação preservada durante a transferência.',
            'content_hash' => hash('sha256', 'provider-claim-review'),
            'status' => 'approved',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'abuse_fingerprint' => hash('sha256', 'provider-claim-fingerprint'),
        ]);
        $claim = $this->claimFor($provider, $claimant);
        $competingClaim = $this->claimFor($provider, $otherClaimant);
        $originalSlug = $provider->slug;

        $this->actingAs($admin)
            ->get(route('admin.provider_claims.index'))
            ->assertOk()
            ->assertSee('Reivindicações de perfis')
            ->assertSee($provider->title)
            ->assertSee($claimant->name)
            ->assertSee('Aprovar e transferir perfil');

        $this->actingAs($admin)
            ->post(route('admin.provider_claims.review', $claim), [
                'action' => 'approve',
                'admin_note' => 'Confirmado pelo telefone público.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect()
            ->assertSessionHas('success');

        $provider->refresh();
        $this->assertSame($claimant->id, $provider->user_id);
        $this->assertTrue($provider->is_claimed);
        $this->assertFalse($provider->claiming_enabled);
        $this->assertNotNull($provider->claimed_at);
        $this->assertSame($originalSlug, $provider->slug);
        $this->assertTrue($provider->reviews()->whereKey($review->id)->exists());
        $this->assertSame(ProviderClaim::STATUS_APPROVED, $claim->fresh()->status);
        $this->assertSame($admin->id, $claim->fresh()->reviewed_by_user_id);
        $this->assertSame(ProviderClaim::STATUS_REJECTED, $competingClaim->fresh()->status);

        $this->actingAs($claimant)
            ->get(route('ad.edit', $provider->id))
            ->assertOk();

        $this->get(route('provider.show', $provider->slug))
            ->assertOk()
            ->assertSee('Perfil reivindicado')
            ->assertDontSee('Reivindicar este perfil');
    }

    public function test_regular_user_cannot_review_claims_and_claimed_profile_cannot_be_requested(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $claimant = User::factory()->create();
        $provider = $this->unclaimedProvider($admin);
        $claim = $this->claimFor($provider, $claimant);

        $this->actingAs($claimant)
            ->post(route('admin.provider_claims.review', $claim), ['action' => 'approve'])
            ->assertForbidden();

        $provider->update(['is_claimed' => true]);

        $this->actingAs($claimant)
            ->get(route('provider.claim.create', $provider))
            ->assertStatus(409);
    }

    private function unclaimedProvider(User $curator, array $attributes = []): Ad
    {
        return Ad::create(array_merge([
            'user_id' => $curator->id,
            'module' => 'services',
            'advertiser_type' => 'Eletricista',
            'title' => 'Eletricista disponível para reivindicação',
            'slug' => 'eletricista-disponivel-para-reivindicacao-'.$curator->id,
            'description' => 'Perfil cadastrado pela plataforma antes do lançamento.',
            'price' => 0,
            'city' => 'Aracaju',
            'status' => 'active',
            'views' => 0,
            'is_claimed' => false,
            'claiming_enabled' => true,
        ], $attributes));
    }

    private function claimFor(Ad $provider, User $claimant): ProviderClaim
    {
        return ProviderClaim::create([
            'ad_id' => $provider->id,
            'claimant_user_id' => $claimant->id,
            'relationship' => 'professional',
            'verification_email' => $claimant->email,
            'status' => ProviderClaim::STATUS_PENDING,
        ]);
    }
}
