@extends('layouts.admin')

@section('title', 'Gerenciar Anúncios - Painel Admin')

@section('content')
@push('styles')
<style>
    /* Estilização da barra de rolagem do Modal Admin */
    #newAdModal .modal-body {
        max-height: calc(80vh - 100px);
        overflow-y: auto !important;
        scrollbar-width: thin;
        scrollbar-color: #0d6efd rgba(0, 0, 0, 0.1);
    }
    #newAdModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    #newAdModal .modal-body::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 4px;
    }
    #newAdModal .modal-body::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 4px;
    }
</style>
@endpush

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

<!-- Filtros por Módulo e Pesquisa -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 p-md-4">
    <form action="{{ route('admin.ads') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-12 col-xl-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control bg-light border-start-0 rounded-end-3" placeholder="Buscar por título, ID ou anunciante...">
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <select name="module" class="form-select bg-light rounded-3" onchange="this.form.submit()">
                <option value="">Todos os Módulos ({{ $ads->total() }})</option>
                <option value="real_estate" {{ request('module') === 'real_estate' ? 'selected' : '' }}>Imóveis</option>
                <option value="vehicles" {{ request('module') === 'vehicles' ? 'selected' : '' }}>Veículos</option>
                <option value="products" {{ request('module') === 'products' ? 'selected' : '' }}>Produtos</option>
                <option value="services" {{ request('module') === 'services' ? 'selected' : '' }}>Serviços</option>
                <option value="jobs" {{ request('module') === 'jobs' ? 'selected' : '' }}>Empregos</option>
                <option value="agro" {{ request('module') === 'agro' ? 'selected' : '' }}>Agro</option>
                <option value="culture" {{ request('module') === 'culture' ? 'selected' : '' }}>Arte & Cultura</option>
                <option value="stores" {{ request('module') === 'stores' ? 'selected' : '' }}>Lojas & Negócios</option>
            </select>
        </div>
        <div class="col-12 col-md-6 col-xl-2">
            <select name="status" class="form-select bg-light rounded-3" onchange="this.form.submit()">
                <option value="">Todos os status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pendentes</option>
                <option value="active" @selected(request('status') === 'active')>Ativos</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inativos</option>
                <option value="sold" @selected(request('status') === 'sold')>Vendidos</option>
                <option value="banned" @selected(request('status') === 'banned')>Bloqueados</option>
            </select>
        </div>
        <div class="col-12 col-xl-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark rounded-3 w-100 fw-bold">
                <i class="fa-solid fa-filter me-1"></i> Filtrar
            </button>
            @if(request('module') || request('status') || request('q'))
                <a href="{{ route('admin.ads') }}" class="btn btn-outline-secondary rounded-3" title="Limpar Filtros">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>
</div>

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
                        <td class="fw-bold text-success">{{ $item->formatted_price }}</td>
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
                            <div class="d-inline-flex flex-wrap align-items-center justify-content-end gap-1">
                                <a href="{{ route('ad.show', $item->slug) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Visualizar anúncio">Ver</a>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick='openEditAdModal(@json($item))' title="Editar anúncio no Painel Admin">Editar</button>
                                <form action="{{ route('admin.ads.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir permanentemente o anúncio/prestador &quot;{{ addslashes($item->title) }}&quot;? Esta ação não pode ser desfeita.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Excluir Anúncio / Prestador">
                                        <i class="fa-solid fa-trash-can me-1"></i> Excluir
                                    </button>
                                </form>
                            </div>
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
        @if($ads->hasPages())
        <div class="card-footer bg-white border-0 pt-3 pb-2 px-4">
            {{ $ads->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Cadastrar Anúncio / Prestador de Serviço (Completo) -->
<div class="modal fade" id="newAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-square-plus text-primary me-2"></i> Cadastrar Anúncio / Prestador de Serviço</h5>
                    <p class="text-muted small mb-0">Preencha os dados abaixo ou <a href="{{ route('ad.create') }}" target="_blank" class="fw-bold text-primary">abra o Formulário Completo do Site em nova aba <i class="fa-solid fa-up-right-from-square ms-1"></i></a>.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.ads.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">

                    {{-- Bloco 1: Cliente e Módulo --}}
                    <div class="card border-0 bg-primary bg-opacity-10 p-3 rounded-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="fa-solid fa-user me-1 text-primary"></i> Cliente / Anunciante *
                            </label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="toggle_new_client" onchange="toggleNewClientFields(this.checked)" role="switch">
                                <label class="form-check-label fw-bold text-primary small cursor-pointer" for="toggle_new_client" style="cursor: pointer;">
                                    <i class="fa-solid fa-user-plus me-1"></i> Cadastrar Novo Cliente Agora
                                </label>
                            </div>
                        </div>

                        {{-- Selecionar Cliente Existente --}}
                        <div id="select_client_box">
                            <select class="form-select rounded-3" id="user_id" name="user_id">
                                <option value="">-- Selecione o cliente da lista --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected($u->email === 'conectadoemsergipe@gmail.com' || (request('new_ad_for') == $u->id))>
                                        {{ $u->name }} ({{ $u->email }}{{ $u->phone ? ' - ' . $u->phone : '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Formulário Rápido de Novo Cliente --}}
                        <div id="new_client_box" class="d-none pt-2 mt-2 border-top border-primary border-opacity-25">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark mb-1">Nome Completo *</label>
                                    <input type="text" class="form-control form-control-sm rounded-3" id="new_client_name" name="new_client_name" placeholder="Ex: João da Silva">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark mb-1">E-mail (Opcional)</label>
                                    <input type="email" class="form-control form-control-sm rounded-3" id="new_client_email" name="new_client_email" placeholder="cliente@exemplo.com">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-dark mb-1">Telefone/WhatsApp (Opcional)</label>
                                    <input type="text" class="form-control form-control-sm rounded-3" id="new_client_phone" name="new_client_phone" placeholder="79999999999">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-wand-magic-sparkles text-primary me-1"></i> A conta deste novo cliente será criada automaticamente ao salvar.
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="module" class="form-label fw-semibold">Módulo do Anúncio *</label>
                        <select class="form-select rounded-3" id="module" name="module" required onchange="filterAdminCategories()">
                            <option value="services" selected>🛠️ Serviços (Prestador de Serviço)</option>
                            <option value="products">📱 Produtos</option>
                            <option value="real_estate">🏢 Imóveis</option>
                            <option value="vehicles">🚗 Veículos</option>
                            <option value="jobs">💼 Empregos</option>
                            <option value="agro">🚜 Agro</option>
                            <option value="culture">🎭 Arte & Cultura</option>
                        </select>
                    </div>

                    {{-- Bloco 2: Categoria e Título --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Categoria / Subcategoria</label>
                            <select class="form-select rounded-3" id="category_id" name="category_id">
                                <option value="">Sem categoria específica</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" data-module="{{ $cat->module }}">
                                        {{ $cat->parent_id ? '└─ ' : '' }}{{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="price" id="price_label" class="form-label fw-semibold">Preço Médio / Diária / Sob Orçamento (R$)</label>
                            <input type="text" class="form-control rounded-3" id="price" name="price" placeholder="Ex: 80,00 ou 1.500,00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" id="title_label" class="form-label fw-semibold">Título do Anúncio / Serviço *</label>
                        <input type="text" class="form-control rounded-3" id="title" name="title" placeholder="Ex: Eletricista Residencial e Comercial em Aracaju" required>
                    </div>

                    {{-- Bloco 3: Contatos e Localização --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="contact_whatsapp" class="form-label fw-semibold"><i class="fa-brands fa-whatsapp text-success me-1"></i> WhatsApp de Atendimento</label>
                            <input type="text" class="form-control rounded-3" id="contact_whatsapp" name="contact_whatsapp" placeholder="(79) 99999-9999">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="contact_phone" class="form-label fw-semibold"><i class="fa-solid fa-phone text-primary me-1"></i> Telefone Fixo / Celular</label>
                            <input type="text" class="form-control rounded-3" id="contact_phone" name="contact_phone" placeholder="(79) 3333-3333">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="contact_telegram" class="form-label fw-semibold"><i class="fa-brands fa-telegram text-info me-1"></i> Telegram</label>
                            <input type="text" class="form-control rounded-3" id="contact_telegram" name="contact_telegram" placeholder="@usuario ou (79) 99999-9999">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-5">
                            <label for="city" class="form-label fw-semibold">Cidade em SE *</label>
                            <select class="form-select rounded-3" id="city" name="city" required>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="public_address" class="form-label fw-semibold">Endereço Público / Bairro</label>
                            <input type="text" class="form-control rounded-3" id="public_address" name="public_address" placeholder="Ex: Rua Laranjeiras, Bairro Centro - Aracaju">
                        </div>
                    </div>

                    {{-- Bloco 4: Fotos e Mídias (Rótulos Dinâmicos e Lógicos por Módulo) --}}
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-images text-primary me-2"></i> Fotos e Mídias do Anúncio / Perfil</h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="logo" id="logo_label" class="form-label fw-semibold text-dark mb-1"><i class="fa-solid fa-user-gear text-warning me-1"></i> Foto de Perfil / Avatar</label>
                                <input type="file" class="form-control form-control-sm rounded-3" id="logo" name="logo" accept="image/*">
                                <small id="logo_help" class="text-muted d-block mt-1" style="font-size: 0.72rem;">Foto de rosto do profissional ou logo da empresa.</small>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="banner" id="banner_label" class="form-label fw-semibold text-dark mb-1"><i class="fa-solid fa-panorama text-info me-1"></i> Capa de Fundo (Banner)</label>
                                <input type="file" class="form-control form-control-sm rounded-3" id="banner" name="banner" accept="image/*">
                                <small id="banner_help" class="text-muted d-block mt-1" style="font-size: 0.72rem;">Imagem de topo do perfil ou anúncio.</small>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="card_image" id="card_image_label" class="form-label fw-semibold text-dark mb-1"><i class="fa-solid fa-camera text-primary me-1"></i> Foto Principal / Vitrine</label>
                                <input type="file" class="form-control form-control-sm rounded-3" id="card_image" name="card_image" accept="image/*">
                                <small id="card_image_help" class="text-muted d-block mt-1" style="font-size: 0.72rem;">Foto exibida nas buscas e cartões.</small>
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 5: Configurações de Destaque Admin --}}
                    <div class="form-check mb-3 p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 ms-0">
                        <div class="form-check ms-1">
                            <input class="form-check-input" type="checkbox" id="is_plan_featured" name="is_plan_featured" value="1" checked>
                            <label class="form-check-label fw-bold text-dark" for="is_plan_featured">
                                ⭐ Marcar como Destaque Pago (Aparece no carrossel de Prestadores/Destaques da Página Inicial)
                            </label>
                        </div>
                    </div>

                    <div class="form-check mb-3 p-3 bg-light rounded-3 border ms-0" id="profile_is_claimed_wrapper">
                        <div class="form-check ms-1">
                            <input class="form-check-input" type="checkbox" id="profile_is_claimed" name="profile_is_claimed" value="1">
                            <label class="form-check-label fw-bold text-dark" for="profile_is_claimed">
                                <i class="fa-solid fa-circle-check text-success me-1"></i> Perfil Reivindicado (Confirmado pelo profissional)
                            </label>
                            <small class="text-muted d-block mt-1">Por padrão fica desmarcado (Perfil não reivindicado) para que o profissional possa reivindicá-lo no futuro.</small>
                        </div>
                    </div>

                    {{-- Bloco 6: Descrição Detalhada --}}
                    <div class="mb-3">
                        <label for="description" id="description_label" class="form-label fw-semibold">Descrição dos Serviços e Especialidades *</label>
                        <textarea class="form-control rounded-3" id="description" name="description" rows="4" placeholder="Descreva os serviços prestados, horário de atendimento, bairros atendidos..." required></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-check me-1"></i> Cadastrar Anúncio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Anúncio / Prestador de Serviço (Admin) -->
<div class="modal fade" id="editAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-warning me-2"></i> Editar Anúncio / Prestador (Admin)</h5>
                    <p class="text-muted small mb-0">Altere os dados diretamente pelo painel administrativo.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAdForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">

                    {{-- Categoria e Preço --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="edit_category_id" class="form-label fw-semibold">Categoria / Subcategoria</label>
                            <select class="form-select rounded-3" id="edit_category_id" name="category_id">
                                <option value="">Sem categoria específica</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" data-module="{{ $cat->module }}">
                                        {{ $cat->parent_id ? '└─ ' : '' }}{{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="edit_price" class="form-label fw-semibold">Preço / Valor (R$)</label>
                            <input type="text" class="form-control rounded-3" id="edit_price" name="price" placeholder="Ex: 80,00 ou 1.500,00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_title" class="form-label fw-semibold">Título do Anúncio / Nome do Profissional *</label>
                        <input type="text" class="form-control rounded-3" id="edit_title" name="title" required>
                    </div>

                    {{-- Contatos --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="edit_contact_whatsapp" class="form-label fw-semibold"><i class="fa-brands fa-whatsapp text-success me-1"></i> WhatsApp</label>
                            <input type="text" class="form-control rounded-3" id="edit_contact_whatsapp" name="contact_whatsapp" placeholder="(79) 99999-9999">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="edit_contact_phone" class="form-label fw-semibold"><i class="fa-solid fa-phone text-primary me-1"></i> Telefone</label>
                            <input type="text" class="form-control rounded-3" id="edit_contact_phone" name="contact_phone" placeholder="(79) 3333-3333">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="edit_contact_telegram" class="form-label fw-semibold"><i class="fa-brands fa-telegram text-info me-1"></i> Telegram</label>
                            <input type="text" class="form-control rounded-3" id="edit_contact_telegram" name="contact_telegram" placeholder="@usuario ou (79) 99999-9999">
                        </div>
                    </div>

                    {{-- Cidade e Endereço --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-5">
                            <label for="edit_city" class="form-label fw-semibold">Cidade em SE *</label>
                            <select class="form-select rounded-3" id="edit_city" name="city" required>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="edit_public_address" class="form-label fw-semibold">Endereço Público / Bairro</label>
                            <input type="text" class="form-control rounded-3" id="edit_public_address" name="public_address" placeholder="Ex: Rua Laranjeiras, Bairro Centro - Aracaju">
                        </div>
                    </div>

                    {{-- Mídias com opção de substituir --}}
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-images text-primary me-2"></i> Substituir Fotos / Mídias (Deixe em branco para manter as atuais)</h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="edit_logo" class="form-label fw-semibold text-dark mb-1"><i class="fa-solid fa-user-gear text-warning me-1"></i> Foto de Perfil / Logo</label>
                                <input type="file" class="form-control form-control-sm rounded-3" id="edit_logo" name="logo" accept="image/*">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_banner" class="form-label fw-semibold text-dark mb-1"><i class="fa-solid fa-panorama text-info me-1"></i> Capa / Banner</label>
                                <input type="file" class="form-control form-control-sm rounded-3" id="edit_banner" name="banner" accept="image/*">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="edit_card_image" class="form-label fw-semibold text-dark mb-1"><i class="fa-solid fa-camera text-primary me-1"></i> Foto Principal / Vitrine</label>
                                <input type="file" class="form-control form-control-sm rounded-3" id="edit_card_image" name="card_image" accept="image/*">
                            </div>
                        </div>
                    </div>

                    {{-- Status e Destaque --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="edit_status" class="form-label fw-semibold">Status do Anúncio *</label>
                            <select class="form-select rounded-3" id="edit_status" name="status" required>
                                <option value="active">Ativo</option>
                                <option value="pending">Pendente</option>
                                <option value="inactive">Inativo</option>
                                <option value="sold">Vendido / Concluído</option>
                                <option value="banned">Bloqueado</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check p-2.5 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 w-100 mb-2 ms-0">
                                <input class="form-check-input ms-1" type="checkbox" id="edit_is_plan_featured" name="is_plan_featured" value="1">
                                <label class="form-check-label fw-bold text-dark ms-1" for="edit_is_plan_featured">
                                    ⭐ Destaque Pago
                                </label>
                            </div>
                            <div class="form-check p-2.5 bg-light rounded-3 border w-100 mb-0 ms-0">
                                <input class="form-check-input ms-1" type="checkbox" id="edit_profile_is_claimed" name="profile_is_claimed" value="1">
                                <label class="form-check-label fw-bold text-dark ms-1" for="edit_profile_is_claimed">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Perfil Reivindicado
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div class="mb-3">
                        <label for="edit_description" class="form-label fw-semibold">Descrição Detalhada *</label>
                        <textarea class="form-control rounded-3" id="edit_description" name="description" rows="4" required></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const MODULE_CONFIGS = {
        services: {
            titleLabel: 'Nome do Profissional ou Serviço *',
            titlePlaceholder: 'Ex: Eletricista Residencial e Comercial em Aracaju',
            priceLabel: 'Preço Médio / Diária / Sob Orçamento (R$)',
            pricePlaceholder: 'Ex: 150 (ou deixe em branco se for sob orçamento)',
            logoLabel: '<i class="fa-solid fa-user-gear text-warning me-1"></i> Foto de Perfil do Profissional',
            logoHelp: 'Foto de rosto do profissional ou logo da empresa.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Capa de Fundo do Perfil',
            bannerHelp: 'Banner que fica no topo do perfil de atendimento.',
            cardImageLabel: '<i class="fa-solid fa-camera text-primary me-1"></i> Foto de Amostra do Trabalho',
            cardImageHelp: 'Foto de demonstração do serviço (aparece nas buscas).',
            descLabel: 'Descrição dos Serviços e Especialidades *',
            descPlaceholder: 'Descreva os serviços prestados, horário de atendimento, bairros atendidos...'
        },
        products: {
            titleLabel: 'Nome do Produto *',
            titlePlaceholder: 'Ex: Smartphone Samsung Galaxy S23 256GB 5G',
            priceLabel: 'Preço do Produto (R$)',
            pricePlaceholder: 'Ex: 80 ou 80.000 ou 1.250',
            cardImageLabel: '<i class="fa-solid fa-image text-primary me-1"></i> Foto Principal do Produto *',
            cardImageHelp: 'Foto principal exibida nas buscas e cartões.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Imagem de Capa (Opcional)',
            bannerHelp: 'Foto secundária ou banner do produto.',
            logoLabel: '<i class="fa-solid fa-shop text-warning me-1"></i> Logomarca da Loja (Opcional)',
            logoHelp: 'Logo da empresa ou marca do vendedor.',
            descLabel: 'Descrição do Produto e Estado de Conservação *',
            descPlaceholder: 'Descreva o produto, se é novo ou usado, se acompanha caixa e acessórios...'
        },
        real_estate: {
            titleLabel: 'Título do Imóvel *',
            titlePlaceholder: 'Ex: Casa Duplex com 3 Suítes e Piscina no Bairro Jardins',
            priceLabel: 'Valor de Venda ou Aluguel (R$)',
            pricePlaceholder: 'Ex: 450.000 ou 1.800/mês',
            cardImageLabel: '<i class="fa-solid fa-house-chimney text-primary me-1"></i> Foto da Fachada / Principal *',
            cardImageHelp: 'Foto principal do imóvel exibida na busca.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Foto Ampla de Capa',
            bannerHelp: 'Foto panorâmica do imóvel.',
            logoLabel: '<i class="fa-solid fa-building text-warning me-1"></i> Logo da Imobiliária',
            logoHelp: 'Logo da imobiliária ou corretor responsável.',
            descLabel: 'Descrição Completa do Imóvel *',
            descPlaceholder: 'Informe o número de quartos, banheiros, vagas, metragem e diferenciais...'
        },
        vehicles: {
            titleLabel: 'Modelo, Marca e Ano do Veículo *',
            titlePlaceholder: 'Ex: Toyota Corolla 2.0 Flex XEi 2022 Completo',
            priceLabel: 'Preço do Veículo (R$)',
            pricePlaceholder: 'Ex: 115.000',
            cardImageLabel: '<i class="fa-solid fa-car text-primary me-1"></i> Foto Principal do Veículo *',
            cardImageHelp: 'Foto principal do veículo exibida na busca.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Foto de Capa do Veículo',
            bannerHelp: 'Foto ampla do veículo.',
            logoLabel: '<i class="fa-solid fa-warehouse text-warning me-1"></i> Logo da Garagem / Vendedor',
            logoHelp: 'Logo da loja de carros ou vendedor.',
            descLabel: 'Descrição Detalhada do Veículo *',
            descPlaceholder: 'Informe quilometragem, revisão, opcionais, aceita troca...'
        },
        jobs: {
            titleLabel: 'Título da Vaga de Emprego *',
            titlePlaceholder: 'Ex: Vendedor Interno para Loja no Shopping',
            priceLabel: 'Salário / Remuneração (R$)',
            pricePlaceholder: 'Ex: 1.800 (ou A combinar)',
            cardImageLabel: '<i class="fa-solid fa-briefcase text-primary me-1"></i> Imagem Principal da Vaga',
            cardImageHelp: 'Arte ou imagem explicativa da vaga.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Banner da Empresa',
            bannerHelp: 'Banner de apresentação da empresa.',
            logoLabel: '<i class="fa-solid fa-building text-warning me-1"></i> Logo da Empresa Contratante',
            logoHelp: 'Logo da marca ou empresa contratante.',
            descLabel: 'Descrição da Vaga, Requisitos e Benefícios *',
            descPlaceholder: 'Descreva as atribuições da vaga, requisitos mínimos e horário de trabalho...'
        },
        agro: {
            titleLabel: 'Nome do Item / Animal / Equipamento *',
            titlePlaceholder: 'Ex: Trator Massey Ferguson 275 com Implementos',
            priceLabel: 'Preço / Valor por Unidade ou Saca (R$)',
            pricePlaceholder: 'Ex: 75.000 ou 85/saca',
            cardImageLabel: '<i class="fa-solid fa-tractor text-primary me-1"></i> Foto do Item Agro *',
            cardImageHelp: 'Foto do animal, semente ou trator.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Banner do Anúncio Agro',
            bannerHelp: 'Foto ampla da fazenda ou máquina.',
            logoLabel: '<i class="fa-solid fa-wheat-awn text-warning me-1"></i> Logo do Produtor / Marca',
            logoHelp: 'Marca do produtor ou fazenda.',
            descLabel: 'Descrição Detalhada do Item Agro *',
            descPlaceholder: 'Descreva detalhes da criação, maquinário, nota fiscal e entrega...'
        },
        culture: {
            titleLabel: 'Nome da Obra, Livro ou Atração Cultural *',
            titlePlaceholder: 'Ex: Escultura em Cerâmica Popular de Sergipe',
            priceLabel: 'Valor da Obra ou Ingresso (R$)',
            pricePlaceholder: 'Ex: 120',
            cardImageLabel: '<i class="fa-solid fa-palette text-primary me-1"></i> Foto da Obra / Capa *',
            cardImageHelp: 'Foto da escultura, livro ou obra de arte.',
            bannerLabel: '<i class="fa-solid fa-panorama text-info me-1"></i> Banner do Evento / Exposição',
            bannerHelp: 'Banner de promoção do evento cultural.',
            logoLabel: '<i class="fa-solid fa-masks-theater text-warning me-1"></i> Assinatura / Logo do Artista',
            logoHelp: 'Marca d’água ou logo do artista.',
            descLabel: 'Descrição da Obra ou Projeto Cultural *',
            descPlaceholder: 'Descreva a obra, artista, técnica utilizada e história...'
        }
    };

    function filterAdminCategories() {
        const moduleSelect = document.getElementById('module');
        const categorySelect = document.getElementById('category_id');
        if (!moduleSelect || !categorySelect) return;

        const selectedModule = moduleSelect.value;
        const options = categorySelect.querySelectorAll('option[data-module]');

        options.forEach(opt => {
            if (!selectedModule || opt.getAttribute('data-module') === selectedModule) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });

        // Reset seleção se a atual ficar oculta
        const selectedOpt = categorySelect.options[categorySelect.selectedIndex];
        if (selectedOpt && selectedOpt.style.display === 'none') {
            categorySelect.value = '';
        }

        // Atualizar textos e rótulos dinamicamente conforme o módulo
        const config = MODULE_CONFIGS[selectedModule] || MODULE_CONFIGS.services;

        const titleLabel = document.getElementById('title_label');
        const titleInput = document.getElementById('title');
        if (titleLabel && titleInput) {
            titleLabel.innerText = config.titleLabel;
            titleInput.placeholder = config.titlePlaceholder;
        }

        const priceLabel = document.getElementById('price_label');
        const priceInput = document.getElementById('price');
        if (priceLabel && priceInput) {
            priceLabel.innerText = config.priceLabel;
            priceInput.placeholder = config.pricePlaceholder;
        }

        const logoLabel = document.getElementById('logo_label');
        const logoHelp = document.getElementById('logo_help');
        if (logoLabel && config.logoLabel) logoLabel.innerHTML = config.logoLabel;
        if (logoHelp && config.logoHelp) logoHelp.innerText = config.logoHelp;

        const bannerLabel = document.getElementById('banner_label');
        const bannerHelp = document.getElementById('banner_help');
        if (bannerLabel && config.bannerLabel) bannerLabel.innerHTML = config.bannerLabel;
        if (bannerHelp && config.bannerHelp) bannerHelp.innerText = config.bannerHelp;

        const cardImageLabel = document.getElementById('card_image_label');
        const cardImageHelp = document.getElementById('card_image_help');
        if (cardImageLabel && config.cardImageLabel) cardImageLabel.innerHTML = config.cardImageLabel;
        if (cardImageHelp && config.cardImageHelp) cardImageHelp.innerText = config.cardImageHelp;

        const descLabel = document.getElementById('description_label');
        const descInput = document.getElementById('description');
        if (descLabel && descInput) {
            descLabel.innerText = config.descLabel;
            descInput.placeholder = config.descPlaceholder;
        }
    }

    function toggleNewClientFields(isNew) {
        const selectBox = document.getElementById('select_client_box');
        const newClientBox = document.getElementById('new_client_box');
        const userIdSelect = document.getElementById('user_id');

        if (isNew) {
            newClientBox.classList.remove('d-none');
            selectBox.classList.add('d-none');
            if (userIdSelect) userIdSelect.value = '';
            const nameInput = document.getElementById('new_client_name');
            if (nameInput) nameInput.focus();
        } else {
            newClientBox.classList.add('d-none');
            selectBox.classList.remove('d-none');
            const nameInput = document.getElementById('new_client_name');
            const emailInput = document.getElementById('new_client_email');
            const phoneInput = document.getElementById('new_client_phone');
            if (nameInput) nameInput.value = '';
            if (emailInput) emailInput.value = '';
            if (phoneInput) phoneInput.value = '';
        }
    }

    function openEditAdModal(ad) {
        const form = document.getElementById('editAdForm');
        form.action = `/admin/anuncios/${ad.id}/editar`;

        document.getElementById('edit_title').value = ad.title || '';
        document.getElementById('edit_price').value = ad.price || '';
        document.getElementById('edit_category_id').value = ad.category_id || '';
        document.getElementById('edit_contact_whatsapp').value = ad.contact_whatsapp || '';
        document.getElementById('edit_contact_phone').value = ad.contact_phone || '';
        document.getElementById('edit_contact_telegram').value = ad.contact_telegram || '';
        document.getElementById('edit_city').value = ad.city || 'Aracaju';
        document.getElementById('edit_public_address').value = ad.public_address || '';
        document.getElementById('edit_status').value = ad.status || 'active';
        document.getElementById('edit_is_plan_featured').checked = !!ad.is_plan_featured;
        document.getElementById('edit_profile_is_claimed').checked = !!ad.is_claimed;
        document.getElementById('edit_description').value = ad.description || '';

        const modal = new bootstrap.Modal(document.getElementById('editAdModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', () => {
        filterAdminCategories();

        @if(request('new_ad_for'))
            const modal = new bootstrap.Modal(document.getElementById('newAdModal'));
            modal.show();
        @endif
    });
</script>
@endsection
