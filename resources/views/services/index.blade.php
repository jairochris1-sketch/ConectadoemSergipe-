@extends('layouts.app')

@section('title', 'Prestadores de Serviços em Sergipe - Conectado em Sergipe')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/services-directory.css') }}?v=2.0">
<style>
    /* ── PÁGINA ── */
    .services-directory-page {
        background:
            radial-gradient(circle at 74% 8%, rgba(0, 91, 255, 0.12), transparent 32%),
            linear-gradient(180deg, var(--accent) 0%, var(--background) 42%, var(--background) 100%);
        min-height: 100vh;
    }

    /* ── BANNER SWIPER & HERO ── */
    .services-banner-swiper {
        height: 310px;
        border-radius: 24px !important;
    }
    .services-banner-slide { background-position: center; background-size: cover; }
    .services-banner-content {
        position: relative; z-index: 5;
        max-width: 800px;
        width: 100%;
        text-shadow: 0 2px 6px rgba(0,0,0,.6);
    }
    @media (max-width: 767.98px) {
        .services-banner-swiper {
            height: 350px;
            border-radius: 18px !important;
        }
    }

    /* ── HERO SEM BANNER ── */
    .services-hero {
        position: relative;
        overflow: hidden;
        padding: 3.25rem 0 6.5rem;
        text-align: center;
    }
    .services-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: inherit;
        z-index: 0;
    }
    .services-hero > .container { position: relative; z-index: 1; }

    /* Eyebrow */
    .services-hero-eyebrow {
        display: inline-block;
        color: #0052cc;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        margin-bottom: .65rem;
    }

    /* Headline */
    .services-hero-title {
        font-size: clamp(1.9rem, 4.5vw, 2.9rem);
        font-weight: 900;
        line-height: 1.18;
        color: var(--foreground);
        margin-bottom: .6rem;
    }
    .services-hero-title .text-sergipe { color: #0052cc; }

    .services-hero-subtitle {
        color: var(--muted-foreground);
        font-size: 1rem;
        margin-bottom: 0;
    }

    /* ── BARRA DE BUSCA PILL ── */
    .services-pill-search {
        display: flex;
        align-items: center;
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: 999px;
        box-shadow: 0 6px 28px rgba(15,23,42,.1);
        overflow: hidden;
        max-width: 760px;
        margin: 1.75rem auto 1.25rem;
    }

    .services-pill-field {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 18px;
        min-height: 56px;
        min-width: 0;
    }

    .services-pill-field i {
        color: #64748b;
        font-size: .9rem;
        flex-shrink: 0;
    }

    .services-pill-field input {
        flex: 1;
        border: 0;
        background: transparent;
        outline: none;
        font-size: .87rem;
        color: var(--foreground);
        min-width: 0;
    }

    .services-pill-field input::placeholder { color: #94a3b8; }

    .services-pill-divider {
        width: 1px;
        height: 28px;
        background: var(--border);
        flex-shrink: 0;
    }

    .services-pill-mic {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 0;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        transition: color .18s;
        flex-shrink: 0;
        margin: 0 4px;
    }
    .services-pill-mic:hover { color: #0052cc; }

    .services-pill-submit {
        flex-shrink: 0;
        height: 44px;
        padding: 0 22px;
        margin: 6px;
        border-radius: 999px;
        border: 0;
        background: #0052cc;
        color: #fff;
        font-size: .85rem;
        font-weight: 800;
        cursor: pointer;
        transition: background .18s;
        white-space: nowrap;
    }
    .services-pill-submit:hover { background: #0044aa; }

    /* ── CHIPS POPULARES ── */
    .services-hero-chips {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 8px;
        max-width: 760px;
        margin: 0 auto;
    }

    .services-hero-chips-label {
        font-size: .73rem;
        font-weight: 700;
        color: var(--muted-foreground);
        flex-shrink: 0;
    }

    .services-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 700;
        text-decoration: none;
        color: var(--foreground);
        background: var(--card);
        border: 1px solid var(--border);
        box-shadow: 0 1px 4px rgba(15,23,42,.06);
        transition: all .18s ease;
    }
    .services-chip:hover,
    .services-chip.active {
        color: #0052cc;
        border-color: rgba(0,82,204,.32);
        background: rgba(0,82,204,.07);
        transform: translateY(-1px);
    }
    .services-chip i { color: #0052cc; font-size: .72rem; }

    /* ── PAINEL DE BUSCA (CONTENT AREA) ── */
    .services-search-panel {
        margin-top: 0;
        padding-top: 2rem;
        position: relative;
        z-index: 3;
    }

    /* legado - mantido para compatibilidade com filtros escondidos */
    .services-filter-card,
    .services-benefits-card,
    .services-sidebar-card {
        background: color-mix(in srgb, var(--card) 94%, transparent);
        color: var(--foreground);
        border: 1px solid color-mix(in srgb, var(--border) 72%, transparent);
        box-shadow: 0 18px 42px rgba(15,23,42,.08);
        backdrop-filter: blur(10px);
    }

    /* ── RESPONSIVO ── */
    @media (max-width: 991.98px) {
        .services-hero { padding-bottom: 5rem; }
    }

    @media (max-width: 767.98px) {
        .services-pill-search {
            flex-direction: column;
            border-radius: 18px;
            overflow: visible;
            border: 0;
            background: transparent;
            box-shadow: none;
            gap: 8px;
        }
        .services-pill-field {
            width: 100%;
            border-radius: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(15,23,42,.07);
        }
        .services-pill-divider { display: none; }
        .services-pill-submit {
            width: 100%;
            height: 46px;
            margin: 0;
            border-radius: 12px;
        }
        .services-pill-mic { margin: 0; }
    }

    @media (max-width: 575.98px) {
        .services-banner-swiper { height: 280px; }
        .services-banner-content { transform: translateY(-18px); }
        .services-banner-content h1 { font-size: 1.75rem; }
        .services-hero { padding-top: 2rem; }
        .services-search-panel { padding-top: 1.25rem; }
        .services-hero-title { font-size: 1.65rem; }
        .services-hero-chips { gap: 6px; }
    }
</style>
@endpush

@section('content')
@php
    $popularSearches = [
        ['label' => 'Eletricista', 'icon' => 'fa-bolt'],
        ['label' => 'Mecânico', 'icon' => 'fa-screwdriver-wrench'],
        ['label' => 'Pedreiro', 'icon' => 'fa-house-chimney'],
        ['label' => 'Pintor', 'icon' => 'fa-paint-roller'],
        ['label' => 'Encanador', 'icon' => 'fa-faucet'],
        ['label' => 'Jardineiro', 'icon' => 'fa-seedling'],
        ['label' => 'Fotógrafo', 'icon' => 'fa-camera'],
    ];
@endphp

<div class="services-directory-page position-relative">
    <a href="{{ route('home') }}" class="btn btn-sm btn-light rounded-pill shadow-sm" style="position:absolute; top:1rem; left:1rem; z-index: 1050; padding: 0.4rem 1rem;">
        <i class="fa-solid fa-arrow-left me-1"></i> Voltar
    </a>
    @if(!empty($serviceBanners))
    <div class="container pt-3 mb-4">
        <section class="swiper services-banner-swiper overflow-hidden position-relative shadow-lg" style="border-radius: 24px;">
            <div class="swiper-wrapper">
                @foreach($serviceBanners as $banner)
                    @php
                        $serviceBannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner);
                        $cityName = rawurldecode(pathinfo($banner, PATHINFO_FILENAME));
                    @endphp
                    <div class="swiper-slide services-banner-slide position-relative"
                         style="background-image: linear-gradient(rgba(8, 24, 48, 0.58), rgba(8, 24, 48, 0.72)), url('{{ $serviceBannerUrl }}'); background-size: cover; background-position: center; height: 100%;">
                        @if($cityName && !str_starts_with($banner, 'http'))
                            <div class="position-absolute top-0 end-0 m-3 z-3">
                                <span class="badge rounded-pill bg-primary bg-gradient px-3 py-2 text-uppercase fw-bold shadow-sm" style="letter-spacing: 0.06em; font-size: 0.75rem;">
                                    <i class="fa-solid fa-location-dot me-1"></i> {{ trim($cityName) }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Conteúdo Fixo + Caixa de Pesquisa DENTRO do Hero -->
            <div class="position-absolute inset-0 w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-center px-3 py-3" style="z-index: 10; pointer-events: none;">
                <div class="w-100" style="max-width: 780px; pointer-events: auto;">
                    <span class="badge rounded-pill bg-primary bg-opacity-75 px-3 py-1.5 mb-2 text-uppercase fw-bold shadow-sm" style="letter-spacing: 0.05em; font-size: 0.72rem;">
                        Profissionais de Sergipe
                    </span>
                    <h1 class="fw-bold mb-1 text-white fs-3" style="text-shadow: 0 2px 8px rgba(0,0,0,0.75);">
                        Encontre Prestadores de Serviços em Sergipe
                    </h1>
                    <p class="small text-white opacity-90 mb-3" style="max-width: 620px; margin: 0 auto; text-shadow: 0 1px 4px rgba(0,0,0,0.65);">
                        Conheça profissionais verificados, veja seus trabalhos e fale diretamente pelo WhatsApp.
                    </p>

                    <!-- Barra de Busca Pill DENTRO do Hero -->
                    <form action="{{ route('module.services') }}" method="GET" class="services-pill-search shadow-lg my-2" style="background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(8px); margin: 0 auto; max-width: 720px;">
                        <div class="services-pill-field">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            <input
                                type="search"
                                name="q"
                                value="{{ $q }}"
                                placeholder="O que você precisa hoje? Ex: Eletricista..."
                                autocomplete="off"
                            >
                        </div>
                        <div class="services-pill-divider"></div>
                        <div class="services-pill-field">
                            <i class="fa-solid fa-location-dot text-muted"></i>
                            <input
                                type="text"
                                name="city"
                                value="{{ $city }}"
                                placeholder="Cidade ou bairro. Ex: Aracaju..."
                                list="hero-cities-list"
                                autocomplete="off"
                            >
                            <datalist id="hero-cities-list">
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}">
                                @endforeach
                            </datalist>
                        </div>
                        <button type="button" class="services-pill-mic" aria-label="Buscar por voz" title="Buscar por voz">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <button type="submit" class="services-pill-submit">Buscar</button>
                    </form>

                    <!-- Chips de Buscas Populares DENTRO do Hero -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-1.5 mt-2">
                        <span class="text-white-50 small me-1" style="font-size: 0.72rem; text-shadow: 0 1px 2px rgba(0,0,0,0.8);">Populares:</span>
                        @foreach(array_slice($popularSearches, 0, 5) as $search)
                            <a
                                href="{{ route('module.services', ['q' => $search['label']]) }}"
                                class="services-chip py-1 px-2.5 bg-white bg-opacity-90 text-dark border-0 shadow-sm {{ strcasecmp($q, $search['label']) === 0 ? 'active' : '' }}"
                                style="font-size: 0.7rem;"
                            >
                                <i class="fa-solid {{ $search['icon'] }} text-primary"></i>
                                {{ $search['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        @include('services._profile-cta', [
                            'profileCta' => $profileCta,
                            'class' => 'btn btn-success bg-gradient rounded-pill px-4 py-2 fw-bold shadow',
                        ])
                    </div>
                </div>
            </div>

            <div class="services-banner-next swiper-button-next text-white" style="z-index: 20;"></div>
            <div class="services-banner-prev swiper-button-prev text-white" style="z-index: 20;"></div>
            <div class="services-banner-pagination swiper-pagination" style="z-index: 20;"></div>
        </section>
    </div>
    @endif

    <div class="container services-search-panel pb-5">
        {{-- Formulário de busca legado (oculto, mantido para compatibilidade) --}}
        <form action="{{ route('module.services') }}" method="GET" class="services-filter-card rounded-4 p-3 p-md-4 mb-4 d-none" aria-hidden="true">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="service-search" class="form-label fw-bold text-dark">Qual profissional você procura?</label>
                    <div class="services-search-input">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" id="service-search" name="q" value="{{ $q }}" class="form-control form-control-lg rounded-pill" placeholder="Ex.: eletricista, pedreiro, advogado, pintor...">
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <label for="service-category" class="form-label fw-bold text-dark">Categoria</label>
                    <select id="service-category" name="category" class="form-select form-select-lg rounded-pill" onchange="this.form.submit()">
                        <option value="">Todas as categorias</option>
                        @foreach($serviceCategories as $serviceCategory)
                            <option value="{{ $serviceCategory }}" {{ $category === $serviceCategory ? 'selected' : '' }}>{{ $serviceCategory }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <label for="service-city" class="form-label fw-bold text-dark">Cidade</label>
                    <select id="service-city" name="city" class="form-select form-select-lg rounded-pill" onchange="this.form.submit()">
                        <option value="">Todas as cidades</option>
                        @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                            <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                <span class="fw-bold text-dark me-2">Mais buscados:</span>
                @foreach($popularSearches as $search)
                    <a href="{{ route('module.services', ['q' => $search['label'], 'city' => $city, 'category' => $category]) }}" class="services-quick-chip btn btn-sm rounded-pill px-3 fw-semibold {{ strcasecmp($q, $search['label']) === 0 ? 'active' : '' }}">
                        <i class="fa-solid {{ $search['icon'] }} me-1"></i>{{ $search['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('module.services') }}" class="btn btn-link text-dark fw-bold text-decoration-none ms-auto">
                    Ver todos <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </form>

        <div class="services-benefits-card rounded-4 p-3 p-md-4 mb-4 d-none" aria-hidden="true">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="services-benefit-icon rounded-circle bg-success text-white d-flex align-items-center justify-content-center"><i class="fa-solid fa-user-check"></i></span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Profissionais verificados</h3>
                            <p class="text-muted small mb-0">Mais segurança para você</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="services-benefit-icon rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"><i class="fa-brands fa-whatsapp"></i></span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Fale direto no WhatsApp</h3>
                            <p class="text-muted small mb-0">Contato rápido e prático</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="services-benefit-icon rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center"><i class="fa-solid fa-shield-halved"></i></span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Avaliações reais</h3>
                            <p class="text-muted small mb-0">Veja o que outros dizem</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="services-benefit-icon rounded-circle bg-info bg-opacity-25 text-primary d-flex align-items-center justify-content-center"><i class="fa-solid fa-award"></i></span>
                        <div>
                            <h3 class="h6 fw-bold mb-1">Destaque seu serviço</h3>
                            <p class="text-muted small mb-0">Mais visibilidade para você</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="services-results-layout">
            <aside class="services-directory-sidebar" aria-label="Filtros de prestadores">
                <div class="services-directory-filter">
                    <button
                        class="services-directory-filter-toggle"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#servicesDirectoryFilters"
                        aria-controls="servicesDirectoryFilters"
                        aria-expanded="{{ ($q || $city || $category) ? 'true' : 'false' }}"
                    >
                        <span><i class="fa-solid fa-sliders me-2"></i>Filtrar profissionais</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>

                    <div id="servicesDirectoryFilters" class="collapse {{ ($q || $city || $category) ? 'show' : '' }} d-md-block">
                        <form action="{{ route('module.services') }}" method="GET" class="services-directory-filter-body">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <h2 class="services-directory-filter-title mb-0">
                                    <i class="fa-solid fa-sliders me-1 text-primary"></i> Filtros
                                </h2>
                                @if($q || $city || $category)
                                    <a href="{{ route('module.services') }}" class="text-primary fw-bold" style="font-size:.72rem;">Limpar</a>
                                @endif
                            </div>
                            <p class="services-directory-filter-subtitle">Encontre o profissional ideal.</p>

                            <div class="services-directory-field">
                                <label for="directory-service-search">Profissional ou serviço</label>
                                <div class="position-relative">
                                    <input type="search" id="directory-service-search" name="q" value="{{ $q }}" class="form-control ps-4" placeholder="Ex.: eletricista, pedreiro...">
                                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: .8rem; pointer-events: none;"></i>
                                </div>
                            </div>

                            <div class="services-directory-field">
                                <label for="directory-service-category">Categoria</label>
                                <select id="directory-service-category" name="category" class="form-select">
                                    <option value="">Todas as categorias</option>
                                    @foreach($serviceCategories as $serviceCategory)
                                        <option value="{{ $serviceCategory }}" {{ $category === $serviceCategory ? 'selected' : '' }}>{{ $serviceCategory }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="services-directory-field">
                                <label for="directory-service-city">Cidade</label>
                                <select id="directory-service-city" name="city" class="form-select">
                                    <option value="">Todas as cidades</option>
                                    @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                        <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Avaliação mínima --}}
                            <label class="services-directory-popular-title mb-2">Avaliação mínima</label>
                            <div class="services-rating-filter mb-3">
                                @foreach([1, 2, 3, 4] as $star)
                                    <a
                                        href="{{ route('module.services', array_merge(request()->query(), ['min_rating' => $star])) }}"
                                        class="services-rating-btn {{ request('min_rating') == $star ? 'active' : '' }}"
                                        title="{{ $star }}+ estrelas"
                                    >
                                        <i class="fa-solid fa-star" style="font-size:.6rem;"></i>{{ $star }}+
                                    </a>
                                @endforeach
                            </div>

                            {{-- Checkboxes --}}
                            <label class="services-directory-popular-title mb-2">Status</label>
                            <div class="services-check-list mb-3">
                                <label>
                                    <input type="checkbox" name="verified" value="1" {{ request('verified') ? 'checked' : '' }}>
                                    <span class="services-check-badge">
                                        Apenas verificados <i class="fa-solid fa-circle-check tick"></i>
                                    </span>
                                </label>
                                <label>
                                    <input type="checkbox" name="available" value="1" {{ request('available') ? 'checked' : '' }}>
                                    <span class="services-check-badge">
                                        Disponível agora <span class="dot"></span>
                                    </span>
                                </label>
                            </div>

                            <span class="services-directory-popular-title">Mais buscados</span>
                            <div class="services-directory-popular">
                                @foreach(array_slice($popularSearches, 0, 6) as $search)
                                    <a
                                        href="{{ route('module.services', ['q' => $search['label'], 'city' => $city, 'category' => $category]) }}"
                                        class="{{ strcasecmp($q, $search['label']) === 0 ? 'active' : '' }}"
                                    >
                                        {{ $search['label'] }}
                                    </a>
                                @endforeach
                            </div>

                            <div class="services-directory-filter-actions">
                                <button type="submit" class="btn btn-primary rounded-pill fw-bold">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i>Aplicar filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>

            <section class="services-directory-content">
                <div class="services-directory-heading">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h2 class="fw-bold mb-0">Perfis profissionais</h2>
                            <span class="services-count-pill rounded-pill fw-bold">{{ $providers->total() }}</span>
                        </div>
                        <p class="text-muted mb-0">
                            @if($providers->total())
                                Mostrando {{ $providers->firstItem() }}–{{ $providers->lastItem() }} de {{ $providers->total() }} resultado(s)
                            @else
                                Mostrando 0 resultado(s)
                            @endif
                        </p>
                    </div>
                    @if($q || $city || $category)
                        <a href="{{ route('module.services') }}" class="btn btn-link text-primary fw-bold text-decoration-none">
                            Ver todos <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>
                    @endif
                </div>

                @if($providers->isEmpty())
                    <div class="text-center bg-white border rounded-4 shadow-sm py-5 px-4">
                        <i class="fa-solid fa-user-tie text-muted display-5 mb-3"></i>
                        <h3 class="h5 fw-bold">Nenhum prestador encontrado</h3>
                        <p class="text-muted mb-0">Tente outra especialidade ou cidade.</p>
                    </div>
                @else
                    <div class="services-directory-grid">
                        @foreach($providers as $provider)
                            @include('services._directory-card', ['provider' => $provider])
                        @endforeach
                    </div>

                    @if($providers->hasPages())
                        <nav class="services-directory-pagination" aria-label="Paginação dos perfis profissionais">
                            {{ $providers->onEachSide(1)->links('pagination::bootstrap-5') }}
                        </nav>
                    @endif
                @endif
            </section>
        </div>
    </div>
</div>

@if(($profileCta['state'] ?? null) === 'limit' || session('professional_profile_limit'))
    <div class="modal fade" id="professionalProfileLimitModal" tabindex="-1" aria-labelledby="professionalProfileLimitTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <h2 class="modal-title h5 fw-bold" id="professionalProfileLimitTitle">
                        <i class="fa-solid fa-user text-primary me-2"></i>Limite do plano gratuito
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body px-4">
                    <p class="mb-2">Você já possui um perfil profissional ativo.</p>
                    <p class="text-muted mb-0">
                        {{ session('professional_profile_limit', 'No plano gratuito é permitido apenas 1 perfil profissional. Para cadastrar outro perfil, escolha um dos nossos planos.') }}
                    </p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Agora não</button>
                    <a href="{{ route('page.plans') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Ver planos</a>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@if(!empty($serviceBanners))
@push('scripts')
<script>
    new Swiper('.services-banner-swiper', {
        loop: {{ count($serviceBanners) > 1 ? 'true' : 'false' }},
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        speed: 1500,
        autoplay: {{ count($serviceBanners) > 1 ? "{ delay: 8000, disableOnInteraction: false }" : 'false' }},
        navigation: {
            nextEl: '.services-banner-next',
            prevEl: '.services-banner-prev'
        },
        pagination: {
            el: '.services-banner-pagination',
            clickable: true
        }
    });
</script>
@endpush
@endif

@if(session('professional_profile_limit'))
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalElement = document.getElementById('professionalProfileLimitModal');
        if (modalElement && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    });
</script>
@endpush
@endif
