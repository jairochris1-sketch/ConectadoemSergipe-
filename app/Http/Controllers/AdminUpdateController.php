<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Throwable;

class AdminUpdateController extends Controller
{
    public function index()
    {
        $lastUpdateAt = Setting::get('system_last_updated_at');
        $lastUpdateLog = Setting::get('system_last_update_log');
        
        $gitInfo = $this->getGitInformation();
        
        return view('admin.update', compact('lastUpdateAt', 'lastUpdateLog', 'gitInfo'));
    }

    public function runUpdate(Request $request)
    {
        @set_time_limit(300);

        $pullGit = $request->boolean('pull_git', true);
        $runMigrations = $request->boolean('run_migrations', true);
        $clearCache = $request->boolean('clear_cache', true);

        $logBuffer = [];
        $hasErrors = false;

        $logBuffer[] = "==========================================================";
        $logBuffer[] = " INÍCIO DA ATUALIZAÇÃO DA APLICAÇÃO - " . now()->format('d/m/Y H:i:s');
        $logBuffer[] = "==========================================================";

        // 1. Atualizar via GIT
        if ($pullGit) {
            $logBuffer[] = "\n[ETAPA 1/3] Atualizando arquivos do repositório (Git Pull)...";
            try {
                $basePath = base_path();
                $gitBin = $this->getGitBinary();
                
                // Detectar branch atual ou usar main
                $branchResult = Process::path($basePath)->run("{$gitBin} rev-parse --abbrev-ref HEAD");
                $branch = $branchResult->successful() ? trim($branchResult->output()) : 'main';

                $logBuffer[] = "Branch detectada: {$branch}";
                $logBuffer[] = "Executável Git utilizado: {$gitBin}";

                // Git fetch & pull
                $fetchResult = Process::path($basePath)->run("{$gitBin} fetch origin");
                if ($fetchResult->successful() && !empty(trim($fetchResult->output()))) {
                    $logBuffer[] = "Git Fetch: " . trim($fetchResult->output());
                }

                $pullResult = Process::path($basePath)->run("{$gitBin} pull origin {$branch}");
                $pullOutput = trim($pullResult->output() . "\n" . $pullResult->errorOutput());
                $logBuffer[] = "Saída do Git Pull:\n" . ($pullOutput ?: 'Comando executado com sucesso.');

                if (!$pullResult->successful()) {
                    $hasErrors = true;
                    $logBuffer[] = "AVISO: O comando git pull retornou código diferente de 0. Verifique as permissões ou status do git.";
                }
            } catch (Throwable $e) {
                $hasErrors = true;
                $logBuffer[] = "ERRO ao executar Git Pull: " . $e->getMessage();
            }
        } else {
            $logBuffer[] = "\n[ETAPA 1/3] Atualização Git ignorada conforme solicitação.";
        }

        // 2. Executar Migrações do Banco de Dados
        if ($runMigrations) {
            $logBuffer[] = "\n[ETAPA 2/3] Executando migrações pendentes do banco de dados...";
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = trim(Artisan::output());
                $logBuffer[] = "Saída das Migrações:\n" . ($migrateOutput ?: 'Nenhuma migração nova para executar.');
            } catch (Throwable $e) {
                $hasErrors = true;
                $logBuffer[] = "ERRO ao executar migrações: " . $e->getMessage();
            }
        } else {
            $logBuffer[] = "\n[ETAPA 2/3] Migrações do banco de dados ignoradas conforme solicitação.";
        }

        // 3. Executar Limpeza de Cache e Otimização
        if ($clearCache) {
            $logBuffer[] = "\n[ETAPA 3/3] Limpando e recarregando caches da aplicação...";
            try {
                Artisan::call('optimize:clear');
                $logBuffer[] = "Limpeza de cache (optimize:clear):\n" . trim(Artisan::output());

                Artisan::call('config:cache');
                $logBuffer[] = "Cache de configurações recarregado.";

                Artisan::call('route:cache');
                $logBuffer[] = "Cache de rotas recarregado.";

                Artisan::call('view:cache');
                $logBuffer[] = "Cache de views recarregado.";
            } catch (Throwable $e) {
                $hasErrors = true;
                $logBuffer[] = "ERRO ao limpar caches: " . $e->getMessage();
            }
        } else {
            $logBuffer[] = "\n[ETAPA 3/3] Limpeza de cache ignorada conforme solicitação.";
        }

        // Script customizado se existir (update.sh)
        $customScriptPath = base_path('update.sh');
        if (file_exists($customScriptPath) && is_executable($customScriptPath)) {
            $logBuffer[] = "\n[ETAPA EXTRA] Executando script customizado 'update.sh'...";
            try {
                $scriptResult = Process::path(base_path())->run('./update.sh');
                $logBuffer[] = "Saída do script update.sh:\n" . trim($scriptResult->output() . "\n" . $scriptResult->errorOutput());
            } catch (Throwable $e) {
                $logBuffer[] = "ERRO ao executar script update.sh: " . $e->getMessage();
            }
        }

        $logBuffer[] = "\n==========================================================";
        $logBuffer[] = $hasErrors 
            ? " ATUALIZAÇÃO CONCLUÍDA COM ALERTAS EM " . now()->format('d/m/Y H:i:s')
            : " ATUALIZAÇÃO CONCLUÍDA COM SUCESSO EM " . now()->format('d/m/Y H:i:s');
        $logBuffer[] = "==========================================================";

        $fullLog = implode("\n", $logBuffer);

        Setting::set('system_last_updated_at', now()->toDateTimeString());
        Setting::set('system_last_update_log', $fullLog);

        if ($hasErrors) {
            return back()->with('warning', 'A atualização foi concluída, mas ocorreram alguns alertas/erros durante a execução. Confira o log abaixo.');
        }

        return back()->with('success', 'Aplicação atualizada com sucesso!');
    }

    private function getGitInformation(): array
    {
        $basePath = base_path();
        $gitBin = $this->getGitBinary();
        
        try {
            $branchResult = Process::path($basePath)->run("{$gitBin} rev-parse --abbrev-ref HEAD");
            $branch = $branchResult->successful() ? trim($branchResult->output()) : 'N/D';

            $commitResult = Process::path($basePath)->run("{$gitBin} log -1 --pretty=format:\"%h - %s (%cr) <%an>\"");
            $commit = $commitResult->successful() ? trim($commitResult->output()) : 'Informação do git não disponível';

            $statusResult = Process::path($basePath)->run("{$gitBin} status --short");
            $hasLocalChanges = $statusResult->successful() && !empty(trim($statusResult->output()));

            return [
                'installed' => true,
                'branch' => $branch,
                'commit' => $commit,
                'has_local_changes' => $hasLocalChanges,
            ];
        } catch (Throwable $e) {
            return [
                'installed' => false,
                'branch' => 'N/D',
                'commit' => 'Git CLI indisponível no servidor',
                'has_local_changes' => false,
            ];
        }
    }

    private function getGitBinary(): string
    {
        $customGit = env('GIT_PATH') ?: Setting::get('system_git_path');
        if ($customGit && file_exists($customGit)) {
            return (str_contains($customGit, ' ') ? '"' . $customGit . '"' : $customGit);
        }

        $possiblePaths = [
            'C:\\Program Files\\Git\\cmd\\git.exe',
            'C:\\Program Files\\Git\\bin\\git.exe',
            'C:\\Program Files (x86)\\Git\\cmd\\git.exe',
            'C:\\Program Files (x86)\\Git\\bin\\git.exe',
            '/usr/bin/git',
            '/usr/local/bin/git',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return (str_contains($path, ' ') ? '"' . $path . '"' : $path);
            }
        }

        return 'git';
    }
}
