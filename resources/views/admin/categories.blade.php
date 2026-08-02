@extends('layouts.admin')

@section('title', 'Gerenciar Categorias - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-success me-2"></i> Gestão de Categorias</h2>
        <p class="text-muted small mb-0">Cadastre novas categorias e configure a exibição no menu público.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
        <i class="fa-solid fa-plus me-1"></i> Nova Categoria
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Ícone</th>
                        <th>Categoria</th>
                        <th>Slug</th>
                        <th>Ordem</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr>
                        <td class="ps-4"><i class="fa-solid {{ $cat->icon }} fs-5" style="color: {{ $cat->color }};"></i></td>
                        <td class="fw-semibold">{{ $cat->name }}</td>
                        <td class="text-muted">{{ $cat->slug }}</td>
                        <td>{{ $cat->sort_order }}</td>
                        <td><span class="badge bg-success bg-opacity-10 text-success">Ativa</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhuma categoria cadastrada ainda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Categoria -->
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
                        <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="Ex: Informática, Serviços Médicos..." required>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="icon" class="form-label fw-semibold">Ícone FontAwesome *</label>
                            <input type="text" class="form-control rounded-3" id="icon" name="icon" placeholder="fa-laptop, fa-wrench" value="fa-tag" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="color" class="form-label fw-semibold">Cor Personalizada (Hex) *</label>
                            <input type="color" class="form-control form-control-color w-100 rounded-3" id="color" name="color" value="#4f46e5" required>
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
