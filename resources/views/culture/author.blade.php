@extends('layouts.app')

@section('title', 'Obras de ' . $author->name . ' - Conectado em Sergipe')

@push('styles')
<style>
    .author-hero {
        background-color: #EFE4D3;
        border-bottom: 2px solid #2B2118;
        position: relative;
        overflow: hidden;
    }
    .author-hero-pattern {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        opacity: 0.15;
        background-image: radial-gradient(#2B2118 1px, transparent 1px);
        background-size: 20px 20px;
    }
    .author-avatar {
        width: 120px;
        height: 120px;
        border: 4px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<main class="culture-author-page pb-5">
    <!-- Hero do Artista -->
    <section class="author-hero py-5 mb-5">
        <div class="author-hero-pattern"></div>
        <div class="container position-relative z-index-2 text-center">
            @if($author->avatar)
                <img src="{{ asset('storage/' . $author->avatar) }}" alt="{{ $author->name }}" class="rounded-circle author-avatar mb-3">
            @else
                <div class="rounded-circle author-avatar d-flex align-items-center justify-content-center bg-primary text-white fs-1 fw-bold mx-auto mb-3">
                    {{ strtoupper(substr($author->name, 0, 1)) }}
                </div>
            @endif
            
            <h1 class="display-5 fw-bold text-dark mb-2" style="font-family: 'Playfair Display', Georgia, serif;">{{ $author->name }}</h1>
            <p class="lead mb-4" style="color: #4A3E31;">Artista / Autor em Sergipe</p>
            
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @if($author->whatsapp)
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $author->whatsapp) }}" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fa-brands fa-whatsapp me-2"></i> Falar no WhatsApp
                    </a>
                @endif
                @if($author->instagram)
                    <a href="https://instagram.com/{{ ltrim($author->instagram, '@') }}" target="_blank" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                        <i class="fa-brands fa-instagram me-2"></i> Instagram
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Obras do Artista -->
    <section class="container mb-5">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
            <h2 class="h3 fw-bold mb-0">Obras Publicadas ({{ $works->total() }})</h2>
        </div>

        @if($works->isEmpty())
            <div class="text-center py-5 bg-light rounded-4 border">
                <div class="display-1 text-muted mb-3">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h3 class="fw-bold">Nenhuma obra publicada ainda.</h3>
                <p class="text-muted">As obras deste artista aparecerão aqui.</p>
            </div>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 mb-4">
                @include('culture._work_grid', ['works' => $works])
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-center mt-4">
                {{ $works->links() }}
            </div>
        @endif
    </section>
</main>
@endsection
