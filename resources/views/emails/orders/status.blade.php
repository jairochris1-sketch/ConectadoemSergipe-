<!DOCTYPE html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f6f4;font-family:Arial,sans-serif;color:#172117">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px">
        <div style="background:#fff;border:1px solid #dfe7df;border-radius:16px;padding:28px">
            <p style="margin:0 0 8px;color:#256c2b;font-size:12px;font-weight:700;letter-spacing:1px">CONECTADO EM SERGIPE</p>
            <h1 style="margin:0 0 12px;font-size:26px">Atualização do pedido</h1>
            <p>O pedido <strong>{{ $order->public_id }}</strong> está com o status:</p>
            <p style="display:inline-block;padding:10px 14px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:700">
                {{ $order->status_label }}
            </p>
            <p style="color:#5f6f61">{{ $order->store_name }} · R$ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
            <a href="{{ $forSeller && $order->store ? route('seller.orders.show', [$order->store, $order]) : route('orders.show', $order) }}"
               style="display:inline-block;margin-top:12px;padding:12px 18px;border-radius:10px;background:#256c2b;color:#fff;text-decoration:none;font-weight:700">
                {{ $forSeller ? 'Gerenciar pedido' : 'Acompanhar pedido' }}
            </a>
        </div>
    </div>
</body>
</html>
