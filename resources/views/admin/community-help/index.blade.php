@extends('layouts.admin')

@section('title', 'Ajuda Comunitária - Painel Administrativo')

@section('content')
@php $statusLabels=['pending'=>'Em análise','open'=>'Aberto','in_progress'=>'Em atendimento','resolved'=>'Resolvido','rejected'=>'Ajustes solicitados','hidden'=>'Oculto']; @endphp
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div><h2 class="fw-bold text-dark mb-1">Ajuda Comunitária</h2><p class="text-muted small mb-0">Analise pedidos locais, respostas e denúncias em um único lugar.</p></div>
    <a href="{{ route('community-help.index') }}" class="btn btn-outline-primary rounded-pill" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir página pública</a>
</div>

<div class="row g-3 mb-4 admin-help-metrics">
    @foreach([
        ['Total',$metrics['total'],'fa-hand-holding-heart','primary'],
        ['Em análise',$metrics['pending'],'fa-clock','warning'],
        ['Ativos',$metrics['active'],'fa-people-group','info'],
        ['Resolvidos',$metrics['resolved'],'fa-circle-check','success'],
        ['Com denúncias',$metrics['reported'],'fa-flag','danger'],
    ] as [$label,$value,$icon,$tone])
        <div class="col-6 col-md-4 col-xxl"><div class="admin-help-metric"><i class="fa-solid {{ $icon }} text-{{ $tone }}"></i><div><strong>{{ $value }}</strong><span>{{ $label }}</span></div></div></div>
    @endforeach
</div>

<form method="GET" class="admin-help-filter mb-4">
    <div class="admin-help-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Pedido, pessoa, bairro ou e-mail"></div>
    <select name="status" aria-label="Status"><option value="">Todos os status</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected($status===$value)>{{ $label }}</option>@endforeach</select>
    <select name="category" aria-label="Categoria"><option value="">Todas as categorias</option>@foreach($categories as $value=>$label)<option value="{{ $value }}" @selected($category===$value)>{{ $label }}</option>@endforeach</select>
    <select name="city" aria-label="Cidade"><option value="">Todas as cidades</option>@foreach($cities as $value)<option value="{{ $value }}" @selected($city===$value)>{{ $value }}</option>@endforeach</select>
    <label class="admin-help-reported"><input type="checkbox" name="reported" value="1" @checked($reportedOnly)> Com denúncias</label>
    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
    @if($search || $status || $category || $city || $reportedOnly)<a class="btn btn-outline-secondary" href="{{ route('admin.community-help.index') }}">Limpar</a>@endif
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0 admin-help-table">
    <thead><tr><th>Pedido</th><th>Membro</th><th>Local</th><th>Interações</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($helpRequests as $item)
        <tr>
            <td><strong>{{ $item->title }}</strong><small>{{ $categories[$item->category] ?? $item->category }} · {{ $item->public_id }}</small></td>
            <td><strong>{{ $item->user?->name }}</strong><small>{{ $item->user?->email }}</small></td>
            <td>{{ $item->neighborhood }}<small>{{ $item->city }}</small></td>
            <td><span><i class="fa-regular fa-comments"></i> {{ $item->responses_count }}</span>@if($item->pending_reports_count)<span class="badge bg-danger ms-2"><i class="fa-solid fa-flag"></i> {{ $item->pending_reports_count }}</span>@endif</td>
            <td><span class="admin-help-status is-{{ $item->status }}">{{ $statusLabels[$item->status] ?? $item->status }}</span></td>
            <td class="text-end"><a href="{{ route('admin.community-help.show',$item) }}" class="btn btn-sm btn-outline-primary rounded-pill">Analisar</a></td>
        </tr>
    @empty<tr><td colspan="6"><div class="admin-help-empty"><i class="fa-solid fa-hands-holding-child"></i><strong>Nenhum pedido encontrado</strong><span>Ajuste os filtros ou aguarde novos pedidos.</span></div></td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $helpRequests->links() }}</div>
@endsection

@push('styles')
<style>
.admin-help-metric{display:flex;align-items:center;gap:12px;height:100%;padding:16px;color:var(--foreground);background:var(--card);border:1px solid var(--border);border-radius:16px}.admin-help-metric>i{width:38px;height:38px;display:grid;place-items:center;background:var(--muted-bg);border-radius:11px}.admin-help-metric strong,.admin-help-metric span{display:block}.admin-help-metric strong{font-size:1.25rem;line-height:1}.admin-help-metric span{margin-top:4px;color:var(--muted-foreground);font-size:.68rem}.admin-help-filter{display:grid;grid-template-columns:minmax(220px,1fr) 160px 195px 170px auto auto auto;gap:9px;padding:14px;background:var(--card);border:1px solid var(--border);border-radius:16px}.admin-help-filter select,.admin-help-search{min-height:42px;color:var(--foreground);background:var(--muted-bg);border:1px solid var(--border);border-radius:10px}.admin-help-filter select{padding:0 9px}.admin-help-search{display:flex;align-items:center;gap:9px;padding:0 12px}.admin-help-search input{width:100%;color:inherit;background:transparent;border:0;outline:0}.admin-help-reported{display:flex;align-items:center;gap:7px;min-height:42px;padding:0 11px;color:var(--foreground);background:var(--muted-bg);border:1px solid var(--border);border-radius:10px;font-size:.7rem;white-space:nowrap}.admin-help-table thead th{padding:13px 16px;color:var(--muted-foreground);background:var(--muted-bg);border-color:var(--border);font-size:.68rem;text-transform:uppercase}.admin-help-table td{padding:14px 16px;color:var(--foreground);background:var(--card);border-color:var(--border);font-size:.75rem}.admin-help-table td small{display:block;margin-top:3px;color:var(--muted-foreground);font-size:.64rem}.admin-help-status{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:.62rem;font-weight:800}.admin-help-status.is-pending{color:#8a5b00;background:rgba(245,184,0,.14)}.admin-help-status.is-open,.admin-help-status.is-in_progress{color:#075fb6;background:rgba(18,101,245,.12)}.admin-help-status.is-resolved{color:#087443;background:rgba(10,166,96,.13)}.admin-help-status.is-rejected,.admin-help-status.is-hidden{color:#b42318;background:rgba(220,53,69,.12)}.admin-help-empty{display:grid;place-items:center;gap:6px;padding:45px;color:var(--muted-foreground)}.admin-help-empty i{font-size:2rem}@media(max-width:1399.98px){.admin-help-filter{grid-template-columns:1fr 1fr}.admin-help-search{grid-column:1/-1}}@media(max-width:575px){.admin-help-filter{grid-template-columns:1fr}.admin-help-filter>*{grid-column:1}.admin-help-table{min-width:850px}}
</style>
@endpush
