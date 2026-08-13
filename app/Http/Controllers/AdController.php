<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use App\Models\ProductVariation;
use App\Models\Store;
use App\Models\User;
use App\Services\ImageOptimizer;
use App\Services\ProductDisplayService;
use App\Services\ReviewDisplayService;
use App\Services\StoreFollowerNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdController extends Controller
{
    private const MODULE_CATEGORY_SLUGS = [
        'real_estate' => 'imoveis',
        'vehicles' => 'veiculos',
        'products' => 'produtos',
        'services' => 'servicos',
        'jobs' => 'empregos',
        'agro' => 'agro',
    ];

    public function show($slug)
    {
        $ad = Ad::with(['user', 'images', 'category', 'store'])->where('slug', $slug)->firstOrFail();

        if ($ad->module === 'services') {
            return redirect()->route('provider.show', $ad->slug);
        }

        if ($ad->module === 'products'
            && $ad->status === 'active'
            && $ad->store?->active
            && $ad->store->isModerationApproved()) {
            return redirect()->route('store.products.show', [$ad->store, $ad], 301);
        }

        $ad->increment('views');
        if ($ad->module === 'vehicles') {
            return view('ads.show-vehicle', compact('ad'));
        }

        $relatedAds = Ad::with(['mainImage'])
            ->where('module', $ad->module)
            ->where('id', '!=', $ad->id)
            ->where('status', 'active')
            ->take(4)
            ->get();

        return view('ads.show', compact('ad', 'relatedAds'));
    }

    public function provider($slug)
    {
        $provider = Ad::with(['user', 'images', 'category'])
            ->where('module', 'services')
            ->where('status', 'active')
            ->where('slug', $slug)
            ->firstOrFail();

        $provider->increment('views');
        $reviewData = app(ReviewDisplayService::class)->forAd($provider, request('reviews_sort'));
        $isExclusiveProviderProfile = $provider->user
            && (float) $provider->user->getPlan()->price >= 25;
        $relatedProviders = ! $isExclusiveProviderProfile
            ? Ad::with(['user', 'mainImage'])
                ->where('module', 'services')
                ->where('status', 'active')
                ->where('id', '!=', $provider->id)
                ->where('city', $provider->city)
                ->latest()
                ->take(4)
                ->get()
            : collect();
        $ownerStores = $isExclusiveProviderProfile
            ? $provider->user->stores()
                ->publiclyVisible()
                ->withCount([
                    'ads as active_ads_count' => fn ($query) => $query->where('status', 'active'),
                ])
                ->oldest('id')
                ->get()
            : collect();

        $currentUserPendingClaim = auth()->check()
            && ! $provider->is_claimed
            && $provider->claiming_enabled
            ? $provider->providerClaims()
                ->where('claimant_user_id', auth()->id())
                ->where('status', \App\Models\ProviderClaim::STATUS_PENDING)
                ->latest()
                ->first()
            : null;

        return view('services.show', compact(
            'provider',
            'relatedProviders',
            'ownerStores',
            'reviewData',
            'currentUserPendingClaim'
        ));
    }

    public function published($slug)
    {
        $ad = Ad::where('slug', $slug)->firstOrFail();

        return view('ads.published', compact('ad'));
    }

    public function create(Request $request)
    {
        // Exige autenticação obrigatoriamente para anunciar
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Para publicar um anúncio no Conectado em Sergipe, por favor entre na sua conta ou cadastre-se gratuitamente.');
        }

        $user = $request->user();
        if ($request->query('module') === 'services' && ! $user->canCreateAnotherProfessionalProfile()) {
            return redirect()
                ->route('module.services')
                ->with('professional_profile_limit', $this->professionalProfileLimitMessage());
        }

        $requestedModule = in_array($request->query('module'), array_keys(self::MODULE_CATEGORY_SLUGS), true)
            ? $request->query('module')
            : 'products';
        $requestedProfileKind = in_array($request->query('profile_kind'), ['professional', 'service_company'], true)
            ? $request->query('profile_kind')
            : 'professional';
        $categories = Category::where('active', true)->orderBy('sort_order', 'asc')->get();
        $availableStores = Store::where('user_id', $user->id)
            ->where('active', true)
            ->where('moderation_status', 'approved')
            ->withCount(['ads as products_count' => fn ($query) => $query->where('module', 'products')])
            ->orderBy('name')
            ->get();
        $storeProductLimit = $user->storeProductLimit();

        return view('ads.create', compact(
            'categories',
            'requestedModule',
            'requestedProfileKind',
            'availableStores',
            'storeProductLimit'
        ));
    }

    public function store(Request $request, StoreFollowerNotifier $notifier)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Por favor, faça login para concluir a publicação do seu anúncio.');
        }

        $user = auth()->user();
        $isFreeUser = $user->role !== 'admin';
        $request->merge([
            'price' => $this->normalizePrice($request->input('price')),
            'sale_price' => $this->normalizePrice($request->input('sale_price')),
        ]);

        $request->validate([
            'module' => 'required|in:real_estate,vehicles,products,services,jobs,agro',
            'profile_kind' => [
                Rule::excludeIf(fn () => $request->input('module') !== 'services'),
                'nullable',
                Rule::in(['professional', 'service_company']),
            ],
            'category_name' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'advertiser_type' => 'nullable|string',
            'cnpj' => 'nullable|string|max:30',
            'region' => 'nullable|string|max:100',
            'public_address' => [
                Rule::excludeIf(fn () => $request->input('module') !== 'services'),
                'nullable',
                'string',
                'max:255',
            ],
            'whatsapp' => [
                Rule::requiredIf(fn () => $request->input('module') === 'services'),
                'nullable',
                'string',
                'max:20',
            ],
            'phone' => [
                Rule::requiredIf(fn () => $request->input('module') === 'services'),
                'nullable',
                'string',
                'max:20',
            ],
            'telegram' => 'nullable|string|max:50',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'profile_is_claimed' => 'nullable|boolean',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'banner' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'images' => 'nullable|array|max:'.($isFreeUser ? 5 : 20),
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'include_in_store' => [
                Rule::excludeIf(fn () => $request->input('module') !== 'products'),
                'nullable',
                'boolean',
            ],
            'store_id' => [
                Rule::excludeIf(fn () => $request->input('module') !== 'products'),
                'nullable',
                'integer',
                Rule::exists('stores', 'id')->where(
                    fn ($query) => $query->where('user_id', $user->id)
                ),
            ],
            'display_mode' => [
                Rule::excludeIf(fn () => $request->input('module') !== 'products'),
                'nullable',
                Rule::in(ProductDisplayService::PRODUCT_MODES),
            ],
            ...$this->productCommerceRules(),
        ]);

        if ($request->module === 'services' && ! $user->canCreateAnotherProfessionalProfile()) {
            return redirect()
                ->route('module.services')
                ->with('professional_profile_limit', $this->professionalProfileLimitMessage());
        }

        $slug = Str::slug($request->title).'-'.time().'-'.rand(1000, 9999);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = ImageOptimizer::convertToWebp($request->file('logo'), 'logo');
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = ImageOptimizer::convertToWebp($request->file('banner'), 'banner');
        }

        $priceType = $request->input(
            'price_type',
            $request->filled('price') ? 'fixed' : 'negotiable'
        );
        $priceValue = $request->filled('price') ? $request->price : 0.00;

        $categoryId = $request->category_id;
        if (! $categoryId) {
            $categoryId = Category::where(
                'slug',
                self::MODULE_CATEGORY_SLUGS[$request->module]
            )->value('id');
        }

        $storeId = $this->resolveStoreId($request, $user, $request->module);
        $isClaimed = $request->module !== 'services'
            || $user->role !== 'admin'
            || $request->boolean('profile_is_claimed');

        $adData = [
            'user_id' => $user->id,
            'store_id' => $storeId,
            'category_id' => $categoryId,
            'module' => $request->module,
            'profile_kind' => $request->module === 'services'
                ? $request->input('profile_kind', 'professional')
                : null,
            'display_mode' => $request->module === 'products'
                ? $request->input('display_mode', 'default')
                : 'default',
            'advertiser_type' => $request->category_name,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $priceValue,
            'cnpj' => $request->cnpj,
            'city' => $request->city,
            'state' => 'Sergipe',
            'region' => $request->module === 'services' ? $request->region : null,
            'public_address' => $request->module === 'services'
                ? $request->input('public_address')
                : null,
            'business_hours' => $request->input('business_hours', []),
            'instagram' => $request->instagram,
            'facebook' => $request->facebook,
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'status' => 'active',
            'views' => 0,
            'publication_ip' => $request->ip(),
            'is_claimed' => $isClaimed,
            'claiming_enabled' => false,
            'claimed_at' => $request->module === 'services' && $user->role === 'admin' && $isClaimed
                ? now()
                : null,
            'contact_phone' => $request->module === 'services' && ! $isClaimed
                ? $request->phone
                : null,
            'contact_whatsapp' => $request->module === 'services' && ! $isClaimed
                ? $request->whatsapp
                : null,
            'contact_telegram' => $request->telegram,
        ];

        if (Schema::hasColumn('ads', 'price_type')) {
            $adData['price_type'] = $priceType;
        }

        if (Schema::hasColumn('ads', 'service_modes')) {
            $adData['service_modes'] = $request->input('service_modes', []);
        }
        if ($request->module === 'products') {
            $adData = array_merge($adData, $this->productCommerceData($request));
        }

        $ad = Ad::create($adData);
        if ($ad->module === 'products') {
            $this->syncProductOptions($ad, $request);
        }

        if ($isClaimed) {
            $user->update([
                'whatsapp' => $request->whatsapp,
                'phone' => $request->phone ?: $user->phone,
            ]);
        }

        $hasMainImage = false;
        if ($request->module !== 'services' && $logoPath) {
            AdImage::create([
                'ad_id' => $ad->id,
                'image_path' => $logoPath,
                'is_main' => true,
            ]);
            $hasMainImage = true;
        }

        // LIMITAÇÃO RIGOROSA: Usuários Gratuitos podem enviar no MÁXIMO 5 fotos na galeria
        if ($request->hasFile('images')) {
            $galleryFiles = $request->file('images');

            $isMain = ! $hasMainImage;
            foreach ($galleryFiles as $file) {
                $webpPath = ImageOptimizer::convertToWebp($file, 'gallery');
                if ($webpPath) {
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image_path' => $webpPath,
                        'is_main' => $isMain,
                    ]);
                    $isMain = false;
                }
            }
        }

        if ($ad->store_id) {
            $notifier->productPublished($ad);
        }

        return redirect()->route('ad.published', $ad->slug);
    }

    private function professionalProfileLimitMessage(): string
    {
        return 'Você já possui 1 perfil profissional, que é o limite do plano gratuito. Assine um plano para cadastrar outro perfil.';
    }

    public function edit($id)
    {
        $ad = Ad::with(['variations', 'addons'])->findOrFail($id);
        $this->authorizeAdManagement($ad);
        $categories = Category::where('active', true)->orderBy('name', 'asc')->get();
        $availableStores = Store::where('user_id', auth()->id())
            ->withCount(['ads as products_count' => fn ($query) => $query->where('module', 'products')])
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();
        $storeProductLimit = auth()->user()->storeProductLimit();

        return view('ads.edit', compact('ad', 'categories', 'availableStores', 'storeProductLimit'));
    }

    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);
        $this->authorizeAdManagement($ad);
        $request->merge([
            'price' => $this->normalizePrice($request->input('price')),
            'sale_price' => $this->normalizePrice($request->input('sale_price')),
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'description' => 'required|string',
            'category_name' => 'nullable|string|max:100',
            'profile_kind' => [
                Rule::excludeIf($ad->module !== 'services'),
                'nullable',
                Rule::in(['professional', 'service_company']),
            ],
            'region' => 'nullable|string|max:100',
            'public_address' => [
                Rule::excludeIf($ad->module !== 'services'),
                'nullable',
                'string',
                'max:255',
            ],
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'banner' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'images' => 'nullable|array|max:20',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer',
            'remove_logo' => 'nullable|boolean',
            'remove_banner' => 'nullable|boolean',
            'include_in_store' => [
                Rule::excludeIf($ad->module !== 'products'),
                'nullable',
                'boolean',
            ],
            'store_id' => [
                Rule::excludeIf($ad->module !== 'products'),
                'nullable',
                'integer',
                Rule::exists('stores', 'id')->where(
                    fn ($query) => $query->where('user_id', auth()->id())
                ),
            ],
            'display_mode' => [
                Rule::excludeIf($ad->module !== 'products'),
                'nullable',
                Rule::in(ProductDisplayService::PRODUCT_MODES),
            ],
            ...$this->productCommerceRules($ad),
        ]);

        $oldLogoPath = $ad->logo;
        $oldBannerPath = $ad->banner;
        $logoPath = $oldLogoPath;
        if ($request->hasFile('logo')) {
            $optimizedLogo = ImageOptimizer::convertToWebp($request->file('logo'), 'logo');
            $logoPath = $optimizedLogo ?: $oldLogoPath;
        } elseif ($request->boolean('remove_logo')) {
            $logoPath = null;
        }

        $bannerPath = $oldBannerPath;
        $coverNoticeAlert = null;
        if ($request->hasFile('banner')) {
            $user = auth()->user();
            $plan = $user ? $user->normalizedSubscriptionPlan() : 'free';

            if ($plan === 'start' && $user->role !== 'admin') {
                $currentChanges = $ad->monthly_cover_changes;
                if ($currentChanges >= 3) {
                    return back()->with('error', '⚠️ Limite atingido (Plano Start): Você já realizou as 3 alterações de capa permitidas para este mês. O limite será renovado no próximo mês ou você pode fazer upgrade para o Plano PRO ou Ouro para alterações ilimitadas.');
                }
            }

            $optimizedBanner = ImageOptimizer::convertToWebp($request->file('banner'), 'banner');
            if ($optimizedBanner) {
                $bannerPath = $optimizedBanner;
                $ad->recordCoverChange();
                $newCount = $ad->monthly_cover_changes;

                if ($plan === 'start' && $user->role !== 'admin') {
                    if ($newCount === 2) {
                        $coverNoticeAlert = '⚠️ Atenção (Plano Start): Você realizou 2 de 3 alterações de capa permitidas para este mês. Resta apenas 1 alteração até a renovação no próximo mês!';
                    } elseif ($newCount === 3) {
                        $coverNoticeAlert = 'ℹ️ Aviso (Plano Start): Você utilizou a 3ª e última alteração de capa permitida para este mês.';
                    }
                }
            }
        } elseif ($request->boolean('remove_banner')) {
            $bannerPath = null;
        }

        $hasPriceType = Schema::hasColumn('ads', 'price_type');
        $priceType = $request->input(
            'price_type',
            $request->filled('price') ? 'fixed' : ($ad->price_type ?? 'negotiable')
        );
        $priceValue = $hasPriceType
            ? (($priceType === 'fixed' && $request->filled('price')) ? $request->price : 0.00)
            : $request->input('price', $ad->price);
        $storeId = $this->resolveStoreId($request, auth()->user(), $ad->module, $ad);

        $adData = [
            'store_id' => $storeId,
            'display_mode' => $ad->module === 'products'
                ? $request->input('display_mode', $ad->display_mode ?: 'default')
                : 'default',
            'title' => $request->title,
            'profile_kind' => $ad->module === 'services'
                ? $request->input('profile_kind', $ad->profile_kind ?: 'professional')
                : null,
            'price' => $priceValue,
            'city' => $request->city,
            'description' => $request->description,
            'category_id' => $request->category_id ?? $ad->category_id,
            'advertiser_type' => $request->input('category_name', $ad->advertiser_type),
            'cnpj' => $request->cnpj ?? $ad->cnpj,
            'region' => $ad->module === 'services'
                ? $request->input('region', $ad->region)
                : null,
            'public_address' => $ad->module === 'services'
                ? $request->input('public_address')
                : null,
            'instagram' => $request->instagram ?? $ad->instagram,
            'facebook' => $request->facebook ?? $ad->facebook,
            'contact_telegram' => $request->telegram ?? $ad->contact_telegram,
            'logo' => $logoPath,
            'banner' => $bannerPath,
            'business_hours' => $request->input('business_hours', $ad->business_hours),
        ];

        if ($hasPriceType) {
            $adData['price_type'] = $priceType;
        }

        if (Schema::hasColumn('ads', 'service_modes')) {
            $adData['service_modes'] = $request->input('service_modes', $ad->service_modes);
        }
        if ($ad->module === 'products') {
            $adData = array_merge($adData, $this->productCommerceData($request, $ad));
        }

        $ad->update($adData);
        if ($ad->module === 'products') {
            $this->syncProductOptions($ad, $request);
        }

        if ($ad->module !== 'services' && $logoPath !== $oldLogoPath) {
            $logoImage = $oldLogoPath
                ? $ad->images()->where('image_path', $oldLogoPath)->first()
                : null;

            if ($logoPath) {
                if ($logoImage) {
                    $logoImage->update([
                        'image_path' => $logoPath,
                        'is_main' => true,
                    ]);
                } else {
                    $ad->images()->update(['is_main' => false]);
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image_path' => $logoPath,
                        'is_main' => true,
                    ]);
                }
            } elseif ($logoImage) {
                $logoImage->delete();
            }
        }

        $removedImagePaths = $ad->images()
            ->whereIn('id', $request->input('remove_image_ids', []))
            ->get()
            ->each
            ->delete()
            ->pluck('image_path');

        if ($request->hasFile('images')) {
            $hasMainImage = $ad->images()->where('is_main', true)->exists() || (bool) $ad->logo;

            foreach ($request->file('images') as $file) {
                $imagePath = ImageOptimizer::convertToWebp($file, 'gallery');
                if (! $imagePath) {
                    continue;
                }

                AdImage::create([
                    'ad_id' => $ad->id,
                    'image_path' => $imagePath,
                    'is_main' => ! $hasMainImage,
                ]);
                $hasMainImage = true;
            }
        }

        if ($logoPath !== $oldLogoPath) {
            $this->deletePublicFileIfUnused($oldLogoPath);
        }

        if ($bannerPath !== $oldBannerPath) {
            $this->deletePublicFileIfUnused($oldBannerPath);
        }

        $removedImagePaths->each(fn ($path) => $this->deletePublicFileIfUnused($path));

        $route = $ad->module === 'services'
            ? 'provider.show'
            : ($ad->module === 'products' && $ad->store_id ? 'store.products.show' : 'ad.show');
        $message = $ad->module === 'services'
            ? 'Perfil profissional atualizado com sucesso!'
            : 'Anúncio atualizado com sucesso!';

        $routeParameters = $route === 'store.products.show'
            ? [$ad->store, $ad]
            : $ad->slug;

        $redirectResponse = redirect()->route($route, $routeParameters)->with('success', $message);
        if ($coverNoticeAlert) {
            $redirectResponse->with('warning', $coverNoticeAlert);
        }

        return $redirectResponse;
    }

    private function resolveStoreId(
        Request $request,
        User $user,
        string $module,
        ?Ad $currentAd = null
    ): ?int {
        if ($module !== 'products' || ! $request->boolean('include_in_store')) {
            return null;
        }

        if ($request->filled('store_id')) {
            $store = Store::where('user_id', $user->id)
                ->findOrFail((int) $request->input('store_id'));

            $isCurrentStore = $currentAd?->store_id === $store->id;
            if (! $isCurrentStore && (! $store->active || ! $store->isModerationApproved())) {
                throw ValidationException::withMessages([
                    'store_id' => 'Escolha uma loja ativa e aprovada para exibir este produto.',
                ]);
            }

            if (! $user->canAddProductToStore($store, $currentAd)) {
                throw ValidationException::withMessages([
                    'store_id' => $this->storeProductLimitMessage($user),
                ]);
            }

            return $store->id;
        }

        $activeStores = Store::where('user_id', $user->id)
            ->where('active', true)
            ->where('moderation_status', 'approved')
            ->get();
        $storesWithCapacity = $activeStores
            ->filter(fn (Store $store) => $user->canAddProductToStore($store, $currentAd));

        if ($storesWithCapacity->count() === 1) {
            return (int) $storesWithCapacity->first()->id;
        }

        throw ValidationException::withMessages([
            'store_id' => $activeStores->isEmpty()
                ? 'Você precisa ter uma loja ativa e aprovada para incluir este produto na vitrine.'
                : ($storesWithCapacity->isEmpty()
                    ? $this->storeProductLimitMessage($user)
                    : 'Escolha em qual loja este produto será exibido.'),
        ]);
    }

    private function storeProductLimitMessage(User $user): string
    {
        $limit = $user->storeProductLimit();

        return $limit === null
            ? 'Você precisa ter uma loja ativa e aprovada para incluir este produto na vitrine.'
            : "Sua loja atingiu o limite de {$limit} produtos do plano {$user->subscriptionPlanLabel()}. Remova um produto da loja ou escolha outro plano.";
    }

    public function destroy(Request $request, $id)
    {
        $ad = Ad::with(['images', 'variations'])->findOrFail($id);
        $this->authorizeAdManagement($ad);
        $files = $ad->images->pluck('image_path')
            ->push($ad->logo)
            ->push($ad->banner)
            ->merge($ad->variations->pluck('image'))
            ->filter()
            ->unique();

        $ad->delete();

        $files->each(fn ($path) => $this->deletePublicFile($path));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Anúncio removido com sucesso!',
                'ad_id' => (int) $ad->id,
            ]);
        }

        return redirect()->route('user.panel')->with('success', 'Anúncio removido com sucesso!');
    }

    private function authorizeAdManagement(Ad $ad): void
    {
        $user = request()->user();

        abort_unless(
            $user && ($user->role === 'admin' || $ad->user_id === $user->id),
            403
        );
    }

    private function deletePublicFile(?string $path): void
    {
        if (! $path || str_contains($path, '://')) {
            return;
        }

        File::delete(public_path(ltrim($path, '/')));
    }

    private function deletePublicFileIfUnused(?string $path): void
    {
        if (! $path) {
            return;
        }

        $isStillUsed = Ad::where('logo', $path)
            ->orWhere('banner', $path)
            ->exists()
            || AdImage::where('image_path', $path)->exists();
        $isStillUsed = $isStillUsed || ProductVariation::where('image', $path)->exists();

        if (! $isStillUsed) {
            $this->deletePublicFile($path);
        }
    }

    private function productCommerceRules(?Ad $ad = null): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('ads', 'sku')->ignore($ad?->id)],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'track_stock' => ['nullable', 'boolean'],
            'allow_backorders' => ['nullable', 'boolean'],
            'minimum_quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'technical_specs_text' => ['nullable', 'string', 'max:10000'],
            'video_url' => ['nullable', 'url:http,https', 'max:255'],
            'variations' => ['nullable', 'array', 'max:30'],
            'variations.*.id' => ['nullable', 'integer'],
            'variations.*.name' => ['nullable', 'string', 'max:150'],
            'variations.*.sku' => ['nullable', 'string', 'max:100'],
            'variations.*.price' => ['nullable', 'numeric', 'min:0'],
            'variations.*.price_adjustment' => ['nullable', 'numeric'],
            'variations.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
            'variations.*.track_stock' => ['nullable', 'boolean'],
            'variations.*.active' => ['nullable', 'boolean'],
            'variations.*.image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'addons' => ['nullable', 'array', 'max:30'],
            'addons.*.id' => ['nullable', 'integer'],
            'addons.*.name' => ['nullable', 'string', 'max:150'],
            'addons.*.price' => ['nullable', 'numeric', 'min:0'],
            'addons.*.active' => ['nullable', 'boolean'],
        ];
    }

    private function productCommerceData(Request $request, ?Ad $ad = null): array
    {
        $technicalSpecs = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('technical_specs_text')))
            ->map(function ($line) {
                [$label, $value] = array_pad(explode(':', $line, 2), 2, null);

                return filled($label) && filled($value)
                    ? [trim($label) => trim($value)]
                    : [];
            })
            ->collapse()
            ->all();

        return [
            'sku' => $request->input('sku') ?: null,
            'sale_price' => $request->filled('sale_price') ? $request->input('sale_price') : null,
            'stock_quantity' => $request->integer('stock_quantity', $ad?->stock_quantity ?? 0),
            'low_stock_threshold' => $request->integer('low_stock_threshold', $ad?->low_stock_threshold ?? 5),
            'track_stock' => $request->boolean('track_stock'),
            'allow_backorders' => $request->boolean('allow_backorders'),
            'minimum_quantity' => $request->integer('minimum_quantity', $ad?->minimum_quantity ?? 1),
            'technical_specs' => $request->has('technical_specs_text')
                ? $technicalSpecs
                : ($ad?->technical_specs ?? []),
            'video_url' => $request->has('video_url')
                ? ($request->input('video_url') ?: null)
                : $ad?->video_url,
        ];
    }

    private function syncProductOptions(Ad $ad, Request $request): void
    {
        $variationIds = [];
        foreach ($request->input('variations', []) as $index => $variationData) {
            if (blank($variationData['name'] ?? null)) {
                continue;
            }
            $existingVariation = filled($variationData['id'] ?? null)
                ? $ad->variations()->find($variationData['id'])
                : null;
            $variationImage = $existingVariation?->image;
            if ($request->hasFile("variations.{$index}.image")) {
                $variationImage = ImageOptimizer::convertToWebp(
                    $request->file("variations.{$index}.image"),
                    'variations'
                ) ?: $variationImage;
            }
            $variation = $ad->variations()->updateOrCreate(
                ['id' => $variationData['id'] ?? null],
                [
                    'name' => trim($variationData['name']),
                    'sku' => filled($variationData['sku'] ?? null) ? trim($variationData['sku']) : null,
                    'price' => filled($variationData['price'] ?? null) ? $variationData['price'] : null,
                    'price_adjustment' => $variationData['price_adjustment'] ?? 0,
                    'stock_quantity' => $variationData['stock_quantity'] ?? 0,
                    'track_stock' => filter_var($variationData['track_stock'] ?? true, FILTER_VALIDATE_BOOL),
                    'active' => filter_var($variationData['active'] ?? true, FILTER_VALIDATE_BOOL),
                    'image' => $variationImage,
                ]
            );
            if ($existingVariation?->image && $existingVariation->image !== $variationImage) {
                $this->deletePublicFileIfUnused($existingVariation->image);
            }
            $variationIds[] = $variation->id;
        }
        $ad->variations()
            ->when($variationIds, fn ($query) => $query->whereNotIn('id', $variationIds))
            ->update(['active' => false]);

        $addonIds = [];
        foreach ($request->input('addons', []) as $addonData) {
            if (blank($addonData['name'] ?? null)) {
                continue;
            }
            $addon = $ad->addons()->updateOrCreate(
                ['id' => $addonData['id'] ?? null],
                [
                    'name' => trim($addonData['name']),
                    'price' => $addonData['price'] ?? 0,
                    'active' => filter_var($addonData['active'] ?? true, FILTER_VALIDATE_BOOL),
                ]
            );
            $addonIds[] = $addon->id;
        }
        $ad->addons()
            ->when($addonIds, fn ($query) => $query->whereNotIn('id', $addonIds))
            ->update(['active' => false]);
    }

    private function normalizePrice(mixed $price): ?float
    {
        if ($price === null || $price === '') {
            return null;
        }

        if (is_int($price) || is_float($price)) {
            return (float) $price;
        }

        $normalized = preg_replace('/[^\d,.\-]/', '', trim((string) $price));

        if ($normalized === '' || $normalized === '-') {
            return null;
        }

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (substr_count($normalized, '.') > 1) {
            $normalized = str_replace('.', '', $normalized);
        } elseif (preg_match('/^\-?\d+\.\d{3}$/', $normalized)) {
            $normalized = str_replace('.', '', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    public function updateCoverPosition(Ad $ad, Request $request)
    {
        $user = $request->user();
        abort_unless($user && ($user->id === $ad->user_id || $user->role === 'admin'), 403, 'Acesso não autorizado.');

        if (! $user->hasPaidPlan()) {
            return response()->json([
                'success' => false,
                'message' => 'O ajuste de posição da capa está disponível a partir dos Planos Pagos.',
            ], 403);
        }

        $validated = $request->validate([
            'cover_position_y' => 'required|integer|min:0|max:100',
        ]);

        $ad->update([
            'cover_position_y' => $validated['cover_position_y'],
        ]);

        return response()->json([
            'success' => true,
            'cover_position_y' => $ad->cover_position_y,
            'message' => 'Posição da capa salva com sucesso!',
        ]);
    }
}
