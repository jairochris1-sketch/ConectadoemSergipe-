<!DOCTYPE html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f6f4;font-family:Arial,sans-serif;color:#172117">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px">
        <div style="background:#fff;border:1px solid #dfe7df;border-radius:16px;padding:28px">
            <p style="margin:0 0 8px;color:#256c2b;font-size:12px;font-weight:700;letter-spacing:1px">CONECTADO EM SERGIPE</p>
            <h1 style="margin:0 0 12px;font-size:26px">
                {{ $forSeller ? 'Você recebeu um novo pedido' : 'Seu pedido foi enviado' }}
            </h1>
            <p style="color:#5f6f61">Pedido <strong>{{ $order->public_id }}</strong> · {{ $order->store_name }}</p>

            @foreach($order->items as $item)
                <div style="display:flex;justify-content:space-between;border-top:1px solid #e7ece7;padding:12px 0">
                    <span>{{ $item->quantity }}× {{ $item->product_title }}</span>
                    <strong>R$ {{ number_format((float) $item->line_total, 2, ',', '.') }}</strong>
                </div>
            @endforeach

            @if((float) $order->discount_total > 0)
                <p style="color:#256c2b">Cupom {{ $order->coupon_code }}: − R$ {{ number_format((float) $order->discount_total, 2, ',', '.') }}</p>
            @endif
            <p style="font-size:20px"><strong>Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></p>
            <p style="color:#5f6f61">Pagamento e eventual entrega serão combinados diretamente entre cliente e loja.</p>

            <a href="{{ $forSeller && $order->store ? route('seller.orders.show', [$order->store, $order]) : route('orders.show', $order) }}"
               style="display:inline-block;margin-top:12px;padding:12px 18px;border-radius:10px;background:#256c2b;color:#fff;text-decoration:none;font-weight:700">
                {{ $forSeller ? 'Gerenciar pedido' : 'Acompanhar pedido' }}
            </a>
        </div>
    </div>
</body>
</html>
