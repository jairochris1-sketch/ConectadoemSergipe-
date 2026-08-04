@extends('layouts.app')

@section('title', 'Varal de Cordel & Espaço Cultural - Conectado em Sergipe')

@push('styles')
<style>
    /* Estilos Especiais do Módulo Cultural & Cordel */
    .cordel-hero {
        background: linear-gradient(135deg, #2c1810 0%, #4a2c11 50%, #1a0f0a 100%);
        color: #fcf8f2;
        position: relative;
        overflow: hidden;
    }
    .cordel-hero-pattern {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        opacity: 0.08;
        background-image: radial-gradient(#fcd34d 1px, transparent 1px);
        background-size: 20px 20px;
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
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<main class="culture-page pb-5">
    <!-- Hero Banner Cultural -->
    <section class="cordel-hero py-5 mb-4 position-relative">
        <div class="cordel-hero-pattern"></div>
        <div class="container position-relative z-index-2 text-center py-4">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm" style="letter-spacing: 1px;">
                <i class="fa-solid fa-feather-pointed me-1"></i> Literatura & Arte Sergipana
            </span>
            <h1 class="display-4 fw-extrabold mb-3">Varal de Cordel & Espaço Cultural</h1>
            <p class="lead max-w-2xl mx-auto opacity-90 mb-4" style="max-width: 700px;">
                Descubra e valorize os folhetos de cordel, poesias, obras literárias, canções e artes dos talentosos artistas de todo o estado de Sergipe.
            </p>

            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3">
                @auth
                    <a href="{{ route('culture.create') }}" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold text-dark shadow">
                        <i class="fa-solid fa-pen-nib me-2"></i> Publicar minha Obra / Cordel
                    </a>
                    <a href="{{ route('culture.my-works') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-book-bookmark me-2"></i> Meus Rascunhos & Obras
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-warning btn-lg rounded-pill px-4 fw-bold text-dark shadow">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Entrar para Publicar Cordel
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Filtros e Busca em Tempo Real -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 p-md-4">
            <form id="culture-filter-form" action="{{ route('culture.index') }}" method="GET" class="row g-3 align-items-center">
                
                <!-- Palavra-chave -->
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-start-0 rounded-end-3" placeholder="Buscar por palavra-chave ou título...">
                    </div>
                </div>

                <!-- Categoria -->
                <div class="col-6 col-md-3">
                    <select name="category" class="form-select bg-light rounded-3">
                        <option value="">Todas as Categorias</option>
                        <option value="cordel" {{ request('category') === 'cordel' ? 'selected' : '' }}>📜 Cordéis</option>
                        <option value="literatura" {{ request('category') === 'literatura' ? 'selected' : '' }}>📚 Literatura & Poesia</option>
                        <option value="musica" {{ request('category') === 'musica' ? 'selected' : '' }}>🎵 Música & Áudio</option>
                        <option value="arte_visual" {{ request('category') === 'arte_visual' ? 'selected' : '' }}>🎨 Artes Visuais</option>
                    </select>
                </div>

                <!-- Autor/Artista -->
                <div class="col-6 col-md-3">
                    <input type="text" name="author" value="{{ request('author') }}" class="form-control bg-light rounded-3" placeholder="Nome do Artista/Autor">
                </div>

                <!-- Botão Filtrar -->
                <div class="col-12 col-md-2 d-grid">
                    <button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Linha Simbolizando o Varal de Cordel -->
        <div class="cordel-varal-line"></div>

        <!-- Grid da Estante / Varal -->
        <div class="row g-4" id="culture-works-container">
            @if($works->isEmpty())
                <div class="col-12 text-center py-5">
                    <div class="p-5 bg-light rounded-4 border">
                        <i class="fa-solid fa-book-open fs-1 text-muted mb-3 d-block"></i>
                        <h3 class="h4 fw-bold text-dark mb-2">Nenhuma obra encontrada</h3>
                        <p class="text-muted mb-4">Tente buscar por outros termos ou categorias, ou seja o primeiro a publicar!</p>
                        @auth
                            <a href="{{ route('culture.create') }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                                <i class="fa-solid fa-plus me-1"></i> Publicar Cordel / Obra
                            </a>
                        @endauth
                    </div>
                </div>
            @else
                @include('culture._work_grid', ['works' => $works])
            @endif
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
    document.addEventListener('DOMContentLoaded', function() {
        let nextPageUrl = "{{ $works->nextPageUrl() }}";
        let isLoading = false;
        const container = document.getElementById('culture-works-container');
        const loader = document.getElementById('infinite-scroll-loader');

        if (!nextPageUrl) return;

        window.addEventListener('scroll', function() {
            if (isLoading || !nextPageUrl) return;

            // Se o usuário rolou até próximo do final da página (250px do fim)
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
