@extends('layouts.app')

@section('title', 'Varal de Cordel & Espaço Cultural - Conectado em Sergipe')

@push('styles')
<style>
    /* Estilos Especiais do Módulo Cultural & Cordel */
    .cordel-hero {
        background-color: #EFE4D3; /* Bege pergaminho/papel antigo */
        border-bottom: 2px solid #2B2118;
        color: #2B2118;
        position: relative;
        overflow: hidden;
    }
    .cordel-hero-pattern {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        opacity: 0.15;
        background-image: radial-gradient(#2B2118 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .hero-title-serif {
        font-family: "Playfair Display", "Georgia", serif;
        font-weight: 900;
        letter-spacing: -1px;
        color: #2B2118;
        line-height: 1.1;
    }
    .btn-cordel-red {
        background-color: #9C2720;
        color: white;
        border: 2px solid #5B130E;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 0;
        transition: all 0.3s;
    }
    .btn-cordel-red:hover {
        background-color: #5B130E;
        color: white;
    }
    .btn-cordel-outline {
        background-color: transparent;
        color: #2B2118;
        border: 2px solid #2B2118;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 0;
        transition: all 0.3s;
    }
    .btn-cordel-outline:hover {
        background-color: #2B2118;
        color: #EFE4D3;
    }
    /* Dark Mode Overrides for Cordel Hero */
    [data-bs-theme="dark"] .cordel-hero {
        background-color: #1a1511;
        border-bottom-color: #3d3024;
        color: #f4ebd9;
    }
    [data-bs-theme="dark"] .cordel-hero-pattern {
        background-image: radial-gradient(#6e5743 1px, transparent 1px);
        opacity: 0.1;
    }
    [data-bs-theme="dark"] .hero-title-serif {
        color: #fdfbf7;
    }
    [data-bs-theme="dark"] .btn-cordel-outline {
        color: #fdfbf7;
        border-color: #fdfbf7;
    }
    [data-bs-theme="dark"] .btn-cordel-outline:hover {
        background-color: #fdfbf7;
        color: #1a1511;
    }
    [data-bs-theme="dark"] .cordel-hero p {
        color: #c9bdae !important;
    }

    [data-bs-theme="dark"] .cordel-cover-wrapper {
        --cordel-cover-bg: linear-gradient(135deg, #2b2b2b 0%, #1a1a1a 100%) !important;
    }
    [data-bs-theme="dark"] .cordel-cover-placeholder {
        --cordel-placeholder-bg: rgba(0, 0, 0, 0.4) !important;
    }

    /* Pegador de Cordel no Card */
    .cordel-pegador-clip {
        width: 32px;
        height: 16px;
        background: #8b5e34;
        border: 2px solid #5c3d21;
        border-radius: 4px;
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        box-shadow: 0 3px 6px rgba(0,0,0,0.3);
    }
    .cordel-pegador-clip::after {
        content: '';
        position: absolute;
        top: 3px; left: 50%;
        transform: translateX(-50%);
        width: 6px; height: 6px;
        background: #d4a373;
        border-radius: 50%;
    }
    .cordel-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: #fff;
    }
    .cordel-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 28px rgba(0,0,0,0.12) !important;
    }
    .cordel-varal-line {
        height: 4px;
        background: repeating-linear-gradient(90deg, #8b5e34 0, #8b5e34 10px, transparent 10px, transparent 15px);
        margin-bottom: 1.5rem;
    }

    /* Estilos dos Modos de Exibição (Grade, Lista, Carrossel) */
    .btn-view-mode {
        border: none !important;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.82rem;
        transition: all 0.2s ease;
    }
    .btn-view-mode.active, .btn-view-mode:hover {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }

    /* MODO LISTA */
    .culture-view-list .cordel-item-col {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
    .culture-view-list .cordel-card {
        flex-direction: row !important;
        align-items: center;
    }
    .culture-view-list .cordel-cover-wrapper {
        width: 150px !important;
        min-width: 150px !important;
        max-width: 150px !important;
        height: 150px !important;
        min-height: 150px !important;
        max-height: 150px !important;
        border-bottom: none !important;
        border-end: 1px solid #dee2e6 !important;
    }
    .culture-view-list .cordel-cover-img {
        max-height: 140px !important;
    }
    .culture-view-list .cordel-pegador-clip {
        display: none !important;
    }
    .culture-view-list .card-body {
        width: calc(100% - 150px) !important;
        overflow: hidden;
    }
    .culture-view-list .card-body > div:first-child {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .culture-view-list .card-body p.line-clamp-2 {
        -webkit-line-clamp: 1 !important;
    }
</style>
@endpush

@section('content')
<main class="culture-page pb-5">
    <!-- Hero Banner Cultural (Reduzido e Dinâmico) -->
    <div class="container pt-3 mb-4">
        @if(!empty($cultureBanners))
        <section class="swiper culture-banner-swiper overflow-hidden position-relative shadow-lg" style="border-radius: 20px; min-height: 250px; height: 280px; max-height: 310px;">
            <div class="swiper-wrapper">
                @foreach($cultureBanners as $banner)
                    @php
                        $cultureBannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner);
                    @endphp
                    <div class="swiper-slide cordel-hero-slide position-relative"
                         style="background-image: linear-gradient(rgba(43, 33, 24, 0.65), rgba(43, 33, 24, 0.78)), url('{{ $cultureBannerUrl }}'); background-size: cover; background-position: center; height: 100%;">
                    </div>
                @endforeach
            </div>

            <!-- Conteúdo Fixo Sobreposto ao Banner -->
            <div class="position-absolute inset-0 w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-center px-3 py-3" style="z-index: 10; pointer-events: none;">
                <div class="w-100" style="max-width: 760px; pointer-events: auto;">
                    <span class="badge text-white px-3 py-1.5 text-uppercase mb-2 shadow-sm" style="background-color: #9C2720; border: 1px solid #5B130E; letter-spacing: 1.5px; border-radius: 0; font-size: 0.72rem;">
                        ARTE & CULTURA SERGIPANA
                    </span>
                    <h1 class="fw-bold hero-title-serif mb-2 text-white fs-3" style="text-shadow: 0 2px 8px rgba(0,0,0,0.85);">
                        Vitrine de Arte, Cultura & Cordel de Sergipe
                    </h1>
                    <p class="small text-white opacity-90 mb-3" style="max-width: 580px; margin: 0 auto; font-family: Georgia, serif; text-shadow: 0 1px 4px rgba(0,0,0,0.75);">
                        Artistas, poetas, artesãos, músicos e escritores divulgam suas obras, cordéis e artesanato sergipano.
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        @if($canManageCultureWorks)
                            <a href="{{ route('culture.create') }}" class="btn btn-cordel-red px-3 py-2 btn-sm">
                                <i class="fa-solid fa-feather-pointed me-1"></i> Publicar Minha Obra / Arte
                            </a>
                            <a href="{{ route('culture.my-works') }}" class="btn btn-cordel-outline text-white border-white px-3 py-2 btn-sm">
                                Minhas Obras
                            </a>
                        @elseif(auth()->check())
                            <a href="{{ route('ad.create', ['module' => 'services', 'profile_kind' => 'cultural_artist']) }}" class="btn btn-cordel-red px-3 py-2 btn-sm">
                                Criar perfil artístico
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-cordel-red px-3 py-2 btn-sm">
                                Sou Artista / Publicar
                            </a>
                        @endif
                        <a href="#culture-works-container" class="btn btn-cordel-outline text-white border-white px-3 py-2 btn-sm">
                            Explorar Obras
                        </a>
                    </div>
                </div>
            </div>

            @if(count($cultureBanners) > 1)
                <div class="culture-banner-next swiper-button-next text-white" style="z-index: 20;"></div>
                <div class="culture-banner-prev swiper-button-prev text-white" style="z-index: 20;"></div>
                <div class="culture-banner-pagination swiper-pagination" style="z-index: 20;"></div>
            @endif
        </section>
        @else
        <!-- Hero Compacto Sem Imagem de Banner -->
        <section class="cordel-hero py-4 px-3 rounded-4 position-relative shadow-sm" style="border-radius: 20px !important;">
            <div class="cordel-hero-pattern"></div>
            <div class="container position-relative z-index-2 text-center py-2">
                <span class="badge text-white px-3 py-1.5 text-uppercase mb-2 shadow-sm" style="background-color: #9C2720; border: 1px solid #5B130E; letter-spacing: 1.5px; border-radius: 0; font-size: 0.72rem;">
                    ARTE & CULTURA SERGIPANA
                </span>
                <h1 class="fw-bold hero-title-serif mb-2 fs-3">Vitrine de Arte, Cultura & Cordel de Sergipe</h1>
                <p class="small mb-3 mx-auto" style="color: #4A3E31; font-family: Georgia, serif; max-width: 580px;">
                    Artistas, poetas, artesãos, músicos e escritores divulgam todas as formas de arte, obras, cordéis e artesanato sergipano.
                </p>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    @if($canManageCultureWorks)
                        <a href="{{ route('culture.create') }}" class="btn btn-cordel-red px-3 py-2 btn-sm">
                            <i class="fa-solid fa-feather-pointed me-1"></i> Publicar Minha Obra / Arte
                        </a>
                        <a href="{{ route('culture.my-works') }}" class="btn btn-cordel-outline px-3 py-2 btn-sm">
                            Minhas Obras
                        </a>
                    @elseif(auth()->check())
                        <a href="{{ route('ad.create', ['module' => 'services', 'profile_kind' => 'cultural_artist']) }}" class="btn btn-cordel-red px-3 py-2 btn-sm">
                            Criar perfil artístico
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-cordel-red px-3 py-2 btn-sm">
                            Sou Artista / Publicar
                        </a>
                    @endif
                    <a href="#culture-works-container" class="btn btn-cordel-outline px-3 py-2 btn-sm">
                        Explorar Obras
                    </a>
                </div>
            </div>
        </section>
        @endif
    </div>

    <div class="container">
        <!-- Filtros e Busca em Tempo Real -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 p-md-4">
            <form id="culture-filter-form" action="{{ route('culture.index') }}" method="GET" class="row g-3 align-items-center">
                
                <!-- Palavra-chave -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-start-0 rounded-end-3" placeholder="Buscar obras, arte ou artistas...">
                    </div>
                </div>

                <!-- Categoria -->
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select bg-light rounded-3">
                        <option value="">Todas as Artes & Culturas</option>
                        <option value="cordel" {{ request('category') === 'cordel' ? 'selected' : '' }}>📜 Cordel & Poesia</option>
                        <option value="artesanato" {{ request('category') === 'artesanato' ? 'selected' : '' }}>🧵 Artesanato & Escultura</option>
                        <option value="arte_visual" {{ request('category') === 'arte_visual' ? 'selected' : '' }}>🎨 Pintura & Artes Visuais</option>
                        <option value="musica" {{ request('category') === 'musica' ? 'selected' : '' }}>🎵 Música & Áudio</option>
                        <option value="literatura" {{ request('category') === 'literatura' ? 'selected' : '' }}>📚 Literatura & Livros</option>
                        <option value="teatro" {{ request('category') === 'teatro' ? 'selected' : '' }}>🎭 Teatro & Performance</option>
                    </select>
                </div>

                <!-- Autor/Artista -->
                <div class="col-6 col-md-3">
                    <input type="text" name="author" value="{{ request('author') }}" class="form-control bg-light rounded-3" placeholder="Nome do Artista/Autor">
                </div>

                <!-- Botão Filtrar e Salvar -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-3" id="btn-save-search" onclick="saveCurrentCultureSearch()" title="Salvar esta busca para voltar depois">
                        <i class="fa-regular fa-bookmark"></i>
                    </button>
                </div>
            </form>

            <!-- Container de Buscas Salvas -->
            <div id="saved-searches-container" class="mt-3 pt-3 border-top d-none">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <small class="fw-bold text-muted"><i class="fa-solid fa-bookmark text-warning me-1"></i> Minhas Buscas Salvas:</small>
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted text-decoration-none small" onclick="clearAllSavedSearches()">Limpar todas</button>
                </div>
                <div class="d-flex flex-wrap gap-2" id="saved-searches-pills"></div>
            </div>
        </div>

        <!-- Control Bar (Header + Switcher de Modos) -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <h3 class="h5 fw-bold text-dark mb-0"><i class="fa-solid fa-book-open text-warning me-2"></i> Estante Cultural</h3>
                <span class="badge bg-light text-dark border rounded-pill px-3">{{ $works->total() }} obras</span>
            </div>

            <!-- Botões de Modo de Exibição: Grade, Lista, Carrossel -->
            <div class="btn-group border rounded-pill p-1 bg-white shadow-sm" role="group" aria-label="Modo de Exibição">
                <button type="button" class="btn btn-sm rounded-pill px-3 btn-view-mode active" id="btn-mode-grid" onclick="setCultureViewMode('grid')" title="Grade Compacta">
                    <i class="fa-solid fa-border-all me-1"></i> Grade
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3 btn-view-mode" id="btn-mode-list" onclick="setCultureViewMode('list')" title="Modo Lista">
                    <i class="fa-solid fa-list me-1"></i> Lista
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-3 btn-view-mode" id="btn-mode-carousel" onclick="setCultureViewMode('carousel')" title="Modo Carrossel">
                    <i class="fa-solid fa-sliders me-1"></i> Carrossel
                </button>
            </div>
        </div>

        <!-- Linha Simbolizando o Varal de Cordel -->
        <div class="cordel-varal-line"></div>

        <!-- Grid / Lista da Estante / Varal -->
        <div class="row g-3" id="culture-works-container">
            @if($works->isEmpty())
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-light rounded-4 border">
                        <i class="fa-solid fa-book-open fs-1 text-muted mb-3 d-block"></i>
                        <h3 class="h4 fw-bold text-dark mb-2">Nenhuma obra encontrada</h3>
                        <p class="text-muted mb-4">Tente buscar por outros termos ou categorias, ou seja o primeiro a publicar!</p>
                        @if($canManageCultureWorks)
                            <a href="{{ route('culture.create') }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                                <i class="fa-solid fa-plus me-1"></i> Publicar Cordel / Obra
                            </a>
                        @elseif(auth()->check())
                            <a href="{{ route('ad.create', ['module' => 'services', 'profile_kind' => 'cultural_artist']) }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                                Criar perfil artístico
                            </a>
                        @endif
                    </div>
                </div>
            @else
                @include('culture._work_grid', ['works' => $works])
            @endif
        </div>

        <!-- Container Carrossel (Swiper.js com Transição Automática de 7.5s) -->
        <div id="culture-carousel-container" class="d-none my-4 position-relative">
            <div class="swiper culture-swiper-instance p-3 bg-white rounded-4 border shadow-sm overflow-hidden">
                <div class="swiper-wrapper" id="culture-carousel-wrapper"></div>
                <div class="swiper-button-next text-warning"></div>
                <div class="swiper-button-prev text-warning"></div>
                <div class="swiper-pagination mt-3"></div>
            </div>
        </div>

        <!-- Indicator de Carregamento Infinito (AJAX Infinite Scroll) -->
        <div id="infinite-scroll-loader" class="text-center py-4 d-none">
            <div class="spinner-border text-warning" role="status">
                <span class="visually-hidden">Carregando mais obras...</span>
            </div>
            <p class="text-muted small mt-2">Buscando mais folhetos e obras no varal...</p>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    const SEARCHES_STORAGE_KEY = 'conectado_culture_saved_searches';

    function getSavedSearches() {
        try {
            return JSON.parse(localStorage.getItem(SEARCHES_STORAGE_KEY)) || [];
        } catch(e) {
            return [];
        }
    }

    function renderSavedSearches() {
        const searches = getSavedSearches();
        const container = document.getElementById('saved-searches-container');
        const pills = document.getElementById('saved-searches-pills');
        if (!container || !pills) return;

        if (searches.length === 0) {
            container.classList.add('d-none');
            return;
        }

        container.classList.remove('d-none');
        pills.innerHTML = '';

        searches.forEach((item, index) => {
            const badge = document.createElement('div');
            badge.className = 'badge bg-light text-dark border p-2 d-flex align-items-center gap-2 rounded-pill shadow-sm';
            badge.style.cursor = 'pointer';
            badge.innerHTML = `
                <span onclick="applySavedSearch(${index})"><i class="fa-solid fa-clock-rotate-left text-warning me-1"></i> ${escapeHtml(item.label)}</span>
                <i class="fa-solid fa-xmark text-danger ms-1" style="cursor:pointer;" onclick="deleteSavedSearch(${index}, event)" title="Excluir busca"></i>
            `;
            pills.appendChild(badge);
        });
    }

    function saveCurrentCultureSearch() {
        const form = document.getElementById('culture-filter-form');
        const q = form.querySelector('[name="q"]').value.trim();
        const category = form.querySelector('[name="category"]').value;
        const author = form.querySelector('[name="author"]').value.trim();

        if (!q && !category && !author) {
            alert('Selecione ao menos um filtro ou digite um termo para salvar a busca.');
            return;
        }

        let parts = [];
        if (q) parts.push(`"${q}"`);
        if (category) parts.push(category.toUpperCase());
        if (author) parts.push(`Autor: ${author}`);

        const label = parts.join(' + ');

        let searches = getSavedSearches();
        searches = searches.filter(s => s.label !== label);
        searches.unshift({ label, q, category, author, timestamp: Date.now() });
        if (searches.length > 10) searches.pop();

        localStorage.setItem(SEARCHES_STORAGE_KEY, JSON.stringify(searches));
        renderSavedSearches();
        alert('Busca e filtros salvos com sucesso! Você pode clicar nela quando quiser voltar.');
    }

    function applySavedSearch(index) {
        const searches = getSavedSearches();
        const item = searches[index];
        if (!item) return;

        const url = new URL(window.location.origin + window.location.pathname);
        if (item.q) url.searchParams.set('q', item.q);
        if (item.category) url.searchParams.set('category', item.category);
        if (item.author) url.searchParams.set('author', item.author);

        window.location.href = url.toString();
    }

    function deleteSavedSearch(index, event) {
        event.stopPropagation();
        let searches = getSavedSearches();
        searches.splice(index, 1);
        localStorage.setItem(SEARCHES_STORAGE_KEY, JSON.stringify(searches));
        renderSavedSearches();
    }

    function clearAllSavedSearches() {
        if (confirm('Deseja apagar todas as suas buscas salvas?')) {
            localStorage.removeItem(SEARCHES_STORAGE_KEY);
            renderSavedSearches();
        }
    }

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    let cultureSwiper = null;

    function setCultureViewMode(mode) {
        const gridContainer = document.getElementById('culture-works-container');
        const carouselContainer = document.getElementById('culture-carousel-container');
        const btnGrid = document.getElementById('btn-mode-grid');
        const btnList = document.getElementById('btn-mode-list');
        const btnCarousel = document.getElementById('btn-mode-carousel');

        [btnGrid, btnList, btnCarousel].forEach(btn => btn?.classList.remove('active'));

        if (mode === 'grid') {
            btnGrid?.classList.add('active');
            carouselContainer?.classList.add('d-none');
            gridContainer?.classList.remove('d-none', 'culture-view-list');
        } else if (mode === 'list') {
            btnList?.classList.add('active');
            carouselContainer?.classList.add('d-none');
            gridContainer?.classList.remove('d-none');
            gridContainer?.classList.add('culture-view-list');
        } else if (mode === 'carousel') {
            btnCarousel?.classList.add('active');
            gridContainer?.classList.add('d-none');
            carouselContainer?.classList.remove('d-none');
            initCultureCarousel();
        }

        try {
            localStorage.setItem('conectado_culture_view_mode', mode);
        } catch(e){}
    }

    function initCultureCarousel() {
        const wrapper = document.getElementById('culture-carousel-wrapper');
        const gridContainer = document.getElementById('culture-works-container');
        if (!wrapper || !gridContainer) return;

        if (!cultureSwiper) {
            wrapper.innerHTML = '';
            const items = gridContainer.querySelectorAll('.cordel-item-col');
            items.forEach(col => {
                const slide = document.createElement('div');
                slide.className = 'swiper-slide';
                const clone = col.cloneNode(true);
                clone.className = 'cordel-item-col w-100';
                slide.appendChild(clone);
                wrapper.appendChild(slide);
            });

            cultureSwiper = new Swiper('.culture-swiper-instance', {
                slidesPerView: 1,
                spaceBetween: 16,
                loop: true,
                autoplay: {
                    delay: 7500,
                    disableOnInteraction: false,
                },
                speed: 800,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    576: { slidesPerView: 2 },
                    992: { slidesPerView: 3 },
                    1200: { slidesPerView: 4 }
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderSavedSearches();

        if (document.querySelector('.culture-banner-swiper')) {
            new Swiper('.culture-banner-swiper', {
                slidesPerView: 1,
                loop: true,
                autoplay: {
                    delay: 8000,
                    disableOnInteraction: false,
                },
                speed: 800,
                pagination: {
                    el: '.culture-banner-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.culture-banner-next',
                    prevEl: '.culture-banner-prev',
                },
            });
        }

        const savedMode = localStorage.getItem('conectado_culture_view_mode') || 'grid';
        if (savedMode !== 'grid') {
            setCultureViewMode(savedMode);
        }

        let nextPageUrl = "{{ $works->nextPageUrl() }}";
        let isLoading = false;
        const container = document.getElementById('culture-works-container');
        const loader = document.getElementById('infinite-scroll-loader');

        if (!nextPageUrl) return;

        window.addEventListener('scroll', function() {
            if (isLoading || !nextPageUrl) return;

            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 250) {
                isLoading = true;
                loader.classList.remove('d-none');

                fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        container.insertAdjacentHTML('beforeend', data.html);
                        nextPageUrl = data.next_page;
                    } else {
                        nextPageUrl = null;
                    }
                })
                .catch(error => console.error('Erro ao carregar mais obras:', error))
                .finally(() => {
                    isLoading = false;
                    loader.classList.add('d-none');
                });
            }
        });
    });
</script>
@endpush
