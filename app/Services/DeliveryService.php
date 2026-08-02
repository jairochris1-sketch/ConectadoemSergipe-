<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public function options(Store $store): array
    {
        return [
            'pickup' => (bool) $store->pickup_available,
            'delivery' => (bool) $store->delivery_available,
        ];
    }

    public function assertAvailable(
        Store $store,
        string $method,
        ?string $city = null,
        ?string $neighborhood = null
    ): void {
        if (! ($this->options($store)[$method] ?? false)) {
            throw ValidationException::withMessages([
                'fulfillment_method' => 'Esta forma de recebimento não está disponível para a loja.',
            ]);
        }

        $cities = collect($store->delivery_cities)->filter()->map(
            fn ($item) => mb_strtolower(trim($item))
        );
        if ($method === 'delivery' && $cities->isNotEmpty()
            && ! $cities->contains(mb_strtolower(trim((string) $city)))) {
            throw ValidationException::withMessages([
                'delivery_city' => 'A loja não realiza entregas nesta cidade.',
            ]);
        }

        $neighborhoods = collect($store->delivery_neighborhoods)->filter()->map(
            fn ($item) => mb_strtolower(trim($item))
        );
        if ($method === 'delivery' && $neighborhoods->isNotEmpty()
            && ! $neighborhoods->contains(mb_strtolower(trim((string) $neighborhood)))) {
            throw ValidationException::withMessages([
                'delivery_neighborhood' => 'A loja não realiza entregas neste bairro.',
            ]);
        }
    }

    public function fee(Store $store, string $method, float $subtotal, ?string $region = null): float
    {
        if ($method !== 'delivery') {
            return 0.0;
        }
        if ($store->free_delivery_threshold !== null
            && $subtotal >= (float) $store->free_delivery_threshold) {
            return 0.0;
        }

        $regionalFee = collect($store->delivery_region_fees)->first(
            fn ($item) => mb_strtolower(trim((string) ($item['region'] ?? '')))
                === mb_strtolower(trim((string) $region))
        );

        return round((float) ($regionalFee['fee'] ?? $store->delivery_fee), 2);
    }
}
