<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Order;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function available(Ad $product, ?ProductVariation $variation = null): ?int
    {
        $stockable = $variation ?: $product;

        return $stockable->track_stock ? (int) $stockable->stock_quantity : null;
    }

    public function assertAvailable(Ad $product, int $quantity, ?ProductVariation $variation = null): void
    {
        $stockable = $variation ?: $product;
        if (! $stockable->track_stock || $product->allow_backorders) {
            return;
        }

        if ((int) $stockable->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'cart' => (int) $stockable->stock_quantity === 0
                    ? 'Este produto está esgotado.'
                    : "Há somente {$stockable->stock_quantity} unidade(s) disponível(is).",
            ]);
        }
    }

    public function deductForOrder(Order $order): void
    {
        if ($order->stock_deducted_at) {
            return;
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->stock_deducted_at) {
                return;
            }

            $lockedOrder->load('items');
            foreach ($lockedOrder->items as $item) {
                if (! $item->ad_id) {
                    continue;
                }
                $product = Ad::query()->lockForUpdate()->find($item->ad_id);
                if (! $product) {
                    throw ValidationException::withMessages(['stock' => 'Um produto do pedido não existe mais.']);
                }

                $variation = $item->product_variation_id
                    ? ProductVariation::query()->lockForUpdate()->find($item->product_variation_id)
                    : null;
                $this->assertAvailable($product, $item->quantity, $variation);
                $stockable = $variation ?: $product;
                if ($stockable->track_stock) {
                    $stockable->decrement('stock_quantity', $item->quantity);
                }
            }

            $lockedOrder->update([
                'stock_deducted_at' => now(),
                'stock_restored_at' => null,
            ]);
        });
    }

    public function restoreForOrder(Order $order): void
    {
        if (! $order->stock_deducted_at || $order->stock_restored_at) {
            return;
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! $lockedOrder->stock_deducted_at || $lockedOrder->stock_restored_at) {
                return;
            }

            $lockedOrder->load('items');
            foreach ($lockedOrder->items as $item) {
                $stockable = $item->product_variation_id
                    ? ProductVariation::query()->lockForUpdate()->find($item->product_variation_id)
                    : Ad::query()->lockForUpdate()->find($item->ad_id);
                if ($stockable?->track_stock) {
                    $stockable->increment('stock_quantity', $item->quantity);
                }
            }

            $lockedOrder->update(['stock_restored_at' => now()]);
        });
    }
}
