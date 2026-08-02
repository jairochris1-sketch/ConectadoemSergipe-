@extends('layouts.app')

@section('title', 'Finalizar pedido - Conectado em Sergipe')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/commerce.css') }}?v=1.0">
@endpush

@section('content')
<div class="commerce-shell">
    <div class="commerce-header">
        <div>
            <span class="commerce-eyebrow">ÚLTIMA ETAPA</span>
            <h1>Finalizar pedido</h1>
            <p class="commerce-muted mb-0">{{ $cart['store']->name }}</p>
        </div>
        <a class="commerce-secondary" href="{{ route('cart.index') }}"><i class="fa-solid fa-arrow-left"></i> Voltar ao carrinho</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revise os dados:</strong>
            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="commerce-card commerce-card-body mb-3">
        <div class="row align-items-end g-3">
            <div class="col-md">
                <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-ticket text-success me-2"></i>Cupom de desconto</h2>
                @if($cart['promotion'])
                    <p class="mb-0">
                        <strong>{{ $cart['coupon_code'] }}</strong> aplicado:
                        {{ $cart['promotion']->discount_label }}
                    </p>
                @else
                    <p class="commerce-muted mb-0">Digite o código divulgado pela loja.</p>
                @endif
            </div>
            <div class="col-md-auto">
                @if($cart['promotion'])
                    <form action="{{ route('checkout.coupon.remove') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="commerce-secondary" type="submit">Remover cupom</button>
                    </form>
                @else
                    <form action="{{ route('checkout.coupon.apply') }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <label class="visually-hidden" for="coupon_code">Código do cupom</label>
                        <input class="form-control text-uppercase" id="coupon_code" name="coupon_code" maxlength="40" placeholder="EX.: SERGIPE10" required>
                        <button class="commerce-secondary" type="submit">Aplicar</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <form action="{{ route('checkout.place') }}" method="POST">
        @csrf
        <div class="commerce-grid">
            <section class="commerce-card">
                <div class="commerce-form-section">
                    <h2><i class="fa-regular fa-user me-2 text-success"></i>Dados para contato</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="customer_name">Nome completo</label>
                            <input class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $user->name) }}" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="customer_phone">Celular/WhatsApp</label>
                            <input class="form-control" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $user->whatsapp ?: $user->phone) }}" maxlength="20" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="customer_email">E-mail</label>
                            <input class="form-control" type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', $user->email) }}" maxlength="255">
                        </div>
                    </div>
                </div>

                <div class="commerce-form-section">
                    <h2><i class="fa-solid fa-box me-2 text-success"></i>Como deseja receber?</h2>
                    <div class="d-grid gap-2">
                        @if($deliveryOptions['pickup'])
                        <label class="border rounded-3 p-3">
                            <input type="radio" name="fulfillment_method" value="pickup" @checked(old('fulfillment_method', 'pickup') === 'pickup')>
                            <strong class="ms-2">Retirar na loja</strong>
                            <span class="commerce-muted d-block ms-4">{{ $cart['store']->pickup_address ?: 'A loja confirmará horário e local.' }}</span>
                        </label>
                        @endif
                        @if($deliveryOptions['delivery'])
                        <label class="border rounded-3 p-3">
                            <input type="radio" name="fulfillment_method" value="delivery" @checked(old('fulfillment_method', $deliveryOptions['pickup'] ? 'pickup' : 'delivery') === 'delivery')>
                            <strong class="ms-2">Solicitar entrega</strong>
                            <span class="commerce-muted d-block ms-4">
                                Taxa: R$ {{ number_format((float) $cart['store']->delivery_fee, 2, ',', '.') }}
                                @if($cart['store']->delivery_min_minutes) · {{ $cart['store']->delivery_min_minutes }}–{{ $cart['store']->delivery_max_minutes ?: $cart['store']->delivery_min_minutes }} min @endif
                            </span>
                        </label>
                        @endif
                    </div>

                    <div id="delivery-fields" class="row g-3 mt-1">
                        <div class="col-12">
                            <label class="form-label" for="delivery_address">Endereço</label>
                            <input class="form-control" id="delivery_address" name="delivery_address" value="{{ old('delivery_address', $user->commercial_address) }}" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="delivery_city">Cidade</label>
                            <input class="form-control" id="delivery_city" name="delivery_city" value="{{ old('delivery_city', $user->city) }}" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="delivery_neighborhood">Bairro</label>
                            <input class="form-control" id="delivery_neighborhood" name="delivery_neighborhood" value="{{ old('delivery_neighborhood') }}" maxlength="120" list="delivery-neighborhood-options">
                            @if(collect($cart['store']->delivery_neighborhoods)->isNotEmpty())
                                <datalist id="delivery-neighborhood-options">
                                    @foreach($cart['store']->delivery_neighborhoods as $neighborhood)<option value="{{ $neighborhood }}"></option>@endforeach
                                </datalist>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="delivery_state">UF</label>
                            <input class="form-control text-uppercase" id="delivery_state" name="delivery_state" value="{{ old('delivery_state', 'SE') }}" maxlength="2">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="delivery_zipcode">CEP</label>
                            <input class="form-control" id="delivery_zipcode" name="delivery_zipcode" value="{{ old('delivery_zipcode') }}" maxlength="10">
                        </div>
                    </div>
                </div>

                <div class="commerce-form-section">
                    <h2><i class="fa-regular fa-message me-2 text-success"></i>Observações</h2>
                    <textarea class="form-control" name="notes" rows="3" maxlength="1000" placeholder="Cor, tamanho, referência ou outra informação para a loja.">{{ old('notes') }}</textarea>
                </div>
            </section>

            <aside class="commerce-card commerce-summary">
                <div class="commerce-card-body">
                    <h2 class="h5 fw-bold">Seu pedido</h2>
                    @foreach($cart['items'] as $item)
                        <div class="commerce-summary-row small">
                            <span>{{ $item['quantity'] }}× {{ $item['product']->title }} @if($item['variation']) · {{ $item['variation']->name }} @endif</span>
                            <strong>R$ {{ number_format($item['line_total'], 2, ',', '.') }}</strong>
                        </div>
                    @endforeach
                    <div class="commerce-summary-row">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($cart['subtotal'], 2, ',', '.') }}</span>
                    </div>
                    @if($cart['discount'] > 0)
                        <div class="commerce-summary-row text-success">
                            <span>Desconto ({{ $cart['coupon_code'] }})</span>
                            <strong>− R$ {{ number_format($cart['discount'], 2, ',', '.') }}</strong>
                        </div>
                    @endif
                    <div class="commerce-summary-row commerce-summary-total">
                        <span>Total dos produtos</span>
                        <span>R$ {{ number_format($cart['total'], 2, ',', '.') }}</span>
                    </div>
                    @if($deliveryOptions['delivery'])
                        <div class="commerce-summary-row" id="delivery-fee-summary" hidden>
                            <span>Entrega</span>
                            <span id="delivery-fee-value">R$ {{ number_format((float) $cart['store']->delivery_fee, 2, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="alert alert-light border small">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i>
                        Este checkout apenas envia o pedido. Pagamento e eventual entrega serão combinados depois.
                    </div>
                    <button class="commerce-primary w-100" type="submit">
                        Confirmar pedido <i class="fa-solid fa-check"></i>
                    </button>
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const fields = document.getElementById('delivery-fields');
        const feeSummary = document.getElementById('delivery-fee-summary');
        const feeValue = document.getElementById('delivery-fee-value');
        const neighborhood = document.getElementById('delivery_neighborhood');
        const regionFees = @json($cart['store']->delivery_region_fees ?? []);
        const baseFee = Number(@json((float) $cart['store']->delivery_fee));
        const freeThreshold = @json($cart['store']->free_delivery_threshold !== null ? (float) $cart['store']->free_delivery_threshold : null);
        const subtotal = Number(@json((float) $cart['subtotal']));
        const updateFee = () => {
            if (!feeValue) return;
            const region = regionFees.find((item) => String(item.region || '').trim().toLocaleLowerCase('pt-BR') === String(neighborhood?.value || '').trim().toLocaleLowerCase('pt-BR'));
            const fee = freeThreshold !== null && subtotal >= freeThreshold ? 0 : Number(region?.fee ?? baseFee);
            feeValue.textContent = fee.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        };
        const update = () => {
            const delivery = document.querySelector('[name="fulfillment_method"]:checked')?.value === 'delivery';
            fields.hidden = !delivery;
            if (feeSummary) feeSummary.hidden = !delivery;
            updateFee();
            fields.querySelectorAll('input').forEach((input) => {
                input.required = delivery && ['delivery_address', 'delivery_city', 'delivery_state'].includes(input.name);
            });
        };
        document.querySelectorAll('[name="fulfillment_method"]').forEach((radio) => radio.addEventListener('change', update));
        neighborhood?.addEventListener('input', updateFee);
        update();
    })();
</script>
@endpush
