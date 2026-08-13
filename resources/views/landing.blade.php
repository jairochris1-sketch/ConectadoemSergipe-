@php
    $imageUrl = static fn (string $path): string => preg_match('/^https?:\/\//i', $path) ? $path : asset($path);
    $cards = [
        1 => ['class' => 'landing-card-service', 'label' => 'Prestador de serviços', 'icon' => 'fa-screwdriver-wrench'],
        2 => ['class' => 'landing-card-store', 'label' => 'Loja local', 'icon' => 'fa-bag-shopping'],
        3 => ['class' => 'landing-card-vehicle', 'label' => 'Veículos', 'icon' => 'fa-car-side'],
        4 => ['class' => 'landing-card-property', 'label' => 'Imóveis', 'icon' => 'fa-house'],
        5 => ['class' => 'landing-card-food', 'label' => 'Alimentação', 'icon' => 'fa-utensils'],
        6 => ['class' => 'landing-card-agro', 'label' => 'Agro', 'icon' => 'fa-leaf'],
        7 => ['class' => 'landing-card-professional', 'label' => 'Profissional local', 'icon' => 'fa-user-tie'],
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f8f6f0">
    <title>{{ \App\Models\Setting::get('site_name', 'Conectado em Sergipe') }} — O ecossistema digital de Sergipe</title>
    <meta name="description" content="{{ \App\Models\Setting::get('landing_description', 'A plataforma que conecta serviços, lojas, produtos, imóveis, veículos, empregos, agro e oportunidades em Sergipe.') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --landing-blue: #075be8;
            --landing-ink: #11141b;
            --landing-paper: #f8f6f0;
            --landing-orange: #ff7a31;
            --landing-green: #4c9b59;
        }

        * { box-sizing: border-box; }
        html { min-width: 320px; background: var(--landing-paper); scroll-behavior: smooth; }
        body { margin: 0; color: var(--landing-ink); background: var(--landing-paper); font-family: 'Inter', sans-serif; }
        .landing-page { overflow: clip; background: var(--landing-paper); }

        .landing-canvas {
            position: relative;
            min-height: 100svh;
            overflow: hidden;
            isolation: isolate;
            background:
                radial-gradient(circle at 12% 22%, rgba(7, 91, 232, .055), transparent 22%),
                radial-gradient(circle at 86% 72%, rgba(255, 122, 49, .06), transparent 23%),
                var(--landing-paper);
        }

        .landing-city-slideshow,
        .landing-city-slide,
        .landing-city-slideshow::after {
            position: absolute;
            inset: 0;
        }

        .landing-city-slideshow { z-index: -3; overflow: hidden; pointer-events: none; }
        .landing-city-slideshow::after {
            content: '';
            z-index: 2;
            background: rgba(248, 246, 240, .84);
            backdrop-filter: blur(1px);
        }

        .landing-city-slide {
            z-index: 1;
            opacity: 0;
            background-position: center;
            background-size: cover;
            filter: blur(9px) saturate(.82);
            transform: scale(1.06);
            animation: landingCityFade var(--city-duration) linear infinite;
            animation-delay: var(--city-delay);
        }

        .landing-brand {
            position: absolute;
            z-index: 7;
            top: 3.5%;
            left: 2.2%;
            display: block;
            width: clamp(118px, 10.5vw, 175px);
            animation: landingFadeUp .8s .1s both;
        }

        .landing-brand img { display: block; width: 100%; height: auto; }

        .landing-canvas::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -2;
            opacity: .28;
            background-image: radial-gradient(rgba(21, 31, 53, .35) .7px, transparent .7px);
            background-size: 9px 9px;
            mask-image: linear-gradient(90deg, #000 0 23%, transparent 39% 61%, #000 77% 100%);
        }

        .landing-center {
            position: absolute;
            z-index: 5;
            top: 48%;
            left: 50%;
            width: min(43vw, 760px);
            text-align: center;
            transform: translate(-50%, -50%);
            animation: landingCenterIn 1s .22s cubic-bezier(.16, 1, .3, 1) both;
        }

        .landing-eyebrow {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
            color: var(--landing-blue);
            font-size: clamp(.68rem, .85vw, .95rem);
            font-weight: 800;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .landing-eyebrow::before {
            content: '';
            width: 20px;
            aspect-ratio: 1;
            flex: 0 0 auto;
            background: var(--landing-blue);
            clip-path: polygon(50% 0, 61% 37%, 100% 50%, 61% 63%, 50% 100%, 39% 63%, 0 50%, 39% 37%);
            animation: landingSpin 7s linear infinite;
        }

        .landing-title {
            margin: 0 auto 22px;
            font-size: clamp(3.5rem, 5.1vw, 6.25rem);
            font-weight: 800;
            line-height: .89;
            letter-spacing: -.068em;
            text-wrap: balance;
        }

        .landing-title span { display: block; color: var(--landing-blue); }

        .landing-description {
            max-width: 610px;
            margin: 0 auto 20px;
            color: #343c4e;
            font-size: clamp(.94rem, 1.12vw, 1.18rem);
            font-weight: 500;
            line-height: 1.55;
            text-wrap: balance;
        }

        .landing-divider { width: 36px; height: 2px; margin: 0 auto 18px; background: var(--landing-blue); }

        .landing-supporting {
            max-width: 560px;
            min-height: 3em;
            margin: 0 auto 29px;
            color: #4a5261;
            font-size: clamp(.82rem, .92vw, 1rem);
            line-height: 1.5;
            text-wrap: balance;
        }

        .landing-typewriter-cursor {
            display: inline-block;
            width: 2px;
            height: 1.05em;
            margin-left: 4px;
            vertical-align: -.12em;
            background: var(--landing-blue);
            animation: landingCursorBlink .78s steps(1) infinite;
        }

        .landing-actions { display: flex; justify-content: center; gap: 14px; }
        .landing-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            min-width: 220px;
            min-height: 54px;
            padding: 0 28px;
            border: 2px solid var(--landing-blue);
            border-radius: 7px;
            color: var(--landing-blue);
            background: rgba(255, 255, 255, .56);
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 16px 36px rgba(7, 91, 232, .08);
            transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
        }

        .landing-button-primary {
            color: #fff;
            background: linear-gradient(135deg, #075be8, #173fe0);
            box-shadow: 0 18px 40px rgba(7, 91, 232, .25);
        }

        .landing-button:hover,
        .landing-button:focus-visible {
            transform: translateY(-3px);
            box-shadow: 0 22px 44px rgba(7, 91, 232, .2);
            outline: 3px solid rgba(7, 91, 232, .18);
            outline-offset: 3px;
        }

        .landing-button-primary:hover { color: #fff; }
        .landing-arrow { font-size: 1.35rem; font-weight: 400; line-height: 1; }

        .landing-card {
            position: absolute;
            z-index: 2;
            margin: 0;
            opacity: 0;
            animation: landingCardIn .9s var(--delay) cubic-bezier(.16, 1, .3, 1) forwards;
        }

        .landing-card img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 14px;
            box-shadow: 0 18px 55px rgba(21, 31, 53, .12);
        }

        .landing-card figcaption {
            position: absolute;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 58%;
            padding: 13px 15px;
            color: var(--landing-ink);
            background: rgba(248, 246, 240, .94);
            font-size: .7rem;
            font-weight: 500;
            line-height: 1.25;
            letter-spacing: 0;
            backdrop-filter: blur(8px);
        }

        .landing-card figcaption i { color: var(--landing-blue); font-size: .9rem; }
        .landing-card-service { --delay: .04s; top: 8.5%; left: 20.5%; width: 14.2%; height: 29%; }
        .landing-card-service figcaption { bottom: -24px; left: 0; }
        .landing-card-store { --delay: .14s; top: 4.6%; right: 20.4%; width: 14.3%; height: 27%; }
        .landing-card-store figcaption { bottom: -24px; left: 2%; }
        .landing-card-vehicle { --delay: .08s; top: 31.5%; left: 1.2%; width: 17.5%; height: 24%; }
        .landing-card-vehicle figcaption { bottom: -24px; left: 0; }
        .landing-card-food { --delay: .18s; bottom: 14%; left: 14.8%; width: 13%; height: 31%; }
        .landing-card-food figcaption { bottom: -24px; left: 0; }
        .landing-card-property { --delay: .25s; top: 19.5%; right: 0; width: 18.4%; height: 34%; }
        .landing-card-property figcaption { bottom: -24px; left: 0; }
        .landing-card-professional { --delay: .38s; right: 12.6%; bottom: 10%; width: 13.8%; height: 29%; }
        .landing-card-professional figcaption { bottom: -24px; left: 0; }
        .landing-card-agro { --delay: .29s; bottom: 1.8%; right: 29.2%; width: 17.2%; height: 18%; }
        .landing-card-agro figcaption { bottom: -15px; left: 0; min-width: 36%; }

        .landing-decor {
            position: absolute;
            z-index: 1;
            pointer-events: none;
        }

        .landing-decor-dots {
            width: 92px;
            height: 92px;
            background-image: radial-gradient(var(--landing-blue) 1.4px, transparent 1.4px);
            background-size: 14px 14px;
            opacity: .75;
        }

        .landing-decor-dots-left { left: 18%; bottom: 2%; }
        .landing-decor-dots-right { right: .8%; top: 3.5%; }
        .landing-decor-orange { left: 19.3%; top: 52%; width: 57px; height: 57px; background: var(--landing-orange); opacity: .92; }
        .landing-decor-blue { right: 18.7%; top: 15%; width: 78px; height: 90px; background: var(--landing-blue); opacity: .9; }

        .landing-platform {
            position: relative;
            min-height: 100svh;
            padding: clamp(50px, 5vw, 72px) 20px 52px;
            overflow: hidden;
            color: #eef4ff;
            background:
                radial-gradient(circle at 18% 20%, rgba(29, 104, 255, .28), transparent 27%),
                radial-gradient(circle at 83% 76%, rgba(69, 162, 111, .18), transparent 24%),
                #071329;
        }

        .landing-platform::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: .18;
            pointer-events: none;
            background-image: radial-gradient(rgba(148, 184, 255, .6) .8px, transparent .8px);
            background-size: 16px 16px;
            mask-image: linear-gradient(90deg, #000, transparent 35% 65%, #000);
        }

        .landing-platform-heading {
            position: relative;
            z-index: 3;
            max-width: 780px;
            margin: 0 auto 20px;
            text-align: center;
        }

        .landing-platform-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 15px;
            color: #72a8ff;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .landing-platform-eyebrow::before { content: ''; width: 24px; height: 2px; background: #3c82f7; }
        .landing-platform-title { margin: 0 0 18px; font-size: clamp(2.4rem, 4.8vw, 5rem); line-height: 1; letter-spacing: -.055em; text-wrap: balance; }
        .landing-platform-description { max-width: 680px; margin: 0 auto; color: #afbed7; font-size: clamp(.95rem, 1.25vw, 1.12rem); line-height: 1.65; text-wrap: balance; }

        .landing-benefits {
            position: relative;
            z-index: 3;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0 auto 17px;
        }

        .landing-benefit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border: 1px solid rgba(130, 169, 231, .2);
            border-radius: 999px;
            color: #d9e6fb;
            background: rgba(255, 255, 255, .055);
            font-size: .8rem;
            font-weight: 650;
            backdrop-filter: blur(8px);
        }

        .landing-benefit i { color: #5e9cff; }
        .landing-video {
            position: relative;
            z-index: 4;
            width: min(100%, 760px);
            margin: 0 auto 20px;
        }

        .landing-video-frame {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            border: 1px solid rgba(122, 166, 235, .24);
            border-radius: 20px;
            background: #020817;
            box-shadow: 0 24px 65px rgba(0, 0, 0, .32);
        }

        .landing-video-frame iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
        .landing-video-label { display: flex; align-items: center; justify-content: center; gap: 8px; margin: 12px 0 0; color: #91a8ca; font-size: .76rem; }
        .landing-video-label i { color: #ff4b55; }
        .landing-city-orbit { position: relative; z-index: 2; max-width: 1080px; margin: 0 auto; }
        .landing-city-stage { position: relative; height: 292px; perspective: 1000px; perspective-origin: 50% 44%; }
        .landing-city-ring {
            --city-radius: 275px;
            position: absolute;
            top: 50%;
            left: 50%;
            width: 168px;
            height: 205px;
            margin: -102px 0 0 -84px;
            transform-style: preserve-3d;
            animation: landingCityOrbit 34s linear infinite;
        }

        .landing-city-ring.is-paused { animation-play-state: paused; }
        .landing-city-card {
            position: absolute;
            inset: 0;
            overflow: hidden;
            border: 1px solid rgba(138, 176, 235, .22);
            border-radius: 15px;
            background: #10213d;
            box-shadow: 0 28px 70px rgba(0, 0, 0, .34);
            transform: rotateY(var(--city-angle)) translateZ(var(--city-radius));
            backface-visibility: hidden;
            color: inherit;
            text-decoration: none;
        }

        .landing-city-card img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease, filter .35s ease; }
        .landing-city-card:hover img, .landing-city-card:focus-visible img { transform: scale(1.06); filter: brightness(1.08); }
        .landing-city-card:focus-visible { outline: 3px solid #72a8ff; outline-offset: 4px; }
        .landing-city-card::after { content: ''; position: absolute; inset: 50% 0 0; background: linear-gradient(transparent, rgba(3, 10, 24, .9)); }
        .landing-city-name { position: absolute; z-index: 2; right: 15px; bottom: 14px; left: 15px; color: #fff; font-size: .88rem; font-weight: 750; text-align: center; }
        .landing-city-name i { margin-left: 5px; font-size: .68rem; opacity: .82; }

        .landing-orbit-controls { position: relative; z-index: 4; display: flex; justify-content: center; gap: 10px; margin-top: 22px; }
        .landing-orbit-toggle,
        .landing-platform-enter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            padding: 0 20px;
            border: 1px solid rgba(122, 166, 235, .32);
            border-radius: 999px;
            color: #e9f2ff;
            background: rgba(255, 255, 255, .07);
            font: inherit;
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: background .2s ease, transform .2s ease;
        }

        .landing-platform-enter { border-color: #2d76ed; background: #1760db; }
        .landing-orbit-toggle:hover, .landing-platform-enter:hover { transform: translateY(-2px); background: #2875ee; }

        .landing-preview {
            position: fixed;
            z-index: 20;
            left: 18px;
            top: 18px;
            padding: 9px 14px;
            border-radius: 999px;
            color: #fff;
            background: #111827;
            font-size: .75rem;
            font-weight: 700;
            box-shadow: 0 10px 28px rgba(0, 0, 0, .18);
        }

        @keyframes landingCenterIn {
            from { opacity: 0; transform: translate(-50%, calc(-50% + 28px)) scale(.97); filter: blur(8px); }
            to { opacity: 1; transform: translate(-50%, -50%) scale(1); filter: blur(0); }
        }
        @keyframes landingCardIn {
            from { opacity: 0; translate: 0 30px; scale: .94; filter: blur(7px); }
            to { opacity: 1; translate: 0 0; scale: 1; filter: blur(0); }
        }
        @keyframes landingFadeUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes landingSpin { to { transform: rotate(360deg); } }
        @keyframes landingCursorBlink { 50% { opacity: 0; } }
        @keyframes landingCityOrbit { to { transform: rotateY(-360deg); } }
        @keyframes landingCityFade {
            0%, 100% { opacity: 0; transform: scale(1.06); }
            7%, 24% { opacity: .22; }
            31% { opacity: 0; transform: scale(1.1); }
        }

        @media (max-width: 1180px) {
            .landing-center { width: 46vw; }
            .landing-title { font-size: clamp(3.3rem, 6vw, 5.4rem); }
            .landing-card figcaption { font-size: .6rem; }
        }

        @media (max-width: 780px) {
            .landing-canvas { display: flex; flex-direction: column; min-height: 100svh; padding: 34px 18px 24px; overflow: hidden; }
            .landing-canvas::before { opacity: .15; mask-image: none; }
            .landing-center { position: relative; top: auto; left: auto; order: 1; width: 100%; transform: none; animation-name: landingCenterInMobile; }
            .landing-brand { position: relative; inset: auto; order: 0; width: 128px; margin: 0 auto 24px; }
            .landing-eyebrow { margin-bottom: 16px; font-size: .62rem; letter-spacing: .12em; }
            .landing-title { margin-bottom: 18px; font-size: clamp(3rem, 15vw, 4.6rem); line-height: .91; }
            .landing-description { margin-bottom: 23px; font-size: .92rem; line-height: 1.5; }
            .landing-actions { flex-direction: column; gap: 10px; }
            .landing-button { width: 100%; min-width: 0; min-height: 53px; }
            .landing-card { position: relative; inset: auto; order: 3; width: 100%; height: 148px; margin-top: 12px; }
            .landing-card figcaption { top: auto; right: auto; bottom: 8px; left: 8px; border-radius: 4px; font-size: .61rem; }
            .landing-card-service, .landing-card-store, .landing-card-professional { display: none; }
            .landing-card-property { margin-top: 30px; }
            .landing-decor { display: none; }
            .landing-preview { top: 8px; left: 8px; font-size: .65rem; }
            .landing-platform { padding: 48px 14px 44px; }
            .landing-platform-heading { margin-bottom: 18px; }
            .landing-video { margin-bottom: 16px; }
            .landing-video-frame { border-radius: 14px; }
            .landing-city-stage { height: 242px; }
            .landing-city-ring { --city-radius: 180px; width: 126px; height: 164px; margin: -82px 0 0 -63px; }
            .landing-city-card { border-radius: 17px; }
            .landing-orbit-controls { flex-direction: column; align-items: stretch; max-width: 310px; margin: 18px auto 0; }
        }

        @keyframes landingCenterInMobile {
            from { opacity: 0; transform: translateY(24px) scale(.97); filter: blur(7px); }
            to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
        }

        @media (min-width: 781px) and (max-height: 760px) {
            .landing-eyebrow { margin-bottom: 12px; }
            .landing-title { margin-bottom: 14px; font-size: clamp(3rem, 5.5vw, 5.3rem); }
            .landing-description { margin-bottom: 18px; font-size: .93rem; }
            .landing-button { min-height: 50px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            .landing-typewriter-cursor { display: none; }
        }
    </style>
</head>
<body>
    @if($preview)
        <div class="landing-preview">Prévia administrativa</div>
    @endif

    <main class="landing-page">
    <section class="landing-canvas" aria-label="Apresentação principal">
        <div class="landing-city-slideshow" aria-hidden="true">
            @php
                $cityDuration = max(count($cityBackgrounds) * 7, 28);
            @endphp
            @foreach($cityBackgrounds as $cityBackground)
                <span class="landing-city-slide" style="background-image: url('{{ $imageUrl($cityBackground) }}'); --city-delay: -{{ $loop->index * 7 }}s; --city-duration: {{ $cityDuration }}s;"></span>
            @endforeach
        </div>

        <a class="landing-brand" href="{{ route('landing') }}" aria-label="Conectado em Sergipe">
            <img src="{{ asset('images/2mapa-sergipe-conectado-azul.png') }}" alt="Conectado em Sergipe">
        </a>

        @foreach($cards as $slot => $card)
            <figure class="landing-card {{ $card['class'] }}">
                <img src="{{ $imageUrl($images[$slot]) }}" alt="{{ $card['label'] }} em Sergipe">
                <figcaption><i class="fa-solid {{ $card['icon'] }}" aria-hidden="true"></i>{{ $card['label'] }}</figcaption>
            </figure>
        @endforeach

        <span class="landing-decor landing-decor-dots landing-decor-dots-left" aria-hidden="true"></span>
        <span class="landing-decor landing-decor-dots landing-decor-dots-right" aria-hidden="true"></span>
        <span class="landing-decor landing-decor-orange" aria-hidden="true"></span>
        <span class="landing-decor landing-decor-blue" aria-hidden="true"></span>

        <section class="landing-center" aria-labelledby="landing-title">
            <div class="landing-eyebrow">{{ \App\Models\Setting::get('landing_eyebrow', 'O ecossistema digital de Sergipe') }}</div>
            <h1 class="landing-title" id="landing-title">
                {{ \App\Models\Setting::get('landing_title', 'Conectado em') }}
                <span>{{ \App\Models\Setting::get('landing_highlight', 'Sergipe') }}</span>
            </h1>
            <p class="landing-description">{{ \App\Models\Setting::get('landing_description', 'A plataforma que conecta serviços, lojas, produtos, imóveis, veículos, empregos, agro e oportunidades dos 75 municípios de Sergipe. Tudo o que você precisa, em um só lugar.') }}</p>
            <div class="landing-divider" aria-hidden="true"></div>
            @php
                $supportingText = \App\Models\Setting::get('landing_supporting_text', 'Conectamos pessoas, profissionais e negócios locais para impulsionar o que Sergipe tem de melhor.');
            @endphp
            <p class="landing-supporting" aria-label="{{ $supportingText }}">
                <span data-landing-typewriter data-text="{{ $supportingText }}" aria-hidden="true"></span><span class="landing-typewriter-cursor" aria-hidden="true"></span>
            </p>
            <div class="landing-actions">
                <a class="landing-button landing-button-primary" href="{{ route('home') }}">
                    {{ \App\Models\Setting::get('landing_primary_label', 'Entrar no site') }}
                    <span class="landing-arrow" aria-hidden="true">→</span>
                </a>
                <a class="landing-button" href="#conhecer-plataforma">
                    {{ \App\Models\Setting::get('landing_secondary_label', 'Conhecer a plataforma') }}
                    <span class="landing-arrow" aria-hidden="true">›</span>
                </a>
            </div>
        </section>
    </section>

    <section class="landing-platform" id="conhecer-plataforma" aria-labelledby="landing-platform-title">
        <header class="landing-platform-heading">
            <div class="landing-platform-eyebrow">{{ \App\Models\Setting::get('landing_about_eyebrow', 'Descubra Sergipe de um novo jeito') }}</div>
            <h2 class="landing-platform-title" id="landing-platform-title">{{ \App\Models\Setting::get('landing_about_title', 'Uma plataforma feita para conectar todo o estado') }}</h2>
            <p class="landing-platform-description">{{ \App\Models\Setting::get('landing_about_description', 'Do litoral ao sertão, aproximamos pessoas de profissionais, empresas, produtos e oportunidades em uma experiência simples, local e confiável.') }}</p>
        </header>

        <div class="landing-benefits" aria-label="Benefícios da plataforma">
            <span class="landing-benefit"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>75 municípios</span>
            <span class="landing-benefit"><i class="fa-solid fa-people-group" aria-hidden="true"></i>Pessoas e negócios locais</span>
            <span class="landing-benefit"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Serviços e oportunidades</span>
        </div>

        <div class="landing-video">
            <div class="landing-video-frame">
                <iframe
                    src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?rel=0&modestbranding=1"
                    title="Vídeo de apresentação da plataforma Conectado em Sergipe"
                    loading="lazy"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                ></iframe>
            </div>
            <p class="landing-video-label"><i class="fa-brands fa-youtube" aria-hidden="true"></i>Conheça a proposta do Conectado em Sergipe</p>
        </div>

        <div class="landing-city-orbit">
            <div class="landing-city-stage" aria-label="Cidades de Sergipe em rotação 3D">
                <div class="landing-city-ring" data-city-ring>
                    @php
                        $orbitCount = max(count($cityBackgrounds), 1);
                    @endphp
                    @foreach($cityBackgrounds as $cityBackground)
                        @php
                            $cityAngle = ($loop->index * 360) / $orbitCount;
                            $cityName = rawurldecode(pathinfo($cityBackground, PATHINFO_FILENAME));
                            $normalizedCityName = preg_replace('/\s+/', ' ', trim($cityName));
                            $wikipediaUrl = $cityWikipediaUrls[$normalizedCityName]
                                ?? 'https://pt.wikipedia.org/wiki/Especial:Pesquisar?search='.rawurlencode($normalizedCityName.' Sergipe');
                        @endphp
                        <a class="landing-city-card" href="{{ $wikipediaUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Conhecer a história de {{ $normalizedCityName }} na Wikipédia" style="--city-angle: {{ $cityAngle }}deg;">
                            <img src="{{ $imageUrl($cityBackground) }}" alt="{{ $cityName }}" loading="lazy">
                            <span class="landing-city-name">{{ $normalizedCityName }} <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="landing-orbit-controls">
                <button class="landing-orbit-toggle" type="button" data-city-toggle aria-pressed="false">
                    <i class="fa-solid fa-pause" aria-hidden="true"></i><span>Pausar rotação</span>
                </button>
                <a class="landing-platform-enter" href="{{ route('home') }}">
                    Entrar na plataforma <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>
    </main>

    <script>
        (() => {
            const typewriter = document.querySelector('[data-landing-typewriter]');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (typewriter) {
                const text = typewriter.dataset.text || '';

                if (prefersReducedMotion) {
                    typewriter.textContent = text;
                } else {
                    let position = 0;
                    let deleting = false;

                    const write = () => {
                        typewriter.textContent = text.slice(0, position);

                        if (!deleting && position < text.length) {
                            position += 1;
                            window.setTimeout(write, 42);
                            return;
                        }

                        if (!deleting) {
                            deleting = true;
                            window.setTimeout(write, 2400);
                            return;
                        }

                        if (position > 0) {
                            position -= 1;
                            window.setTimeout(write, 18);
                            return;
                        }

                        deleting = false;
                        window.setTimeout(write, 650);
                    };

                    write();
                }
            }

            const cityRing = document.querySelector('[data-city-ring]');
            const cityToggle = document.querySelector('[data-city-toggle]');

            cityToggle?.addEventListener('click', () => {
                const paused = cityRing?.classList.toggle('is-paused') ?? false;
                cityToggle.setAttribute('aria-pressed', paused ? 'true' : 'false');
                cityToggle.querySelector('i')?.classList.toggle('fa-pause', !paused);
                cityToggle.querySelector('i')?.classList.toggle('fa-play', paused);
                const label = cityToggle.querySelector('span');
                if (label) label.textContent = paused ? 'Continuar rotação' : 'Pausar rotação';
            });
        })();
    </script>
</body>
</html>
