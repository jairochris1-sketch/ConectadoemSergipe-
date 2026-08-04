@extends('layouts.app')

@section('title', $work->title . ' - Cultura e Cordel Sergipe')

@push('styles')
<style>
    .cordel-reader-page {
        background: #fbf8f3;
        min-height: 80vh;
    }
    .cordel-booklet-paper {
        background: #fffefb;
        border: 1px solid #e8dec8;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(139, 94, 52, 0.08);
        position: relative;
        padding: 2.5rem;
    }
    .cordel-verses {
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 1.18rem;
        line-height: 1.8;
        color: #2c1810;
        white-space: pre-wrap;
    }
    .cordel-top-pegador {
        width: 48px;
        height: 24px;
        background: #8b5e34;
        border: 2px solid #5c3d21;
        border-radius: 6px;
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        box-shadow: 0 4px 8px rgba(0,0,0,0.25);
    }
    .cordel-top-pegador::after {
        content: '';
        position: absolute;
        top: 5px; left: 50%;
        transform: translateX(-50%);
        width: 8px; height: 8px;
        background: #d4a373;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<main class="cordel-reader-page py-5">
    <div class="container">
        <!-- Voltar -->
        <div class="mb-4">
            <a href="{{ route('culture.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Varal de Cordel
            </a>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Coluna Principal (Folheto/Obra) -->
            <div class="col-12 col-lg-8">
                <article class="cordel-booklet-paper position-relative">
                    <div class="cordel-top-pegador" title="Folheto de Cordel"></div>

                    <!-- Cabeçalho da Obra -->
                    <div class="text-center pb-4 border-bottom mb-4">
                        <span class="badge {{ $work->category_badge_class }} mb-2 px-3 py-2 rounded-pill">
                            {{ $work->category_label }}
                        </span>
                        @if($work->theme)
                            <span class="badge bg-secondary mb-2 px-3 py-2 rounded-pill">
                                # {{ $work->theme }}
                            </span>
                        @endif

                        <h1 class="display-5 fw-extrabold text-dark mb-2">{{ $work->title }}</h1>

                        <div class="d-flex align-items-center justify-content-center gap-2 text-muted">
                            <div class="avatar-circle-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                {{ strtoupper(substr($work->user->name, 0, 1)) }}
                            </div>
                            <span class="fw-semibold text-dark">{{ $work->user->name }}</span>
                            <span class="text-muted">· {{ $work->created_at->format('d/m/Y') }}</span>
                            <span class="badge bg-light text-dark border ms-2">Versão {{ $work->version }}</span>
                        </div>
                    </div>

                    <!-- Capa da Obra (se houver) -->
                    @if($work->cover_path)
                        <div class="text-center mb-4">
                            <img src="{{ asset($work->cover_path) }}" alt="{{ $work->title }}" class="img-fluid rounded-4 shadow-sm" style="max-height: 400px; object-fit: contain;">
                        </div>
                    @endif

                    <!-- Resumo -->
                    @if($work->summary)
                        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark rounded-4 p-3 mb-4">
                            <h6 class="fw-bold mb-1"><i class="fa-solid fa-quote-left me-1 text-warning"></i> Sobre esta obra:</h6>
                            <p class="mb-0 small">{{ $work->summary }}</p>
                        </div>
                    @endif

                    <!-- Player de Mídia (YouTube / Spotify se disponível) -->
                    @if($work->embed_media_url)
                        <div class="mb-4 rounded-4 overflow-hidden shadow-sm">
                            @if(Str::contains($work->embed_media_url, ['youtube.com', 'youtu.be']))
                                @php
                                    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $work->embed_media_url, $matches);
                                    $youtubeId = $matches[1] ?? null;
                                @endphp
                                @if($youtubeId)
                                    <div class="ratio ratio-16x9">
                                        <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" title="YouTube video player" allowfullscreen></iframe>
                                    </div>
                                @endif
                            @elseif(Str::contains($work->embed_media_url, 'spotify.com'))
                                <div class="p-2 bg-dark rounded-4">
                                    <iframe src="{{ str_replace('open.spotify.com/', 'open.spotify.com/embed/', $work->embed_media_url) }}" width="100%" height="152" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
                                </div>
                            @else
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <a href="{{ $work->embed_media_url }}" target="_blank" rel="noopener" class="btn btn-outline-success rounded-pill fw-bold">
                                        <i class="fa-solid fa-play me-2"></i> Ouvir / Assistir Mídia Externa
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Conteúdo Principal do Cordel / Texto -->
                    @if($work->content)
                        <div class="cordel-verses py-3 mb-4">
                            {{ $work->content }}
                        </div>
                    @endif

                    <!-- Link Externo Adicional -->
                    @if($work->external_url)
                        <div class="p-4 bg-light rounded-4 border text-center my-4">
                            <h6 class="fw-bold mb-2">Obra disponível em Plataforma Externa</h6>
                            <a href="{{ $work->external_url }}" target="_blank" rel="noopener" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Acessar no Spotify / Amazon / Plataforma
                            </a>
                        </div>
                    @endif
                </article>
            </div>

            <!-- Coluna Lateral (Integração com Pedido de Produto & Autor) -->
            <div class="col-12 col-lg-4">
                <!-- Card de Pedido do Produto (Livro / Folheto impresso) -->
                @if($work->ad)
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border border-warning">
                        <span class="badge bg-warning text-dark fw-bold mb-2 align-self-start px-3 py-2 rounded-pill">
                            <i class="fa-solid fa-bag-shopping me-1"></i> Comprar Impresso
                        </span>
                        <h4 class="fw-bold text-dark mb-2">Peça o folheto físico direto com o autor</h4>
                        <p class="text-muted small mb-3">Esta obra possui versão impressa ou produto cadastrado no catálogo.</p>
                        
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fs-4 fw-extrabold text-success">R$ {{ number_format($work->ad->price, 2, ',', '.') }}</span>
                        </div>

                        @if($work->user->pix_key)
                            <div class="alert alert-success border-success bg-success bg-opacity-10 py-2 px-3 mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fa-brands fa-pix text-success fs-5 me-2"></i>
                                    <strong class="text-success">Pague direto via PIX:</strong>
                                </div>
                                <div class="d-flex align-items-center bg-white border rounded px-2 py-1 user-select-all font-monospace small">
                                    {{ $work->user->pix_key }}
                                </div>
                                <div class="small text-muted mt-1" style="font-size: 0.75rem;">Faça o PIX e envie o comprovante no WhatsApp abaixo.</div>
                            </div>
                        @endif

                        <a href="{{ route('ad.show', $work->ad->slug ?: $work->ad->id) }}" class="btn btn-success btn-lg rounded-pill fw-bold w-100 shadow-sm">
                            <i class="fa-brands fa-whatsapp me-2"></i> Falar no WhatsApp
                        </a>
                    </div>
                @endif

                <!-- Card do Autor / Escritor -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-3">Sobre o Artista / Escritor</h5>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 56px; height: 56px;">
                            {{ strtoupper(substr($work->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <a href="{{ route('culture.author', $work->user->username) }}" class="text-decoration-none text-dark">{{ $work->user->name }}</a>
                            </h6>
                            <small class="text-muted">Autor(a) em Sergipe</small>
                        </div>
                    </div>

                    @if($work->user->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $work->user->whatsapp) }}?text={{ urlencode('Olá! Vi sua obra "' . $work->title . '" no Conectado em Sergipe!') }}" target="_blank" rel="noopener" class="btn btn-outline-success rounded-pill w-100 fw-bold mb-2">
                            <i class="fa-brands fa-whatsapp me-2"></i> Falar com o Autor no WhatsApp
                        </a>
                    @endif
                    <a href="{{ route('culture.author', $work->user->username) }}" class="btn btn-outline-primary rounded-pill w-100 fw-bold">
                        <i class="fa-solid fa-user me-2"></i> Ver Perfil Completo
                    </a>
                </div>

                <!-- Outras obras do mesmo autor -->
                @if($otherWorks->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 bg-white">
                        <h6 class="fw-bold mb-3">Mais obras de {{ $work->user->name }}</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($otherWorks as $other)
                                <li class="mb-3 pb-2 border-bottom">
                                    <a href="{{ route('culture.show', $other->slug) }}" class="text-dark fw-bold text-decoration-none d-block text-truncate">
                                        {{ $other->title }}
                                    </a>
                                    <small class="text-muted">{{ $other->category_label }}</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
