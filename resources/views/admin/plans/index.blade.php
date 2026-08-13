@extends('layouts.admin')

@section('title', 'Gestão de Planos - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-layer-group text-primary me-2"></i> Gestão de Planos</h2>
        <p class="text-muted small mb-0">Gerencie os grupos de acesso e os serviços incluídos em cada plano.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#newPlanModal">
        <i class="fa-solid fa-plus me-1"></i> Novo Plano
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-3 mb-4"><i class="fa-solid fa-circle-xmark me-2"></i>{{ session('error') }}</div>
@endif

{{-- ─── Cards dos Planos ────────────────────────────────────────────────── --}}
<div class="row g-3 mb-5">
    @foreach($plans as $plan)
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 {{ $plan->is_highlighted ? 'border-2 border-primary' : '' }} position-relative">
            @if($plan->badge_label)
                <span class="position-absolute top-0 end-0 badge bg-primary m-2 rounded-pill">{{ $plan->badge_label }}</span>
            @endif
            @if(!$plan->is_active)
                <span class="position-absolute top-0 start-0 badge bg-danger m-2 rounded-pill">Inativo</span>
            @endif
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    @php
                        $adminBadgeStyle = match($plan->slug) {
                            'start'      => 'background:#e0f2fe;color:#0284c7;',
                            'pro'        => 'background:#dbeafe;color:#1d4ed8;',
                            'enterprise' => 'background:#ede9fe;color:#7c3aed;',
                            default      => 'background:#e2e8f0;color:#64748b;',
                        };
                        $adminBadgeIcon = match($plan->slug) {
                            'start'      => 'fa-rocket',
                            'pro'        => 'fa-star',
                            'enterprise' => 'fa-crown',
                            default      => 'fa-leaf',
                        };
                    @endphp
                    <span class="badge fw-bold px-3 py-2 rounded-pill" style="font-size:.78rem;{{ $adminBadgeStyle }}">
                        <i class="fa-solid {{ $adminBadgeIcon }} me-1"></i>
                        {{ strtoupper($plan->slug) }}
                    </span>
                    <span class="fw-bold text-dark fs-5">
                        @if($plan->price == 0)
                            Gratuito
                        @else
                            R$ {{ number_format($plan->price, 2, ',', '.') }}<small class="text-muted fw-normal fs-6">/mês</small>
                        @endif
                    </span>
                </div>
                <h5 class="fw-bold text-dark mb-1">{{ $plan->name }}</h5>
                <p class="text-muted small mb-3">{{ $plan->headline }}</p>
                <p class="text-muted small">{{ $plan->feature_values_count }} feature(s) configurada(s)</p>

                <div class="d-flex gap-2 flex-wrap mt-auto">
                    <a href="{{ route('admin.plans.features', $plan) }}"
                       class="btn btn-sm btn-outline-primary rounded-pill flex-fill">
                        <i class="fa-solid fa-list-check me-1"></i> Features
                    </a>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill"
                            data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <form action="{{ route('admin.plans.toggle', $plan) }}" method="POST">
                        @csrf
                        <button type="submit" title="{{ $plan->is_active ? 'Desativar' : 'Ativar' }}"
                                class="btn btn-sm btn-outline-{{ $plan->is_active ? 'warning' : 'success' }} rounded-pill">
                            <i class="fa-solid fa-{{ $plan->is_active ? 'eye-slash' : 'eye' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Editar Plano --}}
        <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Editar Plano: {{ $plan->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-body px-4">
                            @include('admin.plans._form', ['plan' => $plan])
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ─── Tabela de Features ──────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-puzzle-piece text-primary me-2"></i> Serviços / Features disponíveis</h5>
    <button class="btn btn-outline-primary rounded-pill btn-sm px-3" data-bs-toggle="modal" data-bs-target="#newFeatureModal">
        <i class="fa-solid fa-plus me-1"></i> Nova Feature
    </button>
</div>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Chave (Key)</th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Ordem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($features as $feature)
                <tr>
                    <td class="ps-4"><code class="text-primary">{{ $feature->key }}</code></td>
                    <td class="fw-semibold">{{ $feature->name }}</td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">{{ $feature->type }}</span>
                    </td>
                    <td>{{ $feature->sort_order }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">Nenhuma feature cadastrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ─── Modal Novo Plano ────────────────────────────────────────────────── --}}
<div class="modal fade" id="newPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-layer-group text-primary me-2"></i> Novo Plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.plans.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4">
                    @include('admin.plans._form', ['plan' => null])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Criar Plano</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Modal Nova Feature ──────────────────────────────────────────────── --}}
<div class="modal fade" id="newFeatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-puzzle-piece text-primary me-2"></i> Nova Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.features.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Chave (key) *</label>
                        <input type="text" name="key" class="form-control rounded-3" placeholder="ex: video_limit" required>
                        <div class="form-text">Somente letras, números e underscore. Ex: <code>store_limit</code></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome para exibição *</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="ex: Vídeos por loja" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo *</label>
                        <select name="type" class="form-select rounded-3">
                            <option value="integer">Numérico (com limite)</option>
                            <option value="boolean">Boolean (sim/não)</option>
                            <option value="unlimited">Sempre ilimitado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ordem de exibição</label>
                        <input type="number" name="sort_order" class="form-control rounded-3" value="99">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Criar Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
