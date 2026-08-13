@extends('layouts.admin')

@section('title', 'Features do Plano ' . $plan->name . ' - Painel Admin')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.plans.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
        <i class="fa-solid fa-arrow-left me-1"></i> Voltar
    </a>
    <div>
        <h2 class="fw-bold text-dark mb-0">
            <i class="fa-solid fa-list-check text-primary me-2"></i> Features do Plano:
            <span class="text-primary">{{ $plan->name }}</span>
        </h2>
        <p class="text-muted small mb-0">
            Gerencie os serviços incluídos neste grupo de acesso.
            <strong>Qualquer alteração aqui afeta todos os assinantes automaticamente.</strong>
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
@endif

<div class="row g-4">
    {{-- ─── Features do Plano ──────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header border-0 pt-4 px-4 pb-2">
                <h6 class="fw-bold text-body mb-0">
                    <i class="fa-solid fa-check-circle text-success me-2"></i>
                    Features ativas neste plano ({{ $plan->featureValues->count() }})
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Serviço / Feature</th>
                            <th>Valor</th>
                            <th>Exibir na página</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plan->featureValues->sortBy('feature.sort_order') as $fv)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $fv->feature->name }}</div>
                                <code class="text-muted small">{{ $fv->feature->key }}</code>
                            </td>
                            <td>
                                <form action="{{ route('admin.plans.features.update', [$plan, $fv]) }}" method="POST" class="d-flex align-items-center gap-2">
                                    @csrf @method('PUT')
                                    @if($fv->feature->type === 'boolean')
                                        <select name="value" class="form-select form-select-sm rounded-3" style="width:auto;">
                                            <option value="1" {{ $fv->value === '1' ? 'selected' : '' }}>✅ Sim</option>
                                            <option value="0" {{ $fv->value === '0' ? 'selected' : '' }}>❌ Não</option>
                                        </select>
                                    @else
                                        <select name="value" class="form-select form-select-sm rounded-3" style="width:auto;" id="valSel{{ $fv->id }}">
                                            <option value="unlimited" {{ is_null($fv->value) ? 'selected' : '' }}>∞ Ilimitado</option>
                                            <option value="{{ $fv->value }}" {{ !is_null($fv->value) && $fv->value !== '0' ? 'selected' : '' }}>
                                                {{ $fv->value ?? 'Ilimitado' }}
                                            </option>
                                        </select>
                                        <input type="number" name="value" class="form-control form-control-sm rounded-3" style="width:80px;"
                                               value="{{ $fv->value }}" placeholder="Nº" min="0">
                                    @endif
                                    <input type="hidden" name="show_on_page" value="{{ $fv->show_on_page ? '1' : '0' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Salvar valor">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('admin.plans.features.update', [$plan, $fv]) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="value" value="{{ $fv->value }}">
                                    <input type="hidden" name="show_on_page" value="{{ $fv->show_on_page ? '0' : '1' }}">
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $fv->show_on_page ? 'success' : 'secondary' }} rounded-pill px-3">
                                        {{ $fv->show_on_page ? '👁 Visível' : 'Oculto' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('admin.plans.features.remove', [$plan, $fv]) }}" method="POST"
                                      onsubmit="return confirm('Remover esta feature do plano?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Remover">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                Nenhuma feature configurada neste plano ainda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ─── Adicionar Feature ──────────────────────────────────────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h6 class="fw-bold text-dark mb-3">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i> Adicionar Feature
            </h6>
            @if($freeFeatures->isEmpty())
                <p class="text-muted small">Todas as features já estão adicionadas a este plano.</p>
            @else
                <form action="{{ route('admin.plans.features.add', $plan) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Feature</label>
                        <select name="plan_feature_id" class="form-select rounded-3" id="addFeatureSelect">
                            @foreach($freeFeatures as $feature)
                                <option value="{{ $feature->id }}" data-type="{{ $feature->type }}">
                                    {{ $feature->name }} ({{ $feature->key }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="valueField">
                        <label class="form-label fw-semibold small">Valor</label>
                        <div class="input-group">
                            <select name="value_type" class="form-select rounded-start-3" style="max-width:130px;" id="valueTypeSelect">
                                <option value="unlimited">∞ Ilimitado</option>
                                <option value="number">Numérico</option>
                                <option value="blocked">Bloqueado (0)</option>
                            </select>
                            <input type="number" id="valueNumber" class="form-control rounded-end-3 d-none" placeholder="ex: 500" min="0">
                            <input type="hidden" name="value" id="valueHidden" value="unlimited">
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="show_on_page" value="1" id="showOnPage" checked>
                        <label class="form-check-label fw-semibold small" for="showOnPage">Exibir na página pública de planos</label>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> Adicionar ao Plano
                    </button>
                </form>
            @endif
        </div>

        {{-- Info do Plano --}}
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
            <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-info-circle text-primary me-2"></i> Informações</h6>
            <ul class="list-unstyled small text-muted mb-0">
                <li class="mb-2"><strong>Slug:</strong> <code>{{ $plan->slug }}</code></li>
                <li class="mb-2"><strong>Preço:</strong> {{ $plan->formattedPrice() }}</li>
                <li class="mb-2"><strong>Status:</strong>
                    <span class="badge bg-{{ $plan->is_active ? 'success' : 'danger' }} bg-opacity-15 text-{{ $plan->is_active ? 'success' : 'danger' }}">
                        {{ $plan->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </li>
                <li><strong>Destacado:</strong> {{ $plan->is_highlighted ? 'Sim' : 'Não' }}</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('valueTypeSelect');
    const numInput   = document.getElementById('valueNumber');
    const hidden     = document.getElementById('valueHidden');

    function updateValue() {
        const type = typeSelect.value;
        if (type === 'unlimited') {
            numInput.classList.add('d-none');
            hidden.value = 'unlimited';
        } else if (type === 'blocked') {
            numInput.classList.add('d-none');
            hidden.value = '0';
        } else {
            numInput.classList.remove('d-none');
            hidden.value = numInput.value || '0';
        }
    }

    typeSelect?.addEventListener('change', updateValue);
    numInput?.addEventListener('input', () => { hidden.value = numInput.value; });
    updateValue();
});
</script>
@endpush
@endsection
