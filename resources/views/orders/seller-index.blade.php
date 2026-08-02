@extends('layouts.app')

@section('title', 'Pedidos da loja - Conectado em Sergipe')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/commerce.css') }}?v=1.0">
@endpush

@section('content')
<div class="commerce-shell">
    <div class="commerce-header">
        <div>
            <span class="commerce-eyebrow">GESTÃO DA LOJA</span>
            <h1>Pedidos recebidos</h1>
            <p class="commerce-muted mb-0">{{ $store->name }}</p>
        </div>
        <a class="commerce-secondary" href="{{ route('store.manage', $store) }}"><i class="fa-solid fa-arrow-left"></i> Gerenciar loja</a>
    </div>

    <form method="GET" class="commerce-card commerce-card-body mb-3 d-flex gap-2">
        <select class="form-select" name="status" style="max-width: 280px">
            <option value="">Todos os status</option>
            @foreach(\App\Models\Order::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="commerce-secondary" type="submit">Filtrar</button>
    </form>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="commerce-card">
        @forelse($orders as $order)
            <a class="order-list-item" href="{{ route('seller.orders.show', [$store, $order]) }}">
                <div>
                    <strong>{{ $order->public_id }}</strong>
                    <div>{{ $order->customer_name }}</div>
                    <small class="commerce-muted">{{ $order->items_count }} {{ $order->items_count === 1 ? 'item' : 'itens' }} · {{ $order->placed_at->format('d/m/Y H:i') }}</small>
                </div>
                <div class="text-end">
                    <span class="order-status is-{{ $order->status }}">{{ $order->status_label }}</span>
                    <strong class="d-block mt-2">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                </div>
            </a>
        @empty
            <div class="commerce-empty">
                <i class="fa-solid fa-box-open"></i>
                <h2 class="fw-bold">Nenhum pedido encontrado</h2>
                <p class="commerce-muted">Os novos pedidos aparecerão aqui.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
