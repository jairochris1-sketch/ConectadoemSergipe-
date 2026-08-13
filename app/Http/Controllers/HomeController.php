<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Store;
use App\Support\HomeCityGroupCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
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
                $this->applySmartSearch($query, $q);
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
        $serviceProvidersQuery = Ad::with(['user', 'mainImage', 'category'])
            ->where('status', 'active')
            ->where('module', 'services')
            ->when($city, fn ($query) => $query->where('city', $city));
        $featuredProviderUserIds = $this->featuredProviderUserIds();
        $featuredProviders = (clone $serviceProvidersQuery)
            ->whereIn('user_id', $featuredProviderUserIds)
            ->get()
            ->sortBy(fn (Ad $provider): string => hash(
                'sha256',
                now('America/Fortaleza')->toDateString().'|'.$provider->id
            ))
            ->take(8)
            ->values()
            ->each(fn (Ad $provider) => $provider->setAttribute('is_plan_featured', true));
        $serviceProviders = $featuredProviders;

        if ($serviceProviders->count() < 8) {
            $fallbackProviders = (clone $serviceProvidersQuery)
                ->whereNotIn('user_id', $featuredProviderUserIds)
                ->latest()
                ->take(8 - $serviceProviders->count())
                ->get()
                ->each(fn (Ad $provider) => $provider->setAttribute('is_plan_featured', false));
            $serviceProviders = $serviceProviders->concat($fallbackProviders)->values();
        }
        $paidProviderHighlights = (clone $serviceProvidersQuery)
            ->whereIn('user_id', $featuredProviderUserIds)
            ->orderByDesc('views')
            ->latest()
            ->take(3)
            ->get()
            ->each(fn (Ad $provider) => $provider->setAttribute('is_plan_featured', true));
        $providerHighlights = $paidProviderHighlights;

        if ($providerHighlights->count() < 3) {
            $popularFreeProviders = (clone $serviceProvidersQuery)
                ->whereNotIn('user_id', $featuredProviderUserIds)
                ->orderByDesc('views')
                ->latest()
                ->take(3 - $providerHighlights->count())
                ->get()
                ->each(fn (Ad $provider) => $provider->setAttribute('is_plan_featured', false));
            $providerHighlights = $providerHighlights->concat($popularFreeProviders)->values();
        }

        $generalHighlights = (clone $popularAdsQuery)
            ->orderByDesc('views')
            ->latest()
            ->take(10)
            ->get();
        $featuredForYou = $this->interleaveHomeHighlights($generalHighlights, $providerHighlights);
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
        $serviceSearchCategories = $this->serviceSearchCategories();
        $adSearchCategories = self::AD_SEARCH_CATEGORIES;
        $cityFiles = glob(public_path('Cidades/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}'), GLOB_BRACE) ?: [];
        $defaultHeroBanners = array_map(function ($filePath) {
            return 'Cidades/' . basename($filePath);
        }, $cityFiles);

        if (empty($defaultHeroBanners)) {
            $defaultHeroBanners = [
                'https://images.unsplash.com/photo-1449844908441-8829872d2607?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1519003722824-194d4455a60c?q=80&w=1600&auto=format&fit=crop',
            ];
        }

        $customHeroBanners = collect(range(1, 10))
            ->map(fn (int $slot) => Setting::get("home_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        $heroBanners = collect($customHeroBanners)
            ->merge($defaultHeroBanners)
            ->unique()
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

        $productsBanners = collect(range(1, 5))
            ->map(fn (int $slot) => Setting::get("products_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        if (empty($productsBanners)) {
            $productsBanners = $heroBanners;
        }

        $jobsBanners = collect(range(1, 5))
            ->map(fn (int $slot) => Setting::get("jobs_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        if (empty($jobsBanners)) {
            $jobsBanners = [
                'https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1600&auto=format&fit=crop',
            ];
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
            ->take(10)
            ->get();

        $vehicleAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', 'vehicles')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(10)
            ->get();

        $productAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->where('module', 'products')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(10)
            ->get();

        $jobAgroAds = Ad::with(['mainImage'])
            ->where('status', 'active')
            ->whereIn('module', ['jobs', 'agro'])
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->take(10)
            ->get();

        $homeCityGroups = collect(HomeCityGroupCatalog::all())
            ->map(function (array $group): array {
                $city = $group['city'];
                $slot = $group['slot'];

                return [
                    'slot' => $slot,
                    'city' => $city,
                    'gentilic' => $group['gentilic'],
                    'cover' => Setting::get("home_city_group_cover_{$slot}", $group['cover']),
                    'link' => Setting::get("home_city_group_link_{$slot}") ?: route('home', ['city' => $city]),
                    'enabled' => Setting::get(
                        "home_city_group_enabled_{$slot}",
                        $group['default_enabled'] ? '1' : '0'
                    ) === '1',
                ];
            })
            ->values();

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
            'featuredForYou',
            'serviceProviders',
            'recentStores',
            'serviceSearchCategories',
            'adSearchCategories',
            'heroBanners',
            'realEstateBanners',
            'vehiclesBanners',
            'productsBanners',
            'jobsBanners',
            'realEstateAds',
            'vehicleAds',
            'productAds',
            'jobAgroAds',
            'homeCityGroups'
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
            ], $this->serviceSearchCategories()),
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
        $serviceCategories = collect($this->serviceSearchCategories())
            ->pluck('name')
            ->all();

        $providers = Ad::with(['user', 'mainImage', 'category'])
            ->withCount([
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->withAvg([
                'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
            ], 'rating')
            ->where('status', 'active')
            ->where('module', 'services')
            ->when($category, fn ($query) => $this->applySmartSearch($query, $category))
            ->when($q, fn ($query) => $this->applySmartSearch($query, $q))
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
            ->paginate(21)
            ->withQueryString();

        $profileCta = $this->professionalProfileCta($request);

        $cityBanners = array_map(
            fn ($file) => 'Cidades/' . basename($file),
            glob(public_path('Cidades/*.{webp,jpg,jpeg,png}'), GLOB_BRACE) ?: []
        );

        if (empty($cityBanners)) {
            $cityBanners = [
                'Cidades/Aracaju.webp',
                'Cidades/Canindé de São Francisco.webp',
                'Cidades/Itabaiana.webp',
                'Cidades/Monte Alegre.webp',
                'Cidades/Nossa Senhora da Glória.webp',
                'Cidades/Nossa Senhora das  Dores.webp',
                'Cidades/Tobias Barreto.webp',
            ];
        }

        $serviceBanners = collect(range(1, 10))
            ->map(fn (int $slot) => Setting::get("services_banner_{$slot}"))
            ->filter()
            ->values()
            ->all();

        if (empty($serviceBanners)) {
            $serviceBanners = $cityBanners;
        }

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

    private function serviceSearchCategories(): array
    {
        $configuredCategories = collect(config('marketplace.service_categories', []))
            ->map(fn (string $name): array => [
                'name' => $name,
                'icon' => $this->serviceCategoryIcon($name),
            ]);

        $databaseCategories = Category::query()
            ->select(['categories.name', 'categories.icon'])
            ->where('categories.active', true)
            ->where(function ($query) {
                $query->where('categories.module', 'services')
                    ->orWhereNull('categories.module');
            })
            ->whereNotNull('categories.name')
            ->where('categories.name', '!=', '')
            ->distinct()
            ->orderBy('categories.name')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'icon' => $category->icon ?: 'fa-tag',
            ]);

        return $configuredCategories
            ->concat($databaseCategories)
            ->filter(fn (array $category): bool => filled($category['name'] ?? null))
            ->unique(fn (array $category): string => mb_strtolower($category['name']))
            ->sort(fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']))
            ->values()
            ->all();
    }

    private function serviceCategoryIcon(string $name): string
    {
        return match ($name) {
            'Eletricista' => 'fa-bolt',
            'Encanador' => 'fa-faucet-drip',
            'Pintor' => 'fa-paint-roller',
            'Mecânico' => 'fa-screwdriver-wrench',
            'Advogado' => 'fa-scale-balanced',
            'Faxineira', 'Faxineira / Diarista', 'Diarista' => 'fa-broom',
            'Marceneiro', 'Marcenaria', 'Montador de Móveis', 'Móveis Planejados' => 'fa-hammer',
            'Programador', 'Técnico de Informática', 'TI / Informática' => 'fa-computer',
            'Carro de Mudança', 'Frete e Mudanças' => 'fa-truck-moving',
            'Pizzaria', 'Restaurante', 'Restaurante / Pizzaria' => 'fa-utensils',
            'Pedreiro', 'Pedreiro de Acabamento' => 'fa-trowel-bricks',
            'Jardineiro' => 'fa-seedling',
            default => 'fa-tag',
        };
    }

    private function featuredProviderUserIds(): Collection
    {
        $featureId = DB::table('plan_features')
            ->where('key', 'provider_featured')
            ->value('id');

        if (! $featureId) {
            return collect();
        }

        $eligiblePlanSlugs = DB::table('plans')
            ->join('plan_feature_values', 'plan_feature_values.plan_id', '=', 'plans.id')
            ->where('plan_feature_values.plan_feature_id', $featureId)
            ->where(function ($query) {
                $query->where('plan_feature_values.value', '1')
                    ->orWhereNull('plan_feature_values.value');
            })
            ->pluck('plans.slug');
        $disabledOverrideUserIds = DB::table('user_feature_overrides')
            ->where('plan_feature_id', $featureId)
            ->where('value', '0')
            ->pluck('user_id');
        $enabledOverrideUserIds = DB::table('user_feature_overrides')
            ->where('plan_feature_id', $featureId)
            ->where(function ($query) {
                $query->where('value', '1')->orWhereNull('value');
            })
            ->pluck('user_id');
        $planUserIds = DB::table('users')
            ->whereIn('subscription_plan', $eligiblePlanSlugs)
            ->whereNotIn('id', $disabledOverrideUserIds)
            ->pluck('id');

        return $planUserIds
            ->concat($enabledOverrideUserIds)
            ->unique()
            ->values();
    }

    private function interleaveHomeHighlights(Collection $generalAds, Collection $providers): Collection
    {
        $highlights = collect();
        $generalIndex = 0;
        $providerIndex = 0;
        $providerSlots = [1, 4, 7];

        for ($position = 0; $position < 10; $position++) {
            if (in_array($position, $providerSlots, true) && $providers->has($providerIndex)) {
                $highlights->push($providers->get($providerIndex++));
            } elseif ($generalAds->has($generalIndex)) {
                $highlights->push($generalAds->get($generalIndex++));
            } elseif ($providers->has($providerIndex)) {
                $highlights->push($providers->get($providerIndex++));
            }
        }

        return $highlights->values();
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

    public function featuredPage(Request $request)
    {
        $q = trim((string) ($request->input('q') ?: $request->input('search')));
        $type = $request->input('type', 'all');
        $category = $request->input('category');
        $locationPreferenceActive = (bool) $request->session()->get('location_filter.enabled', false);
        $city = $request->input('city') ?: ($locationPreferenceActive ? $request->session()->get('location_filter.city') : null);

        $featuredProviderUserIds = $this->featuredProviderUserIds();

        // 1. Base Query Prestadores
        $providersBaseQuery = Ad::with(['user', 'mainImage', 'category'])
            ->where('status', 'active')
            ->where('module', 'services')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->when($category, fn ($query) => $query->whereHas('category', fn ($c) => $c->where('name', $category)))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"));
                });
            });

        $totalProvidersCount = (clone $providersBaseQuery)->count();

        // 2. Base Query Lojas
        $storesBaseQuery = Store::query()
            ->with(['user'])
            ->withCount([
                'ads as active_ads_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
            ])
            ->publiclyVisible()
            ->when($city, fn ($query) => $query->where(function ($cq) use ($city) {
                $cq->where('city', $city)
                    ->orWhereHas('user', fn ($uq) => $uq->where('city', $city));
            }))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%");
                });
            });

        $totalStoresCount = (clone $storesBaseQuery)->count();

        // 3. Base Query Produtos das Lojas
        $adsBaseQuery = Ad::with(['user', 'mainImage', 'category', 'store'])
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where('module', 'products')
                    ->orWhereNotNull('store_id');
            })
            ->when($city, fn ($query) => $query->where('city', $city))
            ->when($category, fn ($query) => $query->whereHas('category', fn ($c) => $c->where('name', $category)))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"));
                });
            });

        $totalAdsCount = (clone $adsBaseQuery)->count();

        $providers = null;
        $stores = null;
        $ads = null;
        $perPage = 100;

        $featuredIdsArray = is_array($featuredProviderUserIds) ? $featuredProviderUserIds : $featuredProviderUserIds->toArray();
        $featuredIdsSql = ! empty($featuredIdsArray) ? implode(',', array_map('intval', $featuredIdsArray)) : '0';

        if ($type === 'services') {
            $providers = (clone $providersBaseQuery)
                ->orderByRaw("CASE WHEN user_id IN ({$featuredIdsSql}) THEN 0 ELSE 1 END")
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
            $providers->getCollection()->each(fn (Ad $p) => $p->setAttribute('is_plan_featured', in_array($p->user_id, $featuredIdsArray)));
        } elseif ($type === 'stores') {
            $stores = (clone $storesBaseQuery)
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
        } elseif ($type === 'ads') {
            $ads = (clone $adsBaseQuery)
                ->orderByDesc('views')
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $type = 'all';

            $providers = (clone $providersBaseQuery)
                ->orderByRaw("CASE WHEN user_id IN ({$featuredIdsSql}) THEN 0 ELSE 1 END")
                ->latest()
                ->paginate(100, ['*'], 'providers_page')
                ->withQueryString();
            $providers->getCollection()->each(fn (Ad $p) => $p->setAttribute('is_plan_featured', in_array($p->user_id, $featuredIdsArray)));

            $stores = (clone $storesBaseQuery)
                ->latest()
                ->paginate(100, ['*'], 'stores_page')
                ->withQueryString();

            $ads = (clone $adsBaseQuery)
                ->orderByDesc('views')
                ->latest()
                ->paginate(100, ['*'], 'ads_page')
                ->withQueryString();
        }

        $categories = Category::orderBy('name')->get();
        $cities = Ad::whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('pages.featured', compact(
            'q',
            'type',
            'category',
            'city',
            'providers',
            'stores',
            'ads',
            'totalProvidersCount',
            'totalStoresCount',
            'totalAdsCount',
            'categories',
            'cities'
        ));
    }

    private function applySmartSearch($query, ?string $search)
    {
        $raw = trim((string) $search);
        if ($raw === '') {
            return $query;
        }

        $ascii = \Illuminate\Support\Str::ascii($raw);
        $words = array_values(array_filter(explode(' ', $raw), fn ($w) => mb_strlen(trim($w)) > 0));

        $synonymMap = [
            'lab' => ['lab', 'laboratorio', 'laboratório'],
            'laboratorio' => ['lab', 'laboratorio', 'laboratório'],
            'laboratório' => ['lab', 'laboratorio', 'laboratório'],
            'protese' => ['protese', 'prótese', 'protético', 'protetico'],
            'prótese' => ['protese', 'prótese', 'protético', 'protetico'],
            'dentaria' => ['dentaria', 'dentária', 'dente', 'dentista', 'odontologia', 'odonto'],
            'dentária' => ['dentaria', 'dentária', 'dente', 'dentista', 'odontologia', 'odonto'],
            'dentista' => ['dentista', 'dentaria', 'dentária', 'odontologia', 'odonto'],
            'mecanico' => ['mecanico', 'mecânico', 'oficina', 'auto'],
            'mecânico' => ['mecanico', 'mecânico', 'oficina', 'auto'],
            'eletricista' => ['eletricista', 'eletrica', 'elétrica'],
            'encanador' => ['encanador', 'hidraulica', 'hidráulica'],
            'pedreiro' => ['pedreiro', 'obra', 'construcao', 'construção'],
            'pintor' => ['pintor', 'pintura'],
            'faxineira' => ['faxineira', 'diarista', 'limpeza'],
            'advogado' => ['advogado', 'advocacia', 'juridico', 'jurídico'],
            'frete' => ['frete', 'mudança', 'mudanca', 'transporte'],
            'ti' => ['ti', 'tecnico', 'técnico', 'informatica', 'informática', 'programador', 'computador'],
        ];

        return $query->where(function ($groupQuery) use ($raw, $ascii, $words, $synonymMap) {
            // 1. Exact or normalized phrase match
            $groupQuery->where('title', 'like', "%{$raw}%")
                ->orWhere('title', 'like', "%{$ascii}%")
                ->orWhere('description', 'like', "%{$raw}%")
                ->orWhere('description', 'like', "%{$ascii}%")
                ->orWhere('advertiser_type', 'like', "%{$raw}%")
                ->orWhere('advertiser_type', 'like', "%{$ascii}%")
                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$raw}%")->orWhere('name', 'like', "%{$ascii}%"))
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$raw}%")->orWhere('name', 'like', "%{$ascii}%"));

            // 2. Multi-word search (Each word must match somewhere in title/desc/category/user)
            if (count($words) > 1) {
                $groupQuery->orWhere(function ($multiWordQuery) use ($words, $synonymMap) {
                    foreach ($words as $word) {
                        $wTrimmed = trim($word);
                        if (mb_strlen($wTrimmed) < 2) continue;

                        $wKey = mb_strtolower(\Illuminate\Support\Str::ascii($wTrimmed));
                        $variants = array_unique(array_merge(
                            [$wTrimmed, \Illuminate\Support\Str::ascii($wTrimmed)],
                            $synonymMap[$wKey] ?? []
                        ));

                        $multiWordQuery->where(function ($wordSub) use ($variants) {
                            foreach ($variants as $variant) {
                                $wordSub->orWhere('title', 'like', "%{$variant}%")
                                    ->orWhere('description', 'like', "%{$variant}%")
                                    ->orWhere('advertiser_type', 'like', "%{$variant}%")
                                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$variant}%"))
                                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$variant}%"));
                            }
                        });
                    }
                });
            }

            // 3. Single word synonym expansions
            foreach ($words as $word) {
                $wKey = mb_strtolower(\Illuminate\Support\Str::ascii(trim($word)));
                if (isset($synonymMap[$wKey])) {
                    foreach ($synonymMap[$wKey] as $variant) {
                        $groupQuery->orWhere('title', 'like', "%{$variant}%")
                            ->orWhere('description', 'like', "%{$variant}%")
                            ->orWhere('advertiser_type', 'like', "%{$variant}%")
                            ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$variant}%"))
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$variant}%"));
                    }
                }
            }
        });
    }
}
