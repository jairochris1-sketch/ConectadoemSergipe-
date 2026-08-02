@extends('layouts.admin')

@section('title', 'Gerenciar Usuários - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users text-primary me-2"></i> Gestão de Usuários e Clientes</h2>
        <p class="text-muted small mb-0">Cadastre clientes ou gerencie o nível de privilégios.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="fa-solid fa-user-plus me-1"></i> Cadastrar Novo Cliente
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
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Cidade</th>
                        <th>Plano</th>
                        <th>Perfil</th>
                        <th class="text-end pe-4">Ação</th>
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
                        <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ strtoupper($user->role) }}</span></td>
                        <td class="text-end pe-4">
                            <form action="{{ route('admin.users.toggle_role', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" @disabled($user->is(auth()->user())) title="{{ $user->is(auth()->user()) ? 'Seu próprio acesso não pode ser alterado' : '' }}">
                                    {{ $user->role === 'admin' ? 'Tornar Usuário' : 'Tornar Admin' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Nenhum usuário cadastrado ainda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

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
@endsection
