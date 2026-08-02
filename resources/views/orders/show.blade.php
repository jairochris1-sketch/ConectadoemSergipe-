@extends('layouts.app')

@section('title', 'Pedido ' . $order->public_id . ' - Conectado em Sergipe')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/commerce.css') }}?v=1.0">
@endpush

@section('content')
<div class="commerce-shell">
    <div class="commerce-header">
        <div>
            <span class="commerce-eyebrow">{{ $sellerView ? 'PEDIDO RECEBIDO' : 'SEU PEDIDO' }}</span>
            <h1>{{ $order->public_id }}</h1>
            <p class="commerce-muted mb-0">{{ $order->placed_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
        <span class="order-status is-{{ $order->status }}">{{ $order->status_label }}</span>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="commerce-grid">
        <div>
            <section class="commerce-card mb-3">
                <div class="commerce-card-body">
                    <h2 class="h5 fw-bold mb-3">Itens do pedido</h2>
                    @foreach($order->items as $item)
                        <article class="commerce-item">
                            @if($item->product_image)
                                <img class="commerce-item-image" src="{{ asset($item->product_image) }}" alt="">
                            @else
                                <div class="commerce-item-image d-flex align-items-center justify-content-center"><i class="fa-solid fa-image commerce-muted"></i></div>
                            @endif
                            <div>
                                <h3>{{ $item->product_title }}</h3>
                                @if($item->variation_name)<small class="commerce-muted d-block">{{ $item->variation_name }}</small>@endif
                                @if($item->addons)<small class="commerce-muted d-block">+ {{ collect($item->addons)->pluck('name')->join(', ') }}</small>@endif
                                @if($item->customer_note)<small class="commerce-muted d-block">Obs.: {{ $item->customer_note }}</small>@endif
                                <span class="commerce-muted">{{ $item->quantity }} × R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</span>
                            </div>
                            <strong>R$ {{ number_format((float) $item->line_total, 2, ',', '.') }}</strong>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="commerce-card">
                <div class="commerce-card-body">
                    <h2 class="h5 fw-bold">Contato e recebimento</h2>
                    <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                    <p class="mb-1">{{ $order->customer_phone }}</p>
                    @if($order->customer_email)<p class="mb-1">{{ $order->customer_email }}</p>@endif
                    <hr>
                    <p class="mb-1"><strong>{{ $order->fulfillment_label }}</strong></p>
                    @if($order->fulfillment_method === 'delivery')
                        <p class="commerce-muted mb-0">
                            {{ $order->delivery_address }}, {{ $order->delivery_city }}/{{ $order->delivery_state }}
                            @if($order->delivery_zipcode) · CEP {{ $order->delivery_zipcode }}@endif
                        </p>
                        @if($order->delivery_neighborhood)<p class="commerce-muted mb-0">Bairro: {{ $order->delivery_neighborhood }}</p>@endif
                    @else
                        <p class="commerce-muted mb-0">Horário e local serão confirmados pela loja.</p>
                    @endif
                    @if($order->notes)
                        <hr><p class="mb-0"><strong>Observações:</strong> {{ $order->notes }}</p>
                    @endif
                </div>
            </section>
        </div>

        <aside class="commerce-card commerce-summary">
            <div class="commerce-card-body">
                <h2 class="h5 fw-bold">Resumo</h2>
                <div class="commerce-summary-row">
                    <span>Subtotal</span>
                    <span>R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</span>
                </div>
                @if((float) $order->discount_total > 0)
                    <div class="commerce-summary-row text-success">
                        <span>Desconto ({{ $order->coupon_code }})</span>
                        <strong>− R$ {{ number_format((float) $order->discount_total, 2, ',', '.') }}</strong>
                    </div>
                @endif
                @if((float) $order->delivery_fee > 0)
                    <div class="commerce-summary-row">
                        <span>Entrega</span>
                        <strong>R$ {{ number_format((float) $order->delivery_fee, 2, ',', '.') }}</strong>
                    </div>
                @endif
                <div class="commerce-summary-row commerce-summary-total">
                    <span>Total dos produtos</span>
                    <span>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</span>
                </div>
                <p class="commerce-muted small">Pagamento e entrega não foram cobrados pelo site e devem ser combinados entre cliente e loja.</p>

                @if($sellerView)
                    @php
                        $nextStatuses = [
                            'pending' => ['confirmed' => 'Confirmar pedido', 'cancelled' => 'Cancelar pedido'],
                            'confirmed' => ['preparing' => 'Iniciar preparação', 'cancelled' => 'Cancelar pedido'],
                            'preparing' => ['ready' => 'Marcar como pronto', 'cancelled' => 'Cancelar pedido'],
                            'ready' => ['completed' => 'Concluir pedido'],
                        ][$order->status] ?? [];
                    @endphp
                    @if($nextStatuses)
                        <form action="{{ route('seller.orders.status', [$order->store, $order]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label" for="status">Atualizar pedido</label>
                            <select class="form-select mb-2" name="status" id="status" required>
                                @foreach($nextStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                            </select>
                            <button class="commerce-primary w-100" type="submit">Salvar status</button>
                        </form>
                    @endif
                    <a class="commerce-secondary w-100 mt-2" href="{{ route('seller.orders.index', $order->store) }}">Voltar aos pedidos</a>
                @else
                    @if($order->status === 'pending')
                        <form action="{{ route('orders.cancel', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="commerce-secondary w-100 text-danger" type="submit">Cancelar pedido</button>
                        </form>
                    @endif
                    <a class="commerce-secondary w-100 mt-2" href="{{ route('orders.index') }}">Meus pedidos</a>
                    @if($order->store)
                        <a class="commerce-primary w-100 mt-2" href="{{ route('store.show', $order->store->slug) }}">Ver loja</a>
                    @endif
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
