@extends('layouts.admin')

@section('title', 'Pedido comunitário - Painel Administrativo')

@section('content')
@php
    $statusLabels=['pending'=>'Em análise','open'=>'Aberto','in_progress'=>'Em atendimento','resolved'=>'Resolvido','rejected'=>'Ajustes solicitados','hidden'=>'Oculto'];
    $reasonLabels=['spam'=>'Spam','scam'=>'Possível golpe','inappropriate'=>'Conteúdo impróprio','harassment'=>'Assédio','personal_data'=>'Dados pessoais','other'=>'Outro'];
@endphp
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div><a href="{{ route('admin.community-help.index') }}" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i> Ajuda Comunitária</a><h2 class="fw-bold text-dark mt-2 mb-1">{{ $helpRequest->title }}</h2><p class="text-muted small mb-0">{{ $helpRequest->public_id }} · enviado em {{ $helpRequest->created_at->format('d/m/Y H:i') }}</p></div>
    <span class="admin-help-status is-{{ $helpRequest->status }}">{{ $statusLabels[$helpRequest->status] ?? $helpRequest->status }}</span>
</div>

@if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger rounded-3">{{ $errors->first() }}</div>@endif

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <section class="admin-help-card mb-4">
            <div class="admin-help-card-title"><i class="fa-solid fa-hand-holding-heart"></i><h3>Pedido local</h3></div>
            <div class="d-flex flex-wrap gap-2 mb-3"><span class="badge text-bg-primary">{{ $categories[$helpRequest->category] ?? $helpRequest->category }}</span><span class="badge text-bg-secondary">{{ $urgencies[$helpRequest->urgency] ?? $helpRequest->urgency }}</span></div>
            <p class="admin-help-description">{{ $helpRequest->description }}</p>
            <dl class="admin-help-details">
                <div><dt>Local</dt><dd>{{ $helpRequest->neighborhood }}, {{ $helpRequest->city }}</dd></div>
                <div><dt>Duração</dt><dd>{{ $helpRequest->duration_days }} dias</dd></div>
                <div><dt>Expiração</dt><dd>{{ $helpRequest->expires_at?->format('d/m/Y H:i') ?? 'Ainda não definida' }}</dd></div>
                @if($helpRequest->moderation_reason)<div><dt>Moderação</dt><dd>{{ $helpRequest->moderation_reason }}</dd></div>@endif
            </dl>
        </section>

        <section class="admin-help-card" id="respostas">
            <div class="admin-help-card-title"><i class="fa-solid fa-comments"></i><h3>Respostas ({{ $helpRequest->responses->count() }})</h3></div>
            @forelse($helpRequest->responses as $response)
                @php $pendingReports=$response->reports->where('status','pending'); @endphp
                <article class="admin-help-response {{ $response->status === 'hidden' ? 'is-hidden' : '' }}">
                    <header><div><strong>{{ $response->user?->name }}</strong><small>{{ $response->user?->email }} · {{ $response->created_at->format('d/m/Y H:i') }}</small></div><div>@if($response->is_selected)<span class="badge bg-success">Ajuda confirmada</span>@endif @if($response->status==='hidden')<span class="badge bg-secondary">Oculta</span>@endif @if($pendingReports->isNotEmpty())<span class="badge bg-danger">{{ $pendingReports->count() }} denúncia(s)</span>@endif</div></header>
                    <p>{{ $response->message }}</p>
                    @if($response->moderation_reason)<div class="admin-help-reason"><strong>Motivo da moderação:</strong> {{ $response->moderation_reason }}</div>@endif
                    @if($pendingReports->isNotEmpty())
                        <div class="admin-help-reports">
                            @foreach($pendingReports as $report)<div><strong>{{ $reasonLabels[$report->reason] ?? $report->reason }}</strong><span>{{ $report->reporter?->name }} · {{ $report->created_at->format('d/m/Y H:i') }}</span>@if($report->details)<p>{{ $report->details }}</p>@endif</div>@endforeach
                        </div>
                    @endif
                    <form class="admin-help-action" action="{{ route('community-help.responses.moderate', [$helpRequest,$response]) }}" method="POST">@csrf @method('PATCH')
                        <input class="form-control" name="moderation_reason" maxlength="500" value="{{ $response->moderation_reason }}" placeholder="Motivo da moderação">
                        <div class="d-flex flex-wrap gap-2">
                            @if($response->status==='hidden')<button class="btn btn-sm btn-success" name="action" value="restore">Restaurar resposta</button>
                            @else<button class="btn btn-sm btn-outline-danger" name="action" value="hide">Ocultar resposta</button>@if($pendingReports->isNotEmpty())<button class="btn btn-sm btn-outline-secondary" name="action" value="dismiss_reports">Arquivar denúncias</button>@endif @endif
                        </div>
                    </form>
                </article>
            @empty<p class="text-muted small mb-0">Este pedido ainda não recebeu respostas.</p>@endforelse
        </section>
    </div>

    <div class="col-12 col-xl-4">
        <aside class="admin-help-card mb-4">
            <div class="admin-help-card-title"><i class="fa-solid fa-user"></i><h3>Membro solicitante</h3></div>
            <strong>{{ $helpRequest->user?->name }}</strong><p class="text-muted small mb-1">{{ $helpRequest->user?->email }}</p><p class="text-muted small">{{ $helpRequest->user?->username ? '@'.$helpRequest->user->username : '' }}</p>
            <a href="{{ route('community-help.show',$helpRequest) }}" class="btn btn-sm btn-outline-primary w-100" target="_blank">Visualizar como administrador</a>
        </aside>

        <aside class="admin-help-card">
            <div class="admin-help-card-title"><i class="fa-solid fa-shield-halved"></i><h3>Ação administrativa</h3></div>
            <form action="{{ route('community-help.moderate',$helpRequest) }}" method="POST">@csrf @method('PATCH')
                <label class="form-label small fw-semibold" for="request-moderation-reason">Motivo ou orientação</label>
                <textarea id="request-moderation-reason" class="form-control mb-3" name="moderation_reason" maxlength="500" rows="3" placeholder="Explique a decisão quando necessário">{{ $helpRequest->moderation_reason }}</textarea>
                <div class="d-grid gap-2">
                    @if($helpRequest->status==='pending')<button class="btn btn-success" name="action" value="approve">Aprovar e publicar</button><button class="btn btn-outline-danger" name="action" value="reject">Solicitar ajustes</button>
                    @elseif(in_array($helpRequest->status,['open','in_progress','resolved'],true))<button class="btn btn-outline-danger" name="action" value="hide">Ocultar pedido</button>
                    @elseif(in_array($helpRequest->status,['rejected','hidden'],true))<button class="btn btn-success" name="action" value="restore">Restaurar e publicar</button>@endif
                </div>
            </form>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-help-status{display:inline-flex;padding:7px 12px;border-radius:999px;font-size:.7rem;font-weight:800}.admin-help-status.is-pending{color:#8a5b00;background:rgba(245,184,0,.14)}.admin-help-status.is-open,.admin-help-status.is-in_progress{color:#075fb6;background:rgba(18,101,245,.12)}.admin-help-status.is-resolved{color:#087443;background:rgba(10,166,96,.13)}.admin-help-status.is-rejected,.admin-help-status.is-hidden{color:#b42318;background:rgba(220,53,69,.12)}.admin-help-card{padding:20px;color:var(--foreground);background:var(--card);border:1px solid var(--border);border-radius:18px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.admin-help-card-title{display:flex;align-items:center;gap:10px;margin-bottom:16px}.admin-help-card-title i{width:34px;height:34px;display:grid;place-items:center;color:#1265f5;background:rgba(18,101,245,.1);border-radius:10px}.admin-help-card-title h3{margin:0;font-size:.95rem;font-weight:800}.admin-help-description{white-space:pre-line;font-size:.82rem;line-height:1.7}.admin-help-details{margin:16px 0 0}.admin-help-details>div{display:grid;grid-template-columns:95px minmax(0,1fr);gap:10px;padding:9px 0;border-top:1px solid var(--border)}.admin-help-details dt{color:var(--muted-foreground);font-size:.68rem}.admin-help-details dd{margin:0;font-size:.75rem;font-weight:700}.admin-help-response{padding:16px 0;border-top:1px solid var(--border)}.admin-help-response.is-hidden{opacity:.78}.admin-help-response header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.admin-help-response header small{display:block;margin-top:3px;color:var(--muted-foreground);font-size:.64rem}.admin-help-response>p{margin:12px 0;white-space:pre-line;font-size:.78rem}.admin-help-reason{padding:9px 11px;color:#8a5b00;background:rgba(245,184,0,.1);border-radius:10px;font-size:.7rem}.admin-help-reports{display:grid;gap:8px;margin:12px 0}.admin-help-reports>div{padding:10px;background:rgba(220,53,69,.08);border:1px solid rgba(220,53,69,.15);border-radius:10px;font-size:.7rem}.admin-help-reports span{display:block;color:var(--muted-foreground);font-size:.62rem}.admin-help-reports p{margin:5px 0 0}.admin-help-action{display:grid;grid-template-columns:minmax(180px,1fr) auto;align-items:center;gap:10px;margin-top:12px}.admin-help-card>p{font-size:.75rem}@media(max-width:650px){.admin-help-card{padding:15px}.admin-help-response header{display:block}.admin-help-action{grid-template-columns:1fr}}
</style>
@endpush
