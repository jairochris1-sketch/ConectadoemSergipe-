@extends('layouts.admin')

@section('title', 'Pedido ' . $order->public_id . ' - Painel Administrativo')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="{{ route('admin.orders.index') }}" class="text-decoration-none small"><i class="fa-solid fa-arrow-left me-1"></i> Pedidos</a>
        <h2 class="fw-bold text-dark mb-1 mt-2">{{ $order->public_id }}</h2>
        <p class="text-muted small mb-0">Criado em {{ $order->placed_at->format('d/m/Y \à\s H:i') }}</p>
    </div>
    <span class="admin-order-status is-{{ $order->status }}">{{ $order->status_label }}</span>
</div>

@if(session('success'))<div class="alert alert-success rounded-3">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger rounded-3">{{ $errors->first() }}</div>@endif

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <section class="admin-order-card mb-4">
            <div class="admin-order-card-title"><i class="fa-solid fa-boxes-stacked"></i><h3>Itens do pedido</h3></div>
            @foreach($order->items as $item)
                <article class="admin-order-item">
                    <div class="admin-order-item-image">
                        @if($item->product_image)<img src="{{ asset($item->product_image) }}" alt="">@else<i class="fa-solid fa-image"></i>@endif
                    </div>
                    <div>
                        <strong>{{ $item->product_title }}</strong>
                        @if($item->variation_name)<small>{{ $item->variation_name }}</small>@endif
                        @if($item->addons)<small>Adicionais: {{ collect($item->addons)->pluck('name')->join(', ') }}</small>@endif
                        @if($item->customer_note)<small>Observação: {{ $item->customer_note }}</small>@endif
                        <span>{{ $item->quantity }} × R$ {{ number_format((float) $item->unit_price, 2, ',', '.') }}</span>
                    </div>
                    <strong>R$ {{ number_format((float) $item->line_total, 2, ',', '.') }}</strong>
                </article>
            @endforeach
        </section>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <section class="admin-order-card h-100">
                    <div class="admin-order-card-title"><i class="fa-solid fa-user"></i><h3>Cliente</h3></div>
                    <dl class="admin-order-details">
                        <div><dt>Nome</dt><dd>{{ $order->customer_name }}</dd></div>
                        <div><dt>Telefone</dt><dd>{{ $order->customer_phone }}</dd></div>
                        <div><dt>E-mail</dt><dd>{{ $order->customer_email ?: $order->user?->email ?: 'Não informado' }}</dd></div>
                    </dl>
                </section>
            </div>
            <div class="col-12 col-lg-6">
                <section class="admin-order-card h-100">
                    <div class="admin-order-card-title"><i class="fa-solid fa-truck"></i><h3>{{ $order->fulfillment_label }}</h3></div>
                    @if($order->fulfillment_method === 'delivery')
                        <p>{{ $order->delivery_address }}</p>
                        <p>{{ $order->delivery_neighborhood ? $order->delivery_neighborhood.', ' : '' }}{{ $order->delivery_city }}/{{ $order->delivery_state }}</p>
                        @if($order->delivery_zipcode)<p>CEP {{ $order->delivery_zipcode }}</p>@endif
                    @else
                        <p>Retirada combinada diretamente com a loja.</p>
                    @endif
                    @if($order->notes)<div class="admin-order-note"><strong>Observações</strong><p>{{ $order->notes }}</p></div>@endif
                </section>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <aside class="admin-order-card mb-4">
            <div class="admin-order-card-title"><i class="fa-solid fa-store"></i><h3>Loja</h3></div>
            <strong>{{ $order->store_name }}</strong>
            @if($order->store)
                <p class="text-muted small">Responsável: {{ $order->store->user?->name }}</p>
                <a href="{{ route('admin.stores.show', $order->store) }}" class="btn btn-sm btn-outline-primary w-100">Analisar loja</a>
            @else
                <p class="text-muted small mb-0">A loja original não está mais disponível.</p>
            @endif
        </aside>

        <aside class="admin-order-card mb-4">
            <div class="admin-order-card-title"><i class="fa-solid fa-calculator"></i><h3>Resumo financeiro</h3></div>
            <div class="admin-order-summary"><span>Subtotal</span><strong>R$ {{ number_format((float) $order->subtotal, 2, ',', '.') }}</strong></div>
            @if((float) $order->discount_total > 0)<div class="admin-order-summary text-success"><span>Desconto {{ $order->coupon_code ? '('.$order->coupon_code.')' : '' }}</span><strong>− R$ {{ number_format((float) $order->discount_total, 2, ',', '.') }}</strong></div>@endif
            @if((float) $order->delivery_fee > 0)<div class="admin-order-summary"><span>Entrega</span><strong>R$ {{ number_format((float) $order->delivery_fee, 2, ',', '.') }}</strong></div>@endif
            <div class="admin-order-summary is-total"><span>Total</span><strong>R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div>
            <p class="text-muted small mb-0 mt-3">Pagamento não processado pelo site.</p>
        </aside>

        <aside class="admin-order-card">
            <div class="admin-order-card-title"><i class="fa-solid fa-shield-halved"></i><h3>Ação administrativa</h3></div>
            @if($nextStatuses)
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <label for="admin-order-status" class="form-label small fw-semibold">Próxima etapa permitida</label>
                    <select id="admin-order-status" name="status" class="form-select mb-3" required>
                        @foreach($nextStatuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-primary w-100">Atualizar pedido</button>
                </form>
            @else
                <p class="text-muted small mb-0">Este pedido está encerrado e não permite novas transições.</p>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-order-status { display:inline-flex; padding:7px 12px; border-radius:999px; font-size:.7rem; font-weight:800; }
.admin-order-status.is-pending { color:#8a5b00; background:rgba(245,184,0,.14); }
.admin-order-status.is-confirmed,.admin-order-status.is-preparing,.admin-order-status.is-ready { color:#075fb6; background:rgba(18,101,245,.12); }
.admin-order-status.is-completed { color:#087443; background:rgba(10,166,96,.13); }
.admin-order-status.is-cancelled { color:#b42318; background:rgba(220,53,69,.12); }
.admin-order-card { padding:20px; color:var(--foreground); background:var(--card); border:1px solid var(--border); border-radius:18px; box-shadow:0 8px 24px rgba(15,23,42,.04); }
.admin-order-card-title { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.admin-order-card-title i { width:34px; height:34px; display:grid; place-items:center; color:#1265f5; background:rgba(18,101,245,.1); border-radius:10px; }
.admin-order-card-title h3 { margin:0; font-size:.95rem; font-weight:800; }
.admin-order-item { display:grid; grid-template-columns:58px minmax(0,1fr) auto; align-items:center; gap:12px; padding:13px 0; border-top:1px solid var(--border); }
.admin-order-item-image { width:58px; height:58px; display:grid; place-items:center; overflow:hidden; color:var(--muted-foreground); background:var(--muted-bg); border-radius:10px; }
.admin-order-item-image img { width:100%; height:100%; object-fit:cover; }
.admin-order-item small,.admin-order-item span { display:block; margin-top:3px; color:var(--muted-foreground); font-size:.68rem; }
.admin-order-details { margin:0; }
.admin-order-details>div { display:grid; grid-template-columns:80px minmax(0,1fr); gap:10px; padding:8px 0; border-top:1px solid var(--border); }
.admin-order-details dt { color:var(--muted-foreground); font-size:.68rem; }
.admin-order-details dd { margin:0; overflow-wrap:anywhere; font-size:.75rem; font-weight:700; }
.admin-order-card>p { margin-bottom:5px; font-size:.75rem; }
.admin-order-note { margin-top:12px; padding:10px; background:var(--muted-bg); border-radius:10px; }
.admin-order-note p { margin:4px 0 0; font-size:.72rem; }
.admin-order-summary { display:flex; justify-content:space-between; gap:12px; padding:9px 0; border-top:1px solid var(--border); font-size:.75rem; }
.admin-order-summary.is-total { margin-top:4px; padding-top:13px; font-size:.92rem; }
@media(max-width:575px){.admin-order-card{padding:15px}.admin-order-item{grid-template-columns:48px minmax(0,1fr)}.admin-order-item>strong{grid-column:2}.admin-order-item-image{width:48px;height:48px}}
</style>
@endpush
