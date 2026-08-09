@php
    $rawFiles = collect(\Illuminate\Support\Facades\File::files(public_path('Cidades')))
        ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
        ->sortBy(fn ($file) => $file->getFilename())
        ->values();

    $citySlideshowImages = $rawFiles->map(function($file) {
        $filename = $file->getFilename();
        // The city name is the filename without extension
        $cityName = pathinfo($filename, PATHINFO_FILENAME);
        // Normalize multiple spaces just in case (e.g. 'Nossa Senhora das  Dores')
        $cityName = trim(preg_replace('/\s+/', ' ', $cityName));
        
        return [
            'url' => asset('Cidades/'.$filename),
            'city' => $cityName,
        ];
    });
    
    // Determine the first city to sync with floating cards
    $firstCity = $citySlideshowImages->first()['city'] ?? 'Sergipe';
@endphp

<div class="auth-city-slideshow" aria-hidden="true">
    @foreach($citySlideshowImages as $index => $item)
        <div
            class="auth-city-slide {{ $loop->first ? 'is-active' : '' }}"
            data-city="{{ $item['city'] }}"
            data-index="{{ $index }}"
            style="background-image: url('{{ $item['url'] }}');"
        ></div>
    @endforeach
    <div class="auth-city-overlay"></div>

    <!-- Badge Indicador da Cidade no Topo Direito -->
    <div id="slideshow-city-badge" class="position-absolute top-0 end-0 m-4 px-3 py-2 rounded-pill text-white fw-bold shadow-lg d-flex align-items-center gap-2 border border-white border-opacity-20" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); z-index: 10; font-size: 0.85rem; transition: opacity 0.5s ease;">
        <i class="fa-solid fa-location-dot text-danger fs-6"></i>
        <span id="slideshow-city-name">{{ $citySlideshowImages[0]['city'] === 'Sergipe' ? 'Conectado em Sergipe' : ($citySlideshowImages[0]['city'] . ', SE') }}</span>
    </div>
</div>

@once
    <style>
        .auth-city-slideshow,
        .auth-city-slide,
        .auth-city-overlay {
            position: absolute;
            inset: 0;
        }

        .auth-city-slideshow {
            overflow: hidden;
            background: #0f172a;
        }

        .auth-city-slide {
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            opacity: 0;
            filter: blur(0px);
            transform: scale(1.025);
            transition: opacity 1.4s ease-in-out;
            will-change: opacity;
        }

        .auth-city-slide.is-active {
            opacity: 1;
        }

        .auth-city-overlay {
            z-index: 1;
            background: linear-gradient(
                135deg,
                rgba(2, 6, 23, .15),
                rgba(15, 23, 42, .30)
            );
            pointer-events: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-city-slide {
                transition-duration: .01ms;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.auth-city-slideshow').forEach(function (slideshow) {
                const slides = Array.from(slideshow.querySelectorAll('.auth-city-slide'));

                if (slides.length < 2) {
                    return;
                }

                let currentSlide = 0;

                function updateCityDisplay(slideIndex) {
                    const cityName = slides[slideIndex].getAttribute('data-city');
                    const badgeEl = document.getElementById('slideshow-city-name');
                    const badgeContainer = document.getElementById('slideshow-city-badge');

                    if (badgeEl && badgeContainer) {
                        badgeContainer.style.opacity = '0';
                        setTimeout(function() {
                            badgeEl.textContent = cityName === 'Sergipe' ? 'Conectado em Sergipe' : (cityName + ', SE');
                            badgeContainer.style.opacity = '1';
                        }, 350);
                    }

                    if (typeof window.updateFloatingCardsForCity === 'function') {
                        window.updateFloatingCardsForCity(cityName);
                    }
                }

                // Inicializa na primeira cidade
                updateCityDisplay(0);

                // Intervalo de troca de slide a cada 8 segundos
                window.setInterval(function () {
                    const nextSlide = (currentSlide + 1) % slides.length;
                    slides[nextSlide].classList.add('is-active');
                    slides[currentSlide].classList.remove('is-active');
                    currentSlide = nextSlide;

                    updateCityDisplay(currentSlide);
                }, 8000);
            });
        });
    </script>
@endonce
