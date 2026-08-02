<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private const SERVICE_SEARCH_CATEGORIES = [
        ['name' => 'Eletricista', 'icon' => 'fa-bolt'],
        ['name' => 'Encanador', 'icon' => 'fa-faucet-drip'],
        ['name' => 'Pintor', 'icon' => 'fa-paint-roller'],
        ['name' => 'Mecânico', 'icon' => 'fa-screwdriver-wrench'],
        ['name' => 'Advogado', 'icon' => 'fa-scale-balanced'],
        ['name' => 'Faxineira / Diarista', 'icon' => 'fa-broom'],
        ['name' => 'Marcenaria', 'icon' => 'fa-hammer'],
        ['name' => 'TI / Informática', 'icon' => 'fa-computer'],
        ['name' => 'Frete e Mudanças', 'icon' => 'fa-truck-moving'],
        ['name' => 'Restaurante / Pizzaria', 'icon' => 'fa-utensils'],
        ['name' => 'Pedreiro', 'icon' => 'fa-trowel-bricks'],
        ['name' => 'Jardineiro', 'icon' => 'fa-seedling'],
    ];

    private const AD_SEARCH_CATEGORIES = [
        ['name' => 'Imóveis', 'module' => 'real_estate', 'icon' => 'fa-house'],
        ['name' => 'Produtos', 'module' => 'products', 'icon' => 'fa-bag-shopping'],
        ['name' => 'Empregos', 'module' => 'jobs', 'icon' => 'fa-briefcase'],
        ['name' => 'Agro', 'module' => 'agro', 'icon' => 'fa-tractor'],
    ];

    private const MODULE_LABELS = [
        'services' => 'Serviços',
        'products' => 'Produtos',
        'real_estate' => 'Imóveis',
        'vehicles' => 'Veículos',
        'jobs' => 'Empregos',
        'agro' => 'Agro',
    ];

    public function index(Request $request)
    {
        try {
            \App\Services\DemoAdSeeder::seedIfNeeded();
        } catch (\Throwable $e) {
            // Silence seeder exception
        }

        $q = $request->input('q');
        $module = $request->input('module');
        $type = $request->input('type');
        $intent = $request->input('intent');
        $brand = $request->input('brand');
        $year = $request->input('year');
        $moduleTitle = self::MODULE_LABELS[$module] ?? null;
        $locationPreferenceActive = (bool) $request->session()->get('location_filter.enabled', false);
        $city = $request->input('city') ?: ($locationPreferenceActive ? $request->session()->get('location_filter.city') : null);

        if ($module === 'services') {
            return $this->serviceDirectory($request);
        }

        $isSearch = ! empty($q)
            || ! empty($module)
            || ! empty($type)
            || ! empty($intent)
            || ! empty($brand)
            || ! empty($year)
            || (! empty($city) && ! $locationPreferenceActive);
        $searchResults = [];

        if ($isSearch) {
            $query = Ad::with(['mainImage'])->where('status', 'active');

            if (! empty($q)) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('advertiser_type', 'like', "%{$q}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$q}%"));
                });
            }

            if (! empty($type)) {
                $query->where(function ($sub) use ($type) {
                    $sub->where('title', 'like', "%{$type}%")
                        ->orWhere('description', 'like', "%{$type}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$type}%"));
                });
            }

            if (! empty($intent)) {
                $query->where(function ($sub) use ($intent) {
                    $sub->where('title', 'like', "%{$intent}%")
                        ->orWhere('description', 'like', "%{$intent}%");
                });
            }

            if (! empty($brand)) {
                $query->where(function ($sub) use ($brand) {
                    $sub->where('title', 'like', "%{$brand}%")
                        ->orWhere('description', 'like', "%{$brand}%");
                });
            }

            if (! empty($year)) {
                $query->where(function ($sub) use ($year) {
                    $sub->where('title', 'like', "%{$year}%")
                        ->orWhere('description', 'like', "%{$year}%");
                });
            }

            if (! empty($city)) {
                $query->where('city', $city);
            }

            if (! empty($module)) {
                $query->where('module', $module);
            }

            $searchResults = $query->orderBy('created_at', 'desc')->get();
        }

        $recentAdsQuery = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', '!=', 'services')
            ->when($city, fn ($query) => $query->where('city', $city));
        $popularAdsQuery = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', '!=', 'services')
            ->when($city, fn ($query) => $query->where('city', $city));

        if (! empty($module)) {
            $recentAdsQuery->where('module', $module);
            $popularAdsQuery->where('module', $module);
        }

        $recentAds = $recentAdsQuery->orderBy('created_at', 'desc')->take(10)->get();
        $popularAds = $popularAdsQuery->orderBy('views', 'desc')->take(10)->get();
        $serviceProviders = Ad::with(['user', 'mainImage', 'category'])
            ->where('status', 'active')
            ->where('module', 'services')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(8)
            ->get();
        $recentStores = Store::query()
            ->with(['user'])
            ->withCount([
                'ads as active_ads_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
            ])
            ->publiclyVisible()
            ->when($city, fn ($query) => $query->where(function ($cityQuery) use ($city) {
                $cityQuery
                    ->where('city', $city)
                    ->orWhere(function ($fallbackQuery) use ($city) {
                        $fallbackQuery
                            ->whereNull('city')
                            ->whereHas('user', fn ($userQuery) => $userQuery->where('city', $city));
                    });
            }))
            ->latest()
            ->take(4)
            ->get();
        $serviceSearchCategories = self::SERVICE_SEARCH_CATEGORIES;
        $adSearchCategories = self::AD_SEARCH_CATEGORIES;
        $defaultHeroBanners = [
            1 => 'https://images.unsplash.com/photo-1449844908441-8829872d2607?q=80&w=1600&auto=format&fit=crop',
            2 => 'https://images.unsplash.com/photo-1519003722824-194d4455a60c?q=80&w=1600&auto=format&fit=crop',
        ];
        $heroBanners = collect(range(1, 6))
            ->map(fn (int $slot) => Setting::get("home_banner_{$slot}", $defaultHeroBanners[$slot] ?? null))
            ->filter()
            ->values()
            ->all();

        $realEstateBanners = collect(range(1, 5))
            ->map(fn (int $slot) => Setting::get("real_estate_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        if (empty($realEstateBanners)) {
            $realEstateBanners = $heroBanners;
        }

        $vehiclesBanners = collect(range(1, 5))
            ->map(fn (int $slot) => Setting::get("vehicles_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        if (empty($vehiclesBanners)) {
            $vehiclesBanners = $heroBanners;
        }

        $hasActiveFilters = ! empty($q)
            || ! empty($type)
            || ! empty($intent)
            || ! empty($brand)
            || ! empty($year)
            || (! empty($city) && ! $locationPreferenceActive);

        $realEstateAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', 'real_estate')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(4)
            ->get();

        $vehicleAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', 'vehicles')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(4)
            ->get();

        $productAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', 'products')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(4)
            ->get();

        $jobAgroAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->whereIn('module', ['jobs', 'agro'])
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact(
            'q',
            'city',
            'module',
            'moduleTitle',
            'isSearch',
            'hasActiveFilters',
            'searchResults',
            'recentAds',
            'popularAds',
            'serviceProviders',
            'recentStores',
            'serviceSearchCategories',
            'adSearchCategories',
            'heroBanners',
            'realEstateBanners',
            'vehiclesBanners',
            'realEstateAds',
            'vehicleAds',
            'productAds',
            'jobAgroAds'
        ));
    }

    public function suggestions(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));

        $categorySuggestions = collect([
            ['label' => 'Serviços', 'meta' => 'Categoria', 'url' => route('module.services')],
            ...array_map(fn (array $category) => [
                'label' => $category['name'],
                'meta' => 'Categoria de anúncio',
                'url' => route('home', ['module' => $category['module']]),
            ], self::AD_SEARCH_CATEGORIES),
            ...array_map(fn (array $category) => [
                'label' => $category['name'],
                'meta' => 'Serviço profissional',
                'url' => route('module.services', ['category' => $category['name']]),
            ], self::SERVICE_SEARCH_CATEGORIES),
        ])->when(
            $term !== '',
            fn ($items) => $items->filter(
                fn (array $item) => str_contains(
                    mb_strtolower($item['label']),
                    mb_strtolower($term)
                )
            ),
            fn ($items) => $items->take(5)
        );

        $adsQuery = Ad::query()
            ->select(['id', 'title', 'module', 'slug', 'city'])
            ->where('status', 'active')
            ->when(
                $request->input('city'),
                fn ($query, $city) => $query->where('city', $city)
            );

        if ($term !== '') {
            $adsQuery
                ->where(function ($query) use ($term) {
                    $query->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                })
                ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$term}%"]);
        } else {
            $adsQuery->orderByDesc('views');
        }

        $adSuggestions = $adsQuery
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (Ad $ad) => [
                'label' => $ad->title,
                'meta' => trim(($ad->city ?: 'Sergipe').' · '.(self::MODULE_LABELS[$ad->module] ?? 'Anúncio')),
                'url' => $ad->module === 'services'
                    ? route('provider.show', $ad->slug)
                    : route('ad.show', $ad->slug),
            ]);

        return response()->json([
            'suggestions' => $categorySuggestions
                ->concat($adSuggestions)
                ->unique('url')
                ->take(10)
                ->values(),
        ]);
    }

    public function servicesModule(Request $request)
    {
        return $this->serviceDirectory($request);
    }

    public function productsModule(Request $request)
    {
        return $this->module($request, 'products');
    }

    public function realEstateModule(Request $request)
    {
        return $this->module($request, 'real_estate');
    }

    public function vehiclesModule(Request $request)
    {
        return $this->module($request, 'vehicles');
    }

    public function jobsModule(Request $request)
    {
        return $this->module($request, 'jobs');
    }

    public function agroModule(Request $request)
    {
        return $this->module($request, 'agro');
    }

    private function module(Request $request, string $module)
    {
        $request->merge(['module' => $module]);

        return $this->index($request);
    }

    private function serviceDirectory(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $city = $request->input('city');
        $category = trim((string) $request->input('category'));
        $serviceCategories = [
            'Eletricista',
            'Encanador',
            'Pintor',
            'Mecânico',
            'Advogado',
            'Faxineira / Diarista',
            'Marcenaria',
            'TI / Informática',
            'Frete e Mudanças',
            'Restaurante / Pizzaria',
            'Pedreiro',
            'Jardineiro',
        ];

        $providers = Ad::with(['user', 'mainImage', 'category'])
            ->withCount([
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->withAvg([
                'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
            ], 'rating')
            ->where('status', 'active')
            ->where('module', 'services')
            ->when($category, function ($query) use ($category) {
                $query->where(function ($subQuery) use ($category) {
                    $subQuery->where('title', 'like', "%{$category}%")
                        ->orWhere('description', 'like', "%{$category}%")
                        ->orWhere('advertiser_type', 'like', "%{$category}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$category}%"));
                });
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('advertiser_type', 'like', "%{$q}%");
                });
            })
            ->when($city, fn ($query) => $query->where('city', $city))
            ->when($request->boolean('available'), function ($query) {
                $query->whereHas('user', function ($uQuery) {
                    $uQuery->where('is_available', true)
                        ->where(function ($sub) {
                            $sub->where('last_seen_at', '>=', now()->subMinutes(30))
                                ->orWhereNull('last_seen_at');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $profileCta = $this->professionalProfileCta($request);
        $serviceBanners = collect(range(1, 6))
            ->map(fn (int $slot) => Setting::get("services_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        return view('services.index', compact(
            'providers',
            'q',
            'city',
            'category',
            'serviceCategories',
            'serviceBanners',
            'profileCta'
        ));
    }

    private function professionalProfileCta(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return ['state' => 'create'];
        }

        $profile = $user->professionalProfiles()->latest()->first();

        if (! $profile) {
            return ['state' => 'create'];
        }

        if ($user->isFreePlan()) {
            return [
                'state' => 'manage',
                'profile' => $profile,
            ];
        }

        if ($user->canCreateAnotherProfessionalProfile()) {
            return ['state' => 'create_another'];
        }

        return ['state' => 'limit'];
    }
}
