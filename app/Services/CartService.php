<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\ProductVariation;
use App\Models\Store;
use App\Models\StorePromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    private const SESSION_KEY = 'store_cart';

    public function __construct(private readonly StockService $stock) {}

    public function summary(Request $request): array
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $rawItems = $this->normalizeItems($cart['items'] ?? []);
        if ($rawItems->isEmpty()) {
            return $this->emptySummary();
        }

        $store = Store::query()->publiclyVisible()->find($cart['store_id'] ?? null);
        if (! $store) {
            $this->clear($request);

            return $this->emptySummary();
        }

        $products = Ad::query()
            ->with(['mainImage', 'activeVariations', 'activeAddons'])
            ->whereIn('id', $rawItems->pluck('product_id'))
            ->where('store_id', $store->id)
            ->where('module', 'products')
            ->where('status', 'active')
            ->whereNotNull('price')
            ->get()
            ->keyBy('id');

        $items = $rawItems->map(function (array $raw, string $lineKey) use ($products) {
            $product = $products->get($raw['product_id']);
            if (! $product) {
                return null;
            }

            $variation = $raw['variation_id']
                ? $product->activeVariations->firstWhere('id', $raw['variation_id'])
                : null;
            if ($product->activeVariations->isNotEmpty() && ! $variation) {
                return null;
            }

            $addons = $product->activeAddons->whereIn('id', $raw['addon_ids'])->values();
            $quantity = max($product->minimum_quantity, min(99, $raw['quantity']));
            try {
                $this->stock->assertAvailable($product, $quantity, $variation);
            } catch (ValidationException) {
                return null;
            }

            $unitPrice = $this->unitPrice($product, $variation, $addons);

            return [
                'line_key' => $lineKey,
                'product' => $product,
                'variation' => $variation,
                'addons' => $addons,
                'note' => $raw['note'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * $quantity, 2),
            ];
        })->filter()->values();

        if ($items->isEmpty()) {
            $this->clear($request);

            return $this->emptySummary();
        }

        $couponCode = strtoupper(trim((string) ($cart['coupon_code'] ?? '')));
        $promotion = $couponCode !== ''
            ? $store->promotions()->currentlyActive()->where('coupon_code', $couponCode)->first()
            : null;
        $subtotal = round((float) $items->sum('line_total'), 2);
        $discount = $promotion ? $this->discountFor($promotion, $subtotal) : 0.0;

        $request->session()->put(self::SESSION_KEY, [
            'store_id' => $store->id,
            'items' => $items->mapWithKeys(fn (array $item) => [
                $item['line_key'] => [
                    'product_id' => $item['product']->id,
                    'variation_id' => $item['variation']?->id,
                    'addon_ids' => $item['addons']->pluck('id')->all(),
                    'quantity' => $item['quantity'],
                    'note' => $item['note'],
                ],
            ])->all(),
            'coupon_code' => $promotion?->coupon_code,
        ]);

        return [
            'store' => $store,
            'items' => $items,
            'quantity' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'promotion' => $promotion,
            'coupon_code' => $promotion?->coupon_code,
            'discount' => $discount,
            'total' => max(0, round($subtotal - $discount, 2)),
        ];
    }

    public function add(
        Request $request,
        Ad $product,
        int $quantity,
        ?int $variationId = null,
        array $addonIds = [],
        ?string $note = null
    ): void {
        $product->loadMissing(['store', 'activeVariations', 'activeAddons']);
        $this->ensurePurchasable($product);
        $variation = $variationId ? $product->activeVariations->firstWhere('id', $variationId) : null;
        if ($variationId && ! $variation) {
            throw ValidationException::withMessages(['variation_id' => 'A variação selecionada não pertence a este produto.']);
        }
        if ($product->activeVariations->isNotEmpty() && ! $variation) {
            throw ValidationException::withMessages(['variation_id' => 'Escolha uma opção válida para o produto.']);
        }

        $quantity = max($product->minimum_quantity, min(99, $quantity));
        $this->stock->assertAvailable($product, $quantity, $variation);
        $validAddonIds = $product->activeAddons->whereIn('id', $addonIds)->pluck('id')->sort()->values()->all();
        if (collect($addonIds)->map(fn ($id) => (int) $id)->unique()->count() !== count($validAddonIds)) {
            throw ValidationException::withMessages([
                'addon_ids' => 'Um dos adicionais selecionados não está disponível para este produto.',
            ]);
        }
        $cart = $request->session()->get(self::SESSION_KEY, []);

        if (! empty($cart['store_id']) && (int) $cart['store_id'] !== (int) $product->store_id) {
            throw ValidationException::withMessages([
                'cart' => 'Seu carrinho já contém produtos de outra loja. Finalize ou esvazie o carrinho antes de trocar de loja.',
            ]);
        }

        $lineKey = $this->lineKey($product->id, $variation?->id, $validAddonIds, $note);
        $items = $this->normalizeItems($cart['items'] ?? [])->all();
        $current = (int) ($items[$lineKey]['quantity'] ?? 0);
        $newQuantity = min(99, $current + $quantity);
        $this->stock->assertAvailable($product, $newQuantity, $variation);
        $items[$lineKey] = [
            'product_id' => $product->id,
            'variation_id' => $variation?->id,
            'addon_ids' => $validAddonIds,
            'quantity' => $newQuantity,
            'note' => filled($note) ? trim($note) : null,
        ];

        $request->session()->put(self::SESSION_KEY, [
            'store_id' => $product->store_id,
            'items' => $items,
            'coupon_code' => $cart['coupon_code'] ?? null,
        ]);
    }

    public function update(Request $request, Ad $product, int $quantity, ?string $lineKey = null): void
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $items = $this->normalizeItems($cart['items'] ?? []);
        $lineKey = $lineKey ?: $items->search(fn ($item) => $item['product_id'] === $product->id);
        if (! is_string($lineKey) || ! $items->has($lineKey)) {
            throw ValidationException::withMessages(['cart' => 'Este produto não está no seu carrinho.']);
        }

        $item = $items->get($lineKey);
        $variation = $item['variation_id'] ? ProductVariation::find($item['variation_id']) : null;
        $quantity = max($product->minimum_quantity, min(99, $quantity));
        $this->stock->assertAvailable($product, $quantity, $variation);
        $item['quantity'] = $quantity;
        $items->put($lineKey, $item);
        $cart['items'] = $items->all();
        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public function remove(Request $request, Ad $product, ?string $lineKey = null): void
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        $items = $this->normalizeItems($cart['items'] ?? []);
        $lineKey = $lineKey ?: $items->search(fn ($item) => $item['product_id'] === $product->id);
        if (is_string($lineKey)) {
            $items->forget($lineKey);
        }

        if ($items->isEmpty()) {
            $this->clear($request);
        } else {
            $cart['items'] = $items->all();
            $request->session()->put(self::SESSION_KEY, $cart);
        }
    }

    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    public function applyCoupon(Request $request, string $code): StorePromotion
    {
        $summary = $this->summary($request);
        if (! $summary['store']) {
            throw ValidationException::withMessages(['coupon_code' => 'Adicione um produto antes de aplicar um cupom.']);
        }

        $promotion = $summary['store']->promotions()
            ->currentlyActive()
            ->where('coupon_code', strtoupper(trim($code)))
            ->first();
        if (! $promotion) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Cupom inválido, inativo ou fora do período de uso.',
            ]);
        }

        $cart = $request->session()->get(self::SESSION_KEY, []);
        $cart['coupon_code'] = $promotion->coupon_code;
        $request->session()->put(self::SESSION_KEY, $cart);

        return $promotion;
    }

    public function removeCoupon(Request $request): void
    {
        $cart = $request->session()->get(self::SESSION_KEY, []);
        unset($cart['coupon_code']);
        $request->session()->put(self::SESSION_KEY, $cart);
    }

    public function discountFor(StorePromotion $promotion, float $subtotal): float
    {
        $discount = $promotion->discount_type === 'percentage'
            ? $subtotal * ((float) $promotion->discount_value / 100)
            : (float) $promotion->discount_value;

        return min($subtotal, max(0, round($discount, 2)));
    }

    private function normalizeItems(array $items): Collection
    {
        return collect($items)->mapWithKeys(function ($value, $key) {
            if (is_array($value)) {
                $lineKey = is_string($key) ? $key : $this->lineKey(
                    (int) ($value['product_id'] ?? $key),
                    isset($value['variation_id']) ? (int) $value['variation_id'] : null,
                    $value['addon_ids'] ?? [],
                    $value['note'] ?? null
                );

                return [$lineKey => [
                    'product_id' => (int) ($value['product_id'] ?? $key),
                    'variation_id' => filled($value['variation_id'] ?? null) ? (int) $value['variation_id'] : null,
                    'addon_ids' => array_map('intval', $value['addon_ids'] ?? []),
                    'quantity' => max(1, (int) ($value['quantity'] ?? 1)),
                    'note' => $value['note'] ?? null,
                ]];
            }

            return [$this->lineKey((int) $key, null, [], null) => [
                'product_id' => (int) $key,
                'variation_id' => null,
                'addon_ids' => [],
                'quantity' => max(1, (int) $value),
                'note' => null,
            ]];
        });
    }

    private function lineKey(int $productId, ?int $variationId, array $addonIds, ?string $note): string
    {
        sort($addonIds);

        return 'line_'.sha1(json_encode([$productId, $variationId, $addonIds, trim((string) $note)]));
    }

    private function unitPrice(Ad $product, ?ProductVariation $variation, Collection $addons): float
    {
        $base = $variation?->price !== null
            ? (float) $variation->price
            : $product->effective_price + (float) ($variation?->price_adjustment ?? 0);

        return round($base + (float) $addons->sum('price'), 2);
    }

    private function ensurePurchasable(Ad $product): void
    {
        $valid = $product->module === 'products'
            && $product->status === 'active'
            && $product->store
            && $product->store->active
            && $product->store->isModerationApproved()
            && $product->price !== null
            && $product->effective_price > 0;

        if (! $valid) {
            throw ValidationException::withMessages([
                'cart' => 'Este produto não está disponível para compra pelo carrinho.',
            ]);
        }
    }

    private function emptySummary(): array
    {
        return [
            'store' => null,
            'items' => collect(),
            'quantity' => 0,
            'subtotal' => 0.0,
            'promotion' => null,
            'coupon_code' => null,
            'discount' => 0.0,
            'total' => 0.0,
        ];
    }
}
