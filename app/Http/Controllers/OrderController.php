<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Order;
use App\Models\Store;
use App\Services\CartService;
use App\Services\DeliveryService;
use App\Services\OrderCommunicationService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function checkout(Request $request, CartService $cart, DeliveryService $delivery)
    {
        $summary = $cart->summary($request);
        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Seu carrinho está vazio.']);
        }

        return view('checkout.index', [
            'cart' => $summary,
            'user' => $request->user(),
            'deliveryOptions' => $delivery->options($summary['store']),
        ]);
    }

    public function place(
        Request $request,
        CartService $cart,
        DeliveryService $delivery,
        StockService $stock,
        OrderCommunicationService $communication
    ) {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'fulfillment_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'delivery_address' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:255'],
            'delivery_city' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:100'],
            'delivery_neighborhood' => ['nullable', 'string', 'max:120'],
            'delivery_state' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'size:2'],
            'delivery_zipcode' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $summary = $cart->summary($request);
        if ($summary['items']->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Seu carrinho está vazio.']);
        }

        $order = DB::transaction(function () use ($request, $validated, $summary, $cart, $delivery, $stock) {
            $store = Store::query()->publiclyVisible()->lockForUpdate()->find($summary['store']->id);
            if (! $store) {
                throw ValidationException::withMessages(['cart' => 'A loja não está disponível no momento.']);
            }

            $lockedProducts = Ad::query()
                ->whereIn('id', $summary['items']->pluck('product.id'))
                ->where('store_id', $store->id)
                ->where('module', 'products')
                ->where('status', 'active')
                ->whereNotNull('price')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($lockedProducts->count() !== $summary['items']->pluck('product.id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'cart' => 'Um produto do carrinho deixou de estar disponível. Revise o carrinho.',
                ]);
            }

            $delivery->assertAvailable(
                $store,
                $validated['fulfillment_method'],
                $validated['delivery_city'] ?? null,
                $validated['delivery_neighborhood'] ?? null
            );
            if ($summary['subtotal'] < (float) $store->minimum_order) {
                throw ValidationException::withMessages([
                    'cart' => 'O pedido mínimo desta loja é R$ '.number_format((float) $store->minimum_order, 2, ',', '.').'.',
                ]);
            }

            $items = $summary['items']->map(function (array $item) use ($lockedProducts, $stock) {
                $product = $lockedProducts->get($item['product']->id);
                $stock->assertAvailable($product, $item['quantity'], $item['variation']);
                $unitPrice = $item['unit_price'];

                return [
                    'ad_id' => $product->id,
                    'product_variation_id' => $item['variation']?->id,
                    'product_title' => $product->title,
                    'variation_name' => $item['variation']?->name,
                    'sku' => $item['variation']?->sku ?: $product->sku,
                    'addons' => $item['addons']->map(fn ($addon) => [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => (float) $addon->price,
                    ])->all(),
                    'customer_note' => $item['note'],
                    'product_image' => $product->card_image,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'line_total' => round($unitPrice * $item['quantity'], 2),
                ];
            });
            $subtotal = round((float) $items->sum('line_total'), 2);
            $promotion = null;
            if ($summary['coupon_code']) {
                $promotion = $store->promotions()
                    ->currentlyActive()
                    ->where('coupon_code', $summary['coupon_code'])
                    ->lockForUpdate()
                    ->first();

                if (! $promotion) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'O cupom deixou de estar disponível. Revise o checkout.',
                    ]);
                }
            }
            $discount = $promotion ? $cart->discountFor($promotion, $subtotal) : 0.0;
            $deliveryFee = $delivery->fee(
                $store,
                $validated['fulfillment_method'],
                $subtotal,
                $validated['delivery_neighborhood'] ?? null
            );

            $order = Order::create([
                'public_id' => $this->newPublicId(),
                'user_id' => $request->user()->id,
                'store_id' => $store->id,
                'store_name' => $store->name,
                'store_promotion_id' => $promotion?->id,
                'coupon_code' => $promotion?->coupon_code,
                'discount_type' => $promotion?->discount_type,
                'discount_value' => $promotion?->discount_value,
                'status' => 'pending',
                'fulfillment_method' => $validated['fulfillment_method'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => preg_replace('/\D+/', '', $validated['customer_phone']),
                'customer_email' => $validated['customer_email'] ?? null,
                'delivery_address' => $validated['fulfillment_method'] === 'delivery' ? $validated['delivery_address'] : null,
                'delivery_city' => $validated['fulfillment_method'] === 'delivery' ? $validated['delivery_city'] : null,
                'delivery_neighborhood' => $validated['fulfillment_method'] === 'delivery'
                    ? ($validated['delivery_neighborhood'] ?? null)
                    : null,
                'delivery_state' => $validated['fulfillment_method'] === 'delivery' ? strtoupper($validated['delivery_state']) : null,
                'delivery_zipcode' => $validated['fulfillment_method'] === 'delivery'
                    ? preg_replace('/\D+/', '', $validated['delivery_zipcode'] ?? '')
                    : null,
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => max(0, round($subtotal - $discount + $deliveryFee, 2)),
                'placed_at' => now(),
            ]);
            $order->items()->createMany($items->all());

            return $order;
        });

        $cart->clear($request);
        $communication->orderPlaced($order);

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Pedido enviado para a loja. O pagamento será combinado após a confirmação.');
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('store')
            ->withCount('items')
            ->latest('placed_at')
            ->paginate(12);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $order->load(['store', 'items']);

        return view('orders.show', ['order' => $order, 'sellerView' => false]);
    }

    public function cancel(Request $request, Order $order, OrderCommunicationService $communication)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        if ($order->status !== 'pending') {
            return back()->withErrors(['order' => 'Somente pedidos aguardando confirmação podem ser cancelados.']);
        }

        $order->update(['status' => 'cancelled']);
        $communication->orderCancelled($order);

        return back()->with('success', 'Pedido cancelado.');
    }

    public function sellerIndex(Request $request, Store $store)
    {
        $this->authorizeStoreOwner($request, $store);
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(Order::STATUSES))],
        ]);
        $status = $validated['status'] ?? '';
        $orders = $store->orders()
            ->with('user')
            ->withCount('items')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('placed_at')
            ->paginate(15)
            ->withQueryString();

        return view('orders.seller-index', compact('store', 'orders', 'status'));
    }

    public function sellerShow(Request $request, Store $store, Order $order)
    {
        $this->authorizeStoreOwner($request, $store);
        abort_unless($order->store_id === $store->id, 404);
        $order->load(['user', 'store', 'items']);

        return view('orders.show', ['order' => $order, 'sellerView' => true]);
    }

    public function updateStatus(
        Request $request,
        Store $store,
        Order $order,
        StockService $stock,
        OrderCommunicationService $communication
    ) {
        $this->authorizeStoreOwner($request, $store);
        abort_unless($order->store_id === $store->id, 404);
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
        ]);

        $allowed = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];
        if (! in_array($validated['status'], $allowed[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'Esta alteração de status não é permitida.']);
        }

        DB::transaction(function () use ($order, $validated, $stock) {
            if ($validated['status'] === 'confirmed') {
                $stock->deductForOrder($order);
            }
            if ($validated['status'] === 'cancelled') {
                $stock->restoreForOrder($order);
            }
            $order->update(['status' => $validated['status']]);
        });
        $communication->statusChanged($order);

        return back()->with('success', 'Status do pedido atualizado.');
    }

    private function authorizeStoreOwner(Request $request, Store $store): void
    {
        abort_unless($store->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
    }

    private function newPublicId(): string
    {
        do {
            $publicId = 'PED-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Order::where('public_id', $publicId)->exists());

        return $publicId;
    }
}
