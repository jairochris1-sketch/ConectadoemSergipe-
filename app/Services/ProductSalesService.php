<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\OrderItem;

class ProductSalesService
{
    public function confirmedQuantity(Ad $product): int
    {
        return (int) OrderItem::query()
            ->where('ad_id', $product->id)
            ->whereHas('order', fn ($query) => $query->whereIn('status', [
                'confirmed',
                'preparing',
                'ready',
                'completed',
            ]))
            ->sum('quantity');
    }
}
