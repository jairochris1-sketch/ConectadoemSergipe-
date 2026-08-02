@extends('layouts.admin')

@section('title', $report->reference . ' - Denúncia')

@section('content')
@php
    $isStoreReport = $report->subject_type === 'store';
    $reportedContent = $isStoreReport ? $report->store : $report->ad;
    $reportedCity = $reportedContent?->city;
    $reportedImages = $isStoreReport
        ? collect([$report->store?->banner, $report->store?->logo])
            ->merge($report->store?->media?->pluck('path') ?? collect())
            ->filter()
            ->unique()
        : ($report->ad?->images?->pluck('image_path') ?? collect());
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('admin.reports') }}" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i>Voltar às denúncias</a>
        <h2 class="fw-bold mt-2 mb-0">{{ $report->reference }} · {{ $report->reason_label }}</h2>
    </div>
    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">{{ $report->priority_label }}</span>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold">Conteúdo denunciado</h3>
                <h4 class="h6">{{ $report->ad_title_snapshot }}</h4>
                <div class="row g-3 mt-1">
                    <div class="col-md-6"><small class="text-muted d-block">Tipo</small><strong>{{ $report->subject_label }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Criado em</small><strong>{{ $reportedContent?->created_at?->format('d/m/Y H:i') ?? 'Conteúdo excluído' }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">Localização</small><strong>{{ $reportedCity ?? 'Não disponível' }}</strong></div>
                    <div class="col-md-6"><small class="text-muted d-block">IP de publicação</small><strong>{{ $isStoreReport ? 'Não registrado' : ($report->ad?->publication_ip ?? 'Não registrado') }}</strong></div>
                </div>

                @if($reportedCity)
                    <div class="ratio ratio-21x9 rounded-3 overflow-hidden border mt-4">
                        <iframe src="https://www.google.com/maps?q={{ urlencode($reportedCity . ', Sergipe') }}&output=embed" loading="lazy" title="Mapa da localização"></iframe>
                    </div>
                @endif

                @if($reportedImages->isNotEmpty())
                    <div class="d-flex gap-2 overflow-auto mt-4">
                        @foreach($reportedImages as $imagePath)
                            <a href="{{ asset($imagePath) }}" target="_blank" rel="noopener">
                                <img src="{{ asset($imagePath) }}" class="rounded-3 object-fit-cover border" style="width: 130px; height: 100px;" alt="Imagem do conteúdo denunciado">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold">Relato</h3>
                <p class="mb-2"><strong>Gravidade:</strong> {{ $report->priority_label }}</p>
                <p class="mb-3">{{ $report->details ?: 'Nenhum detalhe adicional.' }}</p>
                <small class="text-muted">Enviado por {{ $report->reporter?->name ?? 'visitante não identificado' }} em {{ $report->created_at->format('d/m/Y H:i') }}</small>

                @if(!empty($report->evidence_paths))
                    <h4 class="h6 fw-bold mt-4">Provas anexadas</h4>
                    <div class="d-flex gap-2 overflow-auto">
                        @foreach($report->evidence_paths as $path)
                            <a href="{{ asset($path) }}" target="_blank" rel="noopener">
                                <img src="{{ asset($path) }}" class="rounded-3 object-fit-cover border" style="width: 140px; height: 110px;" alt="Prova anexada">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Verificação automática</h3>
                @if($isStoreReport)
                    <p class="text-muted small mb-0">As denúncias de lojas exigem conferência administrativa dos dados, imagens, produtos e histórico do proprietário.</p>
                @else
                    <p class="text-muted small">Sinais calculados com dados reais da plataforma; não substituem a análise humana.</p>
                    @forelse($automaticSignals as $signal)
                        <div class="alert alert-warning py-2 mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $signal }}</div>
                    @empty
                        <div class="text-success"><i class="fa-solid fa-check me-2"></i>Nenhum sinal adicional encontrado.</div>
                    @endforelse
                @endif
            </div>
        </section>

        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold">Histórico e outras denúncias</h3>
                <p class="mb-2"><strong>Responsável:</strong> {{ $report->advertiser?->name ?? 'Conta removida' }}</p>
                <p class="mb-2"><strong>E-mail:</strong> {{ $report->advertiser?->email ?? 'Não disponível' }}</p>
                <p class="mb-2"><strong>Telefone:</strong> {{ $report->advertiser?->phone ?? $report->advertiser?->whatsapp ?? 'Não disponível' }}</p>
                <p class="mb-3"><strong>Conta criada:</strong> {{ $report->advertiser?->created_at?->format('d/m/Y') ?? 'Não disponível' }}</p>
                <p class="mb-3"><strong>Conteúdos publicados:</strong> {{ $report->advertiser?->ads()->count() ?? 0 }}</p>
                <p class="mb-3"><strong>Outras denúncias deste conteúdo:</strong> {{ $relatedReports->count() }}</p>
                @forelse($relatedReports as $related)
                    <div class="border-top py-2"><strong>{{ $related->reference }}</strong> · {{ $related->reason_label }} <span class="text-muted">— {{ $related->details ?: 'Sem comentário' }}</span></div>
                @empty
                    <p class="text-muted mb-0">Nenhuma denúncia anterior para este conteúdo.</p>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold mb-3">Ações do administrador</h3>
                <form action="{{ route('admin.reports.action', $report) }}" method="POST" onsubmit="return confirmReportAction();">
                    @csrf
                    <div class="mb-3">
                        <label for="admin-report-action" class="form-label fw-semibold">Escolha uma ação</label>
                        <select id="admin-report-action" name="action" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="archive">Arquivar denúncia</option>
                            @unless($isStoreReport)
                                <option value="correct_category">Corrigir categoria</option>
                            @endunless
                            <option value="request_change">Solicitar alteração ao responsável</option>
                            <option value="hide">Ocultar temporariamente</option>
                            <option value="block">Bloquear {{ $isStoreReport ? 'loja' : 'anúncio ou serviço' }}</option>
                            <option value="suspend">Suspender usuário</option>
                            <option value="delete">Excluir definitivamente</option>
                        </select>
                    </div>
                    @unless($isStoreReport)
                        <div class="mb-3" id="category-action-field" hidden>
                            <label for="category_id" class="form-label fw-semibold">Nova categoria</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">Selecione...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
                    <div class="mb-3">
                        <label for="resolution_note" class="form-label fw-semibold">Observação</label>
                        <textarea id="resolution_note" name="resolution_note" class="form-control" rows="4" maxlength="2000" placeholder="Registre o motivo da decisão..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Aplicar ação</button>
                </form>

                @if($report->reviewed_at)
                    <div class="bg-light rounded-3 p-3 mt-4 small">
                        <strong>Última análise:</strong> {{ $report->reviewed_at->format('d/m/Y H:i') }}<br>
                        <strong>Ação:</strong> {{ $report->admin_action }}<br>
                        <strong>Por:</strong> {{ $report->reviewer?->name ?? 'Administrador' }}
                    </div>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script>
    const reportActionSelect = document.getElementById('admin-report-action');
    const categoryActionField = document.getElementById('category-action-field');
    reportActionSelect?.addEventListener('change', () => {
        if (categoryActionField) {
            categoryActionField.hidden = reportActionSelect.value !== 'correct_category';
        }
    });

    function confirmReportAction() {
        if (['delete', 'suspend', 'block'].includes(reportActionSelect?.value)) {
            return confirm('Esta é uma ação importante e pode afetar o usuário ou o conteúdo. Deseja continuar?');
        }
        return true;
    }
</script>
@endpush
