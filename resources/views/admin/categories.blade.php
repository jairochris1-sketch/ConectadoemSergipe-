@extends('layouts.admin')

@section('title', 'Gerenciar Categorias - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1"><i class="fa-solid fa-list-check text-success me-2"></i> Gestão de Categorias</h2>
        <p class="text-muted small mb-0">Gerencie todas as categorias do site organizadas por módulo (Imóveis, Veículos, Produtos, Serviços, Cultura, etc.).</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
            <i class="fa-solid fa-plus me-1"></i> Nova Categoria
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4 mb-4 border-0 shadow-sm d-flex align-items-center">
        <i class="fa-solid fa-circle-check fs-5 me-3 text-success"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

<!-- Filtros por Módulo e Pesquisa -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 p-md-4">
    <form action="{{ route('admin.categories') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-start-0 rounded-end-3" placeholder="Buscar categoria por nome ou slug...">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <select name="module" class="form-select bg-light rounded-3" onchange="this.form.submit()">
                <option value="">Todos os Módulos ({{ $categories->total() }})</option>
                @foreach($modules as $key => $label)
                    <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark rounded-3 w-100 fw-bold">
                <i class="fa-solid fa-filter me-1"></i> Filtrar
            </button>
            @if(request('module') || request('q'))
                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary rounded-3" title="Limpar Filtros">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabela de Categorias -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 70px;">Ícone</th>
                        <th>Nome da Categoria</th>
                        <th>Slug</th>
                        <th>Módulo</th>
                        <th style="width: 90px;">Ordem</th>
                        <th style="width: 110px;">Status</th>
                        <th class="pe-4 text-end" style="width: 140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="ps-4">
                            <div class="rounded-3 p-2 text-center d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: {{ $cat->color }}15;">
                                <i class="fa-solid {{ $cat->icon }} fs-5" style="color: {{ $cat->color }};"></i>
                            </div>
                        </td>
                        <td>
                            <strong class="text-dark d-block mb-0">{{ $cat->name }}</strong>
                        </td>
                        <td><code class="text-muted bg-light px-2 py-1 rounded">{{ $cat->slug }}</code></td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2.5 py-1.5 rounded-pill">
                                <i class="fa-solid fa-cubes me-1"></i>{{ $modules[$cat->module] ?? 'Geral' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1 fw-bold">{{ $cat->sort_order }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.categories.toggle', $cat->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm p-0 border-0" title="Clique para alterar o status">
                                    @if($cat->active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill">
                                            <i class="fa-solid fa-circle-check me-1"></i>Ativa
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 rounded-pill">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>Inativa
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <!-- Botão Editar -->
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal_{{ $cat->id }}" title="Editar Categoria">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Botão Excluir -->
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal_{{ $cat->id }}" title="Excluir Categoria">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>

                            <!-- Modal Editar Categoria -->
                            <div class="modal fade text-start" id="editCategoryModal_{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar Categoria</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.categories.update', $cat->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label for="name_{{ $cat->id }}" class="form-label fw-semibold">Nome da Categoria *</label>
                                                    <input type="text" class="form-control rounded-3" id="name_{{ $cat->id }}" name="name" value="{{ $cat->name }}" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="module_{{ $cat->id }}" class="form-label fw-semibold">Módulo do Site</label>
                                                    <select class="form-select rounded-3" id="module_{{ $cat->id }}" name="module">
                                                        <option value="">Geral / Sem módulo fixo</option>
                                                        @foreach($modules as $modKey => $modLabel)
                                                            <option value="{{ $modKey }}" {{ $cat->module === $modKey ? 'selected' : '' }}>{{ $modLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-12 col-md-6">
                                                        <label for="icon_{{ $cat->id }}" class="form-label fw-semibold">Ícone FontAwesome *</label>
                                                        <input type="text" class="form-control rounded-3" id="icon_{{ $cat->id }}" name="icon" value="{{ $cat->icon }}" required>
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label for="color_{{ $cat->id }}" class="form-label fw-semibold">Cor Personalizada *</label>
                                                        <input type="color" class="form-control form-control-color w-100 rounded-3" id="color_{{ $cat->id }}" name="color" value="{{ $cat->color }}" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="sort_order_{{ $cat->id }}" class="form-label fw-semibold">Ordem de Exibição</label>
                                                    <input type="number" class="form-control rounded-3" id="sort_order_{{ $cat->id }}" name="sort_order" value="{{ $cat->sort_order }}" min="0" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Salvar Alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Excluir Categoria -->
                            <div class="modal fade text-start" id="deleteCategoryModal_{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content border-0 shadow-lg rounded-4 text-center p-3">
                                        <div class="modal-body p-3">
                                            <i class="fa-solid fa-triangle-exclamation text-danger display-5 mb-3"></i>
                                            <h5 class="fw-bold text-dark mb-2">Excluir Categoria?</h5>
                                            <p class="text-muted small mb-4">Tem certeza que deseja excluir <strong>{{ $cat->name }}</strong>? Esta ação não pode ser desfeita.</p>
                                            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-3 fw-bold">Sim, Excluir</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 d-block mb-2 text-muted opacity-50"></i>
                            Nenhuma categoria encontrada com os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="card-footer bg-white border-0 pt-3 pb-2 px-4">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Cadastrar Nova Categoria -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-folder-plus text-primary me-2"></i> Cadastrar Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nome da Categoria *</label>
                        <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="Ex: Informática, Serviços Médicos, Artesanato..." required>
                    </div>

                    <div class="mb-3">
                        <label for="module" class="form-label fw-semibold">Módulo do Site</label>
                        <select class="form-select rounded-3" id="module" name="module">
                            <option value="">Geral / Sem módulo fixo</option>
                            @foreach($modules as $modKey => $modLabel)
                                <option value="{{ $modKey }}">{{ $modLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="icon" class="form-label fw-semibold">Ícone FontAwesome *</label>
                            <input type="text" class="form-control rounded-3" id="icon" name="icon" placeholder="fa-tag, fa-laptop, fa-wrench" value="fa-tag" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="color" class="form-label fw-semibold">Cor Personalizada *</label>
                            <input type="color" class="form-control form-control-color w-100 rounded-3" id="color" name="color" value="#0d6efd" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Salvar Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
