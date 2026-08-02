@extends('layouts.admin')

@section('title', 'Atualização do Sistema - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-cloud-arrow-down text-primary me-2"></i> Atualização do Sistema
        </h2>
        <p class="text-muted small mb-0">Sincronize o código-fonte da aplicação via GitHub, execute migrações e limpe os caches.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4 shadow-sm mb-4 border-0 d-flex align-items-center gap-3">
        <i class="fa-solid fa-circle-check fs-4 text-success"></i>
        <div>
            <h6 class="fw-bold mb-0">Operação concluída</h6>
            <p class="mb-0 small">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning rounded-4 shadow-sm mb-4 border-0 d-flex align-items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation fs-4 text-warning"></i>
        <div>
            <h6 class="fw-bold mb-0">Atenção durante a atualização</h6>
            <p class="mb-0 small">{{ session('warning') }}</p>
        </div>
    </div>
@endif

<div class="row g-4 mb-4">
    <!-- Status do Repositório & Ambiente -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-server text-primary me-2"></i> Informações da Aplicação</h5>
            </div>
            <div class="card-body p-4">
                <ul class="list-group list-group-flush rounded-3 border-0">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                        <span class="text-muted fw-semibold"><i class="fa-solid fa-code-branch me-2 text-info"></i> Branch Atual</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill font-monospace fw-bold">
                            {{ $gitInfo['branch'] ?? 'main' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                        <span class="text-muted fw-semibold"><i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> Última Atualização</span>
                        <span class="fw-bold text-dark small">
                            {{ $lastUpdateAt ? \Carbon\Carbon::parse($lastUpdateAt)->format('d/m/Y H:i:s') : 'Nenhuma gravada' }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                        <span class="text-muted fw-semibold"><i class="fa-brands fa-php me-2 text-primary"></i> Versão do PHP</span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill font-monospace fw-bold">
                            PHP {{ PHP_VERSION }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom">
                        <span class="text-muted fw-semibold"><i class="fa-brands fa-laravel me-2 text-danger"></i> Versão do Laravel</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill font-monospace fw-bold">
                            v{{ app()->version() }}
                        </span>
                    </li>
                    <li class="list-group-item px-0 py-3">
                        <span class="text-muted fw-semibold d-block mb-1"><i class="fa-solid fa-git-alt me-2 text-danger"></i> Último Commit Git</span>
                        <div class="p-2 rounded-3 bg-light text-dark font-monospace small border text-truncate" title="{{ $gitInfo['commit'] ?? 'N/D' }}">
                            {{ $gitInfo['commit'] ?? 'N/D' }}
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Ação de Atualização -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-bolt text-warning me-2"></i> Executar Atualização</h5>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <form action="{{ route('admin.system.update.run') }}" method="POST" id="updateForm">
                    @csrf
                    <p class="text-muted small mb-4">
                        Marque as opções que deseja processar durante este ciclo de atualização. Ao clicar em atualizar, os comandos serão disparados no servidor.
                    </p>

                    <div class="bg-light p-3 rounded-4 border mb-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="pull_git" name="pull_git" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="pull_git">
                                <i class="fa-solid fa-code-pull-request text-primary me-1"></i> Baixar código mais recente do GitHub (Git Pull)
                            </label>
                            <div class="form-text small text-muted">Sincroniza os arquivos alterados do repositório remoto.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="run_migrations" name="run_migrations" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="run_migrations">
                                <i class="fa-solid fa-database text-success me-1"></i> Executar migrações pendentes do banco (php artisan migrate)
                            </label>
                            <div class="form-text small text-muted">Aplica alterações na estrutura do banco de dados com a flag <code>--force</code>.</div>
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="clear_cache" name="clear_cache" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="clear_cache">
                                <i class="fa-solid fa-broom text-warning me-1"></i> Limpar e recarregar caches (Config, Rotas, Views)
                            </label>
                            <div class="form-text small text-muted">Garante que novas views e rotas entrem em vigor imediatamente.</div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm" id="btnSubmitUpdate" onclick="return confirm('Deseja iniciar a atualização do sistema agora?')">
                            <i class="fa-solid fa-rotate me-2"></i> Atualizar Aplicação Agora
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-muted small d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info text-info"></i>
                    <span><strong>Dica:</strong> Após subir suas alterações locais para o GitHub, basta clicar neste botão.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Terminal de Execução -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-dark text-white p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-danger d-inline-block" style="width: 12px; height: 12px;"></span>
            <span class="rounded-circle bg-warning d-inline-block" style="width: 12px; height: 12px;"></span>
            <span class="rounded-circle bg-success d-inline-block" style="width: 12px; height: 12px;"></span>
            <span class="fw-bold ms-2 small text-white-50 font-monospace">Console Output / Log da Última Atualização</span>
        </div>
        @if($lastUpdateLog)
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="copyLog()">
                <i class="fa-regular fa-copy me-1"></i> Copiar Log
            </button>
        @endif
    </div>
    <div class="card-body p-0 bg-dark">
        <pre id="logTerminal" class="text-success font-monospace p-4 mb-0" style="max-height: 400px; overflow-y: auto; white-space: pre-wrap; font-size: 0.875rem; line-height: 1.5;">{{ $lastUpdateLog ?? "Nenhum histórico de atualização registrado até o momento.\nClique no botão 'Atualizar Aplicação Agora' para rodar o primeiro ciclo de atualização." }}</pre>
    </div>
</div>

<!-- Como Funciona o Fluxo -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-diagram-project text-primary me-2"></i> Como Funciona o Fluxo de Atualização?</h5>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold mb-3" style="width: 36px; height: 36px;">1</div>
                    <h6 class="fw-bold text-dark">Desenvolvimento Local</h6>
                    <p class="text-muted small mb-0">Você faz suas alterações na pasta do projeto no seu computador (código, layout, controllers ou migrações).</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center fw-bold mb-3" style="width: 36px; height: 36px;">2</div>
                    <h6 class="fw-bold text-dark">Envio para o GitHub</h6>
                    <p class="text-muted small mb-0">Envie suas alterações para o repositório no GitHub executando <code>git push origin main</code> no seu terminal local.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center fw-bold mb-3" style="width: 36px; height: 36px;">3</div>
                    <h6 class="fw-bold text-dark">Atualização com 1 Clique</h6>
                    <p class="text-muted small mb-0">Acesse este painel administrativo web e clique no botão <strong>Atualizar Aplicação Agora</strong>. O servidor vai baixar tudo e atualizar!</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyLog() {
        const text = document.getElementById('logTerminal').innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert('Log copiado para a área de transferência!');
        });
    }

    document.getElementById('updateForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitUpdate');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Atualizando Aplicação... Aguarde...';
    });
</script>
@endpush
