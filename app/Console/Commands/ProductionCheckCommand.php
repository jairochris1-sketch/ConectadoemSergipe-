<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionCheckCommand extends Command
{
    protected $signature = 'app:production-check';

    protected $description = 'Verifica configurações essenciais antes de publicar a aplicação';

    public function handle(): int
    {
        $checks = [
            ['Ambiente de produção', app()->environment('production'), 'Defina APP_ENV=production.'],
            ['Modo de depuração desativado', ! config('app.debug'), 'Defina APP_DEBUG=false.'],
            ['Chave da aplicação', filled(config('app.key')), 'Execute php artisan key:generate.'],
            ['URL pública com HTTPS', str_starts_with((string) config('app.url'), 'https://'), 'Defina APP_URL com https://.'],
            ['Banco apropriado para produção', config('database.default') !== 'sqlite', 'Configure MySQL ou PostgreSQL.'],
            ['Fila assíncrona', config('queue.default') !== 'sync', 'Use QUEUE_CONNECTION=database ou redis.'],
            ['E-mail externo', ! in_array(config('mail.default'), ['log', 'array'], true), 'Configure um mailer SMTP ou transacional.'],
            ['Cookie de sessão seguro', (bool) config('session.secure'), 'Defina SESSION_SECURE_COOKIE=true.'],
            ['Sessão criptografada', (bool) config('session.encrypt'), 'Defina SESSION_ENCRYPT=true.'],
            ['Proxy confiável configurado', filled(config('app.trusted_proxies')), 'Defina TRUSTED_PROXIES com os IPs do proxy.'],
        ];

        $failures = 0;
        $this->newLine();
        $this->components->info('Prontidão para produção');

        foreach ($checks as [$label, $passed, $fix]) {
            $this->components->twoColumnDetail(
                $label,
                $passed ? '<fg=green>OK</>' : '<fg=red>AJUSTAR</>'
            );
            if (! $passed) {
                $this->line("  <fg=yellow>{$fix}</>");
                $failures++;
            }
        }

        $this->newLine();
        if ($failures > 0) {
            $this->components->error("Foram encontradas {$failures} pendência(s) de produção.");

            return self::FAILURE;
        }

        $this->components->success('Configuração essencial pronta para publicação.');

        return self::SUCCESS;
    }
}
