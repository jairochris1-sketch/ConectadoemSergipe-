<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Str;

class ProductDisplayService
{
    public const STORE_MODES = ['catalog', 'individual'];

    public const PRODUCT_MODES = ['default', 'catalog', 'individual'];

    private const CATALOG_CATEGORY_TERMS = [
        'alimentacao',
        'confeitaria',
        'pizzaria',
        'hamburgueria',
        'restaurante',
        'lanchonete',
        'marmitaria',
        'padaria',
        'acaiteria',
        'cafeteria',
    ];

    public function suggestForCategory(?string $category): string
    {
        $normalized = Str::lower(Str::ascii((string) $category));

        return collect(self::CATALOG_CATEGORY_TERMS)
            ->contains(fn ($term) => str_contains($normalized, $term))
                ? 'catalog'
                : 'individual';
    }

    public function effectiveFor(Ad $product): string
    {
        if (in_array($product->display_mode, ['catalog', 'individual'], true)) {
            return $product->display_mode;
        }

        $product->loadMissing('store');

        return in_array($product->store?->product_display_mode, self::STORE_MODES, true)
            ? $product->store->product_display_mode
            : 'individual';
    }
}
