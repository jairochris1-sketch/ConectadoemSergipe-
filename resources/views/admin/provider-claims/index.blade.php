@extends('layouts.admin')

@section('title', 'Reivindicações de perfis - Painel Admin')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pendentes',
        'approved' => 'Aprovadas',
        'rejected' => 'Rejeitadas',
    ];
    $relationshipLabels = [
        'owner' => 'Proprietário(a)',
        'professional' => 'O próprio profissional',
        'employee' => 'Funcionário(a)',
        'representative' => 'Representante autorizado(a)',
    ];
@endphp

<div class="admin-page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-user-check text-success me-2"></i> Reivindicações de perfis</h2>
        <p class="text-muted small mb-0">Confirme o vínculo antes de transferir o controle de um perfil profissional.</p>
    </div>
    <div class="btn-group" role="group" aria-label="Filtrar solicitações">
        @foreach($statusLabels as $statusKey => $statusLabel)
            <a href="{{ route('admin.provider_claims.index', ['status' => $statusKey]) }}"
               class="btn btn-sm {{ $status === $statusKey ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $statusLabel }}
            </a>
        @endforeach
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger rounded-3">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="d-grid gap-3">
    @forelse($claims as $claim)
        <article class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-2">Solicitação #{{ $claim->id }}</span>
                        <h3 class="h5 fw-bold mb-1">{{ $claim->ad->title }}</h3>
                        <a href="{{ route('provider.show', $claim->ad->slug) }}" target="_blank" rel="noopener" class="small">Abrir perfil público</a>
                    </div>
                    <div class="text-md-end">
                        <strong class="d-block">{{ $claim->claimant->name }}</strong>
                        <span class="text-muted small">{{ $claim->claimant->email }}</span>
                        @if($claim->claimant->phone)
                            <span class="text-muted small d-block">{{ $claim->claimant->phone }}</span>
                        @endif
                    </div>
                </div>

                <div class="row g-3 small mb-3">
                    <div class="col-12 col-md-4">
                        <span class="text-muted d-block">Relação informada</span>
                        <strong>{{ $relationshipLabels[$claim->relationship] ?? $claim->relationship }}</strong>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="text-muted d-block">Contato para confirmação</span>
                        <strong>{{ $claim->verification_phone ?: $claim->verification_email }}</strong>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="text-muted d-block">Enviada em</span>
                        <strong>{{ $claim->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>

                @if($claim->explanation)
                    <div class="bg-light rounded-3 p-3 mb-3 small">
                        <span class="text-muted d-block mb-1">Informações adicionais</span>
                        {{ $claim->explanation }}
                    </div>
                @endif

                @if($claim->status === 'pending')
                    <form action="{{ route('admin.provider_claims.review', $claim) }}" method="POST">
                        @csrf
                        <label for="admin_note_{{ $claim->id }}" class="form-label small fw-semibold">Observação da análise</label>
                        <textarea class="form-control rounded-3 mb-3" id="admin_note_{{ $claim->id }}" name="admin_note" rows="2" maxlength="1000" placeholder="Ex.: confirmação realizada por ligação para o número publicado."></textarea>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-check me-1"></i>Aprovar e transferir perfil
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-outline-danger rounded-pill px-4">
                                Rejeitar
                            </button>
                        </div>
                    </form>
                @else
                    <div class="border-top pt-3 small">
                        <span class="badge {{ $claim->status === 'approved' ? 'bg-success' : 'bg-danger' }}">
                            {{ $statusLabels[$claim->status] ?? ucfirst($claim->status) }}
                        </span>
                        @if($claim->reviewer)
                            <span class="text-muted ms-2">por {{ $claim->reviewer->name }} em {{ $claim->reviewed_at?->format('d/m/Y H:i') }}</span>
                        @endif
                        @if($claim->admin_note)
                            <p class="mb-0 mt-2">{{ $claim->admin_note }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5 text-center text-muted">
                <i class="fa-regular fa-folder-open fs-2 d-block mb-3"></i>
                Nenhuma solicitação nesta situação.
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $claims->links() }}</div>
@endsection
