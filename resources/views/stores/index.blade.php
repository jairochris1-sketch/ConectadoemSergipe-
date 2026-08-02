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
@endphp

<main class="stores-directory">
    <div class="container stores-directory-container">
        <section class="stores-hero" aria-labelledby="stores-page-title">
            <div class="stores-hero-content">
                <span class="stores-hero-eyebrow">
                    <i class="fa-solid fa-gem" aria-hidden="true"></i>
                    Vitrine de Sergipe
                </span>
                <h1 id="stores-page-title">Lojas <span>on-line</span></h1>
                <p>Descubra vendedores, ateliês e comércios locais dos 75 municípios sergipanos.</p>

                <div class="stores-hero-stats" aria-label="Números da vitrine">
                    <span><i class="fa-solid fa-store"></i> {{ $storesCount }} {{ $storesCount === 1 ? 'loja' : 'lojas' }}</span>
                    <span><i class="fa-solid fa-cube"></i> {{ $productsCount }} {{ $productsCount === 1 ? 'produto' : 'produtos' }}</span>
                </div>
            </div>

            <div class="stores-hero-art" aria-hidden="true">
                <span class="stores-hero-art-icon stores-hero-art-icon-one"><i class="fa-solid fa-bag-shopping"></i></span>
                <span class="stores-hero-art-icon stores-hero-art-icon-two"><i class="fa-solid fa-location-dot"></i></span>
                <div class="stores-device">
                    <div class="stores-device-camera"></div>
                    <div class="stores-device-shop">
                        <span>LOJA</span>
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <i class="fa-solid fa-cart-shopping stores-device-cart"></i>
                </div>
            </div>

            <div class="stores-hero-action">
                <a href="{{ $storeActionUrl }}">
                    <i class="fa-solid fa-shop"></i>
                    <span>{{ $storeActionLabel }}</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
                <small>{{ $currentUserStore ? 'Gerencie suas lojas e produtos' : 'Cadastre sua vitrine comercial' }}</small>
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
                            name="q"
                            value="{{ $q }}"
                            placeholder="Buscar lojas por nome, cidade ou categoria..."
                        >
                    </label>
                    <button type="submit" class="stores-search-submit">Buscar</button>
                </div>

                <div class="stores-search-controls">
                    <button id="stores-near-me" type="button" @class(['stores-near-me', 'is-active' => session('location_filter.enabled', false)]) aria-pressed="{{ session('location_filter.enabled', false) ? 'true' : 'false' }}">
                        <i class="fa-solid {{ session('location_filter.enabled', false) ? 'fa-location-dot' : 'fa-location-crosshairs' }}"></i>
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
                                    <i class="fa-solid fa-location-dot"></i>
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
                        {{ $stores->links() }}
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

    })();
</script>
@endpush
