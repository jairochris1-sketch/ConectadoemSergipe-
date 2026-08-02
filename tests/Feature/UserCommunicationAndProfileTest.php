<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\ReportNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UserCommunicationAndProfileTest extends TestCase
{
    use RefreshDatabase;

    private array $uploadedPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->uploadedPaths as $path) {
            File::delete(public_path($path));
        }

        parent::tearDown();
    }

    public function test_panel_uses_compact_chat_and_notification_icons_instead_of_create_ad_button(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $user->id,
            'content' => 'Mensagem ainda não lida.',
            'is_read' => false,
            'created_at' => now(),
        ]);
        $notification = ReportNotification::create([
            'user_id' => $user->id,
            'kind' => 'review_received',
            'message' => 'Você recebeu uma nova avaliação.',
            'action_url' => route('chat.index', ['with' => $sender->id], false),
        ]);

        $this->actingAs($user)
            ->get(route('user.panel'))
            ->assertOk()
            ->assertSee('class="panel-quick-icon"', false)
            ->assertSee(route('chat.index'), false)
            ->assertSee('href="#notificacoes"', false)
            ->assertSee(route('user.notifications.open', $notification), false)
            ->assertSee('Desativar notificações')
            ->assertSee('Você recebeu uma nova avaliação.')
            ->assertDontSee('Criar Novo Anúncio');

        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($user)
            ->get(route('user.notifications.open', $notification))
            ->assertRedirect(route('chat.index', ['with' => $sender->id], false));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_chat_sends_reads_and_replies_to_messages_and_creates_notification(): void
    {
        $sender = User::factory()->create(['name' => 'Pessoa Remetente']);
        $receiver = User::factory()->create(['name' => 'Pessoa Destinatária']);

        $this->actingAs($sender)
            ->post(route('chat.send'), [
                'receiver_id' => $receiver->id,
                'content' => 'Olá, gostaria de conversar sobre o serviço.',
            ])
            ->assertRedirect(route('chat.index', ['with' => $receiver->id]));

        $message = Message::firstOrFail();
        $this->assertFalse($message->is_read);
        $this->assertDatabaseHas('report_notifications', [
            'user_id' => $receiver->id,
            'kind' => 'message_received',
            'action_url' => route('chat.index', ['with' => $sender->id], false),
        ]);

        $this->actingAs($receiver)
            ->get(route('chat.index', ['with' => $sender->id]))
            ->assertOk()
            ->assertSee('Pessoa Remetente')
            ->assertSee('Olá, gostaria de conversar sobre o serviço.')
            ->assertSee('name="content"', false)
            ->assertSee('name="receiver_id"', false);

        $this->assertTrue($message->fresh()->is_read);

        $this->actingAs($receiver)
            ->post(route('chat.send'), [
                'receiver_id' => $sender->id,
                'content' => 'Olá! Como posso ajudar?',
            ])
            ->assertRedirect(route('chat.index', ['with' => $sender->id]));

        $this->assertDatabaseCount('messages', 2);
    }

    public function test_user_can_disable_and_enable_notifications(): void
    {
        $sender = User::factory()->create(['name' => 'Remetente']);
        $receiver = User::factory()->create(['notifications_enabled' => true]);

        $this->actingAs($receiver)
            ->post(route('user.notifications.preference'), ['notifications_enabled' => 0])
            ->assertSessionHas('notification_preference_success');

        $this->assertFalse($receiver->fresh()->notifications_enabled);

        $this->actingAs($sender)
            ->post(route('chat.send'), [
                'receiver_id' => $receiver->id,
                'content' => 'Mensagem entregue sem gerar notificação.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseCount('report_notifications', 0);

        $this->actingAs($receiver)
            ->post(route('user.notifications.preference'), ['notifications_enabled' => 1])
            ->assertSessionHas('notification_preference_success');

        $this->assertTrue($receiver->fresh()->notifications_enabled);

        $this->actingAs($sender)
            ->post(route('chat.send'), [
                'receiver_id' => $receiver->id,
                'content' => 'Mensagem entregue com notificação ativa.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('messages', 2);
        $this->assertDatabaseCount('report_notifications', 1);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = ReportNotification::create([
            'user_id' => $owner->id,
            'kind' => 'message_received',
            'message' => 'Notificação privada.',
            'action_url' => '/chat',
        ]);

        $this->actingAs($otherUser)
            ->get(route('user.notifications.open', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_save_personal_display_integrations_and_notification_settings(): void
    {
        $user = User::factory()->create(['whatsapp' => '79999999999']);

        $this->actingAs($user)
            ->get(route('user.settings'))
            ->assertOk()
            ->assertSee('Configurações')
            ->assertSee('name="header_layout"', false)
            ->assertSee('name="theme_preference"', false)
            ->assertSee('name="smart_search_enabled"', false)
            ->assertSee('Integrações')
            ->assertSee('notification_messages_enabled', false);

        $this->actingAs($user)
            ->post(route('user.settings.update'), [
                'header_layout' => 'vertical',
                'theme_preference' => 'dark',
                'notifications_enabled' => 1,
                'notification_messages_enabled' => 0,
                'notification_reviews_enabled' => 1,
                'notification_reports_enabled' => 0,
                'smart_search_enabled' => 0,
                'whatsapp' => '(79) 98888-7777',
                'instagram' => '@perfil.teste',
                'facebook' => 'https://facebook.com/perfil.teste',
                'website' => 'https://example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('settings_success')
            ->assertSessionHas('saved_theme_preference', 'dark');

        $user->refresh();
        $this->assertSame('vertical', $user->header_layout);
        $this->assertSame('dark', $user->theme_preference);
        $this->assertFalse($user->notification_messages_enabled);
        $this->assertTrue($user->notification_reviews_enabled);
        $this->assertFalse($user->notification_reports_enabled);
        $this->assertFalse($user->smart_search_enabled);
        $this->assertSame('79988887777', $user->whatsapp);
        $this->assertSame('@perfil.teste', $user->instagram);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('site-header-layout-vertical', false)
            ->assertSee('marketplace-header-layout-vertical', false);
    }

    public function test_user_notification_category_preference_is_respected(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create([
            'notifications_enabled' => true,
            'notification_messages_enabled' => false,
        ]);

        $this->actingAs($sender)
            ->post(route('chat.send'), [
                'receiver_id' => $receiver->id,
                'content' => 'Mensagem entregue sem alerta por preferência individual.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('messages', 1);
        $this->assertDatabaseCount('report_notifications', 0);
    }

    public function test_free_user_can_change_avatar_twice_and_is_then_locked_for_thirty_days(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '79999999999',
            'subscription_plan' => 'free',
        ]);

        $this->actingAs($user)
            ->post(route('user.avatar.update'), ['avatar' => $this->fakeAvatar('foto-1.png')])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $user->refresh();
        $this->uploadedPaths[] = $user->avatar;
        $this->assertSame(1, $user->avatar_change_count);
        $this->assertNull($user->avatar_change_locked_until);

        $this->actingAs($user)
            ->post(route('user.avatar.update'), ['avatar' => $this->fakeAvatar('foto-2.png')])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->uploadedPaths[] = $user->avatar;
        $avatarAfterSecondChange = $user->avatar;
        $this->assertSame(2, $user->avatar_change_count);
        $this->assertNotNull($user->avatar_change_locked_until);
        $this->assertTrue($user->avatar_change_locked_until->between(now()->addDays(29), now()->addDays(31)));

        $this->actingAs($user)
            ->post(route('user.avatar.update'), ['avatar' => $this->fakeAvatar('foto-3.png')])
            ->assertSessionHasErrors('avatar');

        $this->assertSame($avatarAfterSecondChange, $user->fresh()->avatar);

        $this->actingAs($user)
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('Editar Perfil');
    }

    public function test_user_can_update_avatar_directly_from_panel_icon(): void
    {
        $user = User::factory()->create([
            'whatsapp' => '79999999999',
            'subscription_plan' => 'pro',
        ]);

        $response = $this->actingAs($user)
            ->post(route('user.avatar.update'), [
                'avatar' => $this->fakeAvatar('painel-foto.png'),
            ]);

        $response->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Foto de perfil atualizada com sucesso!');

        $user->refresh();
        $this->uploadedPaths[] = $user->avatar;
        $this->assertNotNull($user->avatar);
    }

    private function profilePayload(User $user, UploadedFile $avatar): array
    {
        return [
            'name' => $user->name,
            'username' => $user->username,
            'phone' => $user->phone,
            'whatsapp' => $user->whatsapp,
            'city' => $user->city,
            'avatar' => $avatar,
        ];
    }

    private function fakeAvatar(string $name): UploadedFile
    {
        $onePixelPng = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        );

        return UploadedFile::fake()->createWithContent($name, $onePixelPng);
    }
}
