@php
    $ctaState = $profileCta['state'] ?? 'create';
    $ctaClass = $class ?? 'btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm';
@endphp

@if($ctaState === 'manage')
    <a href="{{ route('ad.edit', $profileCta['profile']->id) }}" class="{{ $ctaClass }}">
        <i class="fa-solid fa-pen me-2"></i>Gerenciar meu perfil
    </a>
@elseif($ctaState === 'create_another')
    <a href="{{ route('ad.create', ['module' => 'services']) }}" class="{{ $ctaClass }}">
        <i class="fa-solid fa-plus me-2"></i>Criar outro perfil
    </a>
@elseif($ctaState === 'limit')
    <button type="button" class="{{ $ctaClass }}" data-bs-toggle="modal" data-bs-target="#professionalProfileLimitModal">
        <i class="fa-solid fa-crown me-2"></i>Aumentar limite
    </button>
@else
    <a href="{{ route('ad.create', ['module' => 'services']) }}" class="{{ $ctaClass }}">
        <i class="fa-solid fa-user-plus me-2"></i>Criar meu perfil profissional
    </a>
@endif
