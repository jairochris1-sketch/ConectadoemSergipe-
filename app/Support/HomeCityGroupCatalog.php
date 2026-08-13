<?php

namespace App\Support;

use App\Core\SergipeCities;

class HomeCityGroupCatalog
{
    public static function all(): array
    {
        $activeDefaults = collect(config('marketplace.home_city_groups', []))
            ->keyBy('city');
        $orderedCities = array_values(array_unique(array_merge(
            $activeDefaults->keys()->all(),
            SergipeCities::getAll()
        )));

        return collect($orderedCities)
            ->values()
            ->map(function (string $city, int $index) use ($activeDefaults): array {
                $default = $activeDefaults->get($city);

                return [
                    'slot' => $index + 1,
                    'city' => $city,
                    'gentilic' => $default['gentilic'] ?? "morador(a) de {$city}",
                    'cover' => $default['cover'] ?? 'images/1mapa-sergipe-conectado.png',
                    'default_enabled' => $default !== null,
                ];
            })
            ->all();
    }
}
