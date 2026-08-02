@extends('layouts.app')

@section('title', 'Meus pedidos - Conectado em Sergipe')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/commerce.css') }}?v=1.0">
@endpush

@section('content')
<div class="commerce-shell">
    <div class="commerce-header">
        <div>
            <span class="commerce-eyebrow">ACOMPANHAMENTO</span>
            <h1>Meus pedidos</h1>
            <p class="commerce-muted mb-0">Acompanhe suas compras nas lojas locais.</p>
        </div>
        <a class="commerce-secondary" href="{{ route('stores.index') }}">Explorar lojas</a>
    </div>

    <div class="commerce-card">
        @forelse($orders as $order)
            <a class="order-list-item" href="{{ route('orders.show', $order) }}">
                <div>
                    <strong>{{ $order->public_id }}</strong>
                    <div class="commerce-muted">{{ $order->store_name }} · {{ $order->items_count }} {{ $order->items_count === 1 ? 'item' : 'itens' }}</div>
                    <small class="commerce-muted">{{ $order->placed_at->format('d/m/Y H:i') }}</small>
                </div>
                <div class="text-end">
                    <span class="order-status is-{{ $order->status }}">{{ $order->status_label }}</span>
                    <strong class="d-block mt-2">R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                </div>
            </a>
        @empty
            <div class="commerce-empty">
                <i class="fa-solid fa-receipt"></i>
                <h2 class="fw-bold">Você ainda não fez pedidos</h2>
                <a class="commerce-primary" href="{{ route('stores.index') }}">Conhecer lojas</a>
            </div>
        @endforelse
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
