@extends('layouts.app')

@section('title', $ad->title . ' - Veículos - Conectado em Sergipe')

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $ad->title . ' - Veículos - Conectado em Sergipe',
        'socialDescription' => \Illuminate\Support\Str::limit(strip_tags($ad->description), 160),
        'socialUrl' => route('ad.show', $ad->slug),
        'socialImage' => asset($ad->card_image ?: $ad->banner ?: 'images/logo-hero.png'),
        'socialType' => 'article',
    ])
@endpush

@php
    $vehicleImages = collect([$ad->card_image, $ad->banner])
        ->merge($ad->images->pluck('image_path'))
        ->filter()
        ->unique()
        ->values();
    $mainVehicleImage = $vehicleImages->first() ?: 'images/logo.png';
    $remainingVehicleImages = max(0, $vehicleImages->count() - 5);
    $vehicleImageUrls = $vehicleImages->map(fn ($imagePath) => asset($imagePath))->values();
    $rawWhatsapp = preg_replace('/\D+/', '', $ad->user->whatsapp ?? '');
    $whatsappNumber = str_starts_with($rawWhatsapp, '55') ? $rawWhatsapp : '55' . $rawWhatsapp;
    $whatsappMessage = urlencode("Olá, vi o veículo {$ad->title} no Conectado em Sergipe.");
@endphp

@section('content')
<div class="vehicle-detail-page">
    <div class="container-fluid vehicle-detail-container py-3 py-md-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb vehicle-breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
                <li class="breadcrumb-item"><a href="{{ route('module.vehicles') }}">Veículos</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $ad->title }}</li>
            </ol>
        </nav>

        <div class="vehicle-detail-layout">
            <section aria-label="Fotos do veículo">
                <div class="vehicle-gallery {{ $vehicleImages->count() <= 1 ? 'vehicle-gallery-single' : '' }}">
                    <button type="button" class="vehicle-main-photo position-relative overflow-hidden" onclick="openVehicleLightbox(0)" aria-label="Ampliar foto principal">
                        <img
                            src="{{ asset($mainVehicleImage) }}"
                            id="vehicle-main-image"
                            class="w-100 h-100 object-fit-cover"
                            alt="{{ $ad->title }}"
                        >
                        <span class="vehicle-photo-count position-absolute top-0 end-0">
                            <i class="fa-solid fa-camera me-1"></i>
                            1 / {{ max(1, $vehicleImages->count()) }}
                        </span>
                    </button>

                    @if($vehicleImages->count() > 1)
                    <div class="vehicle-thumbnail-grid">
                        @foreach($vehicleImages->skip(1)->take(4) as $index => $imagePath)
                            <button
                                type="button"
                                class="vehicle-thumbnail"
                                onclick="openVehicleLightbox({{ $index + 1 }})"
                                aria-label="Ampliar foto {{ $index + 2 }}"
                            >
                                <img src="{{ asset($imagePath) }}" class="w-100 h-100 object-fit-cover" alt="">
                                @if($loop->last && $remainingVehicleImages > 0)
                                    <span class="vehicle-more-photos">+{{ $remainingVehicleImages }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </section>

            <aside>
                <div class="vehicle-summary-card">
                    <span class="vehicle-category-badge">{{ $ad->display_category }}</span>
                    <h1 class="vehicle-title">{{ $ad->title }}</h1>

                    @if($ad->price > 0)
                        <div class="vehicle-price">{{ $ad->formatted_price }}</div>
                    @else
                        <div class="vehicle-price vehicle-price-negotiable">Preço a combinar</div>
                    @endif

                    <div class="vehicle-facts">
                        <div class="vehicle-fact">
                            <span><i class="fa-solid fa-location-dot"></i> Localização</span>
                            <strong>{{ $ad->city }}, SE</strong>
                        </div>
                        <div class="vehicle-fact">
                            <span><i class="fa-solid fa-car-side"></i> Categoria</span>
                            <strong>{{ $ad->display_category }}</strong>
                        </div>
                        <div class="vehicle-fact">
                            <span><i class="fa-solid fa-user"></i> Anunciante</span>
                            <strong>{{ $ad->user->name ?? 'Anunciante' }}</strong>
                        </div>
                    </div>

                    @if($rawWhatsapp)
                        <a
                            href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                            target="_blank"
                            rel="noopener"
                            class="btn btn-success vehicle-whatsapp-button"
                        >
                            <i class="fa-brands fa-whatsapp me-2"></i>Chamar no WhatsApp
                        </a>
                    @elseif($ad->user->phone)
                        <a href="tel:{{ $ad->user->phone }}" class="btn btn-primary vehicle-whatsapp-button">
                            <i class="fa-solid fa-phone me-2"></i>Ligar para o anunciante
                        </a>
                    @endif

                    <button type="button" class="btn vehicle-share-button" data-social-share data-share-title="{{ $ad->title }}" data-share-text="Veja este veículo no Conectado em Sergipe: {{ $ad->title }}" data-share-url="{{ route('ad.show', $ad->slug) }}">
                        <i class="fa-solid fa-arrow-up-from-bracket me-2"></i>Compartilhar anúncio
                    </button>

                    <div class="text-center mt-2">
                        @include('reports._button-and-modal', ['reportable' => $ad])
                    </div>

                    <div class="vehicle-views">
                        <i class="fa-regular fa-eye me-1"></i>{{ $ad->views }} visualizações
                    </div>
                </div>
            </aside>
        </div>

        <section class="vehicle-description-card mt-2">
            <h2><i class="fa-regular fa-rectangle-list me-2"></i>Sobre este veículo</h2>
            <p>{{ $ad->description }}</p>
        </section>

    </div>
</div>

<div class="modal fade vehicle-gallery-modal" id="vehicleGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div class="text-white fw-bold text-truncate pe-3">{{ $ad->title }}</div>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <span class="vehicle-modal-counter" id="vehicle-modal-counter"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
            </div>
            <div class="modal-body">
                <button type="button" class="vehicle-modal-nav vehicle-modal-prev" onclick="changeVehicleModalImage(-1)" aria-label="Foto anterior">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <img src="" id="vehicle-modal-image" alt="{{ $ad->title }}">
                <button type="button" class="vehicle-modal-nav vehicle-modal-next" onclick="changeVehicleModalImage(1)" aria-label="Próxima foto">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .vehicle-detail-page {
        background: var(--background);
        min-height: 70vh;
    }

    .vehicle-detail-container {
        width: calc(100% - 32px);
        max-width: 1580px;
        margin-right: auto;
        margin-left: auto;
        padding-right: 0;
        padding-left: 0;
    }

    .vehicle-detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1175px) minmax(340px, 370px);
        gap: 32px;
        align-items: stretch;
        justify-content: center;
    }

    .vehicle-detail-layout > section,
    .vehicle-detail-layout > aside {
        min-width: 0;
    }

    .vehicle-breadcrumb {
        font-size: 0.82rem;
    }

    .vehicle-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .vehicle-gallery {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 0.35rem;
        width: 100%;
        height: auto;
        min-height: 0;
        aspect-ratio: 2.02 / 1;
        overflow: hidden;
        border-radius: 14px;
        background: var(--muted-bg);
    }

    .vehicle-gallery-single {
        display: block;
    }

    .vehicle-main-photo {
        width: 100%;
        height: 100%;
        min-width: 0;
        padding: 0;
        border: 0;
        background: var(--muted-bg);
        cursor: zoom-in;
    }

    .vehicle-photo-count {
        margin: 0.8rem;
        padding: 0.38rem 0.65rem;
        border-radius: 8px;
        color: #fff;
        background: rgba(0, 0, 0, 0.72);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .vehicle-thumbnail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-rows: repeat(2, minmax(0, 1fr));
        gap: 0.35rem;
        min-width: 0;
        min-height: 0;
    }

    .vehicle-thumbnail {
        position: relative;
        width: 100%;
        height: 100%;
        min-width: 0;
        min-height: 0;
        padding: 0;
        overflow: hidden;
        border: 2px solid transparent;
        border-radius: 0;
        background: var(--muted-bg);
        transition: border-color 0.2s ease, transform 0.2s ease;
    }

    .vehicle-thumbnail:hover,
    .vehicle-thumbnail:focus-visible,
    .vehicle-thumbnail.active {
        border-color: #f97316;
        transform: translateY(-2px);
        outline: none;
    }

    .vehicle-more-photos {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: rgba(15, 23, 42, 0.55);
        font-size: clamp(1.4rem, 3vw, 2.2rem);
        font-weight: 800;
    }

    .vehicle-summary-card,
    .vehicle-description-card {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--card);
        color: var(--foreground);
        box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    }

    .vehicle-summary-card {
        display: flex;
        height: 100%;
        flex-direction: column;
        padding: clamp(1.25rem, 1.8vw, 1.5rem);
    }

    .vehicle-category-badge {
        display: inline-flex;
        margin-bottom: 0.9rem;
        padding: 0.35rem 0.65rem;
        border: 1px solid #fb923c;
        border-radius: 7px;
        color: #ea580c;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .vehicle-title {
        margin-bottom: 1rem;
        color: var(--foreground);
        font-size: clamp(1.35rem, 1.8vw, 1.75rem);
        font-weight: 800;
        line-height: 1.15;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
        overflow: hidden;
    }

    .vehicle-price {
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border);
        color: #169b35;
        font-size: clamp(2rem, 3.5vw, 2.7rem);
        font-weight: 800;
        line-height: 1;
    }

    .vehicle-price-negotiable {
        font-size: 1.7rem;
    }

    .vehicle-facts {
        padding: 0.45rem 0;
    }

    .vehicle-fact {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
        padding: 0.62rem 0;
        font-size: 0.86rem;
    }

    .vehicle-fact span {
        color: var(--muted);
    }

    .vehicle-fact span i {
        width: 20px;
        margin-right: 0.45rem;
        color: var(--foreground);
        text-align: center;
    }

    .vehicle-fact strong {
        color: var(--foreground);
        text-align: right;
    }

    .vehicle-whatsapp-button {
        width: 100%;
        padding: 0.78rem 1rem;
        border: 0;
        border-radius: 8px;
        font-weight: 800;
    }

    .vehicle-share-button {
        width: 100%;
        margin-top: 0.65rem;
        padding: 0.72rem 1rem;
        border: 1px solid #2563eb;
        border-radius: 8px;
        color: #1d4ed8;
        background: var(--card);
        font-weight: 800;
    }

    .vehicle-share-button:hover,
    .vehicle-share-button:focus-visible {
        color: #ffffff;
        background: #2563eb;
    }

    .vehicle-views {
        margin-top: auto;
        padding-top: 1rem;
        color: #94a3b8;
        font-size: 0.75rem;
        text-align: center;
    }

    .vehicle-description-card {
        padding: 1.25rem 1.5rem;
    }

    .vehicle-description-card h2 {
        margin-bottom: 0.75rem;
        color: #111827;
        font-size: 1rem;
        font-weight: 800;
    }

    .vehicle-description-card h2 i {
        color: #f97316;
    }

    .vehicle-description-card p {
        margin: 0;
        color: #475569;
        font-size: 0.9rem;
        line-height: 1.65;
        white-space: pre-line;
    }

    .vehicle-gallery-modal .modal-content {
        border: 0;
        color: #ffffff;
        background: rgba(3, 7, 18, 0.98);
    }

    .vehicle-gallery-modal .modal-header {
        min-height: 64px;
        border-bottom-color: rgba(255, 255, 255, 0.12);
        background: #030712;
    }

    .vehicle-gallery-modal .modal-body {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 0;
        padding: 1rem 5rem;
        overflow: hidden;
    }

    .vehicle-gallery-modal #vehicle-modal-image {
        max-width: 100%;
        max-height: calc(100vh - 96px);
        object-fit: contain;
    }

    .vehicle-modal-counter {
        color: #cbd5e1;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .vehicle-modal-nav {
        position: absolute;
        top: 50%;
        z-index: 3;
        display: flex;
        width: 48px;
        height: 48px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 50%;
        color: #ffffff;
        background: rgba(15, 23, 42, 0.72);
        transform: translateY(-50%);
    }

    .vehicle-modal-nav:hover {
        background: #2563eb;
    }

    .vehicle-modal-prev {
        left: 1.25rem;
    }

    .vehicle-modal-next {
        right: 1.25rem;
    }

    @media (max-width: 1199.98px) {
        .vehicle-detail-container {
            width: calc(100% - 24px);
        }

        .vehicle-detail-layout {
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 20px;
        }

        .vehicle-summary-card {
            padding: 1.25rem;
        }
    }

    @media (max-width: 991.98px) {
        .vehicle-detail-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .vehicle-gallery {
            min-height: 0;
            aspect-ratio: 1.75 / 1;
        }

        .vehicle-summary-card {
            height: auto;
        }

        .vehicle-views {
            margin-top: 0;
        }
    }

    @media (max-width: 767.98px) {
        .vehicle-detail-container {
            width: calc(100% - 20px);
        }

        .vehicle-breadcrumb {
            margin-right: 0;
            margin-left: 0;
        }

        .vehicle-breadcrumb .breadcrumb-item.active {
            max-width: none;
        }

        .vehicle-gallery {
            display: flex;
            height: auto;
            aspect-ratio: auto;
            overflow: visible;
            flex-direction: column;
            background: transparent;
        }

        .vehicle-main-photo {
            height: 300px;
            border-radius: 12px;
            background: #e2e8f0;
        }

        .vehicle-thumbnail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: none;
            gap: 0.45rem;
        }

        .vehicle-thumbnail {
            height: 120px;
            border-radius: 10px;
        }

        .vehicle-summary-card {
            padding: 1.25rem;
        }

        .vehicle-fact {
            font-size: 0.8rem;
        }

        .vehicle-gallery-modal .modal-body {
            padding: 0.75rem 3.25rem;
        }

        .vehicle-modal-nav {
            width: 40px;
            height: 40px;
        }

        .vehicle-modal-prev {
            left: 0.5rem;
        }

        .vehicle-modal-next {
            right: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const vehicleGalleryImages = @json($vehicleImageUrls);
    let currentVehicleImageIndex = 0;

    function renderVehicleModalImage() {
        const modalImage = document.getElementById('vehicle-modal-image');
        const modalCounter = document.getElementById('vehicle-modal-counter');
        modalImage.src = vehicleGalleryImages[currentVehicleImageIndex];
        modalCounter.textContent = `${currentVehicleImageIndex + 1} / ${vehicleGalleryImages.length}`;
    }

    function openVehicleLightbox(index) {
        if (!vehicleGalleryImages.length) return;
        currentVehicleImageIndex = Math.max(0, Math.min(index, vehicleGalleryImages.length - 1));
        renderVehicleModalImage();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('vehicleGalleryModal')).show();
    }

    function changeVehicleModalImage(direction) {
        if (!vehicleGalleryImages.length) return;
        currentVehicleImageIndex = (
            currentVehicleImageIndex + direction + vehicleGalleryImages.length
        ) % vehicleGalleryImages.length;
        renderVehicleModalImage();
    }

    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('vehicleGalleryModal');
        if (!modal.classList.contains('show')) return;
        if (event.key === 'ArrowLeft') changeVehicleModalImage(-1);
        if (event.key === 'ArrowRight') changeVehicleModalImage(1);
    });
</script>
@endpush
