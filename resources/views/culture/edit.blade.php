@extends('layouts.app')

@section('title', 'Editar Obra / Versão - Conectado em Sergipe')

@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <a href="{{ route('culture.my-works') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Minhas Obras
                </a>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                    Versão Atual: v{{ $work->version }}
                </span>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white p-4 p-md-5">
                <h2 class="h3 fw-extrabold text-dark mb-1">Editar Obra: {{ $work->title }}</h2>
                <p class="text-muted mb-4">Atualize os versos, capa, mídias ou crie uma nova versão do seu folheto.</p>

                @if($errors->any())
                    <div class="alert alert-danger rounded-4 mb-4">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('culture.update', $work->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Título -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">Título da Obra / Cordel *</label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg rounded-3" value="{{ old('title', $work->title) }}" required>
                    </div>

                    <!-- Categoria e Tema -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="category" class="form-label fw-bold">Categoria *</label>
                            <select name="category" id="category" class="form-select form-select-lg rounded-3" required>
                                <option value="cordel" {{ old('category', $work->category) === 'cordel' ? 'selected' : '' }}>📜 Cordel / Poesia</option>
                                <option value="literatura" {{ old('category', $work->category) === 'literatura' ? 'selected' : '' }}>📚 Literatura / Livro</option>
                                <option value="musica" {{ old('category', $work->category) === 'musica' ? 'selected' : '' }}>🎵 Música / Áudio (Spotify/YouTube)</option>
                                <option value="arte_visual" {{ old('category', $work->category) === 'arte_visual' ? 'selected' : '' }}>🎨 Artes Visuais / Xilogravura</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="theme" class="form-label fw-bold">Tema / Tag (opcional)</label>
                            <input type="text" name="theme" id="theme" class="form-control form-control-lg rounded-3" value="{{ old('theme', $work->theme) }}">
                        </div>
                    </div>

                    <!-- Resumo / Sinopse -->
                    <div class="mb-4">
                        <label for="summary" class="form-label fw-bold">Resumo / Sinopse (opcional)</label>
                        <textarea name="summary" id="summary" rows="2" class="form-control rounded-3">{{ old('summary', $work->summary) }}</textarea>
                    </div>

                    <!-- Capa / Imagem da Obra -->
                    <div class="mb-4">
                        <label for="cover" class="form-label fw-bold">Capa / Imagem do Folheto (opcional)</label>
                        @if($work->cover_path)
                            <div class="mb-2">
                                <img src="{{ asset($work->cover_path) }}" alt="" class="rounded-3 border" style="height: 100px; object-fit: contain;">
                            </div>
                        @endif
                        <input type="file" name="cover" id="cover" class="form-control rounded-3" accept="image/*">
                        <small class="text-muted">Envie um arquivo se quiser substituir a capa atual.</small>
                    </div>

                    <!-- Editor de Conteúdo Versos / Texto -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold mb-2">Texto Completo / Versos de Cordel</label>
                        <textarea name="content" id="content" rows="12" class="form-control rounded-3 font-monospace p-3" style="font-size: 1.05rem; line-height: 1.7;">{{ old('content', $work->content) }}</textarea>
                    </div>

                    <!-- Mídia Incorporada (YouTube / Spotify) -->
                    <div class="mb-4 p-4 bg-light rounded-4 border">
                        <h6 class="fw-bold mb-2"><i class="fa-brands fa-youtube text-danger me-1"></i> <i class="fa-brands fa-spotify text-success me-1"></i> Mídia Incorporada (Opcional)</h6>
                        <input type="text" name="embed_media_url" id="embed_media_url" class="form-control rounded-3 mb-2" placeholder="URL do YouTube ou Spotify" value="{{ old('embed_media_url', $work->embed_media_url) }}">
                        <input type="url" name="external_url" id="external_url" class="form-control rounded-3" placeholder="Link externo (Amazon, Hotmart, etc.)" value="{{ old('external_url', $work->external_url) }}">
                    </div>

                    <!-- Vincular Produto da Loja -->
                    @if($userAds->isNotEmpty())
                        <div class="mb-4 p-4 bg-warning bg-opacity-10 border border-warning rounded-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-bag-shopping me-1 text-warning"></i> Pedidos do Folheto / Livro Físico</h6>
                            <select name="ad_id" id="ad_id" class="form-select rounded-3">
                                <option value="">Nenhum produto vinculado</option>
                                @foreach($userAds as $ad)
                                    <option value="{{ $ad->id }}" {{ old('ad_id', $work->ad_id) == $ad->id ? 'selected' : '' }}>
                                        {{ $ad->title }} - R$ {{ number_format($ad->price, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Versionamento -->
                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="bump_version" id="bump_version" value="1">
                            <label class="form-check-label fw-bold text-dark" for="bump_version">
                                Incrementar versão da obra (Aumentar para v{{ $work->version + 1 }})
                            </label>
                            <small class="text-muted d-block">Marque esta opção caso tenha feito revisões significativas no texto do cordel.</small>
                        </div>
                    </div>

                    <!-- Publicar ou Salvar Rascunho -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" name="status_switch" {{ old('status', $work->status) === 'published' ? 'checked' : '' }} onchange="document.getElementById('statusValue').value = this.checked ? 'published' : 'draft'; document.getElementById('statusLabel').textContent = this.checked ? 'Publicado' : 'Rascunho';">
                            <label class="form-check-label fw-bold" for="statusSwitch" id="statusLabel">{{ $work->status === 'published' ? 'Publicado' : 'Rascunho' }}</label>
                            <input type="hidden" name="status" id="statusValue" value="{{ old('status', $work->status) }}">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold text-dark shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
