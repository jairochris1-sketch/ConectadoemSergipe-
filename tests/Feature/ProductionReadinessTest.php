<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_sends_message_to_configured_recipient(): void
    {
        Mail::fake();
        Setting::set('contact_email', 'atendimento@conectado.test');

        $this->from(route('page.contact'))
            ->post(route('page.contact.send'), [
                'name' => 'Cliente Sergipano',
                'email' => 'cliente@example.com',
                'subject' => 'Dúvida sobre uma loja',
                'message' => 'Gostaria de receber mais informações.',
            ])
            ->assertRedirect(route('page.contact'))
            ->assertSessionHas('success');

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo('atendimento@conectado.test')
                && $mail->contact['email'] === 'cliente@example.com';
        });
    }

    public function test_production_check_passes_with_safe_configuration(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $originalDatabase = config('database.default');
        config([
            'app.debug' => false,
            'app.key' => 'base64:'.base64_encode(random_bytes(32)),
            'app.url' => 'https://conectado.example.com',
            'app.trusted_proxies' => ['127.0.0.1'],
            'database.default' => 'mysql',
            'queue.default' => 'database',
            'mail.default' => 'smtp',
            'session.secure' => true,
            'session.encrypt' => true,
        ]);

        try {
            $exitCode = Artisan::call('app:production-check');
            $output = Artisan::output();
        } finally {
            config(['database.default' => $originalDatabase]);
        }

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Prontidão para produção', $output);
    }

    public function test_production_check_rejects_unsafe_configuration(): void
    {
        config([
            'app.debug' => true,
            'app.key' => null,
            'app.url' => 'http://localhost',
            'database.default' => 'sqlite',
            'queue.default' => 'sync',
            'mail.default' => 'log',
            'session.secure' => false,
            'session.encrypt' => false,
        ]);

        $this->artisan('app:production-check')
            ->expectsOutputToContain('AJUSTAR')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_public_pages_receive_baseline_security_headers(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(self), geolocation=(self)');
    }
}
