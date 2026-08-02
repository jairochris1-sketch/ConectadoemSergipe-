@extends('layouts.app')

@section('title', ($moduleTitle ? $moduleTitle . ' em Sergipe - ' : '') . 'Conectado em Sergipe')

@php
    $homePreviewImage = \App\Models\Setting::get('home_social_preview', 'images/logo-hero.png');
    $homePreviewImage = str_starts_with($homePreviewImage, 'http') ? $homePreviewImage : asset($homePreviewImage);
    $homeSocialTitle = $moduleTitle
        ? "{$moduleTitle} em Sergipe - Conectado em Sergipe"
        : 'Conectado em Sergipe | Serviços, lojas e oportunidades locais';
    $homeSocialDescription = $moduleTitle
        ? "Encontre {$moduleTitle} em Aracaju e em todo o estado de Sergipe."
        : 'Encontre prestadores de serviços, lojas, produtos, veículos, imóveis e oportunidades em todo o estado de Sergipe.';
@endphp

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $homeSocialTitle,
        'socialDescription' => $homeSocialDescription,
        'socialUrl' => empty($module) ? route('home') : url()->current(),
        'socialImage' => $homePreviewImage,
    ])
@endpush

@push('styles')
<style>
    /* Hero Carousel Responsivo - Mobile vs Desktop */
    .hero-swiper-slide-responsive {
        min-height: 250px;
        max-height: 300px;
    }
    .hero-slide-container-responsive {
        padding-top: 15px;
        padding-bottom: 60px;
    }
    .hero-search-card-container {
        z-index: 10;
        margin-top: -75px;
        margin-bottom: 24px;
    }
    @media (min-width: 992px) {
        .hero-swiper-slide-responsive {
            min-height: 380px;
            max-height: 480px;
        }
        .hero-slide-container-responsive {
            padding-top: 40px;
            padding-bottom: 80px;
        }
        .hero-search-card-container {
            margin-top: -110px;
            margin-bottom: 30px;
        }
    }
    .hero-search-input-box {
        min-height: 44px;
    }
    @media (min-width: 992px) {
        .hero-search-input-box {
            min-height: 50px;
        }
    }
    /* Bordas visualmente marcantes e destacadas para todos os cards */
    .card, .card-premium {
        border: 1px solid var(--border, rgba(200, 210, 225, 0.85)) !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    html[data-theme="dark"] .card,
    html[data-theme="dark"] .card-premium {
        border: 1px solid rgba(255, 255, 255, 0.18) !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
    }
    .card:hover, .card-premium:hover {
        border-color: #0d6efd !important;
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.18) !important;
    }
</style>
@endpush

@section('content')
<!-- Hero Carousel -->
@if(empty($module))
<div class="row mx-0 mb-3">
    <div class="col-12 px-0">
        <div class="swiper swiper-hero overflow-hidden position-relative">
            <div class="swiper-wrapper">
                @foreach($heroBanners as $index => $banner)
                @php
                    $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner);
                @endphp
                <div class="swiper-slide hero-swiper-slide-responsive d-flex flex-column justify-content-center align-items-center px-3 px-md-5" 
                     style="background: linear-gradient(to right, rgba(10, 15, 30, 0.85) 0%, rgba(10, 15, 30, 0.65) 100%), url('{{ $bannerUrl }}') center/cover no-repeat;">
                    
                    <div class="container hero-slide-container-responsive position-relative h-100 d-flex flex-column justify-content-center text-start">
                        <div class="d-flex justify-content-between align-items-start w-100">
                            <div>
                                <h1 class="text-white fw-bold mb-2" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: clamp(1.8rem, 4vw, 2.8rem);">
                                    Encontre tudo em Sergipe.
                                </h1>
                                <p class="text-light opacity-75 mb-0" style="max-width: 650px; text-shadow: 0 1px 3px rgba(0,0,0,0.5); font-size: clamp(0.9rem, 2vw, 1.25rem);">
                                    Produtos, serviços, imóveis, veículos, empregos e muito mais perto de você.
                                </p>
                            </div>
                            <!-- Badge Desktop/Tablet -->
                            <div class="d-none d-md-flex align-items-center rounded-4 px-3 py-2 border border-secondary border-opacity-50 ms-3" style="background: rgba(20, 25, 45, 0.65); backdrop-filter: blur(8px);">
                                <div class="bg-primary bg-opacity-25 rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-users text-primary fs-4"></i>
                                </div>
                                <div class="text-start text-nowrap">
                                    <strong class="text-white d-block lh-1">+ 50 mil usuários</strong>
                                    <small class="text-light opacity-75" style="font-size: 0.75rem;">conectados em todo o estado</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

@if($module === 'real_estate')
    @include('partials.real_estate_hero')
@endif

@if($module === 'vehicles')
    @include('partials.vehicles_hero')
@endif

<!-- Container Busca Rápida Responsiva -->
@if($module !== 'real_estate' && $module !== 'vehicles')
<div class="container position-relative hero-search-card-container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="rounded-4 shadow-lg p-2 p-md-3 p-xl-4 mx-auto" style="background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, 0.15);">
                <form
                    id="home-search-form"
                    action="{{ route('home') }}"
                    method="GET"
                    data-suggestions-url="{{ route('search.suggestions') }}"
                    class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3 w-100 mb-2 mb-md-3"
                >
                    <input type="hidden" id="home-search-module-value" name="module" value="{{ $module }}">
                    <input type="hidden" id="home-search-service-category-value" name="service_category" value="">

                    <!-- Campo Pesquisa -->
                    <div class="position-relative d-flex align-items-center bg-white rounded-3 px-3 py-1 py-md-2 w-100 hero-search-input-box" style="flex: 2.5;">
                        <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
                        <input
                            id="home-search-query"
                            class="form-control bg-transparent border-0 shadow-none p-0 text-dark"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="O que você procura?"
                            autocomplete="off"
                        >
                        <button type="button" id="home-search-microphone" class="btn btn-link text-muted p-0 ms-2 text-decoration-none" title="Buscar por voz">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <div id="home-search-suggestions" class="quick-search-suggestions" hidden></div>
                    </div>

                    <!-- Linha Mobile: Cidade & Categoria -->
                    <div class="d-flex gap-2 w-100" style="flex: 3;">
                        <!-- Cidade com botão GPS -->
                        <div class="position-relative d-flex align-items-center bg-white rounded-3 px-2 px-md-3 py-1 py-md-2 w-50 hero-search-input-box">
                            <i class="fa-solid fa-location-dot text-primary me-1 flex-shrink-0"></i>
                            <select id="home-search-city" name="city" class="form-select bg-transparent border-0 shadow-none p-0 text-dark fw-semibold text-truncate me-1" style="font-size: 0.84rem; max-width: calc(100% - 36px);">
                                <option value="" {{ empty($city) ? 'selected' : '' }}>Todas as cidades</option>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="home-use-location" class="btn btn-primary btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 28px; height: 28px;" title="Detectar minha localização GPS atual" aria-label="Usar minha localização">
                                <i class="fa-solid fa-location-crosshairs" style="font-size: 0.8rem;"></i>
                                <span data-location-button-label class="visually-hidden">Usar minha localização</span>
                            </button>
                        </div>

                        <!-- Categoria -->
                        <div class="position-relative d-flex align-items-center bg-white rounded-3 px-2 px-md-3 py-1 py-md-2 w-50 hero-search-input-box">
                            <i class="fa-solid fa-table-cells-large text-muted me-2"></i>
                            <select id="home-search-category-filter" name="category" class="form-select bg-transparent border-0 shadow-none p-0 text-dark fw-semibold" style="font-size: 0.88rem;">
                                <option value="">Todas categorias</option>
                                <optgroup label="Anúncios">
                                    <option value="real_estate" {{ $module === 'real_estate' ? 'selected' : '' }}>Imóveis</option>
                                    <option value="products" {{ $module === 'products' ? 'selected' : '' }}>Produtos</option>
                                    <option value="vehicles" {{ $module === 'vehicles' ? 'selected' : '' }}>Veículos</option>
                                    <option value="jobs" {{ $module === 'jobs' ? 'selected' : '' }}>Empregos</option>
                                    <option value="agro" {{ $module === 'agro' ? 'selected' : '' }}>Agro</option>
                                </optgroup>
                                <optgroup label="Serviços">
                                    @foreach($serviceSearchCategories as $serviceCategory)
                                        <option value="service:{{ $serviceCategory['name'] }}">{{ $serviceCategory['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- Botão Buscar -->
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 w-100 hero-search-input-box" style="flex: 1; background-color: #0d6efd; border: none;">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Buscar
                    </button>
                </form>

                <!-- Status de Localização e Voz -->
                <div class="d-flex flex-wrap gap-2 px-1 mb-2">
                    <div id="home-location-status" class="quick-search-location-status small text-light opacity-90">
                        @if(!empty($city))
                            <i class="fa-solid fa-location-dot text-success me-1"></i> Cidade ativa: <strong>{{ $city }}</strong>
                        @endif
                    </div>
                    <div id="home-voice-status" class="quick-search-voice-status small text-light opacity-90" hidden></div>
                </div>

                <!-- Chips de Categoria Horizontal (Rolagem Suave no Mobile) -->
                <div class="d-flex align-items-center gap-3 gap-md-4 pt-2 px-1 border-top border-secondary border-opacity-25 overflow-x-auto text-nowrap scrollbar-none" style="scrollbar-width: none;">
                    <a href="{{ route('module.products') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-tag text-primary"></i> Produtos
                    </a>
                    <a href="{{ route('module.real_estate') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-building text-primary"></i> Imóveis
                    </a>
                    <a href="{{ route('module.vehicles') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-car text-primary"></i> Veículos
                    </a>
                    <a href="{{ route('module.services') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-wrench text-primary"></i> Serviços
                    </a>
                    <a href="{{ route('module.jobs') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-briefcase text-primary"></i> Empregos
                    </a>
                    <a href="{{ route('module.agro') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-leaf text-primary"></i> Agro
                    </a>
                    <a href="{{ route('stores.index') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-ellipsis text-primary"></i> Ver todas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Section Destaques para você (Carrossel em movimento) + Profissionais em destaque -->
@if(!$isSearch && empty($module))
<div class="container mb-5">
    <div class="row g-4">
        <!-- Coluna Esquerda: Destaques para você (Swiper Slider em Movimento) -->
        <div class="col-12 col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                    <i class="fa-solid fa-fire text-danger"></i> Destaques para você
                </h4>
                <a href="{{ route('home') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver todos os destaques <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <style>
                .swiper-marquee-esteira .swiper-wrapper {
                    -webkit-transition-timing-function: linear !important;
                    -o-transition-timing-function: linear !important;
                    transition-timing-function: linear !important;
                }
            </style>
            <div class="position-relative overflow-hidden">
                <div class="swiper swiper-featured-ads swiper-marquee-esteira rounded-3 p-1">
                    <div class="swiper-wrapper">
                        @php
                            $loopAds = count($recentAds) < 8 ? $recentAds->concat($recentAds) : $recentAds;
                        @endphp
                        @foreach($loopAds as $ad)
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                                <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                                    <span class="badge bg-success position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Destaque</span>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                        <i class="fa-regular fa-bookmark text-primary"></i>
                                    </button>
                                    @if($ad->card_image)
                                        <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                            <i class="fa-solid fa-tag fs-2"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title fw-bold text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                        </div>
                                        <div>
                                            <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : 'Sob consulta' }}</strong>
                                            <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Profissionais em destaque -->
        <div class="col-12 col-lg-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                    <i class="fa-solid fa-users-gear text-primary"></i> Profissionais em destaque
                </h4>
                <a href="{{ route('module.services') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver todos <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="d-flex flex-column gap-2">
                @foreach($serviceProviders->take(4) as $provider)
                <div class="p-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between border" style="background: var(--card);">
                    <a href="{{ route('provider.show', $provider->slug) }}" class="d-flex align-items-center gap-3 text-decoration-none text-dark flex-grow-1 overflow-hidden me-2">
                        @if($provider->card_image)
                            <img src="{{ asset($provider->card_image) }}" class="rounded-circle flex-shrink-0" width="44" height="44" style="object-fit: cover;" alt="{{ $provider->title }}">
                        @else
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm fw-bold flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-0 text-truncate" style="font-size: 0.85rem;">{{ $provider->title }}</h6>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.73rem;">{{ $provider->display_category ?? 'Serviço profissional' }}</small>
                            <small class="text-warning fw-bold" style="font-size: 0.7rem;">⭐ 4,9 (128) <span class="text-muted ms-1"><i class="fa-solid fa-location-dot"></i> {{ $provider->city ?? 'Aracaju, SE' }}</span></small>
                        </div>
                    </a>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="https://wa.me/5579999999999" target="_blank" class="btn btn-success btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="{{ route('provider.show', $provider->slug) }}" class="btn btn-primary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Ligar"><i class="fa-solid fa-phone"></i></a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Section 2: 🏠 Imóveis em Sergipe -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
            <i class="fa-solid fa-house text-primary"></i> Imóveis em Sergipe
        </h4>
        <a href="{{ route('module.real_estate') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todos os imóveis <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="position-relative">
        <div class="swiper swiper-category-ads rounded-3 p-1">
            <div class="swiper-wrapper">
                @php
                    $displayRealEstate = count($realEstateAds) ? $realEstateAds : $recentAds->where('module', 'real_estate')->take(6);
                @endphp
                @foreach($displayRealEstate as $ad)
                <div class="swiper-slide">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                            <span class="badge bg-primary position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Imóvel</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                <i class="fa-regular fa-bookmark text-primary"></i>
                            </button>
                            @if($ad->card_image)
                                <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                            @else
                                <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                    <i class="fa-solid fa-house fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                </div>
                                <div>
                                    <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : 'Sob consulta' }}</strong>
                                    <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <div class="swiper-pagination swiper-cat-pagination mt-2 position-relative"></div>
    </div>
</div>

<!-- Section 3: 🚗 Veículos em Destaque -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
            <i class="fa-solid fa-car text-primary"></i> Veículos em Destaque
        </h4>
        <a href="{{ route('module.vehicles') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todos os veículos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="position-relative">
        <div class="swiper swiper-category-ads rounded-3 p-1">
            <div class="swiper-wrapper">
                @php
                    $displayVehicles = count($vehicleAds) ? $vehicleAds : $recentAds->where('module', 'vehicles')->take(6);
                @endphp
                @foreach($displayVehicles as $ad)
                <div class="swiper-slide">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                            <span class="badge bg-info text-dark position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Veículo</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                <i class="fa-regular fa-bookmark text-primary"></i>
                            </button>
                            @if($ad->card_image)
                                <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                            @else
                                <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                    <i class="fa-solid fa-car fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                </div>
                                <div>
                                    <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : 'Sob consulta' }}</strong>
                                    <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <div class="swiper-pagination swiper-cat-pagination mt-2 position-relative"></div>
    </div>
</div>

<!-- Section 4: 🏷️ Produtos & Eletrônicos -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
            <i class="fa-solid fa-tag text-primary"></i> Produtos & Eletrônicos
        </h4>
        <a href="{{ route('module.products') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todos os produtos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="position-relative">
        <div class="swiper swiper-category-ads rounded-3 p-1">
            <div class="swiper-wrapper">
                @php
                    $displayProducts = count($productAds) ? $productAds : $recentAds->where('module', 'products')->take(6);
                @endphp
                @foreach($displayProducts as $ad)
                <div class="swiper-slide">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Produto</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                <i class="fa-regular fa-bookmark text-primary"></i>
                            </button>
                            @if($ad->card_image)
                                <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                            @else
                                <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                    <i class="fa-solid fa-tag fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                </div>
                                <div>
                                    <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : 'Sob consulta' }}</strong>
                                    <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <div class="swiper-pagination swiper-cat-pagination mt-2 position-relative"></div>
    </div>
</div>

<!-- Section 5: 💼 Empregos & Agronegócio + Bloco de Planos -->
<div class="container mb-5">
    <div class="row g-4">
        <!-- Esquerda: Empregos e Agro em Swiper -->
        <div class="col-12 col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
                    <i class="fa-solid fa-briefcase text-primary"></i> Empregos & Agro
                </h4>
                <a href="{{ route('module.jobs') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver oportunidades <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="position-relative">
                <div class="swiper swiper-category-ads rounded-3 p-1">
                    <div class="swiper-wrapper">
                        @php
                            $displayJobAgro = count($jobAgroAds) ? $jobAgroAds : $recentAds->whereIn('module', ['jobs', 'agro'])->take(6);
                        @endphp
                        @foreach($displayJobAgro as $ad)
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                                <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">{{ strtoupper($ad->module) }}</span>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                        <i class="fa-regular fa-bookmark text-primary"></i>
                                    </button>
                                    @if($ad->card_image)
                                        <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                            <i class="fa-solid fa-briefcase fs-2"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title fw-bold text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                        </div>
                                        <div>
                                            <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : 'Sob consulta' }}</strong>
                                            <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="swiper-pagination swiper-cat-pagination mt-2 position-relative"></div>
            </div>
        </div>

        <!-- Direita: Card Quer Anunciar (Planos de Anúncio) -->
        <div class="col-12 col-lg-4">
            <div class="p-4 rounded-4 shadow-sm border h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%); border: 1px solid rgba(13, 110, 253, 0.2) !important;">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-1 rounded-pill small fw-bold">Planos de Anúncio</span>
                    <h5 class="fw-bold text-dark mb-2">Quer anunciar sua empresa ou produto?</h5>
                    <p class="text-muted small mb-4">Escolha o plano ideal para você e alcance milhares de clientes em todo o estado de Sergipe.</p>
                    <a href="{{ route('page.plans') }}" class="btn btn-primary fw-bold w-100 rounded-3 py-2 mb-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-gem"></i> Conheça nossos Planos
                    </a>
                </div>
                <div class="d-flex flex-column gap-2 small text-secondary border-top pt-3 border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> <strong>Planos Prata, Ouro e Diamante</strong></div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Destaque no topo das buscas</div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Botão direto de WhatsApp e Ligação</div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Suporte prioritário dedicado</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Explore anúncios por cidade -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem;">Explore anúncios por cidade</h4>
        <a href="{{ route('home') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todas as cidades <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3 overflow-x-auto text-nowrap pb-2 scrollbar-none">
        @foreach(\App\Core\SergipeCities::getAll() as $cityName)
        <a href="{{ route('home', ['city' => $cityName]) }}" class="text-decoration-none text-dark text-center d-flex flex-column align-items-center" style="width: 80px;">
            <div class="rounded-circle shadow-sm mb-2 overflow-hidden border border-2 border-white" style="width: 64px; height: 64px; background: linear-gradient(135deg, #0d6efd 0%, #00d2ff 100%);">
                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold fs-5">
                    {{ strtoupper(substr($cityName, 0, 1)) }}
                </div>
            </div>
            <small class="fw-semibold text-truncate w-100" style="font-size: 0.75rem;">{{ $cityName }}</small>
        </a>
        @endforeach
    </div>
</div>

<!-- Section Banner do Aplicativo -->
<div class="container mb-5">
    <div class="rounded-4 p-4 p-md-5 text-white position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(255, 255, 255, 0.15);">
        <div class="row align-items-center">
            <div class="col-12 col-md-7 mb-4 mb-md-0 z-1">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary bg-opacity-25 p-2 rounded-3 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-mobile-screen-button fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-white" style="font-size: clamp(1.2rem, 3vw, 1.8rem);">Leve o Conectado em Sergipe com você.</h3>
                </div>
                <p class="text-light opacity-75 mb-4" style="max-width: 500px;">Baixe nosso app e encontre oportunidades onde estiver.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#" class="btn btn-dark btn-lg border border-secondary border-opacity-50 rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                        <i class="fa-brands fa-google-play fs-3 text-success"></i>
                        <div class="text-start lh-1">
                            <small class="d-block text-muted" style="font-size: 0.65rem;">DISPONÍVEL NO</small>
                            <strong class="text-white small">Google Play</strong>
                        </div>
                    </a>
                    <a href="#" class="btn btn-dark btn-lg border border-secondary border-opacity-50 rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                        <i class="fa-brands fa-apple fs-3 text-white"></i>
                        <div class="text-start lh-1">
                            <small class="d-block text-muted" style="font-size: 0.65rem;">BAIXAR NA</small>
                            <strong class="text-white small">App Store</strong>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-5 text-center d-none d-md-block z-1">
                <div class="position-relative d-inline-block">
                    <i class="fa-solid fa-mobile-retro text-primary opacity-50" style="font-size: 10rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Resultados da Busca / Categoria -->
@if($isSearch)
<div class="container mt-4 mb-5" id="resultados-busca">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">
                        @if($hasActiveFilters)
                            <i class="fa-solid fa-magnifying-glass text-primary me-2"></i>
                            Resultados da Pesquisa
                        @else
                            <i class="fa-solid {{ $module === 'real_estate' ? 'fa-house' : ($module === 'vehicles' ? 'fa-car' : 'fa-list-ul') }} text-primary me-2"></i>
                            {{ $moduleTitle ? $moduleTitle . ' em Sergipe' : 'Todos os Anúncios' }}
                        @endif
                    </h3>
                    <p class="text-muted mb-0">
                        @if($hasActiveFilters)
                            Encontrados <strong>{{ count($searchResults) }}</strong> resultado(s) {{ !empty($city) ? 'em ' . $city : 'no estado de Sergipe' }}
                        @else
                            Mostrando <strong>{{ count($searchResults) }}</strong> {{ $moduleTitle ? strtolower($moduleTitle) : 'anúncio(s)' }} disponível(eis) {{ !empty($city) ? 'em ' . $city : 'no estado de Sergipe' }}
                        @endif
                    </p>
                </div>
                @if($hasActiveFilters)
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-xmark me-1"></i> Limpar Filtros</a>
                @endif
            </div>

            @if(count($searchResults) === 0)
                <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4">
                    <i class="fa-solid fa-folder-open text-muted fs-1 mb-3"></i>
                    <h5 class="fw-bold text-dark">Nenhum anúncio encontrado</h5>
                    <p class="text-muted small">Tente buscar por outras palavras-chave ou alterar a cidade selecionada.</p>
                </div>
            @else
                <div class="row g-3 g-md-4">
                    @foreach($searchResults as $item)
                    <div class="col-6 col-md-6 col-lg-3">
                        <a href="{{ $item->module === 'services' ? route('provider.show', $item->slug) : route('ad.show', $item->slug) }}" class="text-decoration-none text-dark">
                            <div class="card card-premium h-100 border-0 rounded-4 shadow-sm overflow-hidden">
                                <div class="card-img-wrapper position-relative">
                                    @if($item->card_image)
                                        <img src="{{ asset($item->card_image) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 160px; object-fit: cover;">
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-dark bg-opacity-25 text-muted" style="height: 160px;">
                                            <i class="fa-solid {{ $item->module === 'real_estate' ? 'fa-house' : ($item->module === 'vehicles' ? 'fa-car' : 'fa-tag') }} fs-1 text-primary"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        @php
                                            $moduleBadges = [
                                                'services' => 'PERFIL PROFISSIONAL',
                                                'real_estate' => 'IMÓVEIS',
                                                'vehicles' => 'VEÍCULOS',
                                                'products' => 'PRODUTOS',
                                                'jobs' => 'EMPREGOS',
                                                'agro' => 'AGRO',
                                            ];
                                        @endphp
                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-2 py-1 rounded-pill small fw-semibold">
                                            {{ $moduleBadges[$item->module] ?? strtoupper($item->module) }}
                                        </span>
                                        <h5 class="card-title fw-bold fs-6 text-truncate mb-1">{{ $item->title }}</h5>
                                        <p class="card-text text-muted small text-truncate">{{ $item->city }}</p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        @if($item->module === 'services')
                                            <span class="fw-bold text-primary small">Ver perfil profissional</span>
                                        @else
                                            <span class="fw-bold text-primary fs-5">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endif

</div>
@endsection

@push('scripts')
<script>
    @if(empty($module))
    const swiperHero = new Swiper('.swiper-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
    });

    const swiperFeatured = new Swiper('.swiper-featured-ads', {
        slidesPerView: 2,
        spaceBetween: 10,
        loop: true,
        speed: 6000,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
            pauseOnMouseEnter: false,
        },
        breakpoints: {
            576: { slidesPerView: 2, spaceBetween: 12 },
            768: { slidesPerView: 3, spaceBetween: 14 },
            992: { slidesPerView: 4, spaceBetween: 14 },
        },
    });

    document.querySelectorAll('.swiper-category-ads').forEach((el) => {
        const pagEl = el.parentElement.querySelector('.swiper-cat-pagination');
        new Swiper(el, {
            slidesPerView: 2,
            spaceBetween: 10,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            pagination: pagEl ? { el: pagEl, clickable: true } : false,
            breakpoints: {
                576: { slidesPerView: 2, spaceBetween: 12 },
                768: { slidesPerView: 3, spaceBetween: 14 },
                992: { slidesPerView: 4, spaceBetween: 14 },
            },
        });
    });
    @endif

    @if($module === 'real_estate')
    const swiperRealEstateHero = new Swiper('.swiper-real-estate-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.real-estate-swiper-next', prevEl: '.real-estate-swiper-prev' }
    });
    @endif

    @if($module === 'vehicles')
    const swiperVehiclesHero = new Swiper('.swiper-vehicles-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.vehicles-swiper-next', prevEl: '.vehicles-swiper-prev' }
    });
    @endif

    (() => {
        const storageKey = 'conectado-search-filter';
        const searchForm = document.getElementById('home-search-form');
        const categoryFilter = document.getElementById('home-search-category-filter');
        const moduleValue = document.getElementById('home-search-module-value');
        const serviceCategoryValue = document.getElementById('home-search-service-category-value');
        const queryInput = document.getElementById('home-search-query');
        const citySelect = document.getElementById('home-search-city');
        const microphoneButton = document.getElementById('home-search-microphone');
        const voiceStatus = document.getElementById('home-voice-status');
        const suggestionsBox = document.getElementById('home-search-suggestions');
        const locationButton = document.getElementById('home-use-location');
        const locationStatus = document.getElementById('home-location-status');
        const locationButtonLabel = locationButton?.querySelector('[data-location-button-label]');
        const locationButtonDetail = locationButton?.querySelector('[data-location-button-detail]');
        const locationStoreEndpoint = @json(route('location.store'));
        const locationDestroyEndpoint = @json(route('location.destroy'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        let locationPreferenceEnabled = @json((bool) session('location_filter.enabled', false));
        const municipalityCoordinates = @json(\App\Core\SergipeCities::getCoordinates());
        const quickNav = document.querySelector('.quick-search-model-one-nav');
        const quickNavPages = quickNav
            ? Array.from(quickNav.querySelectorAll('[data-quick-nav-page]'))
            : [];

        if (
            !searchForm
            || !categoryFilter
            || !moduleValue
            || !serviceCategoryValue
            || !queryInput
            || !citySelect
            || !microphoneButton
            || !voiceStatus
            || !suggestionsBox
            || !locationButton
            || !locationStatus
        ) {
            return;
        }

        const smartSearchSetting = searchForm.dataset.smartSearch;
        let smartSearchEnabled = smartSearchSetting === '1';

        if (smartSearchSetting === 'guest') {
            try {
                smartSearchEnabled = localStorage.getItem('conectado-smart-search-enabled') !== '0';
            } catch (error) {
                smartSearchEnabled = true;
            }
        }

        const readSavedFilter = () => {
            try {
                return localStorage.getItem(storageKey);
            } catch (error) {
                return null;
            }
        };

        const saveFilter = (value) => {
            try {
                localStorage.setItem(storageKey, value);
            } catch (error) {
                // A busca continua funcionando se o navegador bloquear o armazenamento.
            }
        };

        const syncFilterValues = () => {
            const selectedValue = categoryFilter.value;
            moduleValue.value = '';
            serviceCategoryValue.value = '';

            if (selectedValue.startsWith('module:')) {
                moduleValue.value = selectedValue.slice('module:'.length);
            } else if (selectedValue.startsWith('service:')) {
                moduleValue.value = 'services';
                serviceCategoryValue.value = selectedValue.slice('service:'.length);
            }
        };

        const currentModule = moduleValue.value;
        const savedFilter = readSavedFilter();

        if (!currentModule && savedFilter && categoryFilter.querySelector(`option[value="${CSS.escape(savedFilter)}"]`)) {
            categoryFilter.value = savedFilter;
        }

        syncFilterValues();

        let automaticSearchTimer = null;
        const automaticSearchDelay = 20000;
        const scheduleAutomaticSearch = () => {
            if (!smartSearchEnabled) {
                return;
            }

            if (automaticSearchTimer) {
                window.clearTimeout(automaticSearchTimer);
            }

            automaticSearchTimer = window.setTimeout(() => {
                syncFilterValues();
                searchForm.requestSubmit();
            }, automaticSearchDelay);
        };

        categoryFilter.addEventListener('change', () => {
            syncFilterValues();
            saveFilter(categoryFilter.value);
            scheduleAutomaticSearch();
        });

        let applyingDetectedCity = false;

        citySelect.addEventListener('change', () => {
            if (!applyingDetectedCity) {
                if (locationPreferenceEnabled) {
                    fetch(locationDestroyEndpoint, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    }).catch(() => null);
                    locationPreferenceEnabled = false;
                }

                try {
                    localStorage.removeItem('conectado-location-enabled');
                    localStorage.removeItem('conectado-detected-city');
                } catch (error) {
                    // A seleção manual continua funcionando sem armazenamento.
                }

                locationStatus.textContent = '';
                locationStatus.className = 'quick-search-location-status';
            }

            scheduleAutomaticSearch();
        });

        searchForm.addEventListener('submit', () => {
            if (automaticSearchTimer) {
                window.clearTimeout(automaticSearchTimer);
            }

            syncFilterValues();
        });

        let suggestions = [];
        let activeSuggestionIndex = -1;
        let suggestionsTimer = null;
        let suggestionsRequest = null;

        const closeSuggestions = () => {
            suggestionsBox.hidden = true;
            suggestionsBox.replaceChildren();
            queryInput.setAttribute('aria-expanded', 'false');
            activeSuggestionIndex = -1;
        };

        const activateSuggestion = (index) => {
            const suggestionButtons = Array.from(suggestionsBox.querySelectorAll('.quick-search-suggestion'));

            if (!suggestionButtons.length) {
                return;
            }

            activeSuggestionIndex = (index + suggestionButtons.length) % suggestionButtons.length;
            suggestionButtons.forEach((button, buttonIndex) => {
                button.classList.toggle('is-active', buttonIndex === activeSuggestionIndex);
            });
            suggestionButtons[activeSuggestionIndex].scrollIntoView({ block: 'nearest' });
        };

        const openSuggestion = (suggestion) => {
            if (!suggestion?.url) {
                return;
            }

            queryInput.value = suggestion.label;
            window.location.assign(suggestion.url);
        };

        const renderSuggestions = () => {
            suggestionsBox.replaceChildren();
            activeSuggestionIndex = -1;

            if (!suggestions.length) {
                const emptyMessage = document.createElement('p');
                emptyMessage.className = 'quick-search-suggestions-empty';
                emptyMessage.textContent = 'Nenhum resultado próximo encontrado.';
                suggestionsBox.appendChild(emptyMessage);
            } else {
                suggestions.forEach((suggestion, index) => {
                    const button = document.createElement('button');
                    const icon = document.createElement('i');
                    const content = document.createElement('span');
                    const label = document.createElement('strong');
                    const meta = document.createElement('small');

                    button.type = 'button';
                    button.className = 'quick-search-suggestion';
                    button.setAttribute('role', 'option');
                    icon.className = 'fa-solid fa-magnifying-glass';
                    label.textContent = suggestion.label;
                    meta.textContent = suggestion.meta || 'Sugestão';
                    content.append(label, meta);
                    button.append(icon, content);
                    button.addEventListener('mouseenter', () => activateSuggestion(index));
                    button.addEventListener('click', () => openSuggestion(suggestion));
                    suggestionsBox.appendChild(button);
                });
            }

            suggestionsBox.hidden = false;
            queryInput.setAttribute('aria-expanded', 'true');
        };

        const loadSuggestions = () => {
            if (!smartSearchEnabled) {
                closeSuggestions();
                return;
            }

            const searchTerm = queryInput.value.trim();
            if (searchTerm.length < 2) {
                closeSuggestions();
                return;
            }

            if (suggestionsRequest) {
                suggestionsRequest.abort();
            }

            suggestionsRequest = new AbortController();
            const url = new URL(searchForm.dataset.suggestionsUrl, window.location.origin);
            url.searchParams.set('q', searchTerm);

            fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: suggestionsRequest.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Não foi possível carregar as sugestões.');
                    }

                    return response.json();
                })
                .then((data) => {
                    suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
                    renderSuggestions();
                })
                .catch((error) => {
                    if (error.name !== 'AbortError') {
                        closeSuggestions();
                    }
                });
        };

        const scheduleSuggestions = () => {
            if (suggestionsTimer) {
                window.clearTimeout(suggestionsTimer);
            }

            suggestionsTimer = window.setTimeout(loadSuggestions, 250);
        };

        queryInput.addEventListener('focus', loadSuggestions);
        queryInput.addEventListener('input', scheduleSuggestions);
        queryInput.addEventListener('keydown', (event) => {
            if (suggestionsBox.hidden || !suggestions.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activateSuggestion(activeSuggestionIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activateSuggestion(activeSuggestionIndex - 1);
            } else if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                event.preventDefault();
                openSuggestion(suggestions[activeSuggestionIndex]);
            } else if (event.key === 'Escape') {
                closeSuggestions();
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.quick-search-model-one-query')) {
                closeSuggestions();
            }
        });

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const normalizeVoiceTerm = (value) => value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('pt-BR')
            .trim();
        const voiceServiceCategories = Array.from(categoryFilter.options)
            .filter((option) => option.value.startsWith('service:'))
            .map((option) => ({
                option,
                normalizedName: normalizeVoiceTerm(option.textContent),
            }))
            .sort((first, second) => second.normalizedName.length - first.normalizedName.length);
        const findSpokenServiceCategory = (transcript) => {
            const normalizedTranscript = normalizeVoiceTerm(transcript);

            return voiceServiceCategories.find(({ normalizedName }) => (
                normalizedTranscript === normalizedName
                || normalizedTranscript.includes(normalizedName)
            ))?.option ?? null;
        };
        let voiceStatusTimer = null;
        const setVoiceStatus = (message, state = '', autoHide = false) => {
            window.clearTimeout(voiceStatusTimer);
            voiceStatus.textContent = message;
            voiceStatus.className = 'quick-search-voice-status';
            voiceStatus.hidden = !message;

            if (state) {
                voiceStatus.classList.add(`is-${state}`);
            }

            if (message && autoHide) {
                voiceStatusTimer = window.setTimeout(() => {
                    voiceStatus.hidden = true;
                    voiceStatus.textContent = '';
                }, 5000);
            }
        };

        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            let recognitionRunning = false;
            let microphonePermissionConfirmed = false;
            recognition.lang = 'pt-BR';
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            microphoneButton.addEventListener('click', async () => {
                if (!window.isSecureContext) {
                    setVoiceStatus('A busca por voz precisa de uma conexão segura (HTTPS).', 'error');
                    return;
                }

                if (recognitionRunning) {
                    recognition.stop();
                    return;
                }

                closeSuggestions();

                try {
                    if (!microphonePermissionConfirmed && navigator.mediaDevices?.getUserMedia) {
                        setVoiceStatus('Aguardando permissão para usar o microfone...', 'loading');
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        stream.getTracks().forEach((track) => track.stop());
                        microphonePermissionConfirmed = true;
                    }

                    recognition.start();
                } catch (error) {
                    const permissionDenied = error?.name === 'NotAllowedError' || error?.name === 'SecurityError';
                    setVoiceStatus(
                        permissionDenied
                            ? 'O acesso ao microfone está bloqueado. Libere a permissão do site no navegador.'
                            : 'Não foi possível iniciar o microfone. Verifique se ele está disponível.',
                        'error'
                    );
                }
            });
            recognition.addEventListener('start', () => {
                recognitionRunning = true;
                microphoneButton.classList.add('is-listening');
                microphoneButton.setAttribute('aria-label', 'Ouvindo sua busca');
                microphoneButton.title = 'Ouvindo... toque novamente para parar.';
                setVoiceStatus('Ouvindo... diga o que deseja procurar.', 'listening');
            });
            recognition.addEventListener('end', () => {
                recognitionRunning = false;
                microphoneButton.classList.remove('is-listening');
                microphoneButton.setAttribute('aria-label', 'Buscar usando a voz');
                microphoneButton.title = 'Buscar usando a voz';
            });
            recognition.addEventListener('result', (event) => {
                const transcript = event.results[0][0].transcript.trim();

                if (transcript) {
                    const spokenCategory = findSpokenServiceCategory(transcript);

                    if (spokenCategory) {
                        categoryFilter.value = spokenCategory.value;
                        queryInput.value = spokenCategory.textContent.trim();
                        syncFilterValues();
                    } else {
                        queryInput.value = transcript;
                    }

                    closeSuggestions();
                    setVoiceStatus(
                        spokenCategory
                            ? `Categoria reconhecida: “${spokenCategory.textContent.trim()}”. Buscando profissionais...`
                            : `Entendi: “${transcript}”. Buscando...`,
                        'success'
                    );
                    window.setTimeout(() => searchForm.requestSubmit(), 400);
                }
            });
            recognition.addEventListener('error', (event) => {
                const messages = {
                    'not-allowed': 'Permita o acesso ao microfone nas configurações do navegador.',
                    'service-not-allowed': 'A busca por voz foi bloqueada pelo navegador.',
                    'audio-capture': 'Nenhum microfone disponível foi encontrado.',
                    'no-speech': 'Não ouvi nenhuma fala. Toque no microfone e tente novamente.',
                    'network': 'A busca por voz está sem conexão. Verifique a internet e tente novamente.',
                    'aborted': '',
                };
                const message = messages[event.error] ?? 'Não foi possível reconhecer sua voz. Tente novamente.';
                if (message) {
                    setVoiceStatus(message, 'error');
                }
            });
            recognition.addEventListener('nomatch', () => {
                setVoiceStatus('Não consegui entender a fala. Tente novamente mais perto do microfone.', 'error');
            });
        } else {
            microphoneButton.title = 'Busca por voz não disponível neste navegador.';
            microphoneButton.setAttribute('aria-label', microphoneButton.title);
            microphoneButton.addEventListener('click', () => {
                setVoiceStatus('Este navegador não oferece busca por voz. No celular, tente usar o Chrome atualizado.', 'error');
            });
        }

        const setLocationStatus = (message, state = '') => {
            locationStatus.textContent = message;
            locationStatus.className = 'quick-search-location-status';

            if (state) {
                locationStatus.classList.add(`is-${state}`);
            }
        };

        const openLocationSearch = () => {
            queryInput.value = '';
            categoryFilter.value = '';
            moduleValue.value = '';
            serviceCategoryValue.value = '';

            try {
                localStorage.removeItem(storageKey);
                localStorage.removeItem('conectado-location-enabled');
                localStorage.removeItem('conectado-detected-city');
            } catch (error) {
                // A localização continua funcionando sem armazenamento.
            }

            const destination = new URL(searchForm.action, window.location.origin);
            destination.search = '';
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

            if (!response.ok) {
                throw new Error('Não foi possível salvar a localização.');
            }

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

            if (!response.ok) {
                throw new Error('Não foi possível desativar a localização.');
            }

            locationPreferenceEnabled = false;
            const destination = new URL(window.location.href);
            destination.searchParams.delete('city');
            window.location.assign(destination.toString());
        };

        const toRadians = (degrees) => degrees * (Math.PI / 180);

        const distanceInKilometers = (latitudeA, longitudeA, latitudeB, longitudeB) => {
            const earthRadius = 6371;
            const latitudeDistance = toRadians(latitudeB - latitudeA);
            const longitudeDistance = toRadians(longitudeB - longitudeA);
            const calculation = Math.sin(latitudeDistance / 2) ** 2
                + Math.cos(toRadians(latitudeA))
                * Math.cos(toRadians(latitudeB))
                * Math.sin(longitudeDistance / 2) ** 2;

            return 2 * earthRadius * Math.asin(Math.sqrt(calculation));
        };

        const findNearestMunicipality = (latitude, longitude) => {
            let nearest = null;

            Object.entries(municipalityCoordinates).forEach(([name, coordinates]) => {
                const distance = distanceInKilometers(
                    latitude,
                    longitude,
                    Number(coordinates.latitude),
                    Number(coordinates.longitude),
                );

                if (!nearest || distance < nearest.distance) {
                    nearest = { name, distance };
                }
            });

            return nearest;
        };

        const isInsideSergipeArea = (latitude, longitude) => (
            latitude >= -11.65
            && latitude <= -9.45
            && longitude >= -38.35
            && longitude <= -36.35
        );

        const locateCurrentCity = () => {
            if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                setLocationStatus('A localização precisa de uma conexão segura (HTTPS).', 'error');
                return;
            }

            if (!navigator.geolocation) {
                setLocationStatus('Este navegador não oferece localização automática.', 'error');
                locationButton.disabled = true;
                return;
            }

            locationButton.disabled = true;
            locationButton.classList.add('is-loading');
            setLocationStatus('Identificando sua cidade...', 'loading');

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    if (!isInsideSergipeArea(latitude, longitude)) {
                        setLocationStatus('Sua localização parece estar fora de Sergipe. Escolha a cidade manualmente.', 'error');
                        locationButton.disabled = false;
                        locationButton.classList.remove('is-loading');
                        return;
                    }

                    const nearestMunicipality = findNearestMunicipality(latitude, longitude);
                    const matchingOption = nearestMunicipality
                        ? Array.from(citySelect.options).find((option) => option.value === nearestMunicipality.name)
                        : null;

                    if (!nearestMunicipality || !matchingOption) {
                        setLocationStatus('Não foi possível relacionar sua localização a uma cidade.', 'error');
                        locationButton.disabled = false;
                        locationButton.classList.remove('is-loading');
                        return;
                    }

                    applyingDetectedCity = true;
                    citySelect.value = nearestMunicipality.name;
                    applyingDetectedCity = false;

                    try {
                        await storeLocationPreference(nearestMunicipality.name);
                        setLocationStatus(`Localização ativa: ${nearestMunicipality.name}.`, 'success');
                        locationButton.classList.add('is-active');
                        locationButton.setAttribute('aria-pressed', 'true');
                        if (locationButtonLabel) {
                            locationButtonLabel.textContent = 'Desativar localização';
                        }
                        if (locationButtonDetail) {
                            locationButtonDetail.textContent = `Resultados filtrados para ${nearestMunicipality.name}.`;
                        }
                        window.setTimeout(openLocationSearch, 450);
                    } catch (error) {
                        setLocationStatus(error.message, 'error');
                        locationButton.disabled = false;
                        locationButton.classList.remove('is-loading');
                    }
                },
                (error) => {
                    const messages = {
                        1: 'Permissão de localização negada. Você ainda pode escolher a cidade manualmente.',
                        2: 'Sua localização não está disponível agora. Tente novamente.',
                        3: 'A localização demorou demais para responder. Tente novamente.',
                    };

                    setLocationStatus(messages[error.code] || 'Não foi possível identificar sua localização.', 'error');
                    locationButton.disabled = false;
                    locationButton.classList.remove('is-loading');
                },
                {
                    enableHighAccuracy: false,
                    timeout: 12000,
                    maximumAge: 300000,
                },
            );
        };

        locationButton.addEventListener('click', async () => {
            if (locationPreferenceEnabled) {
                locationButton.disabled = true;
                setLocationStatus('Desativando localização...', 'loading');
                try {
                    await disableLocationPreference();
                } catch (error) {
                    setLocationStatus(error.message, 'error');
                    locationButton.disabled = false;
                }
                return;
            }

            locateCurrentCity();
        });

        if (quickNavPages.length) {
            let activeQuickNavPage = Math.floor(Math.random() * quickNavPages.length);
            const activeItemByPage = quickNavPages.map(() => 0);

            const activateQuickNavPage = (pageIndex, advanceItem = false) => {
                activeQuickNavPage = pageIndex % quickNavPages.length;

                quickNavPages.forEach((page, currentPageIndex) => {
                    const isCurrentPage = currentPageIndex === activeQuickNavPage;
                    const pageItems = Array.from(page.querySelectorAll('[data-quick-nav]'));

                    page.hidden = !isCurrentPage;
                    page.classList.toggle('is-active', isCurrentPage);

                    if (isCurrentPage && advanceItem && pageItems.length) {
                        activeItemByPage[currentPageIndex] = (activeItemByPage[currentPageIndex] + 1) % pageItems.length;
                    }

                    pageItems.forEach((item, itemIndex) => {
                        const isHighlighted = isCurrentPage && itemIndex === activeItemByPage[currentPageIndex];
                        item.classList.toggle('active', isHighlighted);

                        if (isHighlighted) {
                            item.setAttribute('aria-current', 'true');
                        } else {
                            item.removeAttribute('aria-current');
                        }
                    });
                });

                quickNav.setAttribute(
                    'aria-label',
                    quickNavPages[activeQuickNavPage].dataset.pageLabel || 'Acesso rápido às categorias'
                );
                quickNav.scrollTo({ left: 0, behavior: 'smooth' });
            };

            requestAnimationFrame(() => activateQuickNavPage(activeQuickNavPage));
            window.setInterval(() => {
                activateQuickNavPage(activeQuickNavPage + 1, true);
            }, 10000);
        }
    })();
</script>
@endpush
