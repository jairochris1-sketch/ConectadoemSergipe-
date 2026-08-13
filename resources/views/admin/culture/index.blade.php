@extends('layouts.admin')

@section('title', 'Cultura - Painel Administrativo')

@section('content')
@php
    $statusLabels = ['published'=>'Publicada','draft'=>'Rascunho','hidden'=>'Ocultada'];
@endphp
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div><h2 class="fw-bold text-dark mb-1">Gestão cultural</h2><p class="text-muted small mb-0">Acompanhe obras, autores e visibilidade do Espaço Cultural.</p></div>
    <a href="{{ route('culture.index') }}" class="btn btn-outline-primary rounded-pill" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir espaço</a>
</div>

@if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif

<div class="row g-3 mb-4">
    @foreach([
        ['Total', $metrics['total'], 'fa-book-open', 'primary'],
        ['Publicadas', $metrics['published'], 'fa-circle-check', 'success'],
        ['Rascunhos', $metrics['draft'], 'fa-file-pen', 'warning'],
        ['Ocultadas', $metrics['hidden'], 'fa-eye-slash', 'danger'],
    ] as [$label,$value,$icon,$tone])
        <div class="col-6 col-lg-3"><div class="admin-module-metric"><i class="fa-solid {{ $icon }} text-{{ $tone }}"></i><div><strong>{{ $value }}</strong><span>{{ $label }}</span></div></div></div>
    @endforeach
</div>

<form method="GET" class="admin-module-filter mb-4">
    <div class="admin-module-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Obra, tema, autor ou e-mail"></div>
    <select name="status" aria-label="Filtrar por status"><option value="">Todos os status</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected($status===$value)>{{ $label }}</option>@endforeach</select>
    <select name="category" aria-label="Filtrar por categoria"><option value="">Todas as categorias</option>@foreach($categories as $value=>$label)<option value="{{ $value }}" @selected($category===$value)>{{ $label }}</option>@endforeach</select>
    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
    @if($search || $status || $category)<a class="btn btn-outline-secondary" href="{{ route('admin.culture.index') }}">Limpar</a>@endif
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive"><table class="table align-middle mb-0 admin-module-table">
        <thead><tr><th>Obra</th><th>Autor</th><th>Categoria</th><th>Alcance</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($works as $work)
            <tr>
                <td><strong>{{ $work->title }}</strong><small>{{ $work->theme ?: 'Sem tema' }} · v{{ $work->version }}</small></td>
                <td><strong>{{ $work->user?->name }}</strong><small>{{ $work->user?->email }}</small></td>
                <td>{{ $categories[$work->category] ?? $work->category }}</td>
                <td><span><i class="fa-regular fa-eye"></i> {{ $work->views_count }}</span> <span class="ms-2"><i class="fa-regular fa-heart"></i> {{ $work->likes_count }}</span></td>
                <td><span class="admin-module-status is-{{ $work->status }}">{{ $statusLabels[$work->status] ?? $work->status }}</span></td>
                <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('culture.show', $work->slug) }}" class="btn btn-sm btn-outline-secondary rounded-pill" target="_blank">Ver</a>
                        <form action="{{ route('admin.culture.action', $work) }}" method="POST">@csrf
                            @if($work->status === 'hidden')<button class="btn btn-sm btn-success rounded-pill" name="action" value="publish">Republicar</button>
                            @else<button class="btn btn-sm btn-outline-danger rounded-pill" name="action" value="hide">Ocultar</button>@endif
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="admin-module-empty"><i class="fa-solid fa-book-open"></i><strong>Nenhuma obra encontrada</strong><span>Ajuste os filtros ou aguarde novas publicações.</span></div></td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
<div class="mt-3">{{ $works->links() }}</div>
@endsection

@push('styles')
<style>
.admin-module-metric{display:flex;align-items:center;gap:12px;height:100%;padding:16px;color:var(--foreground);background:var(--card);border:1px solid var(--border);border-radius:16px}.admin-module-metric>i{width:38px;height:38px;display:grid;place-items:center;background:var(--muted-bg);border-radius:11px}.admin-module-metric strong,.admin-module-metric span{display:block}.admin-module-metric strong{font-size:1.25rem;line-height:1}.admin-module-metric span{margin-top:4px;color:var(--muted-foreground);font-size:.7rem}.admin-module-filter{display:grid;grid-template-columns:minmax(240px,1fr) 180px 200px auto auto;gap:10px;padding:14px;background:var(--card);border:1px solid var(--border);border-radius:16px}.admin-module-filter select,.admin-module-search{min-height:42px;color:var(--foreground);background:var(--muted-bg);border:1px solid var(--border);border-radius:10px}.admin-module-filter select{padding:0 10px}.admin-module-search{display:flex;align-items:center;gap:9px;padding:0 12px}.admin-module-search input{width:100%;color:inherit;background:transparent;border:0;outline:0}.admin-module-table thead th{padding:13px 16px;color:var(--muted-foreground);background:var(--muted-bg);border-color:var(--border);font-size:.68rem;text-transform:uppercase}.admin-module-table td{padding:14px 16px;color:var(--foreground);background:var(--card);border-color:var(--border);font-size:.75rem}.admin-module-table td small{display:block;margin-top:3px;color:var(--muted-foreground);font-size:.64rem}.admin-module-status{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:.62rem;font-weight:800}.admin-module-status.is-published{color:#087443;background:rgba(10,166,96,.13)}.admin-module-status.is-draft{color:#8a5b00;background:rgba(245,184,0,.14)}.admin-module-status.is-hidden{color:#b42318;background:rgba(220,53,69,.12)}.admin-module-empty{display:grid;place-items:center;gap:6px;padding:45px;color:var(--muted-foreground)}.admin-module-empty i{font-size:2rem}@media(max-width:1399.98px){.admin-module-filter{grid-template-columns:1fr 1fr}.admin-module-search{grid-column:1/-1}}@media(max-width:575px){.admin-module-filter{grid-template-columns:1fr}.admin-module-filter>*{grid-column:1}.admin-module-table{min-width:820px}}
</style>
@endpush
