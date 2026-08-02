@php
    $citySlideshowImages = collect(\Illuminate\Support\Facades\File::files(public_path('images/cidadesimagem')))
        ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
        ->sortBy(fn ($file) => $file->getFilename())
        ->values()
        ->map(fn ($file) => asset('images/cidadesimagem/'.$file->getFilename()));
@endphp

<div class="auth-city-slideshow" aria-hidden="true">
    @foreach($citySlideshowImages as $image)
        <div
            class="auth-city-slide {{ $loop->first ? 'is-active' : '' }}"
            style="background-image: url('{{ $image }}');"
        ></div>
    @endforeach
    <div class="auth-city-overlay"></div>
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
            filter: blur(1.5px);
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
                rgba(2, 6, 23, .58),
                rgba(15, 23, 42, .72)
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

                window.setInterval(function () {
                    const nextSlide = (currentSlide + 1) % slides.length;
                    slides[nextSlide].classList.add('is-active');
                    slides[currentSlide].classList.remove('is-active');
                    currentSlide = nextSlide;
                }, 10000);
            });
        });
    </script>
@endonce
