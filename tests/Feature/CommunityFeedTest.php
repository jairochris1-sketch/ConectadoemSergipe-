<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\FeedPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommunityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_feed_allows_only_administrators_and_collaborators_to_publish(): void
    {
        $response = $this->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Comunidade Sergipana')
            ->assertSee('Explorar Sergipe')
            ->assertSee('community-two-column-layout', false)
            ->assertSee('community-controls-column', false)
            ->assertSee('community-compose-media-tools', false)
            ->assertSee('community-posts-column', false)
            ->assertDontSee('Entre na sua conta para comentar')
            ->assertSee('<header class="marketplace-header', false)
            ->assertSee('<footer class="site-footer', false);

        $regularUser = User::factory()->create();
        $this->actingAs($regularUser)->post(route('feed.store'), ['body' => 'Teste'])->assertForbidden();

        $professional = $this->publisher();
        $this->actingAs($professional)->post(route('feed.store'), ['body' => 'Tentativa profissional'])
            ->assertForbidden();

        $collaborator = User::factory()->create(['role' => 'collaborator']);
        $this->actingAs($collaborator)->post(route('feed.store'), ['type' => 'post', 'body' => 'Atualização do colaborador'])
            ->assertRedirect(route('feed.index'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('feed.store'), ['body' => 'Novidade local', 'city' => 'Aracaju'])
            ->assertRedirect(route('feed.index'));

        $this->assertDatabaseHas('feed_posts', ['user_id' => $admin->id, 'body' => 'Novidade local', 'status' => 'published']);
        $this->get(route('feed.index'))->assertSee('Novidade local');
    }

    public function test_administrator_image_post_is_published_without_self_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('feed.store'), [
            'body' => 'Foto da comunidade',
            'images' => [UploadedFile::fake()->createWithContent(
                'foto.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            )],
        ])->assertRedirect(route('feed.index'));

        $post = FeedPost::firstOrFail();
        $this->assertSame('published', $post->status);
        $this->assertCount(1, $post->images);
        $this->assertSame('approved', $post->images->first()->moderation_status);
        $this->get(route('feed.index'))->assertSee('Foto da comunidade');

        @unlink(public_path($post->images->first()->path));
    }

    public function test_likes_comments_reports_and_duplicate_protection_work(): void
    {
        $publisher = User::factory()->create(['role' => 'collaborator']);
        $participant = User::factory()->create();
        $this->actingAs($publisher)->post(route('feed.store'), ['body' => 'Conteúdo único']);
        $post = FeedPost::firstOrFail();

        $this->actingAs($publisher)->post(route('feed.store'), ['body' => 'Conteúdo único'])
            ->assertSessionHasErrors('body');
        $this->actingAs($participant)->postJson(route('feed.like', $post))
            ->assertOk()
            ->assertJson(['liked' => true, 'likes_count' => 1]);
        $this->actingAs($participant)->post(route('feed.comment', $post), ['body' => 'Muito bom!'])->assertSessionHas('success');
        $this->actingAs($participant)->post(route('feed.report', $post), ['reason' => 'spam'])->assertSessionHas('success');

        $this->assertDatabaseHas('feed_post_likes', ['feed_post_id' => $post->id, 'user_id' => $participant->id]);
        $this->assertDatabaseHas('feed_comments', ['feed_post_id' => $post->id, 'body' => 'Muito bom!']);
        $this->assertDatabaseHas('feed_post_reports', ['feed_post_id' => $post->id, 'reason' => 'spam']);

        $this->actingAs($participant)->postJson(route('feed.like', $post))
            ->assertOk()
            ->assertJson(['liked' => false, 'likes_count' => 0]);
    }

    public function test_admin_posts_cannot_be_commented_on_or_reported(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $this->actingAs($admin)->post(route('feed.store'), ['body' => 'Comunicado oficial']);
        $post = FeedPost::firstOrFail();

        $this->actingAs($member)
            ->post(route('feed.comment', $post), ['body' => 'Comentário'])
            ->assertForbidden();
        $this->actingAs($member)
            ->post(route('feed.report', $post), ['reason' => 'spam'])
            ->assertForbidden();

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertDontSee('action="'.route('feed.comment', $post).'"', false)
            ->assertDontSee('data-bs-target="#report-'.$post->id.'"', false);
    }

    public function test_post_formatting_is_rendered_safely(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('feed.store'), [
            'body' => '**Importante** *hoje* [Portal](https://example.com) [Lojas](/lojas) <script>alert(1)</script>',
        ]);

        $response = $this->get(route('feed.index'))
            ->assertOk()
            ->assertSee('<strong>Importante</strong>', false)
            ->assertSee('<em>hoje</em>', false)
            ->assertSee('href="https://example.com"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('href="'.url('/lojas').'" class="community-inline-link" data-community-modal-link', false)
            ->assertSee('id="community-link-modal"', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);

        $this->assertLessThan(
            strpos($response->getContent(), 'if(!type)return;'),
            strpos($response->getContent(), "const modal=document.getElementById('community-link-modal')")
        );
    }

    public function test_names_cities_mentions_and_official_badge_link_to_public_pages(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrador Conectado',
            'username' => 'admin.conectado',
            'role' => 'admin',
        ]);
        $member = User::factory()->create([
            'name' => 'Maria Santos',
            'username' => 'maria.santos',
            'city' => 'Lagarto',
        ]);

        $this->actingAs($admin)->post(route('feed.store'), [
            'body' => 'Boas-vindas, @maria.santos!',
            'city' => 'Aracaju',
        ]);
        $post = FeedPost::firstOrFail();
        $this->actingAs($member)->post(route('feed.comment', $post), [
            'body' => 'Obrigada, @admin.conectado!',
        ]);

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Conectado em Sergipe')
            ->assertDontSee('Administrador Conectado')
            ->assertSee(route('profile.show', $admin->username), false)
            ->assertSee(route('profile.show', $member->username), false)
            ->assertSee(route('feed.index', ['city' => 'Aracaju']), false)
            ->assertSee('color:#69b7ff', false)
            ->assertSee('.community-post-header>.community-avatar{width:34px;height:34px', false)
            ->assertSee('Conta oficial');

        $this->get(route('profile.show', $member->username))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('@maria.santos');

        $post->forceFill(['published_at' => now()->subDay()])->save();

        $this->get(route('profile.show', $admin->username))
            ->assertOk()
            ->assertSee('conectadoemsergipe.com')
            ->assertDontSee('@admin.conectado')
            ->assertSee('há 1 dia')
            ->assertDontSee('1 day ago')
            ->assertSee('border:1px solid rgba(255,255,255,.9);border-radius:18px', false);
    }

    public function test_homepage_uses_the_same_blue_clickable_name_pattern(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ad::create([
            'user_id' => $admin->id,
            'module' => 'products',
            'title' => 'Artesanato Sergipano',
            'slug' => 'artesanato-sergipano',
            'description' => 'Produto local em destaque.',
            'price' => 25,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $this->get(route('home', ['module' => 'products']))
            ->assertOk()
            ->assertSee('home-clickable-name', false)
            ->assertSee('Artesanato Sergipano');
    }

    public function test_community_search_filters_posts_by_content_author_and_city(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrador Conectado',
            'username' => 'admin.busca',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->post(route('feed.store'), [
            'body' => 'Festival de fotografia sergipana',
            'city' => 'Aracaju',
        ]);
        $this->actingAs($admin)->post(route('feed.store'), [
            'body' => 'Aviso sobre manutenção',
            'city' => 'Lagarto',
        ]);

        $this->get(route('feed.index', ['q' => 'fotografia']))
            ->assertOk()
            ->assertSee('Festival de fotografia sergipana')
            ->assertDontSee('Aviso sobre manutenção')
            ->assertSee('Suporte')
            ->assertSee(route('page.contact', ['tipo' => 'denuncia']), false)
            ->assertDontSee('aria-label="Pesquisar na comunidade"', false);

        $this->get(route('feed.index', ['q' => 'Administrador Conectado']))
            ->assertOk()
            ->assertSee('Festival de fotografia sergipana')
            ->assertSee('Aviso sobre manutenção');

        $this->get(route('feed.index', ['q' => 'Lagarto']))
            ->assertOk()
            ->assertDontSee('Festival de fotografia sergipana')
            ->assertSee('Aviso sobre manutenção');
    }

    public function test_notice_notifies_members_and_poll_accepts_one_changeable_vote(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'user', 'notifications_enabled' => true]);

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'notice',
            'title' => 'Manutenção programada',
            'notice_level' => 'important',
            'body' => 'O site ficará em manutenção.',
        ])->assertRedirect(route('feed.index'));

        $notice = FeedPost::where('type', 'notice')->firstOrFail();
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $member->id,
            'kind' => 'community_notice',
            'action_url' => '/comunidade#publicacao-'.$notice->id,
        ]);

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'poll',
            'title' => 'Qual conteúdo você prefere?',
            'poll_options' => ['Notícias', 'Eventos'],
        ])->assertRedirect(route('feed.index'));

        $poll = FeedPost::where('type', 'poll')->with('pollOptions')->firstOrFail();
        $this->actingAs($member)->get(route('feed.index'))
            ->assertOk()
            ->assertSee('data-community-poll-form', false)
            ->assertSee('data-community-poll-option', false)
            ->assertSee('data-community-poll-percentage', false)
            ->assertSee('data-community-poll-total', false)
            ->assertDontSee('>Votar</button>', false)
            ->assertDontSee('Alterar voto')
            ->assertDontSee('Registrando voto');
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $member->id,
            'kind' => 'community_poll',
            'action_url' => '/comunidade#publicacao-'.$poll->id,
        ]);
        $first = $poll->pollOptions[0];
        $second = $poll->pollOptions[1];
        $this->actingAs($member)->post(route('feed.vote', $poll), ['option_id' => $first->id])->assertSessionHas('success');
        $this->actingAs($member)
            ->postJson(route('feed.vote', $poll), ['option_id' => $second->id])
            ->assertOk()
            ->assertJsonPath('selected_option_id', $second->id)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('options.1.percentage', 100);

        $this->assertDatabaseCount('feed_poll_votes', 1);
        $this->assertDatabaseHas('feed_poll_votes', [
            'feed_post_id' => $poll->id,
            'feed_poll_option_id' => $second->id,
            'user_id' => $member->id,
        ]);

        $this->actingAs($admin)->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Ver quem votou')
            ->assertSee($member->name);
        $this->actingAs($member)->get(route('feed.index'))
            ->assertOk()
            ->assertDontSee('Ver quem votou');
    }

    public function test_post_owner_and_administrator_can_edit_a_post(): void
    {
        $collaborator = User::factory()->create(['role' => 'collaborator']);
        $member = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($collaborator)->post(route('feed.store'), [
            'body' => 'Texto original',
            'city' => 'Aracaju',
        ]);
        $post = FeedPost::firstOrFail();

        $this->actingAs($member)->patch(route('feed.update', $post), [
            'body' => 'Alteração sem permissão',
        ])->assertForbidden();

        $this->actingAs($collaborator)->patch(route('feed.update', $post), [
            'body' => 'Texto editado pelo autor',
            'city' => 'Lagarto',
            'text_alignment' => 'left',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('feed_posts', [
            'id' => $post->id,
            'body' => 'Texto editado pelo autor',
            'city' => 'Lagarto',
            'text_alignment' => 'left',
        ]);

        $this->actingAs($admin)->patch(route('feed.update', $post), [
            'body' => 'Texto revisado pelo administrador',
            'city' => 'Lagarto',
        ])->assertSessionHas('success');

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Editar post')
            ->assertSee('data-feed-delete-form', false)
            ->assertSee('data-community-align="left"', false)
            ->assertSee('community-text-left', false)
            ->assertSee('data-community-share', false)
            ->assertSee('data-community-like-form', false)
            ->assertDontSee('<i class="fa-regular fa-comment"></i>', false)
            ->assertSee(route('feed.index', ['post' => $post->id]).'#publicacao-'.$post->id, false)
            ->assertSee('color:#3b82f6', false)
            ->assertSee('Texto revisado pelo administrador');
    }

    public function test_post_can_be_deleted_as_json_without_page_reload(): void
    {
        $collaborator = User::factory()->create(['role' => 'collaborator']);
        $post = FeedPost::create([
            'user_id' => $collaborator->id,
            'type' => 'post',
            'body' => 'Post para excluir sem recarregar',
            'content_hash' => hash('sha256', 'post-para-excluir'),
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($collaborator)
            ->deleteJson(route('feed.destroy', $post))
            ->assertOk()
            ->assertJson([
                'message' => 'Publicação excluída.',
                'post_id' => $post->id,
            ]);

        $this->assertDatabaseMissing('feed_posts', ['id' => $post->id]);
    }

    public function test_administrator_can_pin_posts_and_topics_define_card_colors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $olderPost = FeedPost::create([
            'user_id' => $admin->id,
            'type' => 'post',
            'topic' => 'culture',
            'body' => 'Cultura sergipana em destaque',
            'content_hash' => hash('sha256', 'cultura-sergipana'),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        FeedPost::create([
            'user_id' => $admin->id,
            'type' => 'post',
            'topic' => 'urgent',
            'body' => 'Aviso urgente mais recente',
            'content_hash' => hash('sha256', 'aviso-urgente'),
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($member)->patch(route('feed.pin', $olderPost))->assertForbidden();
        $this->actingAs($admin)->patch(route('feed.pin', $olderPost))->assertSessionHas('success');

        $this->assertDatabaseHas('feed_posts', [
            'id' => $olderPost->id,
            'topic' => 'culture',
            'is_pinned' => true,
        ]);

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertSeeInOrder(['Cultura sergipana em destaque', 'Aviso urgente mais recente'])
            ->assertSee('community-topic-culture', false)
            ->assertSee('community-topic-urgent', false)
            ->assertSee('background:#080c10', false)
            ->assertSee('border:1px solid #2b3640', false)
            ->assertSee('.community-official-badge::after{content:"★★★★★"', false)
            ->assertSee('--community-topic-accent:#0f8b8d', false)
            ->assertSee('text-align:justify', false)
            ->assertSee('Fixado');
    }

    public function test_post_can_target_all_of_sergipe_or_a_valid_municipality(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'city' => 'Aracaju']);

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'post',
            'topic' => 'security',
            'city' => 'Sergipe',
            'body' => 'Orientação válida para todo o estado',
        ])->assertRedirect(route('feed.index'));

        $this->assertDatabaseHas('feed_posts', [
            'topic' => 'security',
            'city' => 'Sergipe',
        ]);

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Sergipe — todas as cidades')
            ->assertSee('community-topic-security', false);

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'post',
            'topic' => 'updates',
            'city' => 'Cidade inexistente',
            'body' => 'Cidade inválida',
        ])->assertSessionHasErrors('city');
    }

    public function test_post_can_disappear_after_the_selected_period(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'city' => 'Aracaju']);
        $member = User::factory()->create();

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'post',
            'topic' => 'updates',
            'expires_in' => '24_hours',
            'city' => 'Sergipe',
            'body' => 'Mensagem temporária de vinte e quatro horas',
        ])->assertRedirect(route('feed.index'));

        $post = FeedPost::firstOrFail();
        $this->assertNotNull($post->expires_at);
        $this->assertTrue($post->expires_at->between(now()->addHours(23), now()->addHours(25)));

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Mensagem temporária de vinte e quatro horas')
            ->assertSee('Desaparecer após 48 horas');

        $this->travel(25)->hours();

        $this->get(route('feed.index'))
            ->assertOk()
            ->assertDontSee('Mensagem temporária de vinte e quatro horas');
        $this->actingAs($member)->post(route('feed.like', $post))->assertNotFound();
        $this->assertDatabaseHas('feed_posts', ['id' => $post->id]);
    }

    public function test_staff_can_publish_a_direct_video_url_limited_to_one_minute(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'post',
            'video_url' => 'https://cdn.example.com/videos/sergipe.mp4',
            'video_url_duration' => 45.4,
            'city' => 'Sergipe',
        ])->assertRedirect(route('feed.index'));

        $post = FeedPost::firstOrFail();
        $this->assertSame('https://cdn.example.com/videos/sergipe.mp4', $post->video_url);
        $this->assertSame(46, $post->video_duration_seconds);

        $shareUrl = route('feed.index', ['post' => $post->id]);

        $this->get($shareUrl)
            ->assertOk()
            ->assertSee('class="community-video"', false)
            ->assertSee('controlslist="nodownload noremoteplayback"', false)
            ->assertSee('disablepictureinpicture', false)
            ->assertSee('data-community-video-play', false)
            ->assertSee('@media(hover:none),(pointer:coarse){.community-video-play{display:none!important}}', false)
            ->assertSee('<meta property="og:type" content="video.other">', false)
            ->assertSee('<meta property="og:video" content="'.$post->video_url.'">', false)
            ->assertSee('data-share-url="'.$shareUrl.'#publicacao-'.$post->id.'"', false)
            ->assertSee($post->video_url, false);

        $this->actingAs($admin)->from(route('feed.index'))->post(route('feed.store'), [
            'type' => 'post',
            'video_url' => 'https://cdn.example.com/videos/longo.mp4',
            'video_url_duration' => 61,
        ])->assertSessionHasErrors('video_url_duration');
    }

    public function test_staff_can_upload_a_valid_mp4_and_it_is_deleted_with_the_post(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $payload = "\0\0\0\0".pack('N', 0).pack('N', 0).pack('N', 1000).pack('N', 59000).str_repeat("\0", 20);
        $mvhd = pack('N', 8 + strlen($payload)).'mvhd'.$payload;
        $moov = pack('N', 8 + strlen($mvhd)).'moov'.$mvhd;
        $ftyp = pack('N', 24).'ftyp'.'isom'.pack('N', 0).'isomiso2';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'feed-video-');
        file_put_contents($temporaryPath, $ftyp.$moov);
        $video = new UploadedFile($temporaryPath, 'sergipe.mp4', 'video/mp4', null, true);

        $this->actingAs($admin)->post(route('feed.store'), [
            'type' => 'post',
            'video' => $video,
            'city' => 'Sergipe',
        ])->assertRedirect(route('feed.index'));

        $post = FeedPost::firstOrFail();
        $this->assertSame(59, $post->video_duration_seconds);
        $this->assertNotNull($post->video_path);
        $this->assertFileExists(public_path($post->video_path));

        $storedPath = public_path($post->video_path);
        $this->actingAs($admin)->delete(route('feed.destroy', $post))->assertRedirect();
        $this->assertFileDoesNotExist($storedPath);
    }

    public function test_hybrid_feed_interleaves_relevant_and_sponsored_ads_without_changing_post_permissions(): void
    {
        $this->enableSponsoredPlan('start', 25);
        $admin = User::factory()->create(['role' => 'admin']);
        $viewer = User::factory()->create(['role' => 'user', 'city' => 'Aracaju']);
        $paidOwner = User::factory()->create(['subscription_plan' => 'start']);
        $freeOwner = User::factory()->create(['subscription_plan' => 'free']);

        FeedPost::create([
            'user_id' => $admin->id,
            'body' => 'Primeira atualização oficial',
            'content_hash' => hash('sha256', 'primeira-atualizacao-oficial'),
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        FeedPost::create([
            'user_id' => $admin->id,
            'body' => 'Segunda atualização oficial',
            'content_hash' => hash('sha256', 'segunda-atualizacao-oficial'),
            'status' => 'published',
            'published_at' => now(),
        ]);

        $paidAd = Ad::create([
            'user_id' => $paidOwner->id,
            'module' => 'products',
            'title' => 'Oferta patrocinada de Aracaju',
            'slug' => 'oferta-patrocinada-aracaju',
            'description' => str_repeat('Produto completo e bem descrito em Aracaju. ', 3),
            'price' => 120,
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
        Ad::create([
            'user_id' => $freeOwner->id,
            'module' => 'services',
            'title' => 'Serviço local recomendado',
            'slug' => 'servico-local-recomendado',
            'description' => str_repeat('Serviço profissional disponível na cidade. ', 3),
            'city' => 'Aracaju',
            'status' => 'active',
        ]);

        $this->actingAs($viewer)->get(route('feed.index'))
            ->assertOk()
            ->assertSee('Primeira atualização oficial')
            ->assertSee('Segunda atualização oficial')
            ->assertSee('Oferta patrocinada de Aracaju')
            ->assertSee('Serviço local recomendado')
            ->assertSee('Patrocinado')
            ->assertSee('data-feed-ad-card', false)
            ->assertSee('fa-star', false)
            ->assertSee('Ver anúncio')
            ->assertSee('Por que estou vendo isso?')
            ->assertSee('Para você')
            ->assertSee('Perto de você')
            ->assertDontSee('Criar post no Conectado em Sergipe');

        $this->actingAs($viewer)->postJson(route('feed.ads.event', $paidAd), [
            'event_type' => 'impression',
            'mode' => 'for_you',
            'city' => 'Aracaju',
        ])->assertOk()->assertJson(['recorded' => true]);

        $this->actingAs($viewer)->postJson(route('feed.ads.event', $paidAd), [
            'event_type' => 'impression',
            'mode' => 'for_you',
            'city' => 'Aracaju',
        ])->assertOk();

        $this->assertDatabaseCount('feed_ad_events', 1);
        $this->assertDatabaseHas('feed_ad_events', [
            'user_id' => $viewer->id,
            'ad_id' => $paidAd->id,
            'event_type' => 'impression',
            'is_sponsored' => true,
        ]);

        $this->actingAs($viewer)->postJson(route('feed.ads.event', $paidAd), [
            'event_type' => 'dismiss',
            'mode' => 'for_you',
        ])->assertOk();

        $this->actingAs($viewer)->get(route('feed.index'))
            ->assertOk()
            ->assertDontSee('Oferta patrocinada de Aracaju');

        $this->assertSame('staff_only', config('feed.publishing_mode'));
    }

    public function test_guest_feed_event_uses_a_pseudonymous_session_key_without_ip_data(): void
    {
        $owner = User::factory()->create();
        $ad = Ad::create([
            'user_id' => $owner->id,
            'module' => 'products',
            'title' => 'Produto para visitante',
            'slug' => 'produto-para-visitante',
            'description' => 'Produto recomendado sem identificar o visitante.',
            'price' => 50,
            'city' => 'Lagarto',
            'status' => 'active',
        ]);

        $this->postJson(route('feed.ads.event', $ad), [
            'event_type' => 'impression',
            'mode' => 'recent',
            'city' => 'Lagarto',
        ])->assertOk();

        $event = DB::table('feed_ad_events')->first();
        $this->assertNull($event->user_id);
        $this->assertSame(64, strlen($event->visitor_key));
        $this->assertStringNotContainsString('ip', strtolower((string) $event->context));
        $this->assertStringNotContainsString('agent', strtolower((string) $event->context));
    }

    private function enableSponsoredPlan(string $slug, float $price): void
    {
        $now = now();
        $planId = DB::table('plans')->insertGetId([
            'slug' => $slug,
            'name' => 'Plano de teste',
            'price' => $price,
            'color' => 'primary',
            'is_active' => true,
            'is_highlighted' => false,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $featureId = DB::table('plan_features')->where('key', 'feed_sponsored')->value('id');
        DB::table('plan_feature_values')->insert([
            'plan_id' => $planId,
            'plan_feature_id' => $featureId,
            'value' => '1',
            'show_on_page' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function publisher(): User
    {
        $user = User::factory()->create(['city' => 'Aracaju']);
        Ad::create([
            'user_id' => $user->id,
            'module' => 'services',
            'advertiser_type' => 'Prestador de Serviço',
            'title' => 'Perfil para o feed',
            'slug' => 'perfil-feed-'.$user->id,
            'description' => 'Perfil profissional ativo.',
            'city' => 'Aracaju',
            'status' => 'active',
        ]);
        return $user;
    }
}
