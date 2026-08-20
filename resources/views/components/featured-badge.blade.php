@props([
    'plan' => null,
    'user' => null,
    'ad' => null,
    'provider' => null,
    'label' => 'Em destaque',
    'class' => '',
    'style' => '',
])

@php
    $planSlug = strtolower((string) (
        $plan 
        ?? $user?->subscription_plan 
        ?? $ad?->user?->subscription_plan 
        ?? $provider?->user?->subscription_plan 
        ?? 'start'
    ));

    if (in_array($planSlug, ['enterprise', 'premium', 'gold'])) {
        // Plano Premium: Roxo com degradê e gema/3 estrelas
        $badgeBg = 'background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%); color: #ffffff; box-shadow: 0 2px 8px rgba(124, 58, 237, 0.35); border: 1px solid rgba(255, 255, 255, 0.25);';
        $icons = '<i class="fa-solid fa-gem me-1" style="font-size: 0.85em;"></i>';
    } elseif ($planSlug === 'pro') {
        // Plano Pro: Azul vibrante com 2 estrelas
        $badgeBg = 'background: linear-gradient(135deg, #0284c7 0%, #1e40af 100%); color: #ffffff; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35); border: 1px solid rgba(255, 255, 255, 0.2);';
        $icons = '<i class="fa-solid fa-star" style="font-size: 0.75em;"></i><i class="fa-solid fa-star me-1" style="font-size: 0.75em; margin-left: 1px;"></i>';
    } else {
        // Plano Start ou Destaque padrão: Azul com 1 estrela
        $badgeBg = 'background: #0d6efd; color: #ffffff; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);';
        $icons = '<i class="fa-solid fa-star me-1" style="font-size: 0.8em;"></i>';
    }
@endphp

<span class="badge rounded-pill fw-bold d-inline-flex align-items-center {{ $class }}" style="z-index: 10 !important; position: relative; {{ $badgeBg }} {{ $style }}">
    {!! $icons !!}<span>{{ $label }}</span>
</span>
