@extends('layouts.app')

@section('title', 'Lojas & Vendas em Sergipe - Conectado em Sergipe')
@section('body-class', 'stores-sales-page')

@push('meta')
@include('components.social-meta', [
    'socialTitle'       => 'Lojas & Vendas em Sergipe - Conectado em Sergipe',
    'socialDescription' => 'Explore lojas, produtos, imoveis, veiculos, empregos e agro de todo o estado de Sergipe.',
    'socialUrl'         => route('stores-sales.index'),
    'socialImage'       => asset('images/logo-hero.png'),
])
@endpush

@push('styles')
<style>
.ss-hero {
    position: relative;
    overflow: hidden;
    min-height: 260px;
    display: flex;
    align-items: flex-end;
    padding-bottom: 0;
}
.ss-hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    transition: opacity 1.2s ease;
}
.ss-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, rgba(7,30,70,.82) 0%, rgba(13,110,253,.45) 60%, rgba(0,0,0,.65) 100%);
}
.ss-hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    padding: 40px 0 32px;
}
.ss-hero h1 {
    font-size: clamp(1.35rem, 3vw, 2rem);
    font-weight: 800;
    color: #fff;
    text-shadow: 0 2px 8px rgba(0,0,0,.6);
    margin-bottom: 6px;
    line-height: 1.2;
}
.ss-hero p {
    font-size: clamp(.85rem, 1.6vw, 1rem);
    color: rgba(255,255,255,.9);
    text-shadow: 0 1px 4px rgba(0,0,0,.5);
    margin-bottom: 0;
}
.ss-search-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 18px;
}
.ss-search-bar input {
    flex: 1 1 220px;
    border-radius: 50px;
    border: none;
    padding: 10px 20px;
    font-size: .92rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.18);
}
.ss-search-bar button {
    border-radius: 50px;
    padding: 10px 22px;
    font-weight: 600;
    font-size: .9rem;
    box-shadow: 0 4px 16px rgba(0,0,0,.18);
}
.ss-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}
.ss-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.3);
    color: #fff;
    border-radius: 50px;
    padding: 4px 13px;
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none;
    backdrop-filter: blur(6px);
    transition: background .2s, border-color .2s;
}
.ss-pill:hover {
    background: rgba(255,255,255,.3);
    border-color: rgba(255,255,255,.6);
    color: #fff;
}
.ss-section {
    padding: 28px 0 8px;
}
.ss-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    gap: 10px;
}
.ss-section-title {
    font-size: 1.05rem;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ss-see-all {
    font-size: .8rem;
    font-weight: 600;
    color: var(--bs-primary);
    text-decoration: none;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
}
.ss-see-all:hover { text-decoration: underline; }
.ss-store-card {
    border-radius: 16px;
    overflow: hidden;
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5eaf2);
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    transition: transform .2s, box-shadow .2s;
    text-decoration: none;
    display: block;
    color: inherit;
}
.ss-store-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    color: inherit;
}
.ss-store-banner {
    width: 100%;
    height: 100px;
    object-fit: cover;
}
.ss-store-banner-placeholder {
    width: 100%;
    height: 100px;
    background: linear-gradient(135deg, rgba(13,110,253,.12) 0%, rgba(13,202,240,.12) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}
.ss-store-logo {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
    margin-top: -28px;
    margin-left: 12px;
    position: relative;
    z-index: 1;
    background: #fff;
}
.ss-store-body {
    padding: 6px 12px 12px;
}
.ss-store-name {
    font-size: .88rem;
    font-weight: 700;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ss-store-meta {
    font-size: .74rem;
    color: var(--bs-secondary-color, #6c757d);
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.ss-ad-card {
    border-radius: 14px;
    overflow: hidden;
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5eaf2);
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
    transition: transform .2s, box-shadow .2s;
    text-decoration: none;
    display: block;
    color: inherit;
}
.ss-ad-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    color: inherit;
}
.ss-ad-img {
    width: 100%;
    height: 130px;
    object-fit: cover;
}
.ss-ad-img-placeholder {
    width: 100%;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}
.ss-ad-body {
    padding: 8px 10px 10px;
}
.ss-ad-title {
    font-size: .82rem;
    font-weight: 700;
    margin-bottom: 3px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ss-ad-price {
    font-size: .85rem;
    font-weight: 700;
    color: #0d6efd;
}
.ss-ad-meta {
    font-size: .70rem;
    color: var(--bs-secondary-color, #6c757d);
    margin-top: 4px;
}
.ss-swiper-container {
    position: relative;
    overflow: visible;
}
.ss-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5eaf2);
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    cursor: pointer;
    transition: background .2s, border-color .2s;
    color: #495057;
}
.ss-nav:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }
.ss-nav.ss-prev { left: -14px; }
.ss-nav.ss-next { right: -14px; }
.ss-nav.swiper-button-disabled { opacity: 0.35; pointer-events: none; }
.ss-empty {
    text-align: center;
    padding: 32px 16px;
    color: var(--bs-secondary-color, #6c757d);
    font-size: .88rem;
}
.ss-empty i { font-size: 2.2rem; margin-bottom: 10px; opacity: .4; }
html[data-theme="dark"] .ss-store-card,
[data-bs-theme="dark"] .ss-store-card,
html[data-theme="dark"] .ss-ad-card,
[data-bs-theme="dark"] .ss-ad-card {
    background: var(--card);
    border-color: var(--border);
}
html[data-theme="dark"] .ss-nav,
[data-bs-theme="dark"] .ss-nav {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
html[data-theme="dark"] .ss-store-logo {
    border-color: #1e293b;
}
@media (max-width: 575.98px) {
    .ss-hero { min-height: 200px; }
    .ss-section { padding-top: 20px; }
    .ss-nav.ss-prev { left: -10px; }
    .ss-nav.ss-next { right: -10px; }
}
</style>
@endpush

@section('content')

<section class="ss-hero" id="ss-hero">
    @foreach($heroBanners as $idx => $banner)
        <div class="ss-hero-bg {{ $idx === 0 ? '' : 'd-none' }}" style="background-image: url('{{ str_starts_with($banner, 'http') ? $banner : asset($banner) }}');"></div>
    @endforeach
    <div class="ss-hero-overlay"></div>
    <div class="container ss-hero-content">
        <h1><i class="fa-solid fa-store me-2"></i>Lojas &amp; Vendas em Sergipe</h1>
        <p>Explore lojas, produtos, imoveis, veiculos, empregos e agro de todo o estado.</p>
        <form action="{{ route('stores-sales.index') }}" method="GET" class="ss-search-bar" role="search">
            @if($city)<input type="hidden" name="city" value="{{ $city }}">@endif
            <input type="search" name="q" value="{{ $q }}" placeholder="Buscar produto, loja, imovel..." aria-label="Buscar" id="ss-search-input" autocomplete="off">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Buscar</button>
        </form>
        <div class="ss-pills">
            <a href="{{ route('stores.index') }}" class="ss-pill"><i class="fa-solid fa-store"></i>Lojas</a>
            <a href="{{ route('module.products') }}" class="ss-pill"><i class="fa-solid fa-bag-shopping"></i>Produtos</a>
            <a href="{{ route('module.real_estate') }}" class="ss-pill"><i class="fa-solid fa-building"></i>Imoveis</a>
            <a href="{{ route('module.vehicles') }}" class="ss-pill"><i class="fa-solid fa-car"></i>Veiculos</a>
            <a href="{{ route('module.jobs') }}" class="ss-pill"><i class="fa-solid fa-briefcase"></i>Empregos</a>
            <a href="{{ route('module.agro') }}" class="ss-pill"><i class="fa-solid fa-tractor"></i>Agro</a>
        </div>
    </div>
</section>

<div class="container">

    <section class="ss-section" aria-labelledby="ss-stores-heading">
        <div class="ss-section-header">
            <h2 class="ss-section-title" id="ss-stores-heading">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width:32px;height:32px;"><i class="fa-solid fa-store"></i></span>
                Lojas em Destaque
            </h2>
            <a href="{{ route('stores.index') }}" class="ss-see-all">Ver todas <i class="fa-solid fa-chevron-right" style="font-size:.65rem;"></i></a>
        </div>
        @if($recentStores->isNotEmpty())
            <div class="ss-swiper-container">
                <div class="swiper ss-swiper" id="ss-swiper-stores">
                    <div class="swiper-wrapper">
                        @foreach($recentStores as $store)
                            @php
                                $storeBanner = $store->banner ? asset($store->banner) : null;
                                $storeLogo   = $store->logo ? asset($store->logo) : ($store->user?->avatar ? asset($store->user->avatar) : null);
                                $storeCity   = $store->city ?: $store->user?->city ?: 'Sergipe';
                                $storeSlug   = $store->slug ?? $store->id;
                            @endphp
                            <div class="swiper-slide">
                                <a href="{{ route('store.show', $storeSlug) }}" class="ss-store-card">
                                    @if($storeBanner)
                                        <img src="{{ $storeBanner }}" class="ss-store-banner" alt="{{ $store->name }}" loading="lazy">
                                    @else
                                        <div class="ss-store-banner-placeholder"><i class="fa-solid fa-store text-primary opacity-50" style="font-size:2rem;"></i></div>
                                    @endif
                                    @if($storeLogo)
                                        <img src="{{ $storeLogo }}" class="ss-store-logo" alt="{{ $store->name }}" loading="lazy">
                                    @else
                                        <div class="ss-store-logo d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="font-size:1.2rem;"><i class="fa-solid fa-store"></i></div>
                                    @endif
                                    <div class="ss-store-body">
                                        <div class="ss-store-name">{{ Str::limit($store->name, 22) }}</div>
                                        <div class="ss-store-meta">
                                            <span class="city-badge"><i class="fa-solid fa-location-dot"></i> {{ $storeCity }}</span>
                                            @if($store->active_ads_count)<span><i class="fa-solid fa-box text-success"></i> {{ $store->active_ads_count }} prod.</span>@endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button class="ss-nav ss-prev" id="ss-stores-prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="ss-nav ss-next" id="ss-stores-next" aria-label="Proximo"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        @else
            <div class="ss-empty"><i class="fa-solid fa-store d-block mb-2"></i>Nenhuma loja encontrada.</div>
        @endif
    </section>

    @if($productAds->isNotEmpty())
    <section class="ss-section" aria-labelledby="ss-products-heading">
        <div class="ss-section-header">
            <h2 class="ss-section-title" id="ss-products-heading">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success bg-opacity-10 text-success" style="width:32px;height:32px;"><i class="fa-solid fa-bag-shopping"></i></span>
                Produtos a Venda
            </h2>
            <a href="{{ route('module.products') }}" class="ss-see-all">Ver todos <i class="fa-solid fa-chevron-right" style="font-size:.65rem;"></i></a>
        </div>
        <div class="ss-swiper-container">
            <div class="swiper ss-swiper" id="ss-swiper-products">
                <div class="swiper-wrapper">
                    @foreach($productAds as $ad)
                        @php $adImg = $ad->mainImage?->image_path ? asset($ad->mainImage->image_path) : null; $adPrice = $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : null; @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="ss-ad-card">
                                @if($adImg)<img src="{{ $adImg }}" class="ss-ad-img" alt="{{ $ad->title }}" loading="lazy">
                                @else<div class="ss-ad-img-placeholder bg-success bg-opacity-10 text-success"><i class="fa-solid fa-bag-shopping"></i></div>@endif
                                <div class="ss-ad-body">
                                    <div class="ss-ad-title">{{ $ad->title }}</div>
                                    @if($adPrice)<div class="ss-ad-price">{{ $adPrice }}</div>@endif
                                    <div class="ss-ad-meta"><span class="city-badge"><i class="fa-solid fa-location-dot"></i> {{ $ad->city ?? 'Sergipe' }}</span></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="ss-nav ss-prev" id="ss-products-prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="ss-nav ss-next" id="ss-products-next" aria-label="Proximo"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>
    @endif

    @if($realEstateAds->isNotEmpty())
    <section class="ss-section" aria-labelledby="ss-realestate-heading">
        <div class="ss-section-header">
            <h2 class="ss-section-title" id="ss-realestate-heading">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-info bg-opacity-10 text-info" style="width:32px;height:32px;"><i class="fa-solid fa-building"></i></span>
                Imoveis
            </h2>
            <a href="{{ route('module.real_estate') }}" class="ss-see-all">Ver todos <i class="fa-solid fa-chevron-right" style="font-size:.65rem;"></i></a>
        </div>
        <div class="ss-swiper-container">
            <div class="swiper ss-swiper" id="ss-swiper-realestate">
                <div class="swiper-wrapper">
                    @foreach($realEstateAds as $ad)
                        @php $adImg = $ad->mainImage?->image_path ? asset($ad->mainImage->image_path) : null; $adPrice = $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : null; @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="ss-ad-card">
                                @if($adImg)<img src="{{ $adImg }}" class="ss-ad-img" alt="{{ $ad->title }}" loading="lazy">
                                @else<div class="ss-ad-img-placeholder bg-info bg-opacity-10 text-info"><i class="fa-solid fa-building"></i></div>@endif
                                <div class="ss-ad-body">
                                    <div class="ss-ad-title">{{ $ad->title }}</div>
                                    @if($adPrice)<div class="ss-ad-price">{{ $adPrice }}</div>@endif
                                    <div class="ss-ad-meta"><span class="city-badge"><i class="fa-solid fa-location-dot"></i> {{ $ad->city ?? 'Sergipe' }}</span></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="ss-nav ss-prev" id="ss-realestate-prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="ss-nav ss-next" id="ss-realestate-next" aria-label="Proximo"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>
    @endif

    @if($vehicleAds->isNotEmpty())
    <section class="ss-section" aria-labelledby="ss-vehicles-heading">
        <div class="ss-section-header">
            <h2 class="ss-section-title" id="ss-vehicles-heading">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-danger bg-opacity-10 text-danger" style="width:32px;height:32px;"><i class="fa-solid fa-car"></i></span>
                Veiculos
            </h2>
            <a href="{{ route('module.vehicles') }}" class="ss-see-all">Ver todos <i class="fa-solid fa-chevron-right" style="font-size:.65rem;"></i></a>
        </div>
        <div class="ss-swiper-container">
            <div class="swiper ss-swiper" id="ss-swiper-vehicles">
                <div class="swiper-wrapper">
                    @foreach($vehicleAds as $ad)
                        @php $adImg = $ad->mainImage?->image_path ? asset($ad->mainImage->image_path) : null; $adPrice = $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : null; @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="ss-ad-card">
                                @if($adImg)<img src="{{ $adImg }}" class="ss-ad-img" alt="{{ $ad->title }}" loading="lazy">
                                @else<div class="ss-ad-img-placeholder bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-car"></i></div>@endif
                                <div class="ss-ad-body">
                                    <div class="ss-ad-title">{{ $ad->title }}</div>
                                    @if($adPrice)<div class="ss-ad-price">{{ $adPrice }}</div>@endif
                                    <div class="ss-ad-meta"><span class="city-badge"><i class="fa-solid fa-location-dot"></i> {{ $ad->city ?? 'Sergipe' }}</span></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="ss-nav ss-prev" id="ss-vehicles-prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="ss-nav ss-next" id="ss-vehicles-next" aria-label="Proximo"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>
    @endif

    @if($jobAgroAds->isNotEmpty())
    <section class="ss-section pb-4" aria-labelledby="ss-jobs-heading">
        <div class="ss-section-header">
            <h2 class="ss-section-title" id="ss-jobs-heading">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-warning bg-opacity-10 text-warning" style="width:32px;height:32px;"><i class="fa-solid fa-briefcase"></i></span>
                Empregos &amp; Agro
            </h2>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('module.jobs') }}" class="ss-see-all">Empregos <i class="fa-solid fa-chevron-right" style="font-size:.65rem;"></i></a>
                <a href="{{ route('module.agro') }}" class="ss-see-all">Agro <i class="fa-solid fa-chevron-right" style="font-size:.65rem;"></i></a>
            </div>
        </div>
        <div class="ss-swiper-container">
            <div class="swiper ss-swiper" id="ss-swiper-jobs">
                <div class="swiper-wrapper">
                    @foreach($jobAgroAds as $ad)
                        @php
                            $adImg = $ad->mainImage?->image_path ? asset($ad->mainImage->image_path) : null;
                            $adPrice = $ad->price ? 'R$ ' . number_format($ad->price, 2, ',', '.') : null;
                            $moduleColor = $ad->module === 'agro' ? 'success' : 'warning';
                            $moduleIcon  = $ad->module === 'agro' ? 'fa-tractor' : 'fa-briefcase';
                        @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="ss-ad-card">
                                @if($adImg)<img src="{{ $adImg }}" class="ss-ad-img" alt="{{ $ad->title }}" loading="lazy">
                                @else<div class="ss-ad-img-placeholder bg-{{ $moduleColor }} bg-opacity-10 text-{{ $moduleColor }}"><i class="fa-solid {{ $moduleIcon }}"></i></div>@endif
                                <div class="ss-ad-body">
                                    <div class="ss-ad-title">{{ $ad->title }}</div>
                                    @if($adPrice)<div class="ss-ad-price">{{ $adPrice }}</div>@endif
                                    <div class="ss-ad-meta"><span class="city-badge"><i class="fa-solid fa-location-dot"></i> {{ $ad->city ?? 'Sergipe' }}</span></div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="ss-nav ss-prev" id="ss-jobs-prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="ss-nav ss-next" id="ss-jobs-next" aria-label="Proximo"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>
    @endif

    @if($recentStores->isEmpty() && $productAds->isEmpty() && $realEstateAds->isEmpty() && $vehicleAds->isEmpty() && $jobAgroAds->isEmpty())
        <div class="ss-empty py-5">
            <i class="fa-solid fa-box-open d-block mb-3"></i>
            <p class="fw-semibold mb-1">Nenhum resultado encontrado</p>
            @if($q || $city)
                <p class="small text-muted mb-3">Tente buscar com outros termos ou remova os filtros.</p>
                <a href="{{ route('stores-sales.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">Limpar filtros</a>
            @endif
        </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const swiperConfigs = [
        { id: 'ss-swiper-stores',     prev: 'ss-stores-prev',     next: 'ss-stores-next'     },
        { id: 'ss-swiper-products',   prev: 'ss-products-prev',   next: 'ss-products-next'   },
        { id: 'ss-swiper-realestate', prev: 'ss-realestate-prev', next: 'ss-realestate-next' },
        { id: 'ss-swiper-vehicles',   prev: 'ss-vehicles-prev',   next: 'ss-vehicles-next'   },
        { id: 'ss-swiper-jobs',       prev: 'ss-jobs-prev',       next: 'ss-jobs-next'       },
    ];
    swiperConfigs.forEach(({ id, prev, next }) => {
        const el = document.getElementById(id);
        if (!el) return;
        new Swiper(`#${id}`, {
            slidesPerView: 2.3,
            spaceBetween: 12,
            grabCursor: true,
            navigation: { prevEl: `#${prev}`, nextEl: `#${next}` },
            breakpoints: {
                576:  { slidesPerView: 3,   spaceBetween: 14 },
                768:  { slidesPerView: 4,   spaceBetween: 16 },
                992:  { slidesPerView: 5,   spaceBetween: 16 },
                1200: { slidesPerView: 6,   spaceBetween: 18 },
            },
        });
    });
    const bgs = document.querySelectorAll('.ss-hero-bg');
    if (bgs.length > 1) {
        let current = 0;
        setInterval(() => {
            bgs[current].classList.add('d-none');
            current = (current + 1) % bgs.length;
            bgs[current].classList.remove('d-none');
        }, 5000);
    }
});
</script>
@endpush

@endsection
