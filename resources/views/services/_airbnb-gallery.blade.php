@php
    $providerGalleryImages = collect($images ?? [])
        ->filter()
        ->unique()
        ->map(fn ($path) => asset($path))
        ->values();
    $providerGalleryId = 'providerGallery' . $provider->id;
@endphp

@if($providerGalleryImages->isNotEmpty())
<section class="provider-airbnb-card bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-4">
    <div class="provider-portfolio-heading">
        <div>
            <h2 class="h5 fw-bold mb-1"><i class="fa-solid fa-images text-primary me-2"></i>Trabalhos e portfólio</h2>
            <small class="text-muted">A foto principal muda automaticamente a cada 8 segundos.</small>
        </div>
        <button type="button" class="btn btn-outline-dark rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#{{ $providerGalleryId }}Modal">
            {{ $providerGalleryImages->count() === 1 ? 'Ver a foto' : 'Ver todas as fotos' }} <i class="fa-solid fa-arrow-right ms-1"></i>
        </button>
    </div>

    <div
        class="provider-airbnb-gallery"
        id="{{ $providerGalleryId }}"
        data-provider-gallery
        data-images='@json($providerGalleryImages)'
    >
        <button
            type="button"
            class="provider-airbnb-main"
            data-gallery-main
            aria-label="Ampliar foto principal"
            style="--portfolio-bg: url('{{ $providerGalleryImages->first() }}');"
        >
            <img
                id="{{ $providerGalleryId }}Main"
                src="{{ $providerGalleryImages->first() }}"
                class="provider-airbnb-main-image protected-media"
                alt="Trabalho principal de {{ $provider->title }}"
                fetchpriority="high"
                draggable="false"
                oncontextmenu="return false;"
            >
            <span class="provider-airbnb-main-label"><i class="fa-solid fa-star me-1"></i>Trabalho em destaque</span>
            @if($providerGalleryImages->count() > 1)
                <span class="provider-gallery-dots" aria-hidden="true">
                    @foreach($providerGalleryImages as $index => $imageUrl)
                        <span data-gallery-dot="{{ $index }}"></span>
                    @endforeach
                </span>
            @endif
        </button>
    </div>

    @if($providerGalleryImages->count() > 1)
        <div class="provider-airbnb-thumbnails" aria-label="Miniaturas do portfólio">
            @foreach($providerGalleryImages as $index => $imageUrl)
                <button type="button" class="provider-airbnb-thumb {{ $index === 0 ? 'active' : '' }}" data-gallery-thumb-index="{{ $index }}" aria-label="Exibir foto {{ $index + 1 }}">
                    <img src="{{ $imageUrl }}" class="protected-media" alt="Trabalho {{ $index + 1 }}" loading="{{ $index < 6 ? 'eager' : 'lazy' }}" draggable="false" oncontextmenu="return false;">
                </button>
            @endforeach
        </div>
    @endif
</section>

<div class="modal fade provider-gallery-modal" id="{{ $providerGalleryId }}Modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h2 class="modal-title h5 fw-bold text-white text-truncate">{{ $provider->title }} · Portfólio</h2>
                <span class="provider-gallery-counter ms-auto me-3" data-modal-counter></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center position-relative p-2 p-md-4 protected-media-container">
                <button type="button" class="provider-gallery-nav provider-gallery-prev" data-modal-prev aria-label="Foto anterior">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="position-relative d-inline-flex align-items-center justify-content-center" style="max-width: 100%; max-height: 100%;">
                    <img src="{{ $providerGalleryImages->first() }}" class="provider-gallery-modal-image protected-media" data-modal-image alt="Foto ampliada do portfólio" draggable="false" oncontextmenu="return false;">
                    <span class="protected-media-badge"><i class="fa-solid fa-shield-halved text-primary"></i> Conectado em Sergipe</span>
                </div>
                <button type="button" class="provider-gallery-nav provider-gallery-next" data-modal-next aria-label="Próxima foto">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="modal-footer border-secondary justify-content-start overflow-auto flex-nowrap">
                @foreach($providerGalleryImages as $index => $imageUrl)
                    <button type="button" class="provider-gallery-modal-thumb flex-shrink-0" data-modal-thumb="{{ $index }}">
                        <img src="{{ $imageUrl }}" class="protected-media" alt="Foto {{ $index + 1 }}" loading="lazy" draggable="false" oncontextmenu="return false;">
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
        .provider-portfolio-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .provider-airbnb-gallery {
            position: relative;
            height: clamp(240px, 36vw, 460px);
            overflow: hidden;
            border-radius: 18px;
            background: var(--muted-bg);
        }
        .provider-airbnb-main,
        .provider-airbnb-thumb {
            position: relative;
            display: block;
            width: 100%;
            height: 100%;
            padding: 0;
            border: 0;
            overflow: hidden;
            background: var(--muted-bg);
            cursor: pointer;
        }
        .provider-airbnb-main::before {
            content: "";
            position: absolute;
            inset: -30px;
            z-index: 0;
            background-image: var(--portfolio-bg);
            background-position: center;
            background-size: cover;
            filter: blur(35px);
            opacity: .25;
            transform: scale(1.15);
        }
        .provider-airbnb-main-image,
        .provider-airbnb-thumb img {
            width: 100%;
            height: 100%;
            transition: opacity .28s ease, transform .35s ease;
        }
        .provider-airbnb-main-image {
            position: relative;
            z-index: 2;
            display: block;
            object-fit: contain;
            object-position: center;
        }
        .provider-airbnb-thumb img { object-fit: cover; }
        .provider-airbnb-thumb:hover img { transform: scale(1.025); }
        .provider-airbnb-main-image.is-changing { opacity: .18; }
        .provider-gallery-main-nav {
            position: absolute;
            z-index: 5;
            top: 50%;
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            padding: 0;
            border: 1px solid rgba(15, 23, 42, .12);
            border-radius: 50%;
            color: #0d6efd;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 8px 24px rgba(15, 23, 42, .2);
            transform: translateY(-50%);
            transition: transform .2s ease, background-color .2s ease;
        }
        .provider-gallery-main-nav:hover,
        .provider-gallery-main-nav:focus-visible {
            color: #0a58ca;
            background: #fff;
            transform: translateY(-50%) scale(1.06);
        }
        .provider-gallery-main-prev { left: 18px; }
        .provider-gallery-main-next { right: 18px; }
        .provider-airbnb-main-label {
            position: absolute;
            z-index: 3;
            left: 16px;
            bottom: 16px;
            padding: 7px 12px;
            border-radius: 999px;
            color: #fff;
            background: rgba(15, 23, 42, .78);
            font-size: .78rem;
            font-weight: 800;
            backdrop-filter: blur(8px);
        }
        .provider-gallery-dots {
            position: absolute;
            z-index: 3;
            left: 50%;
            bottom: 12px;
            display: flex;
            gap: 7px;
            padding: 5px 8px;
            border-radius: 999px;
            background: rgba(15, 23, 42, .28);
            transform: translateX(-50%);
            backdrop-filter: blur(5px);
        }
        .provider-gallery-dots span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .58);
            transition: width .2s ease, background-color .2s ease;
        }
        .provider-gallery-dots span.active {
            width: 18px;
            border-radius: 999px;
            background: #fff;
        }
        .provider-airbnb-thumbnails {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(135px, 1fr);
            gap: 10px;
            margin-top: 12px;
            padding: 2px;
            overflow-x: auto;
            overscroll-behavior-inline: contain;
            scrollbar-width: thin;
        }
        .provider-airbnb-thumb {
            height: 106px;
            border: 2px solid transparent;
            border-radius: 13px;
            opacity: .82;
            transition: border-color .2s ease, opacity .2s ease, transform .2s ease;
        }
        .provider-airbnb-thumb:hover,
        .provider-airbnb-thumb.active {
            border-color: var(--primary);
            opacity: 1;
            transform: translateY(-1px);
        }
        .provider-gallery-modal .modal-body { min-height: 0; }
        .provider-gallery-modal-image {
            display: block;
            max-width: calc(100vw - 150px);
            max-height: calc(100vh - 190px);
            object-fit: contain;
        }
        .provider-gallery-nav {
            position: absolute;
            top: 50%;
            z-index: 2;
            width: 48px;
            height: 48px;
            border: 0;
            border-radius: 50%;
            color: #111827;
            background: rgba(255, 255, 255, .9);
            transform: translateY(-50%);
        }
        .provider-gallery-prev { left: 22px; }
        .provider-gallery-next { right: 22px; }
        .provider-gallery-modal-thumb {
            width: 86px;
            height: 62px;
            padding: 0;
            border: 2px solid transparent;
            border-radius: 9px;
            overflow: hidden;
            opacity: .65;
            background: transparent;
        }
        .provider-gallery-modal-thumb.active {
            border-color: #fff;
            opacity: 1;
        }
        .provider-gallery-modal-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        @media (max-width: 767.98px) {
            .provider-portfolio-heading {
                flex-direction: column;
                align-items: flex-start;
                gap: .6rem;
                margin-bottom: 1rem;
            }
            .provider-portfolio-heading > div {
                width: 100%;
            }
            .provider-portfolio-heading .btn {
                padding: .4rem .9rem !important;
                font-size: .8rem;
                align-self: flex-start;
            }
            .provider-airbnb-gallery {
                height: 300px;
                border-radius: 14px;
            }
            .provider-airbnb-thumbnails { grid-auto-columns: 104px; gap: 7px; margin-top: 8px; }
            .provider-airbnb-thumb { height: 76px; border-radius: 10px; }
            .provider-airbnb-main-label {
                left: 10px;
                bottom: 10px;
                font-size: .7rem;
            }
            .provider-gallery-dots { bottom: 8px; }
            .provider-gallery-main-nav { width: 36px; height: 36px; }
            .provider-gallery-main-prev { left: 9px; }
            .provider-gallery-main-next { right: 9px; }
            .provider-gallery-modal-image {
                max-width: calc(100vw - 24px);
                max-height: calc(100vh - 190px);
            }
            .provider-gallery-nav {
                width: 40px;
                height: 40px;
                background: rgba(255, 255, 255, .76);
            }
            .provider-gallery-prev { left: 8px; }
            .provider-gallery-next { right: 8px; }
        }
        @media (max-width: 991.98px) and (min-width: 768px) {
            .provider-airbnb-gallery { height: 380px; }
        }
        @media (max-width: 479.98px) {
            .provider-airbnb-gallery { height: 240px; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.querySelectorAll('[data-provider-gallery]').forEach((gallery) => {
            const images = JSON.parse(gallery.dataset.images || '[]');
            if (!images.length) return;

            const mainButton = gallery.querySelector('[data-gallery-main]');
            const mainImage = mainButton.querySelector('img');
            const galleryCard = gallery.closest('.provider-airbnb-card');
            const galleryThumbs = Array.from(galleryCard.querySelectorAll('[data-gallery-thumb-index]'));
            const galleryDots = Array.from(gallery.querySelectorAll('[data-gallery-dot]'));
            const galleryPrev = gallery.querySelector('[data-gallery-prev]');
            const galleryNext = gallery.querySelector('[data-gallery-next]');
            const modal = gallery.closest('.provider-airbnb-card').nextElementSibling;
            const modalImage = modal.querySelector('[data-modal-image]');
            const modalCounter = modal.querySelector('[data-modal-counter]');
            const modalThumbs = Array.from(modal.querySelectorAll('[data-modal-thumb]'));
            let currentIndex = 0;
            let intervalId = null;

            const updateGalleryNavigation = () => {
                galleryThumbs.forEach((thumb, index) => {
                    const active = index === currentIndex;
                    thumb.classList.toggle('active', active);
                    if (active && thumb.parentElement) {
                        thumb.parentElement.scrollTo({
                            left: Math.max(0, thumb.offsetLeft - (thumb.parentElement.clientWidth - thumb.clientWidth) / 2),
                            behavior: 'smooth'
                        });
                    }
                });
                galleryDots.forEach((dot, index) => dot.classList.toggle('active', index === currentIndex));
            };

            const setMain = (index, animate = true) => {
                currentIndex = (index + images.length) % images.length;
                const update = () => {
                    mainImage.src = images[currentIndex];
                    mainImage.alt = `Trabalho em destaque ${currentIndex + 1}`;
                    mainButton.style.setProperty('--portfolio-bg', `url("${images[currentIndex].replace(/"/g, '%22')}")`);
                    updateGalleryNavigation();
                    mainImage.classList.remove('is-changing');
                };
                if (animate) {
                    mainImage.classList.add('is-changing');
                    window.setTimeout(update, 170);
                } else {
                    update();
                }
            };

            const showModalImage = (index) => {
                currentIndex = (index + images.length) % images.length;
                modalImage.src = images[currentIndex];
                modalCounter.textContent = `${currentIndex + 1} / ${images.length}`;
                modalThumbs.forEach((thumb, thumbIndex) => thumb.classList.toggle('active', thumbIndex === currentIndex));
            };

            const stopRotation = () => {
                if (intervalId) window.clearInterval(intervalId);
                intervalId = null;
            };
            const startRotation = () => {
                stopRotation();
                if (images.length > 1) intervalId = window.setInterval(() => setMain(currentIndex + 1), 8000);
            };

            galleryThumbs.forEach((thumb) => thumb.addEventListener('click', () => {
                setMain(Number(thumb.dataset.galleryThumbIndex));
                startRotation();
            }));
            galleryPrev?.addEventListener('click', () => {
                setMain(currentIndex - 1);
                startRotation();
            });
            galleryNext?.addEventListener('click', () => {
                setMain(currentIndex + 1);
                startRotation();
            });
            mainButton.addEventListener('keydown', (event) => {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
                event.preventDefault();
                setMain(currentIndex + (event.key === 'ArrowLeft' ? -1 : 1));
                startRotation();
            });
            mainButton.addEventListener('click', () => {
                showModalImage(currentIndex);
                bootstrap.Modal.getOrCreateInstance(modal).show();
            });
            modal.querySelector('[data-modal-prev]').addEventListener('click', () => showModalImage(currentIndex - 1));
            modal.querySelector('[data-modal-next]').addEventListener('click', () => showModalImage(currentIndex + 1));
            modalThumbs.forEach((thumb, index) => thumb.addEventListener('click', () => showModalImage(index)));
            modal.addEventListener('show.bs.modal', () => showModalImage(currentIndex));
            modal.addEventListener('shown.bs.modal', stopRotation);
            modal.addEventListener('hidden.bs.modal', () => {
                setMain(currentIndex, false);
                startRotation();
            });
            gallery.addEventListener('mouseenter', stopRotation);
            gallery.addEventListener('mouseleave', startRotation);
            document.addEventListener('visibilitychange', () => document.hidden ? stopRotation() : startRotation());
            document.addEventListener('keydown', (event) => {
                if (!modal.classList.contains('show')) return;
                if (event.key === 'ArrowLeft') showModalImage(currentIndex - 1);
                if (event.key === 'ArrowRight') showModalImage(currentIndex + 1);
            });

            setMain(0, false);
            showModalImage(0);
            startRotation();
        });
    </script>
    @endpush
@endonce
@endif
