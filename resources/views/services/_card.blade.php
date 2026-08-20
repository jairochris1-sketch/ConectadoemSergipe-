@php
    $coverImage = $provider->banner ?: \App\Support\CityImage::for($provider->city);
    $rawWhatsapp = preg_replace('/\D+/', '', $provider->user->whatsapp ?? '');
    $whatsappNumber = str_starts_with($rawWhatsapp, '55') ? $rawWhatsapp : '55' . $rawWhatsapp;
    $whatsappMessage = urlencode("Olá, encontrei seu perfil profissional no Conectado em Sergipe: {$provider->title}");
@endphp

<article class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden provider-card position-relative">
    <a href="{{ route('provider.show', $provider->slug) }}" class="stretched-link" aria-label="Abrir perfil de {{ $provider->title }}"></a>
        <div class="position-relative bg-primary bg-opacity-10 provider-card-cover">
            <img src="{{ asset($coverImage) }}" class="w-100 h-100 object-fit-cover protected-media" alt="" draggable="false" oncontextmenu="return false;">
            <x-featured-badge :provider="$provider" class="position-absolute top-0 end-0 provider-featured-badge" />
        </div>

        <div class="card-body provider-card-body">
            <div class="bg-white border shadow-sm d-flex align-items-center justify-content-center overflow-hidden provider-card-avatar">
                @if($provider->logo)
                    <img src="{{ asset($provider->logo) }}" class="w-100 h-100 object-fit-cover protected-media" alt="{{ $provider->title }}" draggable="false" oncontextmenu="return false;">
                @elseif($provider->mainImage)
                    <img src="{{ asset($provider->mainImage->image_path) }}" class="w-100 h-100 object-fit-cover protected-media" alt="{{ $provider->title }}" draggable="false" oncontextmenu="return false;">
                @else
                    <span class="text-primary d-flex align-items-center justify-content-center">
                        <i class="{{ $provider->category_icon }}" style="font-size: 1.6rem;"></i>
                    </span>
                @endif
            </div>

            <span class="text-primary small fw-bold provider-card-category">
                <i class="{{ $provider->category_icon }} me-1"></i>{{ $provider->display_category }}
            </span>
            <h2 class="h6 fw-bold mt-1 mb-2 provider-card-title">{{ $provider->title }}</h2>
            <span class="provider-card-location">
                <i class="fa-solid fa-location-dot"></i>
                {{ $provider->city }}, SE
                @if($provider->region)
                    · {{ $provider->region }}
                @endif
            </span>
            @if(empty($hideDescription))
                <p class="text-secondary small mb-3 provider-card-description">{{ \Illuminate\Support\Str::limit($provider->description, 110) }}</p>
            @endif

            @if($rawWhatsapp)
                <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener" class="btn btn-success rounded-pill w-100 fw-bold position-relative z-2 {{ !empty($hideDesktopWhatsapp) ? 'd-lg-none' : '' }}">
                    <i class="fa-brands fa-whatsapp me-2"></i>WhatsApp
                </a>
            @else
                <div class="btn btn-outline-primary rounded-pill w-100 fw-bold">
                    Abrir perfil
                </div>
            @endif
        </div>
    </article>
