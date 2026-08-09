@extends('layouts.app')

@section('title', 'Lojas on-line de Sergipe - Conectado em Sergipe')

@section('content')
@php
    $currentUserStores = auth()->check()
        ? auth()->user()->stores()->oldest('id')->get()
        : collect();
    $currentUserStore = $currentUserStores->first();
    $canCreateStore = auth()->check() && auth()->user()->canCreateAnotherStore();
    $storeActionUrl = !auth()->check()
        ? route('login')
        : ($currentUserStore
            ? ($canCreateStore ? route('store.create') : route('user.panel'))
            : route('store.create'));
    $storeActionLabel = !$currentUserStore
        ? 'Criar minha loja'
        : ($canCreateStore ? 'Criar outra loja' : 'Minhas lojas');

    $carouselStores = collect($stores->items())->take(6)->map(function($store) {
        $firstAd = $store->ads->first();
        $cover = $store->banner ?: $firstAd?->mainImage?->image_path ?: 'images/placeholder-cover.jpg';
        $logo = $store->logo ?: $store->user?->avatar ?: 'images/logo.png';
        $storeCity = $store->city ?: $store->user?->city ?: 'Sergipe';
        $storeCategory = $store->category ?: $firstAd?->display_category ?: 'Loja';
        
        $products = $store->ads->take(3)->map(function($ad) {
            return $ad->mainImage?->image_path ? asset($ad->mainImage->image_path) : null;
        })->filter()->values();

        return [
            'name' => \Illuminate\Support\Str::limit($store->name, 20),
            'city' => $storeCity,
            'cover' => asset($cover),
            'logo' => asset($logo),
            'category' => \Illuminate\Support\Str::limit($storeCategory, 18),
            'products' => $products
        ];
    })->values();

    $cidadesPath = public_path('Cidades');
    $cityBackgrounds = [];
    if (\Illuminate\Support\Facades\File::exists($cidadesPath)) {
        $files = \Illuminate\Support\Facades\File::files($cidadesPath);
        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $cityBackgrounds[] = asset('Cidades/' . $file->getFilename());
            }
        }
    }
    if (empty($cityBackgrounds)) {
        $cityBackgrounds = [asset('images/default-hero.jpg')];
    }
@endphp

<main class="stores-directory">
    <div class="container stores-directory-container">
        
        <style>
            .swiper-slide-active .store-premium-card {
                transform: scale(1.05) !important;
                z-index: 10;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
            }
            .store-premium-card {
                transform: scale(0.9);
                transition: all 0.4s ease;
                opacity: 0.8;
            }
            .swiper-slide-active .store-premium-card {
                opacity: 1;
            }

            /* Ajustes responsivos para telas de notebook (1366x768) */
            @media (max-width: 1400px) and (min-width: 768px) {
                .stores-hero-redesign {
                    min-height: 280px !important;
                }
                .stores-hero-redesign h1 {
                    font-size: 1.8rem !important;
                    margin-bottom: 0.3rem !important;
                }
                .stores-hero-redesign p {
                    font-size: 0.88rem !important;
                    margin-bottom: 0.75rem !important;
                }
                .stores-hero-redesign .swiper-slide {
                    width: 195px !important;
                }
                .stores-hero-redesign .store-premium-card > div:first-child {
                    height: 80px !important;
                }
                .stores-hero-redesign .store-premium-card img {
                    width: 48px !important;
                    height: 48px !important;
                }
                .stores-hero-redesign .store-premium-card h5 {
                    font-size: 0.78rem !important;
                }
                .stores-hero-redesign .store-premium-card small {
                    font-size: 0.65rem !important;
                }
                .stores-hero-redesign .store-premium-card .rounded-3 {
                    width: 38px !important;
                    height: 38px !important;
                }
                .stores-hero-redesign .col-lg-3 .bg-primary {
                    max-width: 175px !important;
                }
            }

            /* Celular / Dispositivos móveis (mobile < 768px) - Banner e Busca Ultra Alinhados */
            @media (max-width: 767.98px) {
                .stores-hero-redesign {
                    min-height: auto !important;
                    padding: 0.4rem 0.2rem !important;
                    margin-bottom: 0.75rem !important;
                }
                .stores-hero-redesign .container {
                    padding-top: 0.2rem !important;
                    padding-bottom: 0.2rem !important;
                    padding-left: 0.25rem !important;
                    padding-right: 0.25rem !important;
                }
                .stores-hero-redesign .row {
                    --bs-gutter-y: 0.3rem !important;
                }
                .stores-hero-redesign .d-inline-flex.rounded-pill {
                    margin-bottom: 0.15rem !important;
                    padding: 2px 8px !important;
                    font-size: 0.58rem !important;
                }
                .stores-hero-redesign h1 {
                    font-size: 1.15rem !important;
                    margin-bottom: 0 !important;
                    line-height: 1.1 !important;
                }
                .stores-hero-redesign p,
                .stores-hero-redesign .d-flex.flex-wrap.gap-2 {
                    display: none !important;
                }
                .stores-hero-redesign .col-lg-5 {
                    margin-top: 0.1rem !important;
                }
                .stores-hero-redesign .stores-premium-swiper {
                    padding: 0.1rem 0.2rem !important;
                }
                .stores-hero-redesign .swiper-slide {
                    width: 170px !important;
                }
                .stores-hero-redesign .store-premium-card > div:first-child {
                    height: 55px !important;
                }
                .stores-hero-redesign .store-premium-card img {
                    width: 48px !important;
                    height: 48px !important;
                }
                .stores-hero-redesign .store-premium-card h5 {
                    font-size: 0.7rem !important;
                    margin-bottom: 0 !important;
                }
                .stores-hero-redesign .store-premium-card small {
                    font-size: 0.58rem !important;
                    margin-bottom: 0 !important;
                }
                .stores-hero-redesign .store-premium-card .badge {
                    font-size: 0.55rem !important;
                    padding: 1px 5px !important;
                    margin-bottom: 0 !important;
                }                .stores-hero-redesign .store-premium-card .d-flex.justify-content-center.gap-1 {
                    display: flex !important;
                    gap: 3px !important;
                    margin-top: 3px !important;
                }
                .stores-hero-redesign .store-premium-card .rounded-3 {
                    width: 28px !important;
                    height: 28px !important;
                }
                .stores-hero-redesign .swiper-button-prev-stores,
                .stores-hero-redesign .swiper-button-next-stores {
                    display: none !important;
                }
                .stores-hero-redesign .col-lg-3 {
                    margin-top: 0.1rem !important;
                }
                .stores-hero-redesign .col-lg-3 .bg-primary {
                    max-width: 200px !important;
                    width: 100% !important;
                    padding: 0.35rem 0.5rem !important;
                    border-radius: 8px !important;
                }
                .stores-hero-redesign .col-lg-3 .bg-white.rounded-circle,
                .stores-hero-redesign .col-lg-3 p {
                    display: none !important;
                }
                .stores-hero-redesign .col-lg-3 h5 {
                    font-size: 0.75rem !important;
                    margin-bottom: 0.1rem !important;
                }
                .stores-hero-redesign .col-lg-3 a {
                    font-size: 0.65rem !important;
                    padding: 0.18rem 0.5rem !important;
                }

                /* Painel de busca no celular: 100% alinhado e bonito com microfone */
                .stores-search-panel {
                    margin-top: 10px !important;
                    padding: 10px !important;
                    background: #ffffff !important;
                    border-radius: 14px !important;
                    border: 1px solid rgba(0, 0, 0, 0.08) !important;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04) !important;
                }
                .stores-search-main {
                    display: flex !important;
                    align-items: center !important;
                    grid-template-columns: none !important;
                    height: 44px !important;
                    border-radius: 10px !important;
                    border: 1px solid #cbd5e1 !important;
                    overflow: hidden !important;
                    background: #f8fafc !important;
                }
                .stores-search-query {
                    flex: 1 !important;
                    padding: 0 8px !important;
                    gap: 6px !important;
                }
                .stores-search-query input {
                    height: 42px !important;
                    font-size: 0.78rem !important;
                }
                .stores-search-submit {
                    height: 38px !important;
                    margin: 3px !important;
                    padding: 0 14px !important;
                    font-size: 0.78rem !important;
                    border-radius: 8px !important;
                    white-space: nowrap !important;
                }
                .stores-search-controls {
                    display: grid !important;
                    grid-template-columns: 1fr 1fr !important;
                    gap: 8px !important;
                    padding: 8px 0 0 0 !important;
                }
                .stores-near-me,
                .stores-city-select,
                .stores-more-filters {
                    min-height: 38px !important;
                    height: 38px !important;
                    font-size: 0.74rem !important;
                    border-radius: 10px !important;
                    border: 1px solid #cbd5e1 !important;
                    background: #f8fafc !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                }
                .stores-city-select select {
                    height: 36px !important;
                    font-size: 0.74rem !important;
                    padding-right: 22px !important;
                }
                .stores-more-filters {
                    grid-column: 1 / -1 !important;
                    width: 100% !important;
                }

                /* Categoria Cards Compactos no Celular */
                .stores-category-rail {
                    padding: 6px 0 !important;
                    margin-top: 8px !important;
                    gap: 6px !important;
                }
                .stores-category-rail a,
                .stores-category-rail button {
                    min-width: 58px !important;
                    padding: 4px 2px !important;
                    border-radius: 10px !important;
                }
                .stores-category-rail span {
                    width: 28px !important;
                    height: 28px !important;
                    font-size: 0.8rem !important;
                    margin-bottom: 2px !important;
                }
                .stores-category-rail small {
                    font-size: 0.58rem !important;
                    white-space: nowrap !important;
                }
            }

            /* Estilo do botão de busca por voz (Microfone) */
            .stores-voice-btn {
                border: 0;
                background: transparent;
                color: #64748b;
                font-size: 0.95rem;
                padding: 4px 6px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border-radius: 50%;
                transition: all 0.2s ease;
            }
            .stores-voice-btn:hover {
                color: #0d6efd;
                background: rgba(13, 110, 253, 0.08);
            }
            .stores-voice-btn.listening {
                color: #dc3545 !important;
                animation: pulse-mic 1s infinite alternate;
            }
            @keyframes pulse-mic {
                0% { transform: scale(1); }
                100% { transform: scale(1.2); }
            }}
        </style>

        <section class="stores-hero-redesign position-relative overflow-hidden mb-4" style="border-radius: 16px; min-height: 320px; display: flex; align-items: center;">
            <!-- Background Fader Layers (Seamless 2s Crossfade) -->
            <div id="hero-bg-fader-a" class="position-absolute w-100 h-100 top-0 start-0" style="z-index: 1; background-size: cover; background-position: center; transition: opacity 2s ease-in-out; opacity: 1;"></div>
            <div id="hero-bg-fader-b" class="position-absolute w-100 h-100 top-0 start-0" style="z-index: 1; background-size: cover; background-position: center; transition: opacity 2s ease-in-out; opacity: 0;"></div>
            <!-- Overlay (Darkened Navy Blue) -->
            <div class="position-absolute w-100 h-100 top-0 start-0" style="background: linear-gradient(90deg, rgba(10, 25, 52, 0.90) 0%, rgba(13, 35, 72, 0.80) 55%, rgba(15, 45, 90, 0.70) 100%); z-index: 2;"></div>

            <div class="container position-relative py-3 py-lg-4 px-3 px-lg-5" style="z-index: 10;">
                <div class="row align-items-center g-4">
                    <!-- Left: Content & Stats -->
                    <div class="col-lg-4 text-white text-center text-lg-start">
                        <span class="d-inline-flex align-items-center gap-2 rounded-pill px-3 py-1.5 mb-3" style="background: rgba(255, 255, 255, 0.12); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-gem text-warning"></i>
                            <span style="color: #ffffff !important;">VITRINE DE SERGIPE</span>
                        </span>
                        <h1 class="fw-bold mb-2" style="font-size: clamp(2rem, 3.5vw, 2.8rem); line-height: 1.1; letter-spacing: -1px;">Lojas <span>on-line</span><br>em Sergipe</h1>
                        <p class="opacity-75 mb-4 mx-auto mx-lg-0" style="font-size: 1.05rem; max-width: 90%;">Descubra vendedores, ateliês e comércios locais dos 75 municípios sergipanos.</p>
                        
                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-lg-start">
                            <span class="badge bg-dark bg-opacity-50 border border-white border-opacity-10 rounded-pill px-3 py-2 fw-normal"><i class="fa-solid fa-store me-1 opacity-75"></i> {{ $storesCount }} lojas ativas</span>
                            <span class="badge bg-dark bg-opacity-50 border border-white border-opacity-10 rounded-pill px-3 py-2 fw-normal"><i class="fa-solid fa-cube me-1 opacity-75"></i> {{ $productsCount }} produtos</span>
                            <span class="badge bg-dark bg-opacity-50 border border-white border-opacity-10 rounded-pill px-3 py-2 fw-normal"><i class="fa-solid fa-location-dot me-1 opacity-75"></i> 75 municípios</span>
                        </div>
                    </div>

                    <!-- Center: Swiper Cards -->
                    <div class="col-lg-5 px-0 position-relative mt-3 mt-lg-0">
                        <div class="swiper stores-premium-swiper px-4 py-3">
                            <div class="swiper-wrapper">
                                @foreach($carouselStores as $cStore)
                                <div class="swiper-slide" style="width: 220px;">
                                    <div class="store-premium-card bg-white rounded-4 overflow-hidden shadow mx-auto">
                                        <div style="height: 95px; background: url('{{ $cStore['cover'] }}') center/cover;"></div>
                                        <div class="text-center position-relative" style="margin-top: -28px;">
                                            <div class="d-inline-flex bg-white shadow-sm" style="border-radius: 14px !important; padding: 2px !important;">
                                                <img src="{{ $cStore['logo'] }}" style="width: 55px; height: 55px; object-fit: cover; border-radius: 12px !important;">
                                            </div>
                                        </div>
                                        <div class="text-center px-2 pb-3 pt-2">
                                            <h5 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">{{ $cStore['name'] }}</h5>
                                            <small class="text-muted d-block mb-2" style="font-size: 0.7rem;"><i class="fa-solid fa-location-dot text-danger"></i> {{ $cStore['city'] }}</small>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 mb-2" style="font-size: 0.6rem;">{{ $cStore['category'] }}</span>
                                            
                                            <div class="d-flex justify-content-center gap-1 mt-2">
                                                @foreach($cStore['products'] as $prodImg)
                                                    <div class="rounded-3 shadow-sm border border-light" style="width: 45px; height: 45px; background: url('{{ $prodImg }}') center/cover;"></div>
                                                @endforeach
                                                @for($i = count($cStore['products']); $i < 3; $i++)
                                                    <div class="rounded-3 bg-light border border-light d-flex align-items-center justify-content-center text-muted" style="width: 45px; height: 45px;"><i class="fa-solid fa-image opacity-25"></i></div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @if(count($carouselStores) == 0)
                                    <div class="text-white text-center py-5">
                                        <i class="fa-solid fa-store-slash fs-1 mb-3 opacity-50"></i>
                                        <p>Nenhuma loja com foto cadastrada para exibir aqui.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Navigation -->
                        @if(count($carouselStores) > 0)
                            <div class="swiper-button-prev-stores position-absolute top-50 start-0 translate-middle-y z-3 shadow" style="width: 32px; height: 32px; background: rgba(0,0,0,0.6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid rgba(255,255,255,0.2);"><i class="fa-solid fa-chevron-left small"></i></div>
                            <div class="swiper-button-next-stores position-absolute top-50 end-0 translate-middle-y z-3 shadow" style="width: 32px; height: 32px; background: rgba(0,0,0,0.6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid rgba(255,255,255,0.2);"><i class="fa-solid fa-chevron-right small"></i></div>
                        @endif
                    </div>

                    <!-- Right: CTA (Largo reduzido) -->
                    <div class="col-lg-3 mt-3 mt-lg-0 d-flex justify-content-center">
                        <div class="bg-primary rounded-4 p-2.5 text-center text-white shadow position-relative overflow-hidden" style="background: linear-gradient(145deg, #0d6efd, #0b5ed7) !important; border: 1px solid rgba(255,255,255,0.25); max-width: 175px; width: 100%;">
                            <div class="mb-2">
                                <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-shop text-primary" style="font-size: 0.95rem;"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-1" style="font-size: 0.8rem;">Criar Nova Loja</h5>
                            <p class="small opacity-90 mb-2" style="line-height: 1.25; font-size: 0.68rem;">Tenha sua loja on-line e alcance mais clientes em Sergipe.</p>
                            <a href="{{ $storeActionUrl }}" class="btn btn-light rounded-pill w-100 fw-bold text-primary py-1 px-2 d-flex align-items-center justify-content-center gap-1 shadow-sm" style="font-size: 0.72rem;">
                                Criar loja <i class="fa-solid fa-chevron-right" style="font-size: 0.6rem;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="stores-search-panel" aria-label="Busca de lojas">
            <form id="stores-search-form" action="{{ route('stores.index') }}" method="GET">
                <div class="stores-search-main">
                    <label class="stores-search-query">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <span class="visually-hidden">Buscar lojas</span>
                        <input
                            type="search"
                            id="stores-search-input"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Buscar lojas por nome, cidade ou categoria..."
                        >
                        <button type="button" id="stores-voice-search-btn" class="stores-voice-btn" title="Pesquisar por voz" aria-label="Pesquisar por voz">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                    </label>
                    <button type="submit" class="stores-search-submit">Buscar</button>
                </div>

                <div class="stores-search-controls">
                    <button id="stores-near-me" type="button" @class(['stores-near-me', 'is-active' => session('location_filter.enabled', false)]) aria-pressed="{{ session('location_filter.enabled', false) ? 'true' : 'false' }}">
                        <i class="fa-solid fa-location-dot text-danger"></i>
                        <span>{{ session('location_filter.enabled', false) ? 'Desativar localização' : 'Perto de mim' }}</span>
                    </button>

                    <label class="stores-city-select">
                        <span class="visually-hidden">Cidade</span>
                        <select id="stores-city" name="city">
                            <option value="">Todas as cidades</option>
                            @foreach($cities as $cityName)
                                <option value="{{ $cityName }}" @selected($city === $cityName)>{{ $cityName }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </label>

                    <button
                        id="stores-more-filters"
                        type="button"
                        class="stores-more-filters"
                        aria-expanded="{{ $category ? 'true' : 'false' }}"
                        aria-controls="stores-extra-filters"
                    >
                        <i class="fa-solid fa-filter"></i>
                        Mais filtros
                    </button>
                </div>

                <div id="stores-location-status" class="stores-location-status {{ session('location_filter.enabled', false) ? 'is-success' : '' }}" role="status" aria-live="polite">
                    @if(session('location_filter.enabled', false))
                        Localização ativa: {{ session('location_filter.city') }}.
                    @endif
                </div>

                <div id="stores-extra-filters" class="stores-extra-filters" @if(!$category) hidden @endif>
                    <label>
                        <span>Categoria da loja</span>
                        <select name="category">
                            <option value="">Todas as categorias</option>
                            @foreach($storeCategories as $storeCategory)
                                <option value="{{ $storeCategory['name'] }}" @selected($category === $storeCategory['name'])>
                                    {{ $storeCategory['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    @if($q || $city || $category)
                        <a href="{{ route('stores.index') }}">Limpar filtros</a>
                    @endif
                </div>
            </form>

            <nav class="stores-category-rail" aria-label="Categorias de lojas">
                <a href="{{ route('stores.index', array_filter(['q' => $q, 'city' => $city])) }}" class="{{ !$category ? 'active' : '' }}">
                    <span><i class="fa-solid fa-table-cells-large"></i></span>
                    <small>Ver todas</small>
                </a>
                @foreach($storeCategories as $storeCategory)
                    <a
                        href="{{ route('stores.index', array_filter(['q' => $q, 'city' => $city, 'category' => $storeCategory['name']])) }}"
                        class="{{ $category === $storeCategory['name'] ? 'active' : '' }}"
                    >
                        <span><i class="fa-solid {{ $storeCategory['icon'] }}"></i></span>
                        <small>{{ $storeCategory['name'] }}</small>
                    </a>
                @endforeach
                <button type="button" data-open-store-filters>
                    <span><i class="fa-solid fa-ellipsis"></i></span>
                    <small>Ver mais</small>
                </button>
            </nav>
        </section>

        <section class="stores-results" aria-labelledby="stores-results-title">
            <div class="stores-results-heading">
                <div>
                    <h2 id="stores-results-title">{{ $category ? "Lojas de {$category}" : 'Lojas em destaque' }}</h2>
                    <span aria-hidden="true"></span>
                </div>
                <p>{{ $stores->total() }} {{ $stores->total() === 1 ? 'resultado encontrado' : 'resultados encontrados' }}</p>
            </div>

            @if($stores->isEmpty())
                <div class="stores-empty">
                    <i class="fa-solid fa-store-slash"></i>
                    <h3>Nenhuma loja encontrada</h3>
                    <p>Tente outra cidade, categoria ou termo de busca.</p>
                    <a href="{{ route('stores.index') }}">Ver todas as lojas</a>
                </div>
            @else
                <div class="stores-grid">
                    @foreach($stores as $store)
                        @php
                            $firstAd = $store->ads->first();
                            $cover = $store->banner ?: $firstAd?->mainImage?->image_path;
                            $logo = $store->logo ?: $store->user?->avatar ?: 'images/logo.png';
                            $storeCity = $store->city ?: $store->user?->city ?: 'Sergipe';
                            $storeCategory = $store->category
                                ?: $firstAd?->display_category
                                ?: 'Comércio local';
                            $storeReviewsCount = (int) ($store->approved_reviews_count ?? 0);
                            $storeReviewsAverage = (float) ($store->approved_reviews_average ?? 0);
                            $isFeaturedStore = $store->isCurrentlyFeatured();
                        @endphp
                        <article class="store-directory-card {{ $isFeaturedStore ? 'is-featured' : '' }}">
                            <a href="{{ route('store.show', $store->slug) }}" class="store-card-cover" aria-label="Abrir {{ $store->name }}">
                                @if($cover)
                                    <img src="{{ asset($cover) }}" alt="" loading="lazy">
                                @else
                                    <div class="store-card-cover-fallback">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                @endif
                                <span class="store-card-category">{{ $storeCategory }}</span>
                                @if($isFeaturedStore)
                                    <span class="store-card-featured">
                                        <i class="fa-solid fa-star"></i>
                                        Em destaque
                                    </span>
                                @endif
                            </a>

                            <div class="store-card-body">
                                <img
                                    src="{{ asset($logo) }}"
                                    class="store-card-logo"
                                    alt="Logo da {{ $store->name }}"
                                    loading="lazy"
                                >

                                <div class="store-card-title-row">
                                    <h3>
                                        <a href="{{ route('store.show', $store->slug) }}">{{ $store->name }}</a>
                                        <i class="fa-solid fa-circle-check" title="Loja ativa"></i>
                                    </h3>
                                    @if($storeReviewsCount > 0)
                                        <span class="store-card-rating">
                                            <i class="fa-solid fa-star"></i>
                                            {{ number_format($storeReviewsAverage, 1, ',', '.') }}
                                            <small>({{ $storeReviewsCount }})</small>
                                        </span>
                                    @else
                                        <span class="store-card-verified"><i class="fa-solid fa-circle-check"></i> Loja ativa</span>
                                    @endif
                                </div>

                                <p class="store-card-city">
                                    <i class="fa-solid fa-location-dot text-danger"></i>
                                    {{ $storeCity }}/SE
                                </p>

                                <p class="store-card-description">
                                    {{ \Illuminate\Support\Str::limit($store->description ?: 'Conheça os produtos e novidades desta loja.', 92) }}
                                </p>

                                <div class="store-card-actions">
                                    <a href="{{ route('store.show', $store->slug) }}" class="store-card-open">Ver loja</a>
                                    <span><i class="fa-solid fa-cube"></i> {{ $store->active_ads_count }} {{ $store->active_ads_count === 1 ? 'produto' : 'produtos' }}</span>
                                    @if(auth()->id() !== $store->user_id)
                                        @auth
                                            @php
                                                $isFollowingStore = $followedStoreIds->contains($store->id);
                                            @endphp
                                            <button
                                                type="button"
                                                class="store-card-follow {{ $isFollowingStore ? 'is-following' : '' }}"
                                                data-store-follow
                                                data-store-id="{{ $store->id }}"
                                                data-endpoint="{{ route('store.follow.toggle', $store) }}"
                                                data-label-idle="Seguir"
                                                data-label-following="Seguindo"
                                                aria-pressed="{{ $isFollowingStore ? 'true' : 'false' }}"
                                                aria-label="{{ $isFollowingStore ? 'Deixar de seguir' : 'Seguir' }} {{ $store->name }}"
                                                title="{{ $isFollowingStore ? 'Deixar de seguir' : 'Seguir loja' }}"
                                            >
                                                <i class="{{ $isFollowingStore ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                                                <span class="visually-hidden" data-store-follow-label>{{ $isFollowingStore ? 'Seguindo' : 'Seguir' }}</span>
                                                <small data-store-follow-count>{{ $store->followers_count }}</small>
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}" class="store-card-follow" aria-label="Entre para seguir {{ $store->name }}" title="Entre para seguir a loja">
                                                <i class="fa-regular fa-heart"></i>
                                                <small>{{ $store->followers_count }}</small>
                                            </a>
                                        @endauth
                                    @endif
                                    <button
                                        type="button"
                                        class="store-card-share"
                                        data-social-share
                                        data-share-title="{{ $store->name }}"
                                        data-share-text="Conheça a loja {{ $store->name }} no Conectado em Sergipe."
                                        data-share-url="{{ route('store.show', $store->slug) }}"
                                        aria-label="Compartilhar {{ $store->name }}"
                                    >
                                        <i class="fa-solid fa-share-nodes"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($stores->hasPages())
                    <div class="stores-pagination">
                        {{ $stores->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </section>

        <section class="stores-benefits" aria-label="Vantagens das lojas">
            <div><span class="is-green"><i class="fa-solid fa-location-dot"></i></span><p><strong>Lojas locais</strong><small>Comércios de todo Sergipe</small></p></div>
            <div><span><i class="fa-solid fa-store"></i></span><p><strong>Comércio local</strong><small>Negócios de todo Sergipe</small></p></div>
            <div><span><i class="fa-solid fa-truck"></i></span><p><strong>Apoie sua região</strong><small>Fortaleça a economia local</small></p></div>
            <div><span class="is-whatsapp"><i class="fa-brands fa-whatsapp"></i></span><p><strong>Dúvidas? Fale conosco</strong><small>Atendimento pelo WhatsApp</small></p></div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('stores-search-form');
        const citySelect = document.getElementById('stores-city');
        const nearMeButton = document.getElementById('stores-near-me');
        const locationStatus = document.getElementById('stores-location-status');
        const filtersButton = document.getElementById('stores-more-filters');
        const extraFilters = document.getElementById('stores-extra-filters');
        const coordinates = @json(\App\Core\SergipeCities::getCoordinates());
        const locationStoreEndpoint = @json(route('location.store'));
        const locationDestroyEndpoint = @json(route('location.destroy'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        let locationPreferenceEnabled = @json((bool) session('location_filter.enabled', false));

        if (!form || !citySelect || !nearMeButton || !locationStatus || !filtersButton || !extraFilters) {
            return;
        }

        const toggleFilters = (forceOpen = null) => {
            const shouldOpen = forceOpen ?? extraFilters.hidden;
            extraFilters.hidden = !shouldOpen;
            filtersButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        };

        filtersButton.addEventListener('click', () => toggleFilters());
        document.querySelectorAll('[data-open-store-filters]').forEach((button) => {
            button.addEventListener('click', () => {
                toggleFilters(true);
                extraFilters.querySelector('select')?.focus();
            });
        });

        const navigateWithoutCityParameter = () => {
            const destination = new URL(window.location.href);
            destination.searchParams.delete('city');
            window.location.assign(destination.toString());
        };

        const storeLocationPreference = async (city) => {
            const response = await fetch(locationStoreEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ city }),
            });

            if (!response.ok) throw new Error('Não foi possível salvar a localização.');
            locationPreferenceEnabled = true;
        };

        const disableLocationPreference = async () => {
            const response = await fetch(locationDestroyEndpoint, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) throw new Error('Não foi possível desativar a localização.');
            locationPreferenceEnabled = false;
            navigateWithoutCityParameter();
        };

        citySelect.addEventListener('change', () => {
            if (!locationPreferenceEnabled) return;

            fetch(locationDestroyEndpoint, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            }).catch(() => null);
            locationPreferenceEnabled = false;
        });

        const radians = (degrees) => degrees * Math.PI / 180;
        const distance = (latitudeA, longitudeA, latitudeB, longitudeB) => {
            const latitudeDistance = radians(latitudeB - latitudeA);
            const longitudeDistance = radians(longitudeB - longitudeA);
            const value = Math.sin(latitudeDistance / 2) ** 2
                + Math.cos(radians(latitudeA))
                * Math.cos(radians(latitudeB))
                * Math.sin(longitudeDistance / 2) ** 2;

            return 12742 * Math.asin(Math.sqrt(value));
        };

        nearMeButton.addEventListener('click', async () => {
            if (locationPreferenceEnabled) {
                nearMeButton.disabled = true;
                locationStatus.textContent = 'Desativando localização...';
                try {
                    await disableLocationPreference();
                } catch (error) {
                    locationStatus.textContent = error.message;
                    locationStatus.className = 'stores-location-status is-error';
                    nearMeButton.disabled = false;
                }
                return;
            }

            if (!navigator.geolocation) {
                locationStatus.textContent = 'Localização automática indisponível neste navegador.';
                locationStatus.className = 'stores-location-status is-error';
                return;
            }

            nearMeButton.disabled = true;
            locationStatus.textContent = 'Identificando sua cidade...';
            locationStatus.className = 'stores-location-status';

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    if (latitude < -11.65 || latitude > -9.45 || longitude < -38.35 || longitude > -36.35) {
                        locationStatus.textContent = 'Sua localização parece estar fora de Sergipe.';
                        locationStatus.className = 'stores-location-status is-error';
                        nearMeButton.disabled = false;
                        return;
                    }

                    const nearestCity = Object.entries(coordinates).reduce((nearest, [name, point]) => {
                        const currentDistance = distance(
                            latitude,
                            longitude,
                            Number(point.latitude),
                            Number(point.longitude)
                        );

                        return !nearest || currentDistance < nearest.distance
                            ? { name, distance: currentDistance }
                            : nearest;
                    }, null);

                    if (!nearestCity) {
                        locationStatus.textContent = 'Não foi possível identificar sua cidade.';
                        locationStatus.className = 'stores-location-status is-error';
                        nearMeButton.disabled = false;
                        return;
                    }

                    try {
                        await storeLocationPreference(nearestCity.name);
                        citySelect.value = nearestCity.name;
                        locationStatus.textContent = `Localização ativa: ${nearestCity.name}.`;
                        locationStatus.className = 'stores-location-status is-success';
                        window.setTimeout(navigateWithoutCityParameter, 450);
                    } catch (error) {
                        locationStatus.textContent = error.message;
                        locationStatus.className = 'stores-location-status is-error';
                        nearMeButton.disabled = false;
                    }
                },
                () => {
                    locationStatus.textContent = 'Permita o acesso à localização ou escolha a cidade manualmente.';
                    locationStatus.className = 'stores-location-status is-error';
                    nearMeButton.disabled = false;
                },
                { enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 }
            );
        });

        // Background Image Rotation Logic (Seamless 2-Layer Crossfade every 10s)
        const cityBackgrounds = @json($cityBackgrounds);
        if (cityBackgrounds && cityBackgrounds.length > 0) {
            const bgFaderA = document.getElementById('hero-bg-fader-a');
            const bgFaderB = document.getElementById('hero-bg-fader-b');
            let currentBgIdx = 0;
            let activeLayer = bgFaderA;

            if (bgFaderA && bgFaderB) {
                bgFaderA.style.backgroundImage = `url('${cityBackgrounds[0]}')`;
                bgFaderA.style.opacity = '1';
                bgFaderB.style.opacity = '0';

                if (cityBackgrounds.length > 1) {
                    setInterval(() => {
                        currentBgIdx = (currentBgIdx + 1) % cityBackgrounds.length;
                        const nextUrl = `url('${cityBackgrounds[currentBgIdx]}')`;
                        
                        if (activeLayer === bgFaderA) {
                            bgFaderB.style.backgroundImage = nextUrl;
                            bgFaderB.style.opacity = '1';
                            bgFaderA.style.opacity = '0';
                            activeLayer = bgFaderB;
                        } else {
                            bgFaderA.style.backgroundImage = nextUrl;
                            bgFaderA.style.opacity = '1';
                            bgFaderB.style.opacity = '0';
                            activeLayer = bgFaderA;
                        }
                    }, 10000);
                }
            }
        }

        // Initialize Swiper for Premium Stores Carousel
        if (typeof Swiper !== 'undefined') {
            const storesPremiumSwiper = new Swiper('.stores-premium-swiper', {
                effect: 'coverflow',
                grabCursor: true,
                centeredSlides: true,
                slidesPerView: 'auto',
                loop: true,
                coverflowEffect: {
                    rotate: 0,
                    stretch: 0,
                    depth: 100,
                    modifier: 1.5,
                    slideShadows: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next-stores',
                    prevEl: '.swiper-button-prev-stores',
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                }
            });
        }

        // Voice Search Recognition (Pesquisa por Voz)
        const voiceBtn = document.getElementById('stores-voice-search-btn');
        const searchInput = document.getElementById('stores-search-input');
        const searchForm = document.getElementById('stores-search-form');

        if (voiceBtn && searchInput && ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const recognition = new SpeechRecognition();
            recognition.lang = 'pt-BR';
            recognition.continuous = false;
            recognition.interimResults = false;

            voiceBtn.addEventListener('click', () => {
                if (voiceBtn.classList.contains('listening')) {
                    recognition.stop();
                } else {
                    try {
                        recognition.start();
                    } catch (e) {
                        console.error('Speech recognition error:', e);
                    }
                }
            });

            recognition.onstart = () => {
                voiceBtn.classList.add('listening');
                searchInput.placeholder = 'Ouvindo... Fale o nome da loja ou produto!';
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                searchInput.value = transcript;
                if (searchForm) {
                    searchForm.submit();
                }
            };

            recognition.onerror = () => {
                voiceBtn.classList.remove('listening');
                searchInput.placeholder = 'Buscar lojas por nome, cidade ou categoria...';
            };

            recognition.onend = () => {
                voiceBtn.classList.remove('listening');
                searchInput.placeholder = 'Buscar lojas por nome, cidade ou categoria...';
            };
        } else if (voiceBtn) {
            voiceBtn.title = 'Pesquisa por voz indisponível neste navegador';
        }

    })();
</script>
@endpush
