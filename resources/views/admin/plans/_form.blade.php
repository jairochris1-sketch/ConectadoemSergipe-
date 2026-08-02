{{-- Partial reutilizado no modal de criação e edição --}}
@if($plan)
    {{-- Edição: slug é readonly --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Slug do Plano</label>
        <input type="text" class="form-control rounded-3 bg-light" value="{{ $plan->slug }}" readonly>
    </div>
@else
    {{-- Criação: slug editável --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Slug único *</label>
        <input type="text" name="slug" class="form-control rounded-3" placeholder="ex: starter" required>
        <div class="form-text">Não pode ser alterado depois. Use apenas letras minúsculas e hifens.</div>
    </div>
@endif

<div class="mb-3">
    <label class="form-label fw-semibold">Nome do Plano *</label>
    <input type="text" name="name" class="form-control rounded-3" value="{{ $plan->name ?? '' }}" placeholder="ex: Plano PRO" required>
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <label class="form-label fw-semibold">Preço (R$/mês) *</label>
        <input type="number" name="price" step="0.01" min="0"
               class="form-control rounded-3" value="{{ $plan->price ?? '0' }}" required>
    </div>
    <div class="col-6">
        <label class="form-label fw-semibold">Cor de destaque</label>
        <select name="color" class="form-select rounded-3">
            @foreach(['secondary' => 'Cinza', 'primary' => 'Azul', 'purple' => 'Roxo', 'success' => 'Verde', 'warning' => 'Amarelo', 'danger' => 'Vermelho'] as $val => $label)
                <option value="{{ $val }}" {{ ($plan->color ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Badge / Etiqueta (ex: MAIS ACESSADO)</label>
    <input type="text" name="badge_label" class="form-control rounded-3" value="{{ $plan->badge_label ?? '' }}" placeholder="Opcional">
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Headline (frase de venda)</label>
    <input type="text" name="headline" class="form-control rounded-3" value="{{ $plan->headline ?? '' }}">
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Descrição (subtítulo)</label>
    <textarea name="description" class="form-control rounded-3" rows="2">{{ $plan->description ?? '' }}</textarea>
</div>

<div class="row g-3">
    <div class="col-6">
        <label class="form-label fw-semibold">Ordem de exibição</label>
        <input type="number" name="sort_order" class="form-control rounded-3" value="{{ $plan->sort_order ?? 99 }}">
    </div>
    <div class="col-6 d-flex align-items-end pb-1">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_highlighted" value="1" id="highlighted_{{ $plan->id ?? 'new' }}"
                   {{ ($plan->is_highlighted ?? false) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="highlighted_{{ $plan->id ?? 'new' }}">
                Plano em destaque
            </label>
        </div>
    </div>
</div>
