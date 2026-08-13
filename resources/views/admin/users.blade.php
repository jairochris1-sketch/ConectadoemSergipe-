@extends('layouts.admin')

@section('title', 'Gerenciar Usuários - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users text-primary me-2"></i> Gestão de Usuários e Clientes</h2>
        <p class="text-muted small mb-0">Pesquise contas, defina responsabilidades e controle o acesso à plataforma.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="fa-solid fa-user-plus me-1"></i> Cadastrar usuário
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    </div>
@endif
<div class="row g-3 mb-4">
    @foreach([
        ['Total',$metrics['total'],'fa-users','primary'],
        ['Administradores',$metrics['admins'],'fa-user-shield','success'],
        ['Colaboradores',$metrics['collaborators'],'fa-user-pen','info'],
        ['Suspensos',$metrics['suspended'],'fa-user-slash','danger'],
    ] as [$label,$value,$icon,$tone])
        <div class="col-6 col-lg-3"><div class="admin-user-metric"><i class="fa-solid {{ $icon }} text-{{ $tone }}"></i><div><strong>{{ $value }}</strong><span>{{ $label }}</span></div></div></div>
    @endforeach
</div>

<form method="GET" action="{{ route('admin.users') }}" class="admin-user-filter mb-4">
    <div class="admin-user-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Nome, usuário, e-mail ou telefone"></div>
    <select name="role" aria-label="Filtrar por perfil"><option value="">Todos os perfis</option><option value="user" @selected($role==='user')>Usuários</option><option value="collaborator" @selected($role==='collaborator')>Colaboradores</option><option value="admin" @selected($role==='admin')>Administradores</option></select>
    <select name="account_status" aria-label="Filtrar por situação"><option value="">Todas as situações</option><option value="active" @selected($accountStatus==='active')>Ativos</option><option value="suspended" @selected($accountStatus==='suspended')>Suspensos</option></select>
    <button class="btn btn-primary" type="submit">Filtrar</button>
    @if($search || $role || $accountStatus)<a class="btn btn-outline-secondary" href="{{ route('admin.users') }}">Limpar</a>@endif
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Cidade</th>
                        <th>Plano</th>
                        <th>Perfil</th>
                        <th>Situação</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $user->id }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>{{ $user->phone ?? 'Não informado' }}</td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $user->city ?? 'Aracaju' }}</span></td>
                        <td>
                            {{-- Dropdown de troca de plano --}}
                            <form action="{{ route('admin.users.assign_plan', $user) }}" method="POST" class="d-flex align-items-center gap-1">
                                @csrf
                                <select name="plan_slug" class="form-select form-select-sm rounded-3" style="min-width:130px;"
                                        onchange="this.form.submit()" title="Alterar plano do usuário">
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->slug }}"
                                            {{ ($user->subscription_plan ?? 'free') === $plan->slug ? 'selected' : '' }}>
                                            {{ $plan->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.users.toggle_role', $user->id) }}" method="POST">@csrf
                                <select name="role" class="form-select form-select-sm rounded-3" onchange="this.form.submit()" @disabled($user->is(auth()->user())) title="Definir responsabilidade">
                                    <option value="user" @selected($user->role==='user')>Usuário</option>
                                    <option value="collaborator" @selected($user->role==='collaborator')>Colaborador</option>
                                    <option value="admin" @selected($user->role==='admin')>Administrador</option>
                                </select>
                            </form>
                        </td>
                        <td>@if($user->suspended_at)<span class="badge bg-danger">Suspenso</span><small class="d-block text-muted mt-1">{{ $user->suspended_at->format('d/m/Y') }}</small>@else<span class="badge bg-success bg-opacity-10 text-success">Ativo</span>@endif</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex flex-wrap align-items-center justify-content-end gap-1">
                                <a href="{{ route('admin.ads', ['new_ad_for' => $user->id]) }}" class="btn btn-sm btn-outline-success rounded-pill" title="Criar Anúncio ou Perfil para este Cliente">
                                    <i class="fa-solid fa-square-plus me-1"></i>Anúncio
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="openEditUserModal({{ json_encode($user) }})" title="Editar e-mail e dados do cliente">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Editar
                                </button>
                                <form action="{{ route('admin.users.status', $user) }}" method="POST" class="d-inline">@csrf
                                    @if($user->suspended_at)<button class="btn btn-sm btn-outline-success rounded-pill" name="action" value="restore" @disabled($user->is(auth()->user()))><i class="fa-solid fa-user-check me-1"></i>Reativar</button>
                                    @else<button class="btn btn-sm btn-outline-warning rounded-pill" name="action" value="suspend" @disabled($user->is(auth()->user())) onclick="return confirm('Suspender o acesso desta conta?')"><i class="fa-solid fa-user-slash me-1"></i>Suspender</button>@endif
                                </form>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir permanentemente o cliente &quot;{{ addslashes($user->name) }}&quot;? Esta ação removerá a conta e todos os anúncios vinculados a ela.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" @disabled($user->is(auth()->user())) title="Excluir cliente permanentemente">
                                        <i class="fa-solid fa-trash-can me-1"></i>Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Nenhum usuário encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mb-4">{{ $users->links() }}</div>

<!-- Modal Cadastrar Cliente / Usuário -->
<div class="modal fade" id="newUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-plus text-primary me-2"></i> Cadastrar Novo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nome Completo *</label>
                        <input type="text" class="form-control rounded-3" id="name" name="name" placeholder="Ex: João da Silva" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Nome de usuário *</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control rounded-end-3" id="username" name="username" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._]+" placeholder="joaosilva" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mail do Cliente *</label>
                        <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="cliente@exemplo.com" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="phone" class="form-label fw-semibold">Telefone / WhatsApp</label>
                            <input type="text" class="form-control rounded-3" id="phone" name="phone" placeholder="79999999999">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="city" class="form-label fw-semibold">Cidade em SE</label>
                            <select class="form-select rounded-3" id="city" name="city">
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label fw-semibold">Senha Inicial *</label>
                            <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="role" class="form-label fw-semibold">Tipo de Perfil *</label>
                            <select class="form-select rounded-3" id="role" name="role">
                                <option value="user" selected>Cliente / Anunciante</option>
                                <option value="collaborator">Colaborador da Comunidade</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cadastrar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente / Usuário -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-pen text-primary me-2"></i> Editar Cliente / Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-semibold">Nome Completo *</label>
                        <input type="text" class="form-control rounded-3" id="edit_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_username" class="form-label fw-semibold">Nome de usuário *</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control rounded-end-3" id="edit_username" name="username" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._]+" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label fw-semibold">E-mail do Cliente *</label>
                        <input type="email" class="form-control rounded-3" id="edit_email" name="email" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="edit_phone" class="form-label fw-semibold">Telefone / WhatsApp</label>
                            <input type="text" class="form-control rounded-3" id="edit_phone" name="phone">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="edit_city" class="form-label fw-semibold">Cidade em SE</label>
                            <select class="form-select rounded-3" id="edit_city" name="city">
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}">{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="edit_password" class="form-label fw-semibold">Nova Senha (Opcional)</label>
                            <input type="password" class="form-control rounded-3" id="edit_password" name="password" placeholder="Preencha só se quiser alterar">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="edit_role" class="form-label fw-semibold">Tipo de Perfil *</label>
                            <select class="form-select rounded-3" id="edit_role" name="role">
                                <option value="user">Cliente / Anunciante</option>
                                <option value="collaborator">Colaborador da Comunidade</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditUserModal(user) {
        const form = document.getElementById('editUserForm');
        form.action = `/admin/usuarios/${user.id}/editar`;

        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_username').value = user.username || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_phone').value = user.phone || '';
        document.getElementById('edit_city').value = user.city || 'Aracaju';
        document.getElementById('edit_role').value = user.role || 'user';
        document.getElementById('edit_password').value = '';

        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
        modal.show();
    }
</script>
@endsection

@push('styles')
<style>
.admin-user-metric{display:flex;align-items:center;gap:12px;height:100%;padding:15px;color:var(--foreground);background:var(--card);border:1px solid var(--border);border-radius:16px}.admin-user-metric>i{width:38px;height:38px;display:grid;place-items:center;background:var(--muted-bg);border-radius:11px}.admin-user-metric strong,.admin-user-metric span{display:block}.admin-user-metric strong{font-size:1.2rem;line-height:1}.admin-user-metric span{margin-top:4px;color:var(--muted-foreground);font-size:.68rem}.admin-user-filter{display:grid;grid-template-columns:minmax(260px,1fr) 190px 190px auto auto;gap:10px;padding:14px;background:var(--card);border:1px solid var(--border);border-radius:16px}.admin-user-filter select,.admin-user-search{min-height:42px;color:var(--foreground);background:var(--muted-bg);border:1px solid var(--border);border-radius:10px}.admin-user-filter select{padding:0 10px}.admin-user-search{display:flex;align-items:center;gap:9px;padding:0 12px}.admin-user-search input{width:100%;color:inherit;background:transparent;border:0;outline:0}@media(max-width:1399.98px){.admin-user-filter{grid-template-columns:1fr 1fr}.admin-user-search{grid-column:1/-1}}@media(max-width:575px){.admin-user-filter{grid-template-columns:1fr}.admin-user-filter>*{grid-column:1}}
</style>
@endpush
