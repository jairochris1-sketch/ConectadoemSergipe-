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
                <div class="swiper-slide d-flex flex-column justify-content-center align-items-center px-4" 
                     style="height: 520px; background: linear-gradient(rgba(10, 15, 30, 0.7), rgba(10, 15, 30, 0.7)), url('{{ $bannerUrl }}') center/cover no-repeat;">
                    
                    <div class="container position-relative h-100 d-flex flex-column justify-content-center text-start" style="margin-top: -60px;">
                        <div class="d-flex justify-content-between align-items-start w-100">
                            <div>
                                <h1 class="text-white fw-bold display-5 mb-2" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                    Encontre tudo em Sergipe.
                                </h1>
                                <p class="text-light fs-5 mb-4 opacity-75" style="max-width: 600px; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                                    Produtos, serviços, imóveis, veículos, empregos e muito mais perto de você.
                                </p>
                            </div>
                            <!-- Badge Desktop -->
                            <div class="d-none d-lg-flex align-items-center rounded-4 px-3 py-2 border border-secondary border-opacity-50" style="background: rgba(20, 25, 45, 0.6); backdrop-filter: blur(8px);">
                                <div class="bg-primary bg-opacity-25 rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-users text-primary fs-5"></i>
                                </div>
                                <div class="text-start">
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

<!-- Container com Busca Rápida -->
<div class="container position-relative {{ empty($module) ? 'pt-0' : 'pt-4' }}" style="z-index: 10; {{ empty($module) ? 'margin-top: -240px;' : '' }}">
    @if($module !== 'real_estate' && $module !== 'vehicles')
    <!-- Card Busca Rápida -->
    <div class="row justify-content-center mb-4">
        <div class="col-12 col-xl-11 px-2 px-md-3">
            <div id="busca-rapida" class="rounded-4 shadow-lg mx-auto p-3 p-lg-4" style="max-width: 1140px; scroll-margin-top: 24px; background: var(--card); border: 1px solid var(--border);">
                
                <form
                    id="home-search-form"
                    action="{{ route('home') }}"
                    method="GET"
                    class="d-flex flex-column flex-lg-row align-items-center gap-3 w-100 mb-3"
                    data-suggestions-url="{{ route('search.suggestions') }}"
                    data-smart-search="{{ auth()->check() ? (auth()->user()->smart_search_enabled ? '1' : '0') : 'guest' }}"
                >
                    <!-- Campo O que procura -->
                    <div class="position-relative d-flex align-items-center bg-body rounded-3 border px-3 py-2 w-100" style="min-height: 54px; flex: 2.5;">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        <label for="home-search-query" class="visually-hidden">O que você procura?</label>
                        <input
                            id="home-search-query"
                            class="form-control bg-transparent border-0 ms-2 shadow-none p-0 text-body"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="O que você procura?"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-controls="home-search-suggestions"
                            aria-expanded="false"
                        >
                        <button id="home-search-microphone" type="button" class="btn btn-link text-muted p-0 ms-2 text-decoration-none" aria-label="Buscar usando a voz" title="Buscar usando a voz">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <div id="home-search-suggestions" class="quick-search-suggestions" role="listbox" hidden></div>
                    </div>

                    <!-- Row Mobile para Cidade e Categoria -->
                    <div class="d-flex flex-column flex-md-row gap-3 w-100" style="flex: 2;">
                        <div class="position-relative d-flex align-items-center bg-body rounded-3 border px-3 py-2 w-100" style="min-height: 54px;">
                            <i class="fa-solid fa-location-dot text-muted"></i>
                            <span class="visually-hidden">Cidade</span>
                            <select id="home-search-city" name="city" aria-label="Cidade" class="form-select bg-transparent border-0 shadow-none w-100 ps-2 pe-4 p-0 text-body">
                                <option value="" {{ empty($city) ? 'selected' : '' }}>Todas as cidades</option>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="position-relative d-flex align-items-center bg-body rounded-3 border px-3 py-2 w-100" style="min-height: 54px;">
                            <i class="fa-solid fa-table-cells-large text-muted"></i>
                            <span class="visually-hidden">Categoria</span>
                            <select id="home-search-category-filter" aria-label="Categoria" class="form-select bg-transparent border-0 shadow-none w-100 ps-2 pe-4 p-0 text-body">
                                <option value="">Todas as categorias</option>
                                <optgroup label="Anúncios">
                                    <option value="module:real_estate" {{ $module === 'real_estate' ? 'selected' : '' }}>Imóveis</option>
                                    <option value="module:products" {{ $module === 'products' ? 'selected' : '' }}>Produtos</option>
                                    <option value="module:vehicles" {{ $module === 'vehicles' ? 'selected' : '' }}>Veículos</option>
                                    <option value="module:jobs" {{ $module === 'jobs' ? 'selected' : '' }}>Empregos</option>
                                    <option value="module:agro" {{ $module === 'agro' ? 'selected' : '' }}>Agro</option>
                                </optgroup>
                                <optgroup label="Serviços">
                                    @foreach($serviceSearchCategories as $serviceCategory)
                                        <option value="service:{{ $serviceCategory['name'] }}">{{ $serviceCategory['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <input id="home-search-module-value" type="hidden" name="module" value="{{ $module }}">
                            <input id="home-search-service-category-value" type="hidden" name="category">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold rounded-3 w-100" style="min-height: 54px; flex: 1;">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Buscar
                    </button>
                </form>

                <p id="home-voice-status" class="quick-search-voice-status" role="status" aria-live="polite" hidden></p>

                <!-- Footer do card: Location Link (mobile) & Categorias Rápidas (desktop) -->
                <div class="d-flex flex-column flex-lg-row justify-content-lg-center align-items-center pt-2">
                    <div class="d-lg-none w-100 mb-3 pb-3 border-bottom text-center">
                        <button
                            id="home-use-location"
                            type="button"
                            class="btn btn-link text-decoration-none p-0 m-0 d-inline-flex align-items-center gap-2 fw-semibold"
                            aria-describedby="home-location-status"
                            aria-pressed="{{ session('location_filter.enabled', false) ? 'true' : 'false' }}"
                            style="font-size: 0.85rem;"
                        >
                            <i class="fa-solid {{ session('location_filter.enabled', false) ? 'fa-location-dot text-primary' : 'fa-location-crosshairs text-muted' }}"></i>
                            <span data-location-button-label class="text-body">{{ session('location_filter.enabled', false) ? 'Desativar localização' : 'Usar localização atual' }}</span>
                        </button>
                        <p id="home-location-status" class="d-none" role="status" aria-live="polite"></p>
                    </div>

                    <div class="d-flex flex-wrap gap-4 gap-lg-5 align-items-center justify-content-center w-100">
                        <a href="{{ route('module.products') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-tag text-primary"></i> Produtos
                        </a>
                        <a href="{{ route('module.real_estate') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-building text-primary"></i> Imóveis
                        </a>
                        <a href="{{ route('module.vehicles') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-car text-primary"></i> Veículos
                        </a>
                        <a href="{{ route('module.services') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-wrench text-primary"></i> Serviços
                        </a>
                        <a href="{{ route('module.jobs') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-briefcase text-primary"></i> Empregos
                        </a>
                        <a href="{{ route('module.agro') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-leaf text-primary"></i> Agro
                        </a>
                        <a href="{{ route('stores.index') }}" class="text-decoration-none text-body fw-semibold d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-ellipsis text-primary"></i> Ver todas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

    <!-- Resultados da Busca -->
    @if($isSearch)
    <div class="row mb-5" id="resultados-busca">
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
                <div class="row g-4">
                    @foreach($searchResults as $item)
                    <div class="col-12 col-md-6 col-lg-3">
                        <a href="{{ $item->module === 'services' ? route('provider.show', $item->slug) : route('ad.show', $item->slug) }}" class="text-decoration-none text-dark">
                            <div class="card card-premium h-100 border-0 rounded-4 shadow-sm overflow-hidden">
                                <div class="card-img-wrapper position-relative">
                                    @if($item->card_image)
                                        <img src="{{ asset($item->card_image) }}" class="card-img-top" alt="{{ $item->title }}">
                                    @else
                                        <img src="{{ asset('images/logo.png') }}" class="card-img-top p-4 object-fit-contain" alt="{{ $item->title }}">
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
                                            <span class="fw-bold text-primary">Ver perfil profissional</span>
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
    @endif

    @if(empty($module) && $serviceProviders->isNotEmpty())
    <section class="row mb-5 mt-2">
        <div class="col-12 px-3 px-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 provider-section-heading">
                <div>
                    <span class="text-primary small fw-bold text-uppercase">Profissionais {{ $city ? 'de '.$city : 'de Sergipe' }}</span>
                    <h3 class="section-title fw-bold mb-1">Prestadores de Serviços</h3>
                    <p class="text-muted mb-0">Conheça os profissionais e fale diretamente com eles.</p>
                </div>
                <a href="{{ route('module.services') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                    Ver todos os prestadores
                </a>
            </div>
            <div class="row g-3 g-md-4 provider-card-grid">
                @foreach($serviceProviders as $provider)
                    <div class="col-6 col-md-6 col-xl-3">
                        @include('services._card', [
                            'provider' => $provider,
                            'hideDesktopWhatsapp' => true,
                            'hideDescription' => true,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($recentAds->isNotEmpty())
    <!-- Seção Acabaram de Anunciar -->
    <div id="recentes-anuncios" class="row mb-4 mt-2">
        <div class="col-12 px-3 px-md-4">
            <h3 class="section-title fw-bold mb-3" style="font-size: 1.25rem;"><i class="fa-solid fa-sparkles text-warning me-1"></i> 🆕 Acabaram de anunciar{{ $city ? ' em '.$city : '' }}</h3>
            <div class="row g-3">
                @foreach($recentAds as $ad)
                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border-0 rounded-4 shadow-sm overflow-hidden">
                            <div class="card-img-wrapper position-relative">
                                @if($ad->card_image)
                                    <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}">
                                @else
                                    <img src="{{ asset('images/logo.png') }}" class="card-img-top p-3 object-fit-contain" alt="{{ $ad->title }}">
                                @endif
                            </div>
                            <div class="card-body p-2 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-truncate mb-1" style="font-size: 0.84rem;">{{ $ad->title }}</h6>
                                    <p class="card-text text-muted mb-1" style="font-size: 0.75rem;">{{ $ad->city }}</p>
                                </div>
                                <span class="fw-bold text-primary" style="font-size: 0.95rem;">R$ {{ number_format($ad->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(empty($module) && $recentStores->isNotEmpty())
    <section class="row mb-5 mt-2">
        <div class="col-12 px-3 px-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 provider-section-heading">
                <div>
                    <span class="text-success small fw-bold text-uppercase">Comércio local</span>
                    <h3 class="section-title fw-bold mb-1">Lojas {{ $city ? 'de '.$city : 'criadas recentemente' }}</h3>
                    <p class="text-muted mb-0">Conheça vitrines e produtos disponíveis na sua região.</p>
                </div>
                <a href="{{ route('stores.index') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                    Ver todas as lojas
                </a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($recentStores as $store)
                    @php
                        $storeCity = $store->city ?: $store->user?->city ?: 'Sergipe';
                        $storeLogo = $store->logo ?: 'images/logo.png';
                        $storeCover = $store->banner;
                    @endphp
                    <div class="col-12 col-sm-6 col-xl-3">
                        <article class="store-directory-card h-100">
                            <a href="{{ route('store.show', $store->slug) }}" class="store-card-cover" aria-label="Abrir {{ $store->name }}">
                                @if($storeCover)
                                    <img src="{{ asset($storeCover) }}" alt="" loading="lazy">
                                @else
                                    <div class="store-card-cover-fallback">
                                        <i class="fa-solid fa-store"></i>
                                    </div>
                                @endif
                                <span class="store-card-category">{{ $store->category ?: 'Comércio local' }}</span>
                            </a>
                            <div class="store-card-body">
                                <img src="{{ asset($storeLogo) }}" class="store-card-logo" alt="Logo da {{ $store->name }}" loading="lazy">
                                <div class="store-card-title-row">
                                    <h3>
                                        <a href="{{ route('store.show', $store->slug) }}">{{ $store->name }}</a>
                                        <i class="fa-solid fa-circle-check" title="Loja ativa"></i>
                                    </h3>
                                </div>
                                <p class="store-card-city"><i class="fa-solid fa-location-dot"></i> {{ $storeCity }}/SE</p>
                                <p class="store-card-description">{{ \Illuminate\Support\Str::limit($store->description ?: 'Conheça os produtos e novidades desta loja.', 92) }}</p>
                                <div class="store-card-actions">
                                    <a href="{{ route('store.show', $store->slug) }}" class="store-card-open">Ver loja</a>
                                    <span><i class="fa-solid fa-cube"></i> {{ $store->active_ads_count }} {{ $store->active_ads_count === 1 ? 'produto' : 'produtos' }}</span>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
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
                        locationButtonLabel.textContent = 'Desativar localização';
                        locationButtonDetail.textContent = `Resultados filtrados para ${nearestMunicipality.name}.`;
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
