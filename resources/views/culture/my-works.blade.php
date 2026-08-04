@extends('layouts.app')

@section('title', 'Minhas Obras e Rascunhos - Conectado em Sergipe')

@section('content')
<main class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-extrabold text-dark mb-1"><i class="fa-solid fa-book-bookmark text-warning me-2"></i> Minhas Obras & Rascunhos</h1>
            <p class="text-muted mb-0">Gerencie seus folhetos de cordel, obras literárias, músicas e publicações artísticas.</p>
        </div>

        <a href="{{ route('culture.create') }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Publicar Nova Obra / Cordel
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 mb-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Obra / Título</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Versão</th>
                        <th>Visualizações</th>
                        <th>Produto Vinculado</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($works as $work)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    @if($work->cover_path)
                                        <img src="{{ asset($work->cover_path) }}" alt="" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px;">
                                    @else
                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                            <i class="fa-solid fa-book"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('culture.show', $work->slug) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $work->title }}
                                        </a>
                                        <small class="text-muted">{{ $work->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $work->category_badge_class }}">
                                    {{ $work->category_label }}
                                </span>
                            </td>
                            <td>
                                @if($work->status === 'published')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                        <i class="fa-solid fa-circle-check me-1"></i> Publicado
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                        <i class="fa-solid fa-pen-ruler me-1"></i> Rascunho
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">v{{ $work->version }}</span>
                            </td>
                            <td>
                                <span class="small text-muted"><i class="fa-regular fa-eye me-1"></i>{{ $work->views_count }}</span>
                            </td>
                            <td>
                                @if($work->ad)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill">
                                        <i class="fa-solid fa-bag-shopping me-1"></i> {{ Str::limit($work->ad->title, 20) }}
                                    </span>
                                @else
                                    <span class="text-muted small">Nenhum</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('culture.show', $work->slug) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="Visualizar">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('culture.edit', $work->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Editar / Versões">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('culture.destroy', $work->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta obra?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-book-open fs-2 mb-2 d-block"></i>
                                Você ainda não possui obras ou rascunhos cadastrados.
                                <div class="mt-3">
                                    <a href="{{ route('culture.create') }}" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                                        Criar meu primeiro Cordel / Obra
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($works->hasPages())
            <div class="p-3 border-top">
                {{ $works->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
