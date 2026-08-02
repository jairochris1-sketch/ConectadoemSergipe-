@extends('layouts.app')

@section('title', 'Carrinho - Conectado em Sergipe')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/commerce.css') }}?v=1.0">
@endpush

@section('content')
<div class="commerce-shell">
    <div class="commerce-header">
        <div>
            <span class="commerce-eyebrow">SUA COMPRA</span>
            <h1>Carrinho</h1>
            @if($cart['store'])
                <p class="commerce-muted mb-0">Produtos de {{ $cart['store']->name }}</p>
            @endif
        </div>
        @if($cart['store'])
            <a class="commerce-secondary" href="{{ route('store.show', $cart['store']->slug) }}">
                <i class="fa-solid fa-arrow-left"></i> Continuar comprando
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @if($cart['items']->isEmpty())
        <div class="commerce-card commerce-empty">
            <i class="fa-solid fa-cart-shopping"></i>
            <h2 class="fw-bold">Seu carrinho está vazio</h2>
            <p class="commerce-muted">Encontre produtos nas lojas locais e adicione sua primeira escolha.</p>
            <a class="commerce-primary" href="{{ route('stores.index') }}">Explorar lojas</a>
        </div>
    @else
        <div class="commerce-grid">
            <section class="commerce-card">
                <div class="commerce-card-body">
                    @foreach($cart['items'] as $item)
                        <article class="commerce-item">
                            @if($item['product']->card_image)
                                <img class="commerce-item-image" src="{{ asset($item['product']->card_image) }}" alt="">
                            @else
                                <div class="commerce-item-image d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-image commerce-muted"></i>
                                </div>
                            @endif
                            <div>
                                <h2>{{ $item['product']->title }}</h2>
                                @if($item['variation'])<span class="commerce-muted d-block">{{ $item['variation']->name }}</span>@endif
                                @if($item['addons']->isNotEmpty())<small class="commerce-muted d-block">+ {{ $item['addons']->pluck('name')->join(', ') }}</small>@endif
                                @if($item['note'])<small class="commerce-muted d-block">Obs.: {{ $item['note'] }}</small>@endif
                                <span class="commerce-muted">R$ {{ number_format($item['unit_price'], 2, ',', '.') }} cada</span>
                                <form class="d-flex align-items-center gap-2 mt-2" action="{{ route('cart.update', $item['product']) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="line_key" value="{{ $item['line_key'] }}">
                                    <label class="visually-hidden" for="quantity-{{ $loop->index }}">Quantidade</label>
                                    <input id="quantity-{{ $loop->index }}" class="form-control form-control-sm" style="width: 82px" type="number" name="quantity" min="{{ $item['product']->minimum_quantity }}" max="{{ $item['variation']?->track_stock && !$item['product']->allow_backorders ? max(1, $item['variation']->stock_quantity) : ($item['product']->track_stock && !$item['product']->allow_backorders ? max(1, $item['product']->stock_quantity) : 99) }}" value="{{ $item['quantity'] }}">
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">Atualizar</button>
                                </form>
                            </div>
                            <div class="text-end">
                                <strong>R$ {{ number_format($item['line_total'], 2, ',', '.') }}</strong>
                                <form action="{{ route('cart.remove', $item['product']) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="line_key" value="{{ $item['line_key'] }}">
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">Remover</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <aside class="commerce-card commerce-summary">
                <div class="commerce-card-body">
                    <h2 class="h5 fw-bold">Resumo</h2>
                    <div class="commerce-summary-row">
                        <span>{{ $cart['quantity'] }} {{ $cart['quantity'] === 1 ? 'item' : 'itens' }}</span>
                        <strong>R$ {{ number_format($cart['subtotal'], 2, ',', '.') }}</strong>
                    </div>
                    @if($cart['discount'] > 0)
                        <div class="commerce-summary-row text-success">
                            <span>Cupom {{ $cart['coupon_code'] }}</span>
                            <strong>− R$ {{ number_format($cart['discount'], 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    <div class="commerce-summary-row commerce-summary-total">
                        <span>Total dos produtos</span>
                        <span>R$ {{ number_format($cart['total'], 2, ',', '.') }}</span>
                    </div>
                    <p class="commerce-muted small">Entrega e pagamento serão combinados com a loja. Nenhuma cobrança será feita agora.</p>
                    <a class="commerce-primary w-100" href="{{ route('checkout.index') }}">
                        Ir para o checkout <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="mt-2 text-center">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-link text-danger btn-sm" type="submit">Esvaziar carrinho</button>
                    </form>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
