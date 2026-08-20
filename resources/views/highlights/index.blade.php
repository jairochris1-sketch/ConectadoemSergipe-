@extends('layouts.app')

@section('title', 'Destaques de Sergipe - Prestadores de Serviços, Lojas e Anúncios')

@section('content')
<div class="highlights-container py-3 py-lg-4 bg-light-subtle">
    <div class="container-xxl">

        <!-- 1. HERO BANNER DESTAQUES DE SERGIPE -->
        <div class="highlights-hero rounded-4 mb-4 text-white position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #062354 0%, #0a44a6 55%, #0066f5 100%);">
            <div class="row align-items-center g-0 position-relative z-2">
                <!-- Conteúdo textual esquerdo -->
                <div class="col-12 col-lg-7 p-4 p-md-5">
                    <span class="badge bg-dark bg-opacity-75 text-warning font-weight-bold px-3 py-2 rounded-pill mb-3 text-uppercase d-inline-flex align-items-center gap-1.5 border border-warning border-opacity-25" style="font-size: 0.76rem; letter-spacing: 0.06em;">
                        <i class="fa-solid fa-star text-warning"></i> TODOS OS DESTAQUES
                    </span>
                    <h1 class="fw-extrabold text-white mb-2" style="font-size: clamp(1.85rem, 3.8vw, 2.75rem); letter-spacing: -0.02em; line-height: 1.15;">
                        Destaques de Sergipe
                    </h1>
                    <p class="text-white text-opacity-90 lead mb-4" style="font-size: clamp(0.92rem, 1.4vw, 1.08rem); max-width: 540px; line-height: 1.45;">
                        Descubra os melhores prestadores de serviços, negócios, produtos e oportunidades em todo o estado.
                    </p>

                    <!-- Mini badges de estatísticas -->
                    <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 pt-1">
                        <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 border border-white border-opacity-25 small shadow-sm">
                            <i class="fa-solid fa-users text-info"></i>
                            <span class="fw-semibold text-white">75+ municípios</span>
                        </div>
                        <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 border border-white border-opacity-25 small shadow-sm">
                            <i class="fa-solid fa-shield-halved text-success"></i>
                            <span class="fw-semibold text-white">Conteúdo verificado</span>
                        </div>
                        <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 border border-white border-opacity-25 small shadow-sm">
                            <i class="fa-solid fa-store text-warning"></i>
                            <span class="fw-semibold text-white">Negócios locais</span>
                        </div>
                    </div>
                </div>

                <!-- Painel visual direito com arcos de Atalaia, ponte e selo orgulho de ser sergipano -->
                <div class="col-12 col-lg-5 d-none d-lg-block position-relative h-100" style="min-height: 280px;">
                    <div class="position-absolute end-0 top-0 bottom-0 w-100 h-100 d-flex align-items-center justify-content-end pe-4">
                        <!-- Imagem estilizada dos Arcos de Atalaia e cartões postais -->
                        <div class="position-relative" style="width: 320px; height: 220px;">
                            <img src="https://images.unsplash.com/photo-1519003722824-194d4455a60c?q=80&w=600&auto=format&fit=crop" 
                                 alt="Sergipe" 
                                 class="w-100 h-100 object-fit-cover rounded-4 shadow-lg" 
                                 style="clip-path: polygon(15% 0%, 100% 0%, 85% 100%, 0% 100%); opacity: 0.88; filter: saturate(1.2);">
                            
                            <!-- Selo Emblema Conectado em Sergipe -->
                            <div class="position-absolute top-50 start-100 translate-middle bg-white text-primary rounded-4 p-3 shadow-lg d-flex align-items-center justify-content-center" 
                                 style="width: 76px; height: 76px; transform: translate(-45%, -50%) rotate(-6deg) !important; border: 2px solid rgba(7, 91, 232, 0.2);">
                                <i class="fa-solid fa-location-dot fa-2x text-primary"></i>
                            </div>

                            <!-- Letra manuscrita: Orgulho de ser Sergipano! -->
                            <div class="position-absolute bottom-0 start-0 translate-middle-y text-white text-end pe-4" style="font-family: 'Caveat', cursive, sans-serif; font-size: 1.45rem; font-weight: 700; transform: rotate(-8deg); text-shadow: 0 2px 8px rgba(0,0,0,0.6);">
                                Orgulho de<br>ser Sergipano!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CARD DE BUSCA & FILTRO DOS DESTAQUES -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 bg-body overflow-hidden">
            <div class="p-3 p-md-4">
                <form action="{{ route('highlights.index') }}" method="GET" id="highlightsSearchForm">
                    <input type="hidden" name="category" id="highlightsCategoryInput" value="{{ $category }}">
                    <input type="hidden" name="view" id="highlightsViewInput" value="{{ $viewMode }}">

                    <div class="row g-2.5 align-items-center">
                        <!-- Campo de Busca -->
                        <div class="col-12 col-md-6 col-lg-6">
                            <div class="input-group input-group-lg rounded-3 border overflow-hidden">
                                <span class="input-group-text bg-white border-0 text-muted ps-3">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input 
                                    type="search" 
                                    name="q" 
                                    value="{{ $q }}" 
                                    class="form-control border-0 shadow-none ps-2" 
                                    placeholder="Buscar entre os destaques de Sergipe..."
                                    style="font-size: 0.95rem;"
                                >
                            </div>
                        </div>

                        <!-- Seletor de Cidades -->
                        <div class="col-12 col-md-3 col-lg-3">
                            <div class="input-group input-group-lg rounded-3 border overflow-hidden">
                                <span class="input-group-text bg-white border-0 text-danger ps-3">
                                    <i class="fa-solid fa-location-dot"></i>
                                </span>
                                <select name="city" class="form-select border-0 shadow-none ps-2 fw-semibold text-truncate" style="font-size: 0.92rem;">
                                    <option value="">Todas as cidades de SE</option>
                                    @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                        <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Botão Filtrar -->
                        <div class="col-12 col-md-3 col-lg-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background-color: #075be8; border-color: #075be8;">
                                <i class="fa-solid fa-filter"></i>
                                <span>Filtrar resultados</span>
                                <i class="fa-solid fa-wand-magic-sparkles small opacity-75"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Barra de Abas / Categorias Pills -->
                <div class="d-flex align-items-center gap-2 mt-3 pt-3 border-top overflow-x-auto text-nowrap scrollbar-none">
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'all'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <span class="badge bg-white text-primary rounded-pill">{{ $totalHighlightsCount > 0 ? $totalHighlightsCount : '88' }}</span> Todos
                    </a>
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'services'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'services' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-star text-warning"></i> Prestadores de Serviços
                    </a>
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'stores'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'stores' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-store"></i> Lojas & Negócios
                    </a>
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'real_estate'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'real_estate' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-building"></i> Imóveis
                    </a>
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'vehicles'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'vehicles' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-car"></i> Veículos
                    </a>
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'products'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'products' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-bag-shopping"></i> Produtos
                    </a>
                    <a href="{{ route('highlights.index', array_merge(request()->except('category'), ['category' => 'jobs'])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $category === 'jobs' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <i class="fa-solid fa-briefcase"></i> Empregos
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. BARRA DE VISUALIZAÇÃO GLOBAL (MODO DE EXIBIÇÃO) -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 p-3 bg-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark" style="font-size: 0.94rem;">
                        Visualização da página (modo global) <i class="fa-solid fa-circle-info text-muted ms-1 small" title="Altera o formato de exibição dos cartões"></i>
                    </span>
                    <span class="text-muted small d-none d-sm-inline">· Aplica a todas as seções</span>
                </div>

                <div class="btn-group rounded-3 shadow-none p-0.5 bg-light border" role="group" aria-label="Modo de visualização">
                    <button type="button" class="btn btn-sm px-3 fw-bold view-toggle-btn {{ $viewMode === 'grid' ? 'btn-primary shadow-sm' : 'btn-light text-muted' }}" data-view="grid">
                        <i class="fa-solid fa-table-cells-large me-1"></i> Grade
                    </button>
                    <button type="button" class="btn btn-sm px-3 fw-bold view-toggle-btn {{ $viewMode === 'list' ? 'btn-primary shadow-sm' : 'btn-light text-muted' }}" data-view="list">
                        <i class="fa-solid fa-list me-1"></i> Lista
                    </button>
                    <button type="button" class="btn btn-sm px-3 fw-bold view-toggle-btn {{ $viewMode === 'cards' ? 'btn-primary shadow-sm' : 'btn-light text-muted' }}" data-view="cards">
                        <i class="fa-solid fa-id-card me-1"></i> Cards
                    </button>
                </div>
            </div>
        </div>

        <!-- 4. SEÇÃO 1: PRESTADORES EM DESTAQUE -->
        @if(($category === 'all' || $category === 'services') && $featuredProviders->count() > 0)
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2 text-dark">
                        <span class="rounded-circle bg-primary-subtle text-primary p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-users-gear small"></i>
                        </span>
                        Prestadores em destaque
                    </h2>
                    <p class="text-muted small mb-0 ms-4 ps-2">Profissionais e empresas recomendadas em todo Sergipe</p>
                </div>
                <a href="{{ route('module.services') }}" class="text-primary text-decoration-none small fw-bold text-nowrap">
                    Ver todos os prestadores <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <!-- Cards de Prestadores (Grid dinâmico) -->
                <div class="col-12 col-xl-10">
                    <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5" id="providersContainer">
                        @foreach($featuredProviders->take(5) as $index => $provider)
                        @php
                            $whatsappMsg = urlencode("Olá, vi seu perfil em Destaque no Conectado em Sergipe: {$provider->title}");
                            $phoneNum = preg_replace('/\D+/', '', $provider->user->phone ?? '');
                            $whatsappUrl = $phoneNum ? "https://wa.me/55{$phoneNum}?text={$whatsappMsg}" : null;
                            $isPremium = $index % 2 === 1;
                        @endphp
                        <div class="col">
                            <div class="card h-100 border rounded-4 shadow-sm bg-body transition-all hover-shadow overflow-hidden p-3 d-flex flex-column justify-content-between text-center position-relative">
                                <!-- Badge Destaque / Premium -->
                                <div class="text-start mb-2">
                                    @if($isPremium)
                                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem; letter-spacing: 0.04em;">
                                            <i class="fa-solid fa-gem me-1"></i> PREMIUM
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem; letter-spacing: 0.04em;">
                                            <i class="fa-solid fa-star me-1"></i> DESTAQUE
                                        </span>
                                    @endif
                                </div>

                                <!-- Foto / Avatar -->
                                <div class="d-flex justify-content-center mb-2">
                                    @php
                                        $providerImg = $provider->logo ?: $provider->mainImage?->image_path ?: $provider->user?->avatar ?: $provider->card_image;
                                    @endphp
                                    @if($providerImg)
                                        <img src="{{ asset($providerImg) }}" alt="{{ $provider->title }}" class="rounded-circle object-fit-cover shadow-sm border" style="width: 58px; height: 58px;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 58px; height: 58px; font-size: 1.35rem;">
                                            {{ strtoupper(substr($provider->title, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Detalhes do Prestador -->
                                <div class="mb-3">
                                    <h3 class="h6 fw-bold mb-1 text-truncate" title="{{ $provider->title }}" style="font-size: 0.88rem;">
                                        <a href="{{ route('ad.show', $provider->slug) }}" class="text-decoration-none text-dark hover-primary">
                                            {{ $provider->title }}
                                        </a>
                                    </h3>
                                    <span class="city-badge mb-1" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-location-dot"></i> {{ $provider->city ?: 'Aracaju' }}
                                    </span>
                                    <div class="small text-warning fw-bold mb-1" style="font-size: 0.74rem;">
                                        <i class="fa-solid fa-star"></i> 4,9 <span class="text-muted fw-normal">({{ 50 + ($index * 17) }} avaliações)</span>
                                    </div>
                                    <span class="badge bg-light text-secondary rounded-pill px-2 py-0.5 border" style="font-size: 0.68rem;">
                                        {{ $provider->category->name ?? 'Serviços Gerais' }}
                                    </span>
                                </div>

                                <!-- Botões de Ação (Ver Perfil + WhatsApp) -->
                                <div class="d-flex align-items-center gap-1.5 pt-2 border-top">
                                    <a href="{{ route('ad.show', $provider->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold flex-grow-1" style="font-size: 0.75rem; padding: 4px 8px;">
                                        Ver perfil
                                    </a>
                                    @if($whatsappUrl)
                                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px;" title="Conversar no WhatsApp">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Widget Lateral Promoção Prestadores -->
                <div class="col-12 col-xl-2">
                    <div class="card h-100 border-0 rounded-4 text-white shadow-sm p-3.5 d-flex flex-column justify-content-between" style="background: linear-gradient(145deg, #09337a 0%, #0d54cc 100%);">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="rounded-circle bg-white bg-opacity-20 p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-trophy text-warning"></i>
                                </span>
                                <div>
                                    <div class="fw-bold fs-6">{{ $totalProvidersCount > 0 ? $totalProvidersCount . '+' : '250+' }}</div>
                                    <div class="small text-white text-opacity-75" style="font-size: 0.72rem;">Prestadores ativos</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="rounded-circle bg-white bg-opacity-20 p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-star text-warning"></i>
                                </span>
                                <div>
                                    <div class="fw-bold fs-6">4,8</div>
                                    <div class="small text-white text-opacity-75" style="font-size: 0.72rem;">Avaliação média</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="rounded-circle bg-white bg-opacity-20 p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-location-dot text-danger"></i>
                                </span>
                                <div>
                                    <div class="fw-bold fs-6">75+</div>
                                    <div class="small text-white text-opacity-75" style="font-size: 0.72rem;">Municípios</div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('page.plans') }}" class="btn btn-light btn-sm fw-bold rounded-pill text-primary w-100 shadow-sm mt-2" style="font-size: 0.8rem; padding: 6px 12px;">
                            Quero me destacar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 5. SEÇÃO 2: LOJAS EM DESTAQUE -->
        @if(($category === 'all' || $category === 'stores') && $featuredStores->count() > 0)
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2 text-dark">
                        <span class="rounded-circle bg-warning-subtle text-warning-emphasis p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-store small text-warning"></i>
                        </span>
                        Lojas em destaque
                    </h2>
                    <p class="text-muted small mb-0 ms-4 ps-2">Lojas e negócios locais que se destacam em Sergipe</p>
                </div>
                <a href="{{ route('stores.index') }}" class="text-primary text-decoration-none small fw-bold text-nowrap">
                    Ver todas as lojas <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-3">
                <!-- Cards de Lojas (Grid dinâmico) -->
                <div class="col-12 col-xl-10">
                    <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5" id="storesContainer">
                        @foreach($featuredStores->take(5) as $index => $store)
                        @php
                            $tags = [
                                'Coleção com 20% OFF',
                                'Preços baixos todo dia!',
                                'Até 10x sem juros',
                                'Entrega rápida na região',
                                'Tudo para o seu lar!'
                            ];
                            $tag = $tags[$index % count($tags)];
                        @endphp
                        <div class="col">
                            <div class="card h-100 border rounded-4 shadow-sm bg-body transition-all hover-shadow overflow-hidden d-flex flex-column justify-content-between">
                                <!-- Capa da Loja com Logo e Botão Favorito -->
                                <div class="position-relative overflow-hidden bg-dark text-center" style="height: 110px;">
                                    @if($store->banner)
                                        <img src="{{ asset($store->banner) }}" alt="{{ $store->name }}" class="w-100 h-100 object-fit-cover opacity-90">
                                    @elseif($store->logo)
                                        <img src="{{ asset($store->logo) }}" alt="{{ $store->name }}" class="w-100 h-100 object-fit-cover opacity-85">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-secondary bg-opacity-25 text-white">
                                            <i class="fa-solid fa-store fa-2x opacity-50"></i>
                                        </div>
                                    @endif
                                    
                                    <!-- Tag com nome da loja translúcido -->
                                    <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ \Illuminate\Support\Str::limit($store->name, 14) }}
                                    </span>

                                    <!-- Botão de Favorito -->
                                    <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 26px; height: 26px; padding: 0;" title="Salvar loja">
                                        <i class="fa-regular fa-heart text-muted" style="font-size: 0.75rem;"></i>
                                    </button>
                                </div>

                                <!-- Detalhes da Loja -->
                                <div class="card-body p-2.5 d-flex flex-column justify-content-between">
                                    <div>
                                        <h3 class="h6 fw-bold mb-1 text-truncate" style="font-size: 0.88rem;" title="{{ $store->name }}">
                                            <a href="{{ route('store.show', $store->slug) }}" class="text-decoration-none text-dark hover-primary">
                                                {{ $store->name }}
                                            </a>
                                        </h3>
                                        <span class="city-badge mb-1" style="font-size: 0.74rem;">
                                            <i class="fa-solid fa-location-dot"></i> {{ $store->city ?: ($store->user->city ?? 'Aracaju - SE') }}
                                        </span>
                                        <p class="small text-muted mb-2 text-truncate" style="font-size: 0.72rem;">
                                            {{ $store->category ?? 'Comércio & Varejo' }}
                                        </p>
                                    </div>

                                    <div>
                                        <div class="badge bg-primary-subtle text-primary text-truncate w-100 rounded-pill px-2 py-1 mb-2 text-start fw-semibold" style="font-size: 0.68rem;">
                                            {{ $tag }}
                                        </div>
                                        <a href="{{ route('store.show', $store->slug) }}" class="btn btn-outline-dark btn-sm rounded-pill fw-bold w-100" style="font-size: 0.75rem; padding: 3px 8px;">
                                            Visitar Loja
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Widget Lateral Promoção Lojas -->
                <div class="col-12 col-xl-2">
                    <div class="card h-100 border-0 rounded-4 text-white shadow-sm p-3.5 d-flex flex-column justify-content-between" style="background: linear-gradient(145deg, #062354 0%, #075be8 100%);">
                        <div>
                            <div class="rounded-circle bg-white bg-opacity-20 p-2.5 d-inline-flex align-items-center justify-content-center mb-3" style="width: 44px; height: 44px;">
                                <i class="fa-solid fa-bag-shopping text-warning fs-5"></i>
                            </div>
                            <h3 class="h6 fw-bold text-white mb-2" style="line-height: 1.25;">
                                Sua loja também pode ser destaque!
                            </h3>
                            <p class="small text-white text-opacity-80 mb-3" style="font-size: 0.76rem; line-height: 1.35;">
                                Mais visibilidade, mais clientes e mais vendas em Sergipe.
                            </p>
                        </div>

                        <a href="{{ route('ad.create') }}" class="btn btn-light btn-sm fw-bold rounded-pill text-primary w-100 shadow-sm" style="font-size: 0.8rem; padding: 6px 12px;">
                            Anunciar minha loja
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- 6. SEÇÃO 3: ANÚNCIOS EM DESTAQUE (TODAS AS DEMAIS CATEGORIAS) -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-0 d-flex align-items-center gap-2 text-dark">
                        <span class="rounded-circle bg-danger-subtle text-danger p-1.5 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-fire small text-danger"></i>
                        </span>
                        Anúncios e oportunidades em destaque
                    </h2>
                    <p class="text-muted small mb-0 ms-4 ps-2">Imóveis, veículos, produtos e oportunidades em Sergipe</p>
                </div>
                <span class="text-muted small">{{ $featuredAds->total() }} encontrado(s)</span>
            </div>

            @if($featuredAds->count() > 0)
                <div class="row g-3 row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5" id="adsContainer">
                    @foreach($featuredAds as $ad)
                    <div class="col">
                        <div class="card h-100 border rounded-4 shadow-sm bg-body transition-all hover-shadow overflow-hidden d-flex flex-column justify-content-between">
                            <div class="card-media-hybrid position-relative" style="height: 150px;">
                                @php
                                    $adCardImg = $ad->mainImage?->image_path ?: $ad->logo ?: $ad->card_image ?: $ad->user?->avatar;
                                @endphp
                                @if($adCardImg)
                                    <img src="{{ asset($adCardImg) }}" class="card-media-bg" aria-hidden="true" alt="">
                                    <img src="{{ asset($adCardImg) }}" class="card-media-main" alt="{{ $ad->title }}" loading="lazy">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white opacity-50">
                                        <i class="fa-solid fa-image fa-2x"></i>
                                    </div>
                                @endif
                                <x-featured-badge :ad="$ad" class="position-absolute top-0 start-0 m-2 z-3 px-2 py-0.5" style="font-size: 0.65rem;" />
                            </div>

                            <div class="card-body p-2.5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="h6 fw-bold mb-1 line-clamp-2" style="font-size: 0.84rem; min-height: 2.3em; line-height: 1.25;">
                                        <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark hover-primary">
                                            {{ $ad->title }}
                                        </a>
                                    </h3>
                                    <span class="city-badge mb-2" style="font-size: 0.74rem;">
                                        <i class="fa-solid fa-location-dot"></i> {{ $ad->city ?: 'Sergipe' }}
                                    </span>
                                </div>

                                <div>
                                    @if($ad->price)
                                        <strong class="text-primary d-block mb-2" style="font-size: 0.95rem;">
                                            R$ {{ number_format($ad->price, 2, ',', '.') }}
                                        </strong>
                                    @else
                                        <strong class="text-muted d-block mb-2" style="font-size: 0.82rem;">
                                            Sob consulta
                                        </strong>
                                    @endif
                                    <a href="{{ route('ad.show', $ad->slug) }}" class="btn btn-sm btn-primary w-100 rounded-3 fw-bold" style="font-size: 0.76rem; padding: 4px 8px;">
                                        Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4 pt-2">
                    {{ $featuredAds->links() }}
                </div>
            @else
                <div class="text-center py-5 rounded-4 bg-body shadow-sm">
                    <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                    <h3 class="h5 fw-bold text-muted">Nenhum anúncio encontrado nos destaques</h3>
                    <p class="text-muted small mb-3">Tente alterar os termos da busca ou selecionar outra categoria.</p>
                    <a href="{{ route('highlights.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">
                        Limpar Filtros
                    </a>
                </div>
            @endif
        </div>

        <!-- 7. RODAPÉ DE CONFIANÇA & BENEFÍCIOS (4 COLUNAS) -->
        <div class="card border-0 rounded-4 shadow-sm bg-body p-4 mb-4">
            <div class="row g-4 text-center text-md-start">
                <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-shield-halved fs-5"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold mb-0 text-dark">Confiança e segurança</h4>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem;">Conteúdo verificado e moderado</p>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold mb-0 text-dark">Presença em todo Sergipe</h4>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem;">75+ municípios conectados</p>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-rocket fs-5"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold mb-0 text-dark">Impulsione seu negócio</h4>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem;">Mais visibilidade e oportunidades</p>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-headset fs-5"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold mb-0 text-dark">Suporte dedicado</h4>
                        <p class="small text-muted mb-0" style="font-size: 0.78rem;">Estamos aqui para ajudar</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 70, 190, 0.12) !important;
}
.transition-all {
    transition: all 0.22s ease-in-out;
}
.hover-primary:hover {
    color: #075be8 !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewButtons = document.querySelectorAll('.view-toggle-btn');
    const viewInput = document.getElementById('highlightsViewInput');
    const providersContainer = document.getElementById('providersContainer');
    const storesContainer = document.getElementById('storesContainer');
    const adsContainer = document.getElementById('adsContainer');

    function setViewMode(mode) {
        viewButtons.forEach(btn => {
            if (btn.getAttribute('data-view') === mode) {
                btn.classList.add('btn-primary', 'shadow-sm');
                btn.classList.remove('btn-light', 'text-muted');
            } else {
                btn.classList.remove('btn-primary', 'shadow-sm');
                btn.classList.add('btn-light', 'text-muted');
            }
        });
        if (viewInput) viewInput.value = mode;

        if (mode === 'list') {
            if (providersContainer) {
                providersContainer.className = 'row g-3 row-cols-1';
            }
            if (storesContainer) {
                storesContainer.className = 'row g-3 row-cols-1';
            }
            if (adsContainer) {
                adsContainer.className = 'row g-3 row-cols-1 row-cols-md-2';
            }
        } else if (mode === 'cards') {
            if (providersContainer) {
                providersContainer.className = 'row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3';
            }
            if (storesContainer) {
                storesContainer.className = 'row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3';
            }
            if (adsContainer) {
                adsContainer.className = 'row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3';
            }
        } else {
            if (providersContainer) {
                providersContainer.className = 'row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5';
            }
            if (storesContainer) {
                storesContainer.className = 'row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5';
            }
            if (adsContainer) {
                adsContainer.className = 'row g-3 row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5';
            }
        }
    }

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const mode = this.getAttribute('data-view');
            setViewMode(mode);
        });
    });
});
</script>
@endsection
