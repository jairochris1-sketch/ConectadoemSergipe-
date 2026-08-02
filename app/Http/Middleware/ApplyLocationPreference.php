<?php

namespace App\Http\Middleware;

use App\Core\SergipeCities;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyLocationPreference
{
    private const FILTERED_ROUTES = [
        'home',
        'search.suggestions',
        'module.services',
        'module.products',
        'module.real_estate',
        'module.vehicles',
        'module.jobs',
        'module.agro',
        'stores.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || ! $request->routeIs(...self::FILTERED_ROUTES)) {
            return $next($request);
        }

        $preference = $request->session()->get('location_filter', []);
        $activeCity = ($preference['enabled'] ?? false) ? trim((string) ($preference['city'] ?? '')) : '';

        if ($activeCity && ! in_array($activeCity, SergipeCities::getAll(), true)) {
            $request->session()->forget('location_filter');
            $activeCity = '';
        }

        if ($request->query->has('city')) {
            $requestedCity = trim((string) $request->query('city'));
            if ($activeCity && $requestedCity !== $activeCity) {
                $request->session()->forget('location_filter');
            }
        } elseif ($activeCity) {
            $request->merge(['city' => $activeCity]);
        }

        return $next($request);
    }
}
