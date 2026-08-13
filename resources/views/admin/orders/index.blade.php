@extends('layouts.admin')

@section('title', 'Pedidos - Painel Administrativo')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Gestão de pedidos</h2>
        <p class="text-muted small mb-0">Acompanhe pedidos, lojas, clientes e etapas de atendimento.</p>
    </div>
</div>

<div class="row g-3 mb-4 admin-order-metrics">
    @foreach([
        ['label' => 'Total', 'value' => $metrics['total'], 'icon' => 'fa-receipt', 'tone' => 'primary'],
        ['label' => 'Aguardando', 'value' => $metrics['pending'], 'icon' => 'fa-clock', 'tone' => 'warning'],
        ['label' => 'Em andamento', 'value' => $metrics['in_progress'], 'icon' => 'fa-arrows-rotate', 'tone' => 'info'],
        ['label' => 'Concluídos', 'value' => $metrics['completed'], 'icon' => 'fa-circle-check', 'tone' => 'success'],
        ['label' => 'Cancelados', 'value' => $metrics['cancelled'], 'icon' => 'fa-ban', 'tone' => 'danger'],
    ] as $metric)
        <div class="col-6 col-md-4 col-xxl">
            <div class="admin-order-metric h-100">
                <i class="fa-solid {{ $metric['icon'] }} text-{{ $metric['tone'] }}"></i>
                <div><strong>{{ $metric['value'] }}</strong><span>{{ $metric['label'] }}</span></div>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" class="admin-order-filter mb-4">
    <div class="admin-order-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Pedido, cliente, telefone ou loja">
    </div>
    <select name="status" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        @foreach(\App\Models\Order::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <select name="store_id" aria-label="Filtrar por loja">
        <option value="">Todas as lojas</option>
        @foreach($stores as $store)
            <option value="{{ $store->id }}" @selected($storeId === $store->id)>{{ $store->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
    @if($search || $status || $storeId)
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Limpar</a>
    @endif
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0 admin-orders-table">
            <thead>
                <tr>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Loja</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->public_id }}</strong>
                            <small>{{ $order->items_count }} {{ $order->items_count === 1 ? 'item' : 'itens' }} · {{ $order->placed_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td><strong>{{ $order->customer_name }}</strong><small>{{ $order->user?->email }}</small></td>
                        <td>{{ $order->store_name }}</td>
                        <td><span class="admin-order-status is-{{ $order->status }}">{{ $order->status_label }}</span></td>
                        <td class="fw-bold">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</td>
                        <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary rounded-pill">Analisar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="admin-order-empty"><i class="fa-solid fa-box-open"></i><strong>Nenhum pedido encontrado</strong><span>Ajuste os filtros ou aguarde novos pedidos.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $orders->links() }}</div>
@endsection

@push('styles')
<style>
.admin-order-metric { display:flex; align-items:center; gap:12px; padding:16px; color:var(--foreground); background:var(--card); border:1px solid var(--border); border-radius:16px; }
.admin-order-metric > i { width:38px; height:38px; display:grid; place-items:center; background:var(--muted-bg); border-radius:11px; }
.admin-order-metric strong,.admin-order-metric span { display:block; }
.admin-order-metric strong { font-size:1.25rem; line-height:1; }
.admin-order-metric span { margin-top:4px; color:var(--muted-foreground); font-size:.7rem; }
.admin-order-filter { display:grid; grid-template-columns:minmax(220px,1fr) 190px 210px auto auto; gap:10px; padding:14px; background:var(--card); border:1px solid var(--border); border-radius:16px; }
.admin-order-filter select,.admin-order-search { min-height:42px; color:var(--foreground); background:var(--muted-bg); border:1px solid var(--border); border-radius:10px; }
.admin-order-filter select { padding:0 10px; }
.admin-order-search { display:flex; align-items:center; gap:9px; padding:0 12px; }
.admin-order-search input { width:100%; color:inherit; background:transparent; border:0; outline:0; }
.admin-orders-table thead th { padding:13px 16px; color:var(--muted-foreground); background:var(--muted-bg); border-color:var(--border); font-size:.68rem; text-transform:uppercase; }
.admin-orders-table td { padding:14px 16px; color:var(--foreground); background:var(--card); border-color:var(--border); font-size:.78rem; }
.admin-orders-table td small { display:block; margin-top:3px; color:var(--muted-foreground); font-size:.64rem; }
.admin-order-status { display:inline-flex; padding:5px 9px; border-radius:999px; font-size:.62rem; font-weight:800; }
.admin-order-status.is-pending { color:#8a5b00; background:rgba(245,184,0,.14); }
.admin-order-status.is-confirmed,.admin-order-status.is-preparing,.admin-order-status.is-ready { color:#075fb6; background:rgba(18,101,245,.12); }
.admin-order-status.is-completed { color:#087443; background:rgba(10,166,96,.13); }
.admin-order-status.is-cancelled { color:#b42318; background:rgba(220,53,69,.12); }
.admin-order-empty { display:grid; place-items:center; gap:6px; padding:45px; color:var(--muted-foreground); }
.admin-order-empty i { font-size:2rem; }
@media(max-width:1399.98px){.admin-order-filter{grid-template-columns:1fr 1fr}.admin-order-search{grid-column:1/-1}}
@media(max-width:575px){.admin-order-filter{grid-template-columns:1fr}.admin-order-filter>*{grid-column:1}.admin-orders-table{min-width:760px}}
</style>
@endpush
