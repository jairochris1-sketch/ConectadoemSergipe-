@extends('layouts.admin')

@section('title', 'Gerenciar Anúncios - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-rectangle-ad text-warning me-2"></i> Gestão de Anúncios e Prestadores</h2>
        <p class="text-muted small mb-0">Cadastre novos serviços/anúncios para clientes ou modere os existentes.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#newAdModal">
        <i class="fa-solid fa-plus me-1"></i> Novo Anúncio / Prestador
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
                        <th class="ps-4">ID</th>
                        <th>Anúncio / Prestador</th>
                        <th>Cliente</th>
                        <th>Módulo</th>
                        <th>Preço</th>
                        <th>Cidade</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ads as $item)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $item->id }}</td>
                        <td class="fw-semibold text-truncate" style="max-width: 220px;">{{ $item->title }}</td>
                        <td><small class="fw-bold text-dark">{{ $item->user->name ?? 'Usuário' }}</small></td>
                        @php
                            $adminModuleLabels = [
                                'services' => 'Serviços',
                                'real_estate' => 'Imóveis',
                                'vehicles' => 'Veículos',
                                'products' => 'Produtos',
                                'jobs' => 'Empregos',
                                'agro' => 'Agro',
                            ];
                        @endphp
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $adminModuleLabels[$item->module] ?? strtoupper($item->module) }}</span></td>
                        <td class="fw-bold text-success">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                        <td>{{ $item->city }}</td>
                        <td>
                            <form action="{{ route('admin.ads.toggle_status', $item->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                @csrf
                                <select name="status" class="form-select form-select-sm rounded-3" aria-label="Status do anúncio #{{ $item->id }}" style="min-width: 118px;">
                                    <option value="active" @selected($item->status === 'active')>Ativo</option>
                                    <option value="pending" @selected($item->status === 'pending')>Pendente</option>
                                    <option value="inactive" @selected($item->status === 'inactive')>Inativo</option>
                                    <option value="sold" @selected($item->status === 'sold')>Vendido</option>
                                    <option value="banned" @selected($item->status === 'banned')>Bloqueado</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-3" title="Salvar status" aria-label="Salvar status do anúncio #{{ $item->id }}">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('ad.show', $item->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">Ver</a>
                            <a href="{{ route('ad.edit', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Nenhum anúncio cadastrado ainda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Anúncio / Prestador de Serviço -->
<div class="modal fade" id="newAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-square-plus text-primary me-2"></i> Cadastrar Anúncio / Prestador de Serviço</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.ads.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="user_id" class="form-label fw-semibold">Selecione o Cliente *</label>
                            <select class="form-select rounded-3" id="user_id" name="user_id" required>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="module" class="form-label fw-semibold">Módulo do Anúncio *</label>
                            <select class="form-select rounded-3" id="module" name="module" required>
                                <option value="services" selected>🛠️ Serviços (Prestador de Serviço)</option>
                                <option value="products">📱 Produtos</option>
                                <option value="real_estate">🏢 Imóveis</option>
                                <option value="vehicles">🚗 Veículos</option>
                                <option value="jobs">💼 Empregos</option>
                                <option value="agro">🚜 Agro</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Título do Anúncio / Serviço *</label>
                        <input type="text" class="form-control rounded-3" id="title" name="title" placeholder="Ex: Eletricista Residencial e Comercial em Aracaju" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="price" class="form-label fw-semibold">Valor / Diária (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control rounded-3" id="price" name="price" placeholder="Opcional para prestadores">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="city" class="form-label fw-semibold">Cidade em SE *</label>
                            <select class="form-select rounded-3" id="city" name="city" required>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Descrição do Serviço / Anúncio *</label>
                        <textarea class="form-control rounded-3" id="description" name="description" rows="4" placeholder="Descreva os serviços prestados, especialidades e dados de atendimento..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cadastrar Anúncio</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
