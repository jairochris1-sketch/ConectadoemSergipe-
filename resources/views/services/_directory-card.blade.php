@php
    $coverImage = $provider->banner ?: \App\Support\CityImage::for($provider->city);
    $profileImage = $provider->logo ?: $provider->mainImage?->image_path;
    $rawWhatsapp = preg_replace('/\D+/', '', $provider->user->whatsapp ?? '');
    $whatsappNumber = str_starts_with($rawWhatsapp, '55') ? $rawWhatsapp : '55' . $rawWhatsapp;
    $whatsappMessage = urlencode("Olá, encontrei seu perfil profissional no Conectado em Sergipe: {$provider->title}");
    $reviewsCount = (int) ($provider->approved_reviews_count ?? 0);
    $reviewsAverage = (float) ($provider->approved_reviews_average ?? 0);
    $chatUrl = auth()->id() === $provider->user_id
        ? route('user.panel')
        : route('chat.index', ['with' => $provider->user_id]);
    $description = \Illuminate\Support\Str::limit($provider->description ?? '', 120);

    $categoryLower = mb_strtolower($provider->display_category ?? $provider->advertiser_type ?? '');
    $allowsHomeService = \Illuminate\Support\Str::contains($categoryLower, [
        'manicure', 'pedicure', 'cabeleireira', 'cabeleleira', 'cabeleireiro', 'maquiadora', 'maquiador', 'maquiagem'
    ]);
    $isAvailableNow = $provider->user ? $provider->user->isAvailableNow() : true;
@endphp

<article class="sdc-card services-directory-card">
    {{-- Banner Cover Superior --}}
    <div class="sdc-banner" style="background-image: url('{{ asset($coverImage) }}');">
        <x-featured-badge :provider="$provider" class="sdc-banner-badge" />
    </div>

    {{-- Corpo do Card --}}
    <div class="sdc-body">
        {{-- Avatar Sobreposto + Sinalização de Online (Bolinha verde/cinza de status) --}}
        <a href="{{ route('provider.show', $provider->slug) }}" class="sdc-avatar-wrap services-directory-avatar">
            @if($profileImage)
                <img src="{{ asset($profileImage) }}" alt="{{ $provider->title }}" loading="lazy" class="sdc-avatar-img protected-media" draggable="false" oncontextmenu="return false;">
            @else
                <span class="sdc-avatar-initials d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #1265f5 0%, #004ecc 100%);">
                    <i class="{{ $provider->category_icon }}" style="font-size: 1.55rem;"></i>
                </span>
            @endif
            @if($isAvailableNow)
                <span class="sdc-avatar-dot" title="Disponível agora"></span>
            @endif
        </a>

        {{-- Categoria com Ícone --}}
        <span class="sdc-category">
            <i class="{{ $provider->category_icon }} me-1"></i>{{ $provider->display_category }}
        </span>

        {{-- Título --}}
        <h2 class="sdc-title">
            <a href="{{ route('provider.show', $provider->slug) }}">{{ $provider->title }}</a>
        </h2>

        {{-- Avaliação por Estrelas --}}
        <div class="sdc-rating">
            <div class="sdc-stars">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= round($reviewsAverage))
                        <i class="fa-solid fa-star text-warning"></i>
                    @else
                        <i class="fa-regular fa-star text-muted" style="opacity: .4;"></i>
                    @endif
                @endfor
            </div>
            <span class="sdc-rating-val">{{ number_format($reviewsAverage, 1, ',', '.') }}</span>
            <span class="sdc-reviews-count">({{ $reviewsCount }} {{ $reviewsCount === 1 ? 'avaliação' : 'avaliações' }})</span>
        </div>

        {{-- Cidade --}}
        <p class="sdc-location services-directory-location">
            <i class="fa-solid fa-location-dot"></i> {{ $provider->city }} · SE
            <span class="visually-hidden">{{ $coverImage }}</span>
        </p>

        {{-- Descrição do Prestador --}}
        @if($provider->description)
        <p class="sdc-description">{{ $provider->description }}</p>
        @endif

        {{-- Badge (Atendimento em domicílio quando aplicável) --}}
        @if($allowsHomeService)
        <div class="sdc-badges">
            <span class="sdc-badge sdc-badge-home">
                <i class="fa-solid fa-house-user"></i> Atendimento em domicílio
            </span>
        </div>
        @endif

        {{-- Botões de Ação Inferiores --}}
        <div class="sdc-actions">
            <a href="{{ route('provider.show', $provider->slug) }}" class="sdc-btn sdc-btn-profile">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir
            </a>

            @if($rawWhatsapp)
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener" class="sdc-btn sdc-btn-whatsapp" aria-label="WhatsApp">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            @else
                <span class="sdc-btn sdc-btn-whatsapp sdc-btn-disabled" aria-disabled="true">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </span>
            @endif
        </div>
    </div>
</article>
