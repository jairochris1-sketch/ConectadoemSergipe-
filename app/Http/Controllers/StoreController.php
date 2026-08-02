<?php

namespace App\Http\Controllers;

use App\Core\SergipeCities;
use App\Models\Ad;
use App\Models\Store;
use App\Models\StoreBusinessHour;
use App\Models\StoreEvent;
use App\Services\ImageOptimizer;
use App\Services\ProductDisplayService;
use App\Services\ReviewDisplayService;
use App\Services\StoreAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    private const CATEGORIES = [
        ['name' => 'Moda', 'icon' => 'fa-shirt'],
        ['name' => 'Beleza', 'icon' => 'fa-pump-soap'],
        ['name' => 'Casa e Decoração', 'icon' => 'fa-couch'],
        ['name' => 'Alimentação', 'icon' => 'fa-utensils'],
        ['name' => 'Eletrônicos', 'icon' => 'fa-computer'],
        ['name' => 'Artigos', 'icon' => 'fa-bag-shopping'],
        ['name' => 'Esportes', 'icon' => 'fa-futbol'],
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $city = trim((string) ($validated['city'] ?? ''));
        $category = trim((string) ($validated['category'] ?? ''));

        $storesQuery = Store::query()
            ->select('stores.*')
            ->selectRaw(
                'CASE WHEN stores.featured = 1 AND (stores.featured_until IS NULL OR stores.featured_until > ?) THEN 1 ELSE 0 END AS featured_rank',
                [now()]
            )
            ->with([
                'user',
                'ads' => fn ($query) => $query
                    ->with(['mainImage', 'category'])
                    ->where('module', 'products')
                    ->where('status', 'active')
                    ->latest(),
            ])
            ->withCount([
                'ads as active_ads_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved'),
                'followers',
            ])
            ->withAvg([
                'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
            ], 'rating')
            ->publiclyVisible()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($storeQuery) use ($q) {
                    $storeQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('city', 'like', "%{$q}%"))
                        ->orWhereHas('ads', function ($adQuery) use ($q) {
                            $adQuery
                                ->where('module', 'products')
                                ->where('status', 'active')
                                ->where(function ($activeAdQuery) use ($q) {
                                    $activeAdQuery
                                        ->where('title', 'like', "%{$q}%")
                                        ->orWhere('description', 'like', "%{$q}%")
                                        ->orWhere('advertiser_type', 'like', "%{$q}%");
                                });
                        });
                });
            })
            ->when($city, fn ($query) => $query->where(function ($cityQuery) use ($city) {
                $cityQuery
                    ->where('city', $city)
                    ->orWhere(function ($fallbackQuery) use ($city) {
                        $fallbackQuery
                            ->whereNull('city')
                            ->whereHas('user', fn ($userQuery) => $userQuery->where('city', $city));
                    });
            }))
            ->when($category, function ($query) use ($category) {
                $query->where(function ($storeQuery) use ($category) {
                    $storeQuery
                        ->where('category', 'like', "%{$category}%")
                        ->orWhereHas('ads', function ($adQuery) use ($category) {
                            $adQuery
                                ->where('module', 'products')
                                ->where('status', 'active')
                                ->where(function ($activeAdQuery) use ($category) {
                                    $activeAdQuery
                                        ->where('advertiser_type', 'like', "%{$category}%")
                                        ->orWhere('title', 'like', "%{$category}%")
                                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$category}%"));
                                });
                        });
                });
            });

        $stores = $storesQuery
            ->orderByDesc('featured_rank')
            ->orderByDesc('active_ads_count')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $storesCount = $stores->total();
        $productsCount = Ad::whereNotNull('store_id')
            ->where('module', 'products')
            ->where('status', 'active')
            ->when($city, fn ($query) => $query->where('city', $city))
            ->whereHas('store', fn ($storeQuery) => $storeQuery->publiclyVisible())
            ->count();
        $storeCategories = self::CATEGORIES;
        $cities = SergipeCities::getAll();
        $followedStoreIds = $request->user()
            ? $request->user()->followedStores()->pluck('stores.id')
            : collect();

        return view('stores.index', compact(
            'stores',
            'storesCount',
            'productsCount',
            'storeCategories',
            'cities',
            'q',
            'city',
            'category',
            'followedStoreIds'
        ));
    }

    public function create(Request $request)
    {
        if (! $request->user()->canCreateAnotherStore()) {
            $message = $this->storeLimitMessage($request);

            return redirect()
                ->route('user.panel')
                ->with('store_limit', $message)
                ->withErrors(['store' => $message]);
        }

        return $this->managementView($request, null);
    }

    public function store(Request $request)
    {
        if (! $request->user()->canCreateAnotherStore()) {
            $message = $this->storeLimitMessage($request);

            return redirect()
                ->route('user.panel')
                ->with('store_limit', $message)
                ->withErrors(['store' => $message]);
        }

        $validated = $this->validateStore($request);
        $this->validateStoreMediaLimits($request, null);
        [$logoPath, $bannerPath] = $this->processStoreImages($request);
        try {
            $mediaUploads = $this->processAdditionalStoreMediaFiles($request);
        } catch (\Throwable $error) {
            $this->deleteUpload($logoPath);
            $this->deleteUpload($bannerPath);
            throw $error;
        }

        try {
            $store = DB::transaction(function () use (
                $request,
                $validated,
                $logoPath,
                $bannerPath,
                $mediaUploads
            ) {
                $store = Store::create([
                    'user_id' => $request->user()->id,
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug($validated['name']),
                    'description' => $validated['description'] ?? null,
                    'category' => $validated['category'],
                    'product_display_mode' => $validated['product_display_mode']
                        ?? app(ProductDisplayService::class)->suggestForCategory($validated['category']),
                    'city' => $validated['city'],
                    'state' => 'SE',
                    'phone' => $this->normalizePhone($validated['phone'] ?? null),
                    'whatsapp' => $this->normalizePhone($validated['whatsapp']),
                    'instagram' => $validated['instagram'] ?? null,
                    'website' => $validated['website'] ?? null,
                    'logo' => $logoPath,
                    'banner' => $bannerPath,
                    'active' => true,
                    'moderation_status' => 'approved',
                    ...$this->deliveryData($validated, $request),
                ]);
                $store->media()->createMany($mediaUploads);

                return $store;
            });
        } catch (\Throwable $error) {
            collect($mediaUploads)->pluck('path')->each(fn ($path) => $this->deleteUpload($path));
            $this->deleteUpload($logoPath);
            $this->deleteUpload($bannerPath);
            throw $error;
        }

        $destination = $request->user()->stores()->count() === 1
            ? route('store.edit')
            : route('store.manage', $store);

        return redirect()
            ->to($destination)
            ->with('store_success', "A loja {$store->name} foi criada com sucesso.");
    }

    public function edit(Request $request)
    {
        $store = $request->user()->stores()->oldest('id')->first();

        if (! $store) {
            return redirect()->route('store.create');
        }

        return redirect()->route('store.manage', $store);
    }

    public function manage(Request $request, Store $store)
    {
        $this->authorizeStoreOwnership($request, $store);

        return $this->managementView($request, $store);
    }

    public function update(Request $request)
    {
        $store = $request->user()->stores()->oldest('id')->first();
        abort_unless($store, 404);

        return $this->updateOwnedStore($request, $store);
    }

    public function updateStore(Request $request, Store $store)
    {
        $this->authorizeStoreOwnership($request, $store);

        return $this->updateOwnedStore($request, $store);
    }

    private function updateOwnedStore(Request $request, Store $store)
    {
        $validated = $this->validateStore($request);
        $this->validateStoreMediaLimits($request, $store);
        $oldLogo = $store->logo;
        $oldBanner = $store->banner;
        [$newLogo, $newBanner] = $this->processStoreImages($request);
        try {
            $mediaUploads = $this->processAdditionalStoreMediaFiles($request);
        } catch (\Throwable $error) {
            $this->deleteUpload($newLogo);
            $this->deleteUpload($newBanner);
            throw $error;
        }

        $logo = $newLogo ?: ($request->boolean('remove_logo') ? null : $oldLogo);
        $banner = $newBanner ?: ($request->boolean('remove_banner') ? null : $oldBanner);
        $removedMedia = $store->media()
            ->whereIn('id', $request->input('remove_media_ids', []))
            ->get();

        try {
            DB::transaction(function () use (
                $store,
                $validated,
                $logo,
                $banner,
                $removedMedia,
                $mediaUploads
            ) {
                $store->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'category' => $validated['category'],
                    'product_display_mode' => $validated['product_display_mode']
                        ?? $store->product_display_mode
                        ?? app(ProductDisplayService::class)->suggestForCategory($validated['category']),
                    'city' => $validated['city'],
                    'state' => 'SE',
                    'phone' => $this->normalizePhone($validated['phone'] ?? null),
                    'whatsapp' => $this->normalizePhone($validated['whatsapp']),
                    'instagram' => $validated['instagram'] ?? null,
                    'website' => $validated['website'] ?? null,
                    'logo' => $logo,
                    'banner' => $banner,
                    ...$this->deliveryData($validated, request(), $store),
                ]);
                $store->media()->whereIn('id', $removedMedia->pluck('id'))->delete();
                $store->media()->createMany($mediaUploads);
            });
        } catch (\Throwable $error) {
            collect($mediaUploads)->pluck('path')->each(fn ($path) => $this->deleteUpload($path));
            $this->deleteUpload($newLogo);
            $this->deleteUpload($newBanner);
            throw $error;
        }

        if ($oldLogo && $oldLogo !== $logo) {
            $this->deleteUpload($oldLogo);
        }
        if ($oldBanner && $oldBanner !== $banner) {
            $this->deleteUpload($oldBanner);
        }
        $removedMedia->pluck('path')->each(fn ($path) => $this->deleteUpload($path));

        return back()->with('store_success', 'Dados da loja atualizados com sucesso.');
    }

    public function toggleStatus(Request $request)
    {
        $store = $request->user()->stores()->oldest('id')->first();
        abort_unless($store, 404);

        return $this->toggleOwnedStoreStatus($store);
    }

    public function toggleStoreStatus(Request $request, Store $store)
    {
        $this->authorizeStoreOwnership($request, $store);

        return $this->toggleOwnedStoreStatus($store);
    }

    private function toggleOwnedStoreStatus(Store $store)
    {
        if (! $store->isModerationApproved()) {
            return back()->with(
                'store_warning',
                'Esta loja está bloqueada pela moderação e não pode ser reativada. Consulte a observação administrativa.'
            );
        }

        $activating = ! $store->active;
        $changes = ['active' => $activating];
        if (! $activating) {
            $changes['featured'] = false;
            $changes['featured_until'] = null;
        }
        $store->update($changes);

        return back()->with(
            'store_success',
            $store->active
                ? 'Sua loja está ativa e voltou a aparecer na vitrine.'
                : 'Sua loja foi desativada e não aparecerá na vitrine pública.'
        );
    }

    public function destroy(Request $request)
    {
        $store = $request->user()->stores()->oldest('id')->first();
        abort_unless($store, 404);

        return $this->destroyOwnedStore($store);
    }

    public function destroyStore(Request $request, Store $store)
    {
        $this->authorizeStoreOwnership($request, $store);

        return $this->destroyOwnedStore($store);
    }

    private function destroyOwnedStore(Store $store)
    {
        $logo = $store->logo;
        $banner = $store->banner;
        $mediaPaths = $store->media()->pluck('path');

        DB::transaction(function () use ($store) {
            $store->ads()->update(['store_id' => null]);
            $store->delete();
        });

        $this->deleteUpload($logo);
        $this->deleteUpload($banner);
        $mediaPaths->each(fn ($path) => $this->deleteUpload($path));

        return redirect()
            ->route('user.panel')
            ->with('store_success', 'A loja foi excluída. Seus anúncios foram preservados fora da loja.');
    }

    private function managementView(Request $request, ?Store $store)
    {
        $storeProducts = collect();

        if ($store) {
            $store->load(['media', 'promotions', 'businessHours']);
            $store->loadCount([
                'ads as products_count' => fn ($query) => $query->where('module', 'products'),
                'ads as active_ads_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
            ]);
            $storeProducts = $store->ads()
                ->where('module', 'products')
                ->with(['mainImage', 'activeVariations'])
                ->withCount(['variations', 'activeAddons'])
                ->latest()
                ->get();
        }

        return view('stores.manage', [
            'store' => $store,
            'storeProducts' => $storeProducts,
            'cities' => SergipeCities::getAll(),
            'categories' => self::CATEGORIES,
            'storeUsage' => $request->user()->stores()->count(),
            'storeLimit' => $request->user()->storeLimit(),
            'productLimit' => $request->user()->storeProductLimit(),
            'bannerLimit' => $request->user()->storeMediaLimit('banner'),
            'galleryLimit' => $request->user()->storeMediaLimit('gallery'),
            'bannerUsage' => ($store?->banner ? 1 : 0)
                + ($store?->media->where('type', 'banner')->count() ?? 0),
            'galleryUsage' => $store?->media->where('type', 'gallery')->count() ?? 0,
            'promotionLimit' => $request->user()->storePromotionLimit(),
            'activePromotionsUsage' => $store?->promotions
                ->where('active', true)
                ->where('ends_at', '>', now())
                ->count() ?? 0,
            'weekdays' => StoreBusinessHour::WEEKDAYS,
            'businessHoursByDay' => $store?->businessHours->keyBy('day_of_week') ?? collect(),
            'planLabel' => $request->user()->subscriptionPlanLabel(),
            'analytics' => $store
                ? app(StoreAnalyticsService::class)->forStore($store, $request->user())
                : null,
        ]);
    }

    private function authorizeStoreOwnership(Request $request, Store $store): void
    {
        abort_unless($store->user_id === $request->user()->id, 403);
    }

    private function storeLimitMessage(Request $request): string
    {
        $user = $request->user();
        $limit = $user->storeLimit();
        $planLabel = $user->subscriptionPlanLabel();

        if ($limit === 0) {
            return 'A criação de Loja Online é uma funcionalidade disponível a partir do Plano Start. Faça o upgrade do seu plano para ativar sua loja e vender online!';
        }

        if ($limit === 1) {
            return "Você já possui 1 loja cadastrada, que é o limite do seu plano atual ({$planLabel}). Faça um upgrade para o Plano Premium para cadastrar mais lojas!";
        }

        return "Você já atingiu o limite de {$limit} lojas do seu plano ({$planLabel}). Faça um upgrade de plano para cadastrar novas lojas!";
    }

    public function show(Request $request, $slug)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['recent', 'price_asc', 'price_desc'])],
            'reviews_sort' => ['nullable', Rule::in(['relevant', 'recent', 'highest', 'lowest'])],
        ]);
        $q = trim((string) ($validated['q'] ?? ''));
        $category = trim((string) ($validated['category'] ?? ''));
        $sort = $validated['sort'] ?? 'recent';

        $store = Store::with([
            'user',
            'media',
            'promotions' => fn ($query) => $query->currentlyActive()->reorder('ends_at'),
            'businessHours',
        ])
            ->withCount('followers')
            ->where('slug', $slug)
            ->firstOrFail();
        $privilegedViewer = auth()->id() === $store->user_id || auth()->user()?->role === 'admin';
        abort_if(
            (! $store->active || ! $store->isModerationApproved()) && ! $privilegedViewer,
            404
        );

        $productsQuery = Ad::query()
            ->where('store_id', $store->id)
            ->where('module', 'products')
            ->where('status', 'active');
        $storeProductsCount = (clone $productsQuery)->count();
        $productCategories = (clone $productsQuery)
            ->whereNotNull('advertiser_type')
            ->where('advertiser_type', '!=', '')
            ->orderBy('advertiser_type')
            ->distinct()
            ->pluck('advertiser_type');

        $ads = $productsQuery
            ->with(['images', 'mainImage', 'category', 'store', 'activeVariations', 'activeAddons'])
            ->withCount([
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->withAvg([
                'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
            ], 'rating')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($searchQuery) use ($q) {
                    $searchQuery
                        ->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('advertiser_type', 'like', "%{$q}%");
                });
            })
            ->when($category, fn ($query) => $query->where('advertiser_type', $category))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'recent', fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();
        $reviewData = app(ReviewDisplayService::class)->forStore($store, request('reviews_sort'));
        $isFollowing = $request->user()
            ? $request->user()->followedStores()->whereKey($store->id)->exists()
            : false;
        $storePromotions = $store->promotions;
        $businessStatus = $store->businessStatus();
        $weeklyBusinessHours = collect(StoreBusinessHour::WEEKDAYS)
            ->map(fn ($label, $day) => [
                'day' => $day,
                'label' => $label,
                'hours' => $store->businessHours->firstWhere('day_of_week', $day),
            ])
            ->values();

        return view('store.show', compact(
            'store',
            'ads',
            'reviewData',
            'storeProductsCount',
            'productCategories',
            'q',
            'category',
            'sort',
            'isFollowing',
            'storePromotions',
            'businessStatus',
            'weeklyBusinessHours'
        ));
    }

    public function toggleFollow(Request $request, Store $store)
    {
        abort_unless($store->active && $store->isModerationApproved(), 404);

        if ($store->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'Você não pode seguir a própria loja.',
            ], 422);
        }

        $alreadyFollowing = $request->user()
            ->followedStores()
            ->whereKey($store->id)
            ->exists();

        if ($alreadyFollowing) {
            $request->user()->followedStores()->detach($store->id);
        } else {
            $request->user()->followedStores()->attach($store->id);
        }

        return response()->json([
            'following' => ! $alreadyFollowing,
            'followers_count' => $store->followers()->count(),
            'message' => $alreadyFollowing
                ? 'Loja removida das suas lojas seguidas.'
                : 'Agora você está seguindo esta loja.',
        ]);
    }

    public function recordEvent(Request $request, Store $store)
    {
        abort_unless($store->active && $store->isModerationApproved(), 404);

        if ($request->user()?->id === $store->user_id || $request->user()?->role === 'admin') {
            return response()->noContent();
        }

        $validated = $request->validate([
            'event_type' => ['required', Rule::in(StoreEvent::TYPES)],
            'ad_id' => ['nullable', 'integer'],
        ]);

        $adId = isset($validated['ad_id']) ? (int) $validated['ad_id'] : null;
        if ($validated['event_type'] === 'product_click') {
            abort_unless(
                $adId && $store->ads()
                    ->whereKey($adId)
                    ->where('module', 'products')
                    ->where('status', 'active')
                    ->exists(),
                422
            );
        } else {
            $adId = null;
        }

        $visitorHash = hash_hmac(
            'sha256',
            implode('|', [
                (string) $request->ip(),
                (string) $request->userAgent(),
                (string) ($request->user()?->id ?: 'guest'),
            ]),
            (string) config('app.key')
        );
        $deduplicationStart = $validated['event_type'] === 'page_view'
            ? now()->startOfDay()
            : now()->subMinute();
        $duplicateQuery = $store->events()
            ->where('event_type', $validated['event_type'])
            ->where('visitor_hash', $visitorHash)
            ->where('created_at', '>=', $deduplicationStart);
        $adId
            ? $duplicateQuery->where('ad_id', $adId)
            : $duplicateQuery->whereNull('ad_id');

        if (! $duplicateQuery->exists()) {
            $store->events()->create([
                'ad_id' => $adId,
                'user_id' => $request->user()?->id,
                'event_type' => $validated['event_type'],
                'visitor_hash' => $visitorHash,
                'occurred_on' => now()->toDateString(),
            ]);
        }

        return response()->noContent();
    }

    private function validateStore(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(array_column(self::CATEGORIES, 'name'))],
            'product_display_mode' => ['nullable', Rule::in(ProductDisplayService::STORE_MODES)],
            'pickup_available' => ['nullable', 'boolean'],
            'delivery_available' => ['nullable', 'boolean'],
            'delivery_cities_text' => ['nullable', 'string', 'max:2000'],
            'delivery_neighborhoods_text' => ['nullable', 'string', 'max:4000'],
            'delivery_region_fees_text' => ['nullable', 'string', 'max:6000'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'delivery_min_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'delivery_max_minutes' => ['nullable', 'integer', 'gte:delivery_min_minutes', 'max:10080'],
            'minimum_order' => ['nullable', 'numeric', 'min:0'],
            'pickup_address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', Rule::in(SergipeCities::getAll())],
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'instagram' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'additional_banners' => ['nullable', 'array', 'max:6'],
            'additional_banners.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'gallery_images' => ['nullable', 'array', 'max:20'],
            'gallery_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
        ]);

        if ($request->has('pickup_available')
            && $request->has('delivery_available')
            && ! $request->boolean('pickup_available')
            && ! $request->boolean('delivery_available')) {
            throw ValidationException::withMessages([
                'delivery_available' => 'Disponibilize pelo menos retirada ou entrega para receber pedidos.',
            ]);
        }

        return $validated;
    }

    private function deliveryData(array $validated, Request $request, ?Store $store = null): array
    {
        $lines = fn (?string $value) => collect(preg_split('/[\r\n,;]+/', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $regionFees = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['delivery_region_fees_text'] ?? '')))
            ->map(function ($line) {
                [$region, $fee] = array_pad(preg_split('/[|:;]/', $line, 2), 2, null);
                $normalizedFee = str_replace(',', '.', trim((string) $fee));

                return filled($region) && is_numeric($normalizedFee)
                    ? [['region' => trim($region), 'fee' => round((float) $normalizedFee, 2)]]
                    : [];
            })
            ->collapse()
            ->values()
            ->all();

        return [
            'pickup_available' => $request->has('pickup_available')
                ? $request->boolean('pickup_available')
                : ($store?->pickup_available ?? true),
            'delivery_available' => $request->has('delivery_available')
                ? $request->boolean('delivery_available')
                : ($store?->delivery_available ?? true),
            'delivery_cities' => $request->has('delivery_cities_text')
                ? $lines($validated['delivery_cities_text'] ?? null)
                : ($store?->delivery_cities ?? []),
            'delivery_neighborhoods' => $request->has('delivery_neighborhoods_text')
                ? $lines($validated['delivery_neighborhoods_text'] ?? null)
                : ($store?->delivery_neighborhoods ?? []),
            'delivery_region_fees' => $request->has('delivery_region_fees_text')
                ? $regionFees
                : ($store?->delivery_region_fees ?? []),
            'delivery_fee' => $validated['delivery_fee'] ?? $store?->delivery_fee ?? 0,
            'free_delivery_threshold' => $validated['free_delivery_threshold'] ?? $store?->free_delivery_threshold,
            'delivery_min_minutes' => $validated['delivery_min_minutes'] ?? $store?->delivery_min_minutes,
            'delivery_max_minutes' => $validated['delivery_max_minutes'] ?? $store?->delivery_max_minutes,
            'minimum_order' => $validated['minimum_order'] ?? $store?->minimum_order ?? 0,
            'pickup_address' => $validated['pickup_address'] ?? $store?->pickup_address,
        ];
    }

    private function validateStoreMediaLimits(Request $request, ?Store $store): void
    {
        $removedIds = collect($request->input('remove_media_ids', []))
            ->map(fn ($id) => (int) $id);
        $currentMedia = $store
            ? $store->media()->get()->reject(fn ($media) => $removedIds->contains($media->id))
            : collect();

        $primaryBannerCount = $request->hasFile('banner')
            ? 1
            : ($request->boolean('remove_banner') ? 0 : ($store?->banner ? 1 : 0));
        $bannerCount = $primaryBannerCount
            + $currentMedia->where('type', 'banner')->count()
            + count($request->file('additional_banners', []));
        $galleryCount = $currentMedia->where('type', 'gallery')->count()
            + count($request->file('gallery_images', []));

        $bannerLimit = $request->user()->storeMediaLimit('banner');
        $galleryLimit = $request->user()->storeMediaLimit('gallery');
        $errors = [];

        if ($bannerLimit !== null && $bannerCount > $bannerLimit) {
            $errors['additional_banners'] = "Seu plano permite até {$bannerLimit} "
                .($bannerLimit === 1 ? 'banner' : 'banners')
                .' por loja, contando o banner principal.';
        }
        if ($galleryLimit !== null && $galleryCount > $galleryLimit) {
            $errors['gallery_images'] = "Seu plano permite até {$galleryLimit} fotos na galeria da loja.";
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function processAdditionalStoreMediaFiles(Request $request): array
    {
        $uploads = [];
        $groups = [
            'additional_banners' => ['type' => 'banner', 'prefix' => 'store_banner_extra'],
            'gallery_images' => ['type' => 'gallery', 'prefix' => 'store_gallery'],
        ];

        try {
            foreach ($groups as $field => $settings) {
                foreach ($request->file($field, []) as $position => $file) {
                    $path = ImageOptimizer::convertToWebp($file, $settings['prefix']);
                    if (! $path) {
                        throw ValidationException::withMessages([
                            $field => 'Não foi possível processar uma das imagens enviadas.',
                        ]);
                    }

                    $uploads[] = [
                        'type' => $settings['type'],
                        'path' => $path,
                        'sort_order' => $position,
                    ];
                }
            }
        } catch (\Throwable $error) {
            collect($uploads)->pluck('path')->each(fn ($path) => $this->deleteUpload($path));
            throw $error;
        }

        return $uploads;
    }

    private function processStoreImages(Request $request): array
    {
        $logoPath = $request->hasFile('logo')
            ? ImageOptimizer::convertToWebp($request->file('logo'), 'store_logo')
            : null;
        $bannerPath = $request->hasFile('banner')
            ? ImageOptimizer::convertToWebp($request->file('banner'), 'store_banner')
            : null;

        if ($request->hasFile('logo') && ! $logoPath) {
            throw ValidationException::withMessages(['logo' => 'Não foi possível processar o logo enviado.']);
        }
        if ($request->hasFile('banner') && ! $bannerPath) {
            $this->deleteUpload($logoPath);
            throw ValidationException::withMessages(['banner' => 'Não foi possível processar o banner enviado.']);
        }

        return [$logoPath, $bannerPath];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'loja';
        $slug = $base;
        $suffix = 2;

        while (Store::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        if (str_starts_with($digits, '55') && in_array(strlen($digits), [12, 13], true)) {
            $digits = substr($digits, 2);
        }

        return $digits !== '' ? $digits : null;
    }

    private function deleteUpload(?string $path): void
    {
        $relative = ltrim((string) $path, '/\\');

        if ($relative !== '' && str_starts_with($relative, 'uploads/')) {
            File::delete(public_path($relative));
        }
    }
}
