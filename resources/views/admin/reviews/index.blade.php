@extends('layouts.admin')

@section('title', 'Avaliações - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1"><i class="fa-solid fa-star text-warning me-2"></i>Avaliações</h2>
        <p class="text-muted small mb-0">Modere denúncias sem permitir que o anunciante escolha avaliações.</p>
    </div>
    <span class="badge bg-danger rounded-pill px-3 py-2">{{ $pendingReportsCount }} denúncia(s) pendente(s)</span>
</div>

@if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.reviews', ['status' => 'reported']) }}" class="btn {{ $status === 'reported' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill">Denunciadas</a>
    <a href="{{ route('admin.reviews', ['status' => 'approved']) }}" class="btn {{ $status === 'approved' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill">Aprovadas</a>
    <a href="{{ route('admin.reviews', ['status' => 'hidden']) }}" class="btn {{ $status === 'hidden' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill">Ocultas</a>
    <a href="{{ route('admin.reviews', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill">Todas</a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Avaliação</th><th>Conteúdo avaliado</th><th>Denúncias</th><th>Segurança</th><th>Status</th><th class="text-end pe-4">Moderação</th></tr></thead>
            <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td class="ps-4" style="min-width: 290px;">
                        <div class="text-warning">@for($star = 1; $star <= 5; $star++)<i class="fa-{{ $star <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>@endfor</div>
                        <strong>{{ $review->user->name }}</strong>
                        <p class="small mb-0 text-break">{{ $review->comment }}</p>
                    </td>
                    <td>
                        <strong>{{ $review->store?->name ?? $review->ad?->title ?? 'Conteúdo removido' }}</strong>
                        <small class="d-block text-muted">
                            {{ $review->store ? 'Loja' : 'Perfil profissional' }}
                            · Dono: {{ $review->store?->user?->name ?? $review->ad?->user?->name ?? 'Não disponível' }}
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-danger bg-opacity-10 text-danger">{{ $review->reports->where('status', 'pending')->count() }} pendente(s)</span>
                        @foreach($review->reports->where('status', 'pending') as $report)<small class="d-block mt-1">{{ \App\Models\ReviewReport::REASONS[$report->reason] ?? $report->reason }} · {{ $report->reporter->name }}</small>@endforeach
                    </td>
                    <td><small class="d-block">IP: {{ $review->ip_address ?? '—' }}</small><small class="text-muted d-block text-truncate" style="max-width: 190px;" title="{{ $review->user_agent }}">{{ $review->user_agent ?: 'Sem agente' }}</small></td>
                    <td><span class="badge {{ $review->status === 'approved' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($review->status) }}</span></td>
                    <td class="text-end pe-4" style="min-width: 180px;">
                        <form action="{{ route('admin.reviews.action', $review) }}" method="POST" class="d-grid gap-1">
                            @csrf
                            @if($review->status !== 'hidden')<button name="action" value="hide" class="btn btn-sm btn-outline-danger rounded-pill">Ocultar</button>@endif
                            @if($review->status !== 'approved')<button name="action" value="approve" class="btn btn-sm btn-outline-success rounded-pill">Aprovar / restaurar</button>@endif
                            @if($review->reports->where('status', 'pending')->isNotEmpty())<button name="action" value="dismiss_reports" class="btn btn-sm btn-outline-secondary rounded-pill">Arquivar denúncias</button>@endif
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">Nenhuma avaliação nesta situação.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
