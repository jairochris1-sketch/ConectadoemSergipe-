@extends('layouts.app')

@section('title', 'Publicar Cordel / Obra Cultural - Conectado em Sergipe')

@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <a href="{{ route('culture.my-works') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Minhas Obras
                </a>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">
                    <i class="fa-solid fa-feather-pointed me-1"></i> Editor de Cordel & Cultura
                </span>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white p-4 p-md-5">
                <h2 class="h3 fw-extrabold text-dark mb-1">Nova Obra / Folheto de Cordel</h2>
                <p class="text-muted mb-4">Escreva seus versos, publique obras literárias, músicas ou vincule produtos impressos.</p>

                @if($errors->any())
                    <div class="alert alert-danger rounded-4 mb-4">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('culture.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Título -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">Título da Obra / Cordel *</label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg rounded-3" placeholder="Ex: O Valente Sertanejo e o Pavão Misterioso" value="{{ old('title') }}" required>
                    </div>

                    <!-- Categoria e Tema -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="category" class="form-label fw-bold">Categoria *</label>
                            <select name="category" id="category" class="form-select form-select-lg rounded-3" required>
                                <option value="cordel" {{ old('category') === 'cordel' ? 'selected' : '' }}>📜 Cordel & Poesia</option>
                                <option value="artesanato" {{ old('category') === 'artesanato' ? 'selected' : '' }}>🧵 Artesanato & Escultura</option>
                                <option value="arte_visual" {{ old('category') === 'arte_visual' ? 'selected' : '' }}>🎨 Pintura & Artes Visuais / Xilogravura</option>
                                <option value="musica" {{ old('category') === 'musica' ? 'selected' : '' }}>🎵 Música / Áudio (Spotify/YouTube)</option>
                                <option value="literatura" {{ old('category') === 'literatura' ? 'selected' : '' }}>📚 Literatura / Livro</option>
                                <option value="teatro" {{ old('category') === 'teatro' ? 'selected' : '' }}>🎭 Teatro & Performance</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="theme" class="form-label fw-bold">Tema / Tag (opcional)</label>
                            <input type="text" name="theme" id="theme" class="form-control form-control-lg rounded-3" placeholder="Ex: Sertão, Amor, Humor, Cultura Local" value="{{ old('theme') }}">
                        </div>
                    </div>

                    <!-- Resumo / Sinopse -->
                    <div class="mb-4">
                        <label for="summary" class="form-label fw-bold">Resumo / Sinopse (opcional)</label>
                        <textarea name="summary" id="summary" rows="2" class="form-control rounded-3" placeholder="Breve introdução sobre o cordel ou a obra...">{{ old('summary') }}</textarea>
                    </div>

                    <!-- Capa / Imagem da Obra -->
                    <div class="mb-4">
                        <label for="cover" class="form-label fw-bold">Capa / Imagem do Folheto (opcional)</label>
                        <input type="file" name="cover" id="cover" class="form-control rounded-3" accept="image/*">
                        <small class="text-muted">Recomendado: Imagem vertical ou formato capa de livro/folheto de cordel (jpg, png, webp).</small>
                    </div>

                    <!-- Editor de Conteúdo Versos / Texto -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label for="content" class="form-label fw-bold mb-0">Texto Completo / Versos de Cordel</label>
                            <small class="text-muted"><i class="fa-solid fa-pen-nib me-1"></i> Formato poesia / estrofes</small>
                        </div>
                        <textarea name="content" id="content" rows="12" class="form-control rounded-3 font-monospace p-3" style="font-size: 1.05rem; line-height: 1.7;" placeholder="Escreva seus versos aqui...&#10;&#10;No sertão de Sergipe nasceu uma história&#10;De coragem, encanto e memória...">{{ old('content') }}</textarea>
                    </div>

                    <!-- Mídia Incorporada (YouTube / Spotify) -->
                    <div class="mb-4 p-4 bg-light rounded-4 border">
                        <h6 class="fw-bold mb-2"><i class="fa-brands fa-youtube text-danger me-1"></i> <i class="fa-brands fa-spotify text-success me-1"></i> Mídia Incorporada (Opcional)</h6>
                        <p class="text-muted small mb-3">Se você possui música ou vídeo no YouTube/Spotify, cole a URL para exibir o player junto com a obra.</p>
                        <input type="text" name="embed_media_url" id="embed_media_url" class="form-control rounded-3 mb-2" placeholder="Ex: https://www.youtube.com/watch?v=... ou https://open.spotify.com/track/..." value="{{ old('embed_media_url') }}">
                        <input type="url" name="external_url" id="external_url" class="form-control rounded-3" placeholder="Link externo (Ex: Link de venda na Amazon, Hotmart, etc.)" value="{{ old('external_url') }}">
                    </div>

                    <!-- Vincular Produto da Loja para Venda do Folheto Físico -->
                    @if($userAds->isNotEmpty())
                        <div class="mb-4 p-4 bg-warning bg-opacity-10 border border-warning rounded-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-bag-shopping me-1 text-warning"></i> Pedidos do Folheto / Livro Físico</h6>
                            <p class="text-muted small mb-3">Se você vende a versão impressa desta obra nos seus produtos, selecione o item para que o leitor peça diretamente pelo site!</p>
                            <select name="ad_id" id="ad_id" class="form-select rounded-3">
                                <option value="">Nenhum produto vinculado</option>
                                @foreach($userAds as $ad)
                                    <option value="{{ $ad->id }}" {{ old('ad_id') == $ad->id ? 'selected' : '' }}>
                                        {{ $ad->title }} - R$ {{ number_format($ad->price, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Publicar ou Salvar Rascunho -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" name="status" value="published" {{ old('status', 'published') === 'published' ? 'checked' : '' }} onchange="document.getElementById('statusValue').value = this.checked ? 'published' : 'draft'; document.getElementById('statusLabel').textContent = this.checked ? 'Publicar Imediatamente' : 'Salvar como Rascunho';">
                            <label class="form-check-label fw-bold" for="statusSwitch" id="statusLabel">Publicar Imediatamente</label>
                            <input type="hidden" name="status" id="statusValue" value="{{ old('status', 'published') }}">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold text-dark shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Obra
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
