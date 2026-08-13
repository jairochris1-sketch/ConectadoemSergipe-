@extends('layouts.app')

@section('title', ($moduleTitle ? $moduleTitle . ' em Sergipe - ' : '') . 'Conectado em Sergipe')
@section('body-class', empty($module) ? (auth()->check() ? 'home-authenticated' : 'home-guest') : '')

@php
    $homePreviewImage = \App\Models\Setting::get('home_social_preview', 'images/logo-hero.png');
    $homePreviewImage = str_starts_with($homePreviewImage, 'http') ? $homePreviewImage : asset($homePreviewImage);
    $homeSocialTitle = $moduleTitle
        ? "{$moduleTitle} em Sergipe - Conectado em Sergipe"
        : 'Conectado em Sergipe | Serviços, lojas e oportunidades locais';
    $homeSocialDescription = $moduleTitle
        ? "Encontre {$moduleTitle} em Aracaju e em todo o estado de Sergipe."
        : 'Encontre prestadores de serviços, lojas, produtos, veículos, imóveis e oportunidades em todo o estado de Sergipe.';
@endphp

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $homeSocialTitle,
        'socialDescription' => $homeSocialDescription,
        'socialUrl' => empty($module) ? route('home') : url()->current(),
        'socialImage' => $homePreviewImage,
    ])
@endpush

@push('styles')
<style>
    /* Hero Carousel Responsivo - Mobile vs Desktop */
    .hero-swiper-slide-responsive {
        min-height: 160px;
        max-height: 160px;
    }
    .hero-slide-container-responsive {
        padding-top: 48px;
        padding-bottom: 5px;
    }
    .hero-slide-container-responsive h1 {
        font-size: 1.05rem;
        font-weight: 800;
        margin-bottom: 2px;
        color: #ffffff;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.7);
    }
    .hero-slide-container-responsive p {
        font-size: clamp(.82rem, 1.55vw, 1.08rem);
        line-height: 1.25;
        color: rgba(255, 255, 255, 0.92);
        max-width: 760px;
        margin-bottom: 0;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.7);
    }
    .home-hero-navigation {
        position: absolute;
        z-index: 15;
        top: 50%;
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        padding: 0;
        color: #fff;
        background: rgba(5, 18, 39, .58);
        border: 1px solid rgba(255, 255, 255, .45);
        border-radius: 50%;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .24);
        backdrop-filter: blur(8px);
        transform: translateY(-50%);
        transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
    }
    .home-hero-navigation:hover,
    .home-hero-navigation:focus-visible {
        color: #fff;
        background: #0d6efd;
        border-color: #73b0ff;
        transform: translateY(-50%) scale(1.06);
    }
    .home-hero-navigation:focus-visible {
        outline: 3px solid rgba(255, 255, 255, .8);
        outline-offset: 2px;
    }
    .home-hero-prev { left: clamp(10px, 2vw, 28px); }
    .home-hero-next { right: clamp(10px, 2vw, 28px); }
    .home-hero-copy {
        min-width: 0;
        max-width: 780px;
    }
    .home-hero-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        margin-top: 14px;
        padding: 8px 18px;
        color: #fff;
        background: #0d6efd;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 10px;
        box-shadow: 0 8px 22px rgba(13, 110, 253, .28);
        font-size: .82rem;
        font-weight: 800;
        text-decoration: none;
        transition: background-color .2s ease, transform .2s ease;
    }
    .home-hero-cta:hover,
    .home-hero-cta:focus-visible {
        color: #fff;
        background: #0057d9;
        transform: translateY(-2px);
    }
    .hero-search-card-container {
        z-index: 10;
        margin-top: -95px;
        margin-bottom: 10px;
    }
    @media (max-width: 767.98px) {
        .home-main-hero {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .hero-swiper-slide-responsive {
            min-height: 200px !important;
            max-height: 200px !important;
        }
        .hero-slide-container-responsive {
            padding-top: 50px !important;
            padding-bottom: 10px !important;
        }
        .hero-slide-container-responsive h1 {
            font-size: 1.05rem !important;
        }
        .hero-slide-container-responsive p {
            max-width: calc(100% - 62px);
            font-size: .68rem;
            line-height: 1.3;
        }
        .home-hero-navigation {
            width: 34px;
            height: 34px;
            font-size: .8rem;
        }
        .home-hero-copy {
            max-width: 100%;
        }
        .home-hero-cta {
            display: none;
        }
        .hero-search-card-container {
            margin-top: -105px !important;
            margin-bottom: 12px !important;
        }
        #home-use-location {
            display: none !important;
        }
    }
    @media (min-width: 992px) {
        .home-hero-prev { left: clamp(64px, 6vw, 112px); }
        .home-hero-next { right: clamp(64px, 6vw, 112px); }
        .hero-swiper-slide-responsive {
            min-height: 380px;
            max-height: 480px;
        }
        .hero-slide-container-responsive {
            padding-top: 20px;
            padding-bottom: 100px;
        }
        .hero-search-card-container {
            margin-top: -155px;
            margin-bottom: 24px;
        }
    }
    /* Fix: em monitores 1366x768 o texto fica atrás da caixa de busca */
    @media (min-width: 992px) and (max-height: 800px) {
        .hero-swiper-slide-responsive {
            min-height: 320px !important;
            max-height: 360px !important;
        }
        .hero-slide-container-responsive {
            padding-top: 24px !important;
            padding-bottom: 120px !important;
        }
        .hero-search-card-container {
            margin-top: -145px !important;
        }
    }
    body.home-guest .hero-search-card-container {
        margin-top: -103px;
    }
    @media (max-width: 767.98px) {
        body.home-guest .hero-search-card-container {
            margin-top: -113px !important;
        }
    }
    @media (min-width: 992px) {
        body.home-guest .hero-search-card-container {
            margin-top: -165px;
        }
    }
    @media (min-width: 992px) and (max-height: 800px) {
        body.home-guest .hero-search-card-container {
            margin-top: -155px !important;
        }
    }
    .hero-search-input-box {
        min-height: 38px;
    }
    @media (min-width: 992px) {
        .hero-search-input-box {
            min-height: 42px;
        }
    }
    /* Bordas suaves e elegantes para todos os cards */
    .card, .card-premium {
        border: 1px solid var(--border, rgba(226, 232, 240, 0.8)) !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    html[data-theme="dark"] .card,
    html[data-theme="dark"] .card-premium {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    }
    .card:hover, .card-premium:hover {
        border-color: #0d6efd !important;
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.18) !important;
    }
    .home-clickable-name,
    a:visited .home-clickable-name {
        color: #174f91;
        font-weight: 700;
        text-decoration: none;
    }
    a:hover .home-clickable-name,
    a:focus-visible .home-clickable-name {
        color: #0c376c;
        text-decoration: none;
    }
    [data-bs-theme="dark"] .home-clickable-name,
    [data-bs-theme="dark"] a:visited .home-clickable-name {
        color: #7db5f1;
    }
    [data-bs-theme="dark"] a:hover .home-clickable-name,
    [data-bs-theme="dark"] a:focus-visible .home-clickable-name {
        color: #a9cefa;
    }
    .card-premium > button[aria-label="Favoritar"] {
        width: 30px;
        height: 30px;
        min-width: 30px;
        padding: 0 !important;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .home-auth-mobile-bottom-nav,
    .home-auth-mobile-providers {
        display: none;
    }
    .home-city-groups {
        position: relative;
        overflow: hidden;
        padding: clamp(16px, 2vw, 24px);
        border: 1px solid rgba(34, 128, 255, .28);
        border-radius: 18px;
        color: #10213a;
        background:
            radial-gradient(circle at 50% 0, rgba(13, 110, 253, .12), transparent 40%),
            linear-gradient(145deg, #f8fbff, #edf5ff 72%);
        box-shadow: 0 16px 38px rgba(29, 78, 216, .1);
    }
    .home-city-groups-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .home-city-groups-pin {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: grid;
        place-items: center;
        border: 1px solid #00c8ff;
        border-radius: 50%;
        color: #8bdcff;
        background: rgba(10, 61, 133, .34);
        box-shadow: 0 0 28px rgba(0, 160, 255, .18);
        font-size: 1.1rem;
    }
    .home-city-groups-title { margin: 0 0 3px; color: #10213a; font-size: clamp(1.05rem, 1.7vw, 1.4rem); font-weight: 800; }
    .home-city-groups-subtitle { margin: 0; color: #60718a; font-size: clamp(.72rem, 1vw, .84rem); }
    .home-city-groups-rail {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 2px 2px 10px;
        scroll-snap-type: x proximity;
        scrollbar-color: #56708d rgba(255, 255, 255, .1);
        scrollbar-width: thin;
    }
    .home-city-groups-rail::-webkit-scrollbar { height: 7px; }
    .home-city-groups-rail::-webkit-scrollbar-track { border-radius: 999px; background: rgba(255, 255, 255, .1); }
    .home-city-groups-rail::-webkit-scrollbar-thumb { border-radius: 999px; background: #56708d; }
    .home-city-group-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 152px;
        min-width: 152px;
        padding: 12px 10px 10px;
        scroll-snap-align: start;
        text-align: center;
        border: 1px solid rgba(39, 132, 255, .38);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(29, 78, 216, .08);
    }
    .home-city-group-card::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: -1;
        background-image: linear-gradient(rgba(255, 255, 255, .8), rgba(247, 251, 255, .94)), var(--city-group-background);
        background-position: center;
        background-size: cover;
    }
    .home-city-group-card > * { position: relative; z-index: 1; }
    .home-city-group-cover {
        width: 72px;
        height: 72px;
        margin: 0 auto 8px;
        padding: 2px;
        overflow: hidden;
        border: 1px solid #51e493;
        border-radius: 50%;
        background: linear-gradient(145deg, #25d366, #087f5b);
        box-shadow: 0 0 22px rgba(37, 211, 102, .22);
    }
    .home-city-group-cover img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .home-city-group-name { min-height: 2.35em; margin: 0 0 2px; color: #10213a; font-size: .82rem; font-weight: 800; line-height: 1.17; }
    .home-city-group-type { margin: 0 0 6px; color: #53657e; font-size: .68rem; }
    .home-city-group-status { display: flex; align-items: center; justify-content: center; gap: 5px; margin-bottom: 8px; color: #53657e; font-size: .64rem; }
    .home-city-group-status i { color: #18a957; }
    .home-city-group-enter {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 32px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, #25d366, #128c7e);
        font-size: .7rem;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 9px 20px rgba(37, 211, 102, .2);
        transition: transform .2s ease, filter .2s ease;
    }
    .home-city-group-enter:hover,
    .home-city-group-enter:focus-visible { color: #fff; filter: brightness(1.1); transform: translateY(-2px); }
    .home-city-group-card.is-inactive { opacity: .42; border-color: rgba(125, 145, 174, .24); filter: saturate(.25); }
    .home-city-group-card.is-inactive .home-city-group-cover img { filter: grayscale(1); }
    .home-city-group-enter.is-disabled { color: #7c8ba0; background: #dce5f0; box-shadow: none; cursor: not-allowed; }
    .home-city-groups-note { display: flex; justify-content: center; align-items: flex-start; gap: 9px; margin: 14px auto 0; color: #60718a; font-size: .7rem; line-height: 1.4; }
    .home-city-groups-note i { margin-top: 2px; color: #38bdf8; }
    html[data-theme="dark"] .home-city-groups,
    [data-bs-theme="dark"] .home-city-groups {
        color: #fff;
        background: radial-gradient(circle at 50% 0, rgba(0, 111, 255, .16), transparent 38%), linear-gradient(145deg, #061326, #020b1b 72%);
        box-shadow: 0 22px 55px rgba(2, 11, 27, .25);
    }
    html[data-theme="dark"] .home-city-groups-title,
    [data-bs-theme="dark"] .home-city-groups-title,
    html[data-theme="dark"] .home-city-group-name,
    [data-bs-theme="dark"] .home-city-group-name { color: #fff; }
    html[data-theme="dark"] .home-city-groups-subtitle,
    [data-bs-theme="dark"] .home-city-groups-subtitle,
    html[data-theme="dark"] .home-city-groups-note,
    [data-bs-theme="dark"] .home-city-groups-note { color: #aebed7; }
    html[data-theme="dark"] .home-city-group-card,
    [data-bs-theme="dark"] .home-city-group-card { background: #07172e; box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04); }
    html[data-theme="dark"] .home-city-group-card::before,
    [data-bs-theme="dark"] .home-city-group-card::before {
        background-image: linear-gradient(rgba(3, 16, 36, .7), rgba(3, 13, 31, .94)), var(--city-group-background);
    }
    html[data-theme="dark"] .home-city-group-type,
    [data-bs-theme="dark"] .home-city-group-type { color: #d5deec; }
    html[data-theme="dark"] .home-city-group-status,
    [data-bs-theme="dark"] .home-city-group-status { color: #9fb3d0; }
    html[data-theme="dark"] .home-city-group-enter.is-disabled,
    [data-bs-theme="dark"] .home-city-group-enter.is-disabled { color: #8b9bb2; background: #17243a; }
    .home-city-group-confirmation {
        position: fixed;
        z-index: 1090;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 18px;
        background: rgba(2, 10, 24, .68);
        backdrop-filter: blur(6px);
    }
    .home-city-group-confirmation[hidden] { display: none !important; }
    .home-city-group-confirmation-dialog {
        width: min(100%, 440px);
        padding: 22px;
        border: 1px solid #d9e5f3;
        border-radius: 18px;
        color: #17243a;
        background: #fff;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .28);
    }
    .home-city-group-confirmation-icon {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        margin-bottom: 13px;
        border-radius: 50%;
        color: #fff;
        background: linear-gradient(135deg, #25d366, #128c7e);
        font-size: 1.15rem;
    }
    .home-city-group-confirmation-title { margin: 0 0 10px; font-size: 1.2rem; font-weight: 800; }
    .home-city-group-confirmation-text { margin: 0 0 10px; color: #52627a; font-size: .84rem; line-height: 1.55; }
    .home-city-group-confirmation-actions { display: flex; gap: 9px; margin-top: 18px; }
    .home-city-group-confirmation-actions > * {
        flex: 1;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 10px;
        font: inherit;
        font-size: .82rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }
    .home-city-group-confirmation-join { color: #fff; background: linear-gradient(135deg, #25d366, #128c7e); }
    .home-city-group-confirmation-join:hover { color: #fff; filter: brightness(1.06); }
    .home-city-group-confirmation-close { color: #334155; background: #e9eef5; }
    html[data-theme="dark"] .home-city-group-confirmation-dialog,
    [data-bs-theme="dark"] .home-city-group-confirmation-dialog { color: #f8fafc; border-color: #2b3d57; background: #0d192b; }
    html[data-theme="dark"] .home-city-group-confirmation-text,
    [data-bs-theme="dark"] .home-city-group-confirmation-text { color: #aebed7; }
    html[data-theme="dark"] .home-city-group-confirmation-close,
    [data-bs-theme="dark"] .home-city-group-confirmation-close { color: #e2e8f0; background: #25334a; }
    @media (max-width: 575.98px) {
        .home-city-groups { margin-inline: -4px; padding: 14px 10px; border-radius: 14px; }
        .home-city-groups-header { align-items: flex-start; gap: 9px; margin-bottom: 13px; }
        .home-city-groups-pin { width: 38px; height: 38px; flex-basis: 38px; font-size: .9rem; }
        .home-city-group-card { width: 136px; min-width: 136px; padding-inline: 8px; }
        .home-city-group-cover { width: 62px; height: 62px; }
        .home-city-groups-note { justify-content: flex-start; }
    }
    @auth
    @media (max-width: 767.98px) {
        body.home-authenticated {
            padding-bottom: 70px;
            background: var(--background);
        }
        body.home-authenticated .marketplace-header {
            position: absolute !important;
            z-index: 1040;
            top: 0;
            right: 0;
            left: 0;
            padding: 8px 12px 0;
            background: transparent !important;
            border: 0 !important;
        }
        body.home-authenticated .marketplace-header-shell {
            background: transparent;
            box-shadow: none;
        }
        body.home-authenticated .marketplace-header-row {
            min-height: 48px;
            gap: 8px;
            padding: 0;
        }
        body.home-authenticated .marketplace-brand {
            order: 0;
            margin-right: auto;
            color: #fff;
        }
        body.home-authenticated .marketplace-brand-logo,
        body.home-authenticated .marketplace-brand-divider,
        body.home-authenticated .marketplace-top-announce,
        body.home-authenticated .marketplace-location-active {
            display: none !important;
        }
        body.home-authenticated .marketplace-brand-name {
            display: flex;
            color: #fff;
            font-size: 0.82rem;
            font-weight: 500;
            letter-spacing: -.02em;
            line-height: 1.05;
            text-shadow: 0 2px 8px rgba(0, 0, 0, .45);
        }
        body.home-authenticated .marketplace-brand-name span:last-child {
            position: relative;
            font-size: 1.15rem;
            font-weight: 800;
        }
        body.home-authenticated .marketplace-brand-name span:last-child::after {
            display: none !important;
        }
        body.home-authenticated .marketplace-header-actions {
            order: 1;
            margin-left: 0;
        }
        body.home-authenticated .marketplace-account-button:not(.marketplace-guest-login) {
            width: 40px;
            height: 40px;
            min-height: 40px;
            flex-basis: 40px;
            color: #fff;
            background: rgba(15, 30, 55, .85);
            border: 1px solid rgba(255, 255, 255, .2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .2);
            border-radius: 50%;
        }
        body.home-authenticated .marketplace-account-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        body.home-authenticated .marketplace-account-fallback {
            color: #ffffff;
            font-size: .95rem;
        }
        body.home-authenticated .marketplace-mobile-toggle {
            order: 2;
            width: 40px;
            height: 40px;
            flex-basis: 40px;
            color: #fff;
            background: rgba(15, 30, 55, .85);
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 50%;
            font-size: 1.05rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .2);
        }
        body.home-authenticated .marketplace-header.mobile-menu-open .marketplace-header-shell {
            padding-bottom: 10px;
            background: rgba(10, 20, 38, .98);
            border-radius: 18px;
        }
        body.home-authenticated .marketplace-mobile-menu {
            color: var(--foreground);
            background: var(--card);
            border-radius: 0 0 16px 16px;
        }
        body.home-authenticated .home-main-hero {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        body.home-authenticated .hero-swiper-slide-responsive {
            min-height: 200px !important;
            max-height: 200px !important;
        }
        body.home-authenticated .hero-slide-container-responsive {
            justify-content: flex-start !important;
            padding: 50px 14px 10px !important;
        }
        body.home-authenticated .hero-slide-container-responsive h1 {
            display: block !important;
            font-size: 1.05rem !important;
            font-weight: 800 !important;
            margin-bottom: 2px !important;
            color: #ffffff !important;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.7) !important;
        }
        body.home-authenticated .hero-slide-container-responsive p {
            display: block !important;
            font-size: 0.75rem !important;
            line-height: 1.25 !important;
            color: rgba(255, 255, 255, 0.92) !important;
            max-width: 290px !important;
            margin-bottom: 0 !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.7) !important;
        }
        body.home-authenticated .hero-search-card-container {
            width: 100%;
            margin-top: -105px !important;
            margin-bottom: 12px !important;
            padding: 0 8px;
        }
        body.home-authenticated .home-search-panel {
            padding: 8px !important;
            background: rgba(8, 20, 39, .97) !important;
            border: 1px solid rgba(255, 255, 255, .22) !important;
            border-radius: 14px !important;
            box-shadow: 0 6px 20px rgba(5, 14, 29, .24) !important;
        }
        body.home-authenticated #home-search-form {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px 0 !important;
            margin-bottom: 8px !important;
        }
        body.home-authenticated #home-search-form > .home-search-query-field {
            min-width: 0;
            min-height: 42px;
            padding-left: 12px !important;
            border-radius: 12px 0 0 12px !important;
        }
        body.home-authenticated #home-search-query {
            min-width: 0;
            font-size: .84rem;
        }
        body.home-authenticated #home-search-microphone {
            padding: 0 8px !important;
            border-right: 1px solid #d7dfeb;
            border-radius: 0;
            font-size: .9rem;
        }
        body.home-authenticated .home-search-filter-row {
            grid-column: 1 / -1;
            grid-row: 2;
            gap: 6px !important;
        }
        body.home-authenticated .home-search-filter-row > div {
            min-height: 38px;
            padding: 4px 8px !important;
            border-radius: 10px !important;
        }
        body.home-authenticated .home-search-filter-row select {
            font-size: .78rem !important;
            padding-right: 4px !important;
        }
        body.home-authenticated #home-use-location {
            display: none !important;
        }
        body.home-authenticated .home-search-submit {
            min-height: 42px;
            width: auto !important;
            padding: 0 14px !important;
            border-radius: 0 12px 12px 0 !important;
            font-size: .84rem;
            background-color: #0d6efd !important;
            background: #0d6efd !important;
            border: none !important;
            color: #ffffff !important;
        }
        body.home-authenticated .home-search-submit i {
            display: none;
        }
        body.home-authenticated .home-search-submit-label-desktop {
            display: none;
        }
        body.home-authenticated .home-search-submit-label-mobile {
            display: inline !important;
        }
        body.home-authenticated .home-search-status-row {
            margin-bottom: 4px !important;
        }
        body.home-authenticated .home-search-shortcuts {
            gap: 4px !important;
            justify-content: flex-start;
            overflow-x: auto;
            padding: 6px 2px 2px !important;
            border-top: 1px solid rgba(255, 255, 255, .14) !important;
            border-radius: 0;
            scrollbar-width: none;
        }
        body.home-authenticated .home-search-shortcuts::-webkit-scrollbar {
            display: none;
        }
        body.home-authenticated .home-search-shortcuts a {
            flex: 0 0 auto;
            padding: 4px 8px;
            font-size: .74rem !important;
        }
        body.home-authenticated .home-search-shortcuts a:nth-of-type(n+5):not(:last-child) {
            display: inline-flex !important;
        }
        body.home-authenticated .home-highlights-layout {
            padding-right: 12px;
            padding-left: 12px;
        }
        body.home-authenticated .home-highlights-layout > .row {
            --bs-gutter-x: 0;
        }
        body.home-authenticated .home-featured-column h4 {
            font-size: 1.05rem !important;
            white-space: nowrap;
        }
        body.home-authenticated .home-featured-column .d-flex.justify-content-between a {
            font-size: .76rem !important;
            white-space: nowrap;
        }
        body.home-authenticated .home-featured-column .swiper-featured-ads {
            overflow: visible;
        }
        body.home-authenticated .home-featured-column .card,
        body.home-authenticated .home-featured-column .card-premium {
            height: 155px !important;
        }
        body.home-authenticated .home-featured-column .card-img-top,
        body.home-authenticated .home-featured-column .card-img-placeholder {
            height: 75px !important;
        }
        body.home-authenticated .home-featured-column .card-body {
            padding: 4px 6px !important;
        }
        body.home-authenticated .home-featured-column .card-body small.text-muted.d-block {
            display: none !important;
        }
        body.home-authenticated .home-featured-column .card-title {
            font-size: .76rem !important;
            margin-bottom: 2px !important;
            line-height: 1.15 !important;
        }
        body.home-authenticated .home-featured-column strong.text-primary {
            font-size: .78rem !important;
        }
        body.home-authenticated .home-provider-desktop-column {
            display: none !important;
        }
        body.home-authenticated .home-auth-mobile-providers {
            display: block;
            margin-top: 24px;
            padding-right: 12px;
            padding-left: 12px;
        }
        .home-auth-provider-list {
            display: grid;
            gap: 6px;
        }
        .home-auth-provider-card {
            display: grid;
            grid-template-columns: 60px minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
            min-height: 85px;
            padding: 8px;
            color: var(--foreground);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        }
        .home-auth-provider-photo,
        .home-auth-provider-photo-placeholder {
            width: 60px;
            height: 72px;
            object-fit: cover;
            border-radius: 12px;
        }
        .home-auth-provider-photo-placeholder {
            display: grid;
            place-items: center;
            color: #0d6efd;
            background: #eaf2ff;
            font-size: 1.2rem;
        }
        .home-auth-provider-copy {
            min-width: 0;
        }
        .home-auth-provider-copy strong,
        .home-auth-provider-copy span,
        .home-auth-provider-copy small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .home-auth-provider-copy strong {
            color: #174f91;
            font-size: .88rem;
        }
        .home-auth-provider-copy span {
            margin: 2px 0 6px;
            color: var(--muted-foreground);
            font-size: .75rem;
        }
        .home-auth-provider-copy small {
            color: #f59f00;
            font-size: .7rem;
            font-weight: 700;
        }
        .home-auth-provider-copy small em {
            margin-left: 4px;
            color: var(--muted-foreground);
            font-style: normal;
        }
        .home-auth-provider-actions {
            display: flex;
            gap: 6px;
        }
        .home-auth-provider-actions a {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            color: #fff;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(15, 23, 42, .12);
            font-size: .85rem;
        }
        .home-auth-provider-actions .is-whatsapp { background: #079455; }
        .home-auth-provider-actions .is-profile { background: #0d6efd; }
        body.home-authenticated .home-auth-mobile-bottom-nav {
            position: fixed;
            z-index: 1060;
            right: 0;
            bottom: 0;
            left: 0;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            min-height: 58px;
            padding: 4px max(8px, env(safe-area-inset-right)) calc(4px + env(safe-area-inset-bottom)) max(8px, env(safe-area-inset-left));
            background: rgba(255, 255, 255, .97);
            border-top: 1px solid #dbe3ef;
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -6px 20px rgba(15, 23, 42, .1);
            backdrop-filter: blur(14px);
        }
        .home-auth-mobile-bottom-nav a {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 2px;
            color: #52627a;
            font-size: .62rem;
            font-weight: 700;
            text-decoration: none;
        }
        .home-auth-mobile-bottom-nav i {
            font-size: 1.1rem;
        }
        .home-auth-mobile-bottom-nav a.is-active {
            color: #0d6efd;
        }
        .home-auth-mobile-bottom-nav a.is-primary i {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            margin-top: -12px;
            color: #fff;
            background: #0d6efd;
            border: 3px solid #fff;
            border-radius: 50%;
            box-shadow: 0 4px 14px rgba(13, 110, 253, .34);
            font-size: 1.02rem;
        }
        body.home-authenticated .theme-toggle-wrapper,
        body.home-authenticated .theme-toggle-container {
            bottom: 68px !important;
            right: 12px !important;
            z-index: 1070 !important;
        }
        .theme-toggle-btn {
            width: 34px !important;
            height: 34px !important;
        }
        [vw] [vw-access-button] {
            transform: scale(0.72) !important;
            transform-origin: right center !important;
        }
        body.home-authenticated .swiper-featured-prev,
        body.home-authenticated .swiper-featured-next {
            display: none !important;
        }
        body.home-authenticated footer {
            padding-bottom: 75px !important;
        }
    }
    @endauth
</style>
@endpush

@section('content')
<!-- Hero Carousel -->
@if(empty($module))
<div class="row mx-0 mb-3 home-main-hero">
    <div class="col-12 px-0">
        <div class="swiper swiper-hero overflow-hidden position-relative">
            <div class="swiper-wrapper">
                @php
                    $bannerBrightness = (int) \App\Models\Setting::get('home_banner_brightness', 62);
                    $bannerBlur = (int) \App\Models\Setting::get('home_banner_blur', 0);
                    $overlayOpacityLeft = number_format($bannerBrightness / 100, 2, '.', '');
                    $overlayOpacityRight = number_format(max(0, ($bannerBrightness - 22) / 100), 2, '.', '');
                    $blurStyle = $bannerBlur > 0 ? "filter: blur({$bannerBlur}px);" : "";
                    $heroMessages = [
                        [
                            'title' => 'Encontre tudo em Sergipe.',
                            'description' => 'O maior ecossistema digital de Sergipe, conectando prestadores de serviços, lojas, produtos, veículos, imóveis e oportunidades nos 75 municípios do estado.',
                        ],
                        [
                            'title' => 'Tudo o que você procura em Sergipe, em um só lugar.',
                            'description' => 'Serviços, lojas, produtos, veículos, imóveis, empregos, agro e oportunidades nos 75 municípios.',
                        ],
                        [
                            'title' => 'Sergipe inteiro mais perto de você.',
                            'description' => 'Encontre serviços, lojas, produtos, imóveis, veículos e oportunidades em um só lugar.',
                        ],
                        [
                            'title' => 'Valorize quem é daqui.',
                            'description' => 'Descubra profissionais, lojas e negócios que fazem Sergipe acontecer todos os dias.',
                        ],
                        [
                            'title' => 'Assine agora nossos planos e saia na frente.',
                            'description' => 'Dê mais visibilidade aos seus anúncios, serviços e produtos e aumente suas oportunidades.',
                            'cta_label' => 'Conhecer planos',
                            'cta_url' => route('page.plans'),
                        ],
                        [
                            'title' => 'Encontre. Conecte. Resolva.',
                            'description' => 'Pesquise, encontre e fale diretamente com quem oferece o que você precisa perto de você.',
                        ],
                        [
                            'title' => 'Da capital ao sertão.',
                            'description' => 'Encontre pessoas, serviços e oportunidades nos 75 municípios de Sergipe.',
                        ],
                        [
                            'title' => 'Profissionais perto de você, em poucos cliques.',
                            'description' => 'Encontre eletricistas, pintores, mecânicos, diaristas e outros profissionais da sua região.',
                        ],
                        [
                            'title' => 'Descubra lojas e negócios locais.',
                            'description' => 'Veja produtos, conheça novas opções e compre de quem movimenta a economia de Sergipe.',
                        ],
                        [
                            'title' => 'Faça parte dessa conexão.',
                            'description' => 'Crie sua conta gratuitamente e descubra tudo o que Sergipe tem para oferecer.',
                        ],
                        [
                            'title' => 'Faça seu negócio ser encontrado.',
                            'description' => 'Seu cliente pode estar procurando exatamente o que você oferece neste momento.',
                        ],
                        [
                            'title' => 'Seu próximo cliente pode estar a uma busca de distância.',
                            'description' => 'Mais do que anunciar: esteja presente onde seus clientes procuram.',
                        ],
                        [
                            'title' => 'Mais destaque. Mais presença. Mais oportunidades.',
                            'description' => 'Escolha um plano e aumente suas chances de ser encontrado por quem realmente procura.',
                            'cta_label' => 'Ver planos',
                            'cta_url' => route('page.plans'),
                        ],
                        [
                            'title' => 'Conecte-se. Divulgue. Venda. Cresça em Sergipe.',
                            'description' => 'Conectamos pessoas, profissionais e negócios para movimentar oportunidades em todo o estado.',
                        ],
                    ];
                @endphp
                @foreach($heroBanners as $index => $banner)
                @php
                    $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner);
                    $heroMessage = $heroMessages[$index % count($heroMessages)];
                @endphp
                <div class="swiper-slide hero-swiper-slide-responsive d-flex flex-column justify-content-center align-items-center px-3 px-md-5" 
                     style="background-color: #0b172a; background-image: linear-gradient(to right, rgba(10, 15, 30, {{ $overlayOpacityLeft }}) 0%, rgba(10, 15, 30, {{ $overlayOpacityRight }}) 100%), url('{{ $bannerUrl }}'); background-size: cover; background-position: center; {{ $blurStyle }}">
                    
                    <div class="container hero-slide-container-responsive position-relative h-100 d-flex flex-column justify-content-center text-start">
                        <div class="d-flex justify-content-between align-items-start w-100">
                            <div class="home-hero-copy">
                                <h1 class="text-white fw-bold mb-2" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5); font-size: clamp(1.8rem, 4vw, 2.8rem);">
                                    {{ $heroMessage['title'] }}
                                </h1>
                                <p class="text-light opacity-90 mb-0">
                                    {{ $heroMessage['description'] }}
                                </p>
                                @if(!empty($heroMessage['cta_url']))
                                    <a href="{{ $heroMessage['cta_url'] }}" class="home-hero-cta">
                                        {{ $heroMessage['cta_label'] }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                            @if($loop->first)
                                <div id="home-hero-plans-card" class="home-hero-plans-card d-none d-md-flex align-items-center rounded-4 px-3 py-2 ms-3 shadow-lg" style="position: relative; padding-right: 34px; background: linear-gradient(135deg, rgba(37, 99, 235, 0.95), rgba(29, 78, 216, 0.95)); border: 1px solid rgba(255, 255, 255, 0.2);">
                                    <a href="{{ route('page.plans') }}" class="d-flex align-items-center text-decoration-none">
                                        <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="background: rgba(255, 255, 255, 0.2);">
                                            <i class="fa-solid fa-gem text-white fs-4"></i>
                                        </div>
                                        <div class="text-start text-nowrap">
                                            <strong class="text-white d-block lh-1 mb-1">Planos Premium</strong>
                                            <small class="text-white opacity-90">Assine agora o Conectado em Sergipe</small>
                                        </div>
                                    </a>
                                    <button type="button" data-close-home-plans aria-label="Fechar card de planos" title="Fechar" style="position: absolute; top: 5px; right: 5px; width: 22px; height: 22px; padding: 0; border: 1px solid rgba(255,255,255,.35); border-radius: 50%; color: #fff; background: rgba(0,0,0,.28);">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
            @if(count($heroBanners) > 1)
                <button type="button" class="home-hero-navigation home-hero-prev" aria-label="Ver banner anterior" title="Banner anterior">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="home-hero-navigation home-hero-next" aria-label="Ver próximo banner" title="Próximo banner">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            @endif
        </div>
    </div>
</div>
@endif

@if($module === 'real_estate')
    @include('partials.real_estate_hero')
@elseif($module === 'vehicles')
    @include('partials.vehicles_hero')
@elseif($module === 'products')
    @include('partials.products_hero')
@elseif($module === 'jobs')
    @include('partials.jobs_hero')
@endif

<!-- Container Busca Rápida Responsiva -->
@if(empty($module) || ($module !== 'real_estate' && $module !== 'vehicles' && $module !== 'products' && $module !== 'jobs'))
<div class="container position-relative hero-search-card-container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="rounded-4 shadow-lg p-2 p-md-2.5 px-xl-3 py-xl-2.5 mx-auto home-search-panel" style="background: rgba(15, 23, 42, 0.92); backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, 0.15);">
                <form
                    id="home-search-form"
                    action="{{ route('home') }}"
                    method="GET"
                    data-suggestions-url="{{ route('search.suggestions') }}"
                    class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-2 gap-lg-3 w-100 mb-2 mb-md-3"
                >
                    <input type="hidden" id="home-search-module-value" name="module" value="{{ $module }}">
                    <input type="hidden" id="home-search-service-category-value" name="service_category" value="">

                    <!-- Campo Pesquisa -->
                    <div class="position-relative d-flex align-items-center bg-white rounded-3 px-3 py-1 py-md-2 w-100 hero-search-input-box home-search-query-field" style="flex: 2.5;">
                        <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
                        <input
                            id="home-search-query"
                            class="form-control bg-transparent border-0 shadow-none p-0 text-dark"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="O que você procura em {{ !empty($city) ? $city : 'Sergipe' }}?"
                            autocomplete="off"
                        >
                        <button type="button" id="home-search-microphone" class="btn btn-link text-muted p-0 ms-2 text-decoration-none" title="Buscar por voz">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <div id="home-search-suggestions" class="quick-search-suggestions" hidden></div>
                    </div>

                    <!-- Linha Mobile: Cidade & Categoria -->
                    <div class="d-flex gap-2 w-100 home-search-filter-row" style="flex: 3;">
                        <!-- Cidade com botão GPS -->
                        <div class="position-relative d-flex align-items-center bg-white rounded-3 px-2 px-md-3 py-1 py-md-2 w-50 hero-search-input-box overflow-hidden">
                            <i class="fa-solid fa-location-dot text-danger me-1 flex-shrink-0"></i>
                            <select id="home-search-city" name="city" class="form-select bg-transparent border-0 shadow-none py-0 ps-0 pe-4 text-dark fw-semibold text-truncate me-1" style="font-size: 0.84rem; max-width: calc(100% - 36px); width: 100%; min-width: 0;">
                                <option value="" {{ empty($city) ? 'selected' : '' }}>Todas as cidades</option>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                @endforeach
                            </select>
                            <button type="button" id="home-use-location" class="btn btn-danger btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 28px; height: 28px; background-color: #dc3545; border-color: #dc3545; color: #ffffff;" title="Detectar minha localização GPS atual" aria-label="Usar minha localização">
                                <i class="fa-solid fa-location-dot" style="font-size: 0.85rem;"></i>
                                <span data-location-button-label class="visually-hidden">Usar minha localização</span>
                            </button>
                        </div>

                        <!-- Categoria -->
                        <div class="position-relative d-flex align-items-center bg-white rounded-3 px-2 px-md-3 py-1 py-md-2 w-50 hero-search-input-box overflow-hidden">
                            <i class="fa-solid fa-table-cells-large text-muted me-2 flex-shrink-0"></i>
                            <select id="home-search-category-filter" name="category" class="form-select bg-transparent border-0 shadow-none py-0 ps-0 pe-4 text-dark fw-semibold text-truncate" style="font-size: 0.88rem; width: 100%; min-width: 0;">
                                <option value="">Todas categorias</option>
                                <optgroup label="Anúncios">
                                    <option value="real_estate" {{ $module === 'real_estate' ? 'selected' : '' }}>Imóveis</option>
                                    <option value="products" {{ $module === 'products' ? 'selected' : '' }}>Produtos</option>
                                    <option value="vehicles" {{ $module === 'vehicles' ? 'selected' : '' }}>Veículos</option>
                                    <option value="jobs" {{ $module === 'jobs' ? 'selected' : '' }}>Empregos</option>
                                    <option value="agro" {{ $module === 'agro' ? 'selected' : '' }}>Agro</option>
                                </optgroup>
                                <optgroup label="Serviços">
                                    @foreach($serviceSearchCategories as $serviceCategory)
                                        <option value="service:{{ $serviceCategory['name'] }}">{{ $serviceCategory['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- Botão Consultar -->
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4 w-100 hero-search-input-box home-search-submit" style="flex: 1; background-color: #0d6efd; border: none;">
                        <i class="fa-solid fa-magnifying-glass me-2"></i><span class="home-search-submit-label-desktop">Consultar</span><span class="home-search-submit-label-mobile d-none">Consultar</span>
                    </button>
                </form>

                <!-- Status de Localização e Voz -->
                <div class="d-flex flex-wrap gap-2 px-1 mb-2 home-search-status-row">
                    <div id="home-location-status" class="quick-search-location-status small text-light opacity-90">
                        @if(!empty($city))
                            <i class="fa-solid fa-location-dot text-success me-1"></i> Cidade ativa: <strong>{{ $city }}</strong>
                        @endif
                    </div>
                    <div id="home-voice-status" class="quick-search-voice-status small text-light opacity-90" hidden></div>
                </div>

                <!-- Chips de Categoria Horizontal (Rolagem Suave no Mobile) -->
                <div class="d-flex align-items-center gap-3 gap-md-4 pt-2 px-1 border-top border-secondary border-opacity-25 overflow-x-auto text-nowrap scrollbar-none home-search-shortcuts" style="scrollbar-width: none;">
                    <a href="{{ route('module.products') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-tag text-primary"></i> Produtos
                    </a>
                    <a href="{{ route('module.real_estate') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-building text-primary"></i> Imóveis
                    </a>
                    <a href="{{ route('module.vehicles') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-car text-primary"></i> Veículos
                    </a>
                    <a href="{{ route('module.services') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-wrench text-primary"></i> Serviços
                    </a>
                    <a href="{{ route('module.jobs') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-briefcase text-primary"></i> Empregos
                    </a>
                    <a href="{{ route('module.agro') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-leaf text-primary"></i> Agro
                    </a>
                    <a href="{{ route('culture.index') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-palette text-primary"></i> Arte e Cultura
                    </a>
                    <a href="{{ route('stores.index') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-ellipsis text-primary"></i> Ver todas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Section Destaques para você (Carrossel em movimento) + Profissionais em destaque -->
@if(!$isSearch && empty($module))
<div class="container mb-3 mb-md-4 home-highlights-layout">
    <div class="row g-3 g-md-4">
        <!-- Coluna Esquerda: Destaques para você (Swiper Slider em Movimento) -->
        <div class="col-12 col-lg-8 home-featured-column">
            <div class="d-flex justify-content-between align-items-center mb-2.5">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                    Destaques para você
                </h4>
                <a href="{{ route('home') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver todos os destaques <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <style>
                .swiper-marquee-esteira .swiper-wrapper {
                    -webkit-transition-timing-function: linear !important;
                    -o-transition-timing-function: linear !important;
                    transition-timing-function: linear !important;
                }
                .swiper-providers, .swiper-providers .swiper-wrapper, .swiper-providers .swiper-slide {
                    touch-action: auto !important;
                }
                .swiper-providers-container {
                    height: 312px;
                    padding-top: 14px;
                    padding-bottom: 14px;
                    overflow: visible !important;
                }
                body.home-guest .swiper-providers-container {
                    height: 300px;
                }
                .swiper-providers {
                    height: 100%;
                    overflow: hidden !important;
                    border-radius: 16px;
                }
                /* Swiper Destaques: altura uniforme e sem sobreposição horizontal */
                .swiper-featured-ads .swiper-wrapper {
                    align-items: stretch !important;
                }
                .swiper-featured-ads .swiper-slide {
                    height: auto !important;
                    box-sizing: border-box !important;
                    min-width: 0 !important;
                }
                .swiper-featured-ads .swiper-slide > a {
                    display: flex !important;
                    flex-direction: column !important;
                    height: 100% !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    min-width: 0 !important;
                }
                .swiper-featured-ads .card-premium {
                    height: 100% !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    min-width: 0 !important;
                    display: flex !important;
                    flex-direction: column !important;
                    box-sizing: border-box !important;
                }
                .swiper-featured-ads .card-body {
                    flex: 1 1 auto !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    min-width: 0 !important;
                }
            </style>
            <div class="position-relative overflow-hidden px-md-3">
                <div class="swiper swiper-featured-ads swiper-marquee-esteira rounded-3 p-1">
                    <div class="swiper-wrapper">
                        @php
                            $loopAds = count($featuredForYou) < 8 ? $featuredForYou->concat($featuredForYou) : $featuredForYou;
                        @endphp
                        @foreach($loopAds as $ad)
                        <div class="swiper-slide">
                            <a href="{{ $ad->module === 'services' ? route('provider.show', $ad->slug) : route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                                <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                                    <span class="badge bg-success position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">
                                        {{ $ad->module === 'services' && $ad->is_plan_featured ? 'Prestador pago' : 'Mais procurado' }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                        <i class="fa-regular fa-bookmark text-primary"></i>
                                    </button>
                                    @if($ad->card_image)
                                        <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                            <i class="fa-solid fa-tag fs-2"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title home-clickable-name text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                        </div>
                                        <div>
                                            <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->formatted_price }}</strong>
                                            <small class="text-muted text-truncate d-block" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 start-0 translate-middle-y z-3 swiper-featured-prev d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Anterior">
                    <i class="fa-solid fa-chevron-left text-dark small"></i>
                </button>
                <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 end-0 translate-middle-y z-3 swiper-featured-next d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Próximo">
                    <i class="fa-solid fa-chevron-right text-dark small"></i>
                </button>
            </div>
        </div>

        <!-- Coluna Direita: Profissionais em destaque -->
        <div class="col-12 col-lg-4 home-provider-desktop-column">
            <div class="d-flex justify-content-between align-items-center mb-2.5">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                    <i class="fa-solid fa-briefcase text-primary"></i> Prestadores de Serviços — Em destaque
                </h4>
                <a href="{{ route('module.services') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver todos <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="position-relative swiper-providers-container pt-1 pb-1" style="overflow: visible;">
                <div class="swiper swiper-providers h-100">
                    <div class="swiper-wrapper">
                        @foreach($serviceProviders as $provider)
                        <div class="swiper-slide swiper-no-swiping">
                            <div class="p-1.5 pe-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between border" style="background: var(--card); height: 68px;">
                                <a href="{{ route('provider.show', $provider->slug) }}" class="d-flex align-items-center gap-3 text-decoration-none text-dark flex-grow-1 overflow-hidden me-2 h-100">
                                    @if($provider->card_image)
                                        <img src="{{ asset($provider->card_image) }}" class="rounded-3 flex-shrink-0 shadow-sm border-0" width="56" height="56" style="width: 56px; height: 56px; object-fit: cover; border-radius: 12px !important;" alt="{{ $provider->title }}">
                                    @else
                                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm fw-bold flex-shrink-0 border-0" style="width: 56px; height: 56px; border-radius: 12px !important;">
                                            <i class="fa-solid fa-user fs-3"></i>
                                        </div>
                                    @endif
                                    <div class="overflow-hidden my-auto ms-1">
                                        <h6 class="home-clickable-name mb-0 text-truncate" style="font-size: 0.85rem; line-height: 1.2;">
                                            {{ $provider->title }}
                                            @if($provider->is_plan_featured)
                                                <i class="fa-solid fa-star text-warning ms-1" title="Destaque do plano pago" aria-label="Destaque do plano pago"></i>
                                            @endif
                                        </h6>
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">{{ $provider->display_category ?? 'Serviço profissional' }}</small>
                                        <small class="text-warning fw-bold" style="font-size: 0.7rem;">⭐ 4,9 (128) <span class="text-muted ms-1"><i class="fa-solid fa-location-dot"></i> {{ $provider->city ?? 'Aracaju, SE' }}</span></small>
                                    </div>
                                </a>
                                <div class="d-flex gap-2 flex-shrink-0 my-auto ms-1">
                                    <a href="https://wa.me/5579999999999" target="_blank" class="btn btn-success btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 30px; height: 30px;" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                                    <a href="{{ route('provider.show', $provider->slug) }}" class="btn btn-primary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 30px; height: 30px;" title="Ligar"><i class="fa-solid fa-phone"></i></a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <!-- Navigation Arrows -->
                <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-0 start-50 translate-middle-x z-3 swiper-providers-prev d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #fff; border: 1px solid rgba(0,0,0,0.12); margin-top: -10px;" aria-label="Subir">
                    <i class="fa-solid fa-chevron-up text-dark" style="font-size: 0.72rem;"></i>
                </button>
                <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute bottom-0 start-50 translate-middle-x z-3 swiper-providers-next d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #fff; border: 1px solid rgba(0,0,0,0.12); margin-bottom: -10px;" aria-label="Descer">
                    <i class="fa-solid fa-chevron-down text-dark" style="font-size: 0.72rem;"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@auth
<section class="home-auth-mobile-providers" aria-labelledby="mobile-provider-highlights-title">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 id="mobile-provider-highlights-title" class="h5 fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-toolbox text-primary"></i>
            Prestadores de Serviços em destaque
        </h2>
        <a href="{{ route('module.services') }}" class="text-primary text-decoration-none small fw-bold text-nowrap">
            Ver todos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="home-auth-provider-list">
        @foreach($serviceProviders->take(4) as $provider)
            @php
                $providerPhone = preg_replace('/\D+/', '', $provider->publicWhatsapp() ?? '');
                $providerPhone = $providerPhone && !str_starts_with($providerPhone, '55') ? '55'.$providerPhone : $providerPhone;
                $providerMessage = urlencode("Olá, encontrei seu perfil profissional no Conectado em Sergipe: {$provider->title}");
            @endphp
            <article class="home-auth-provider-card">
                <a href="{{ route('provider.show', $provider->slug) }}" class="text-decoration-none">
                    @if($provider->card_image)
                        <img src="{{ asset($provider->card_image) }}" class="home-auth-provider-photo" alt="{{ $provider->title }}">
                    @else
                        <span class="home-auth-provider-photo-placeholder" aria-hidden="true"><i class="fa-solid fa-user-tie"></i></span>
                    @endif
                </a>
                <a href="{{ route('provider.show', $provider->slug) }}" class="home-auth-provider-copy text-decoration-none">
                    <strong>{{ $provider->title }}</strong>
                    <span>{{ $provider->display_category ?? 'Serviço profissional' }}</span>
                    <small><i class="fa-solid fa-star"></i> 4,9 (128) <em><i class="fa-solid fa-location-dot text-danger"></i> {{ $provider->city ?? 'Aracaju' }}</em></small>
                </a>
                <div class="home-auth-provider-actions" aria-label="Ações de {{ $provider->title }}">
                    @if($providerPhone)
                        <a href="https://wa.me/{{ $providerPhone }}?text={{ $providerMessage }}" target="_blank" rel="noopener" class="is-whatsapp" title="Conversar pelo WhatsApp" aria-label="WhatsApp de {{ $provider->title }}"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="tel:+{{ $providerPhone }}" class="is-profile" title="Ligar" aria-label="Ligar para {{ $provider->title }}"><i class="fa-solid fa-phone"></i></a>
                    @endif
                    <a href="{{ route('provider.show', $provider->slug) }}" class="is-profile" title="Ver perfil" aria-label="Ver perfil de {{ $provider->title }}"><i class="fa-solid fa-circle-info"></i></a>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endauth

@if($recentStores->isNotEmpty())
<section class="container mb-3 mb-md-4" aria-labelledby="home-stores-title">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 id="home-stores-title" class="h4 fw-bold mb-0">Lojas perto de você</h2>
        <a href="{{ route('stores.index') }}" class="text-primary text-decoration-none small fw-bold">Ver todas</a>
    </div>
    <div class="row g-3">
        @foreach($recentStores as $recentStore)
            <div class="col-6 col-lg-3">
                <a href="{{ route('store.show', $recentStore->slug) }}" class="card h-100 rounded-4 p-3 text-decoration-none">
                    <strong class="home-clickable-name">{{ $recentStore->name }}</strong>
                    <small class="text-muted">{{ $recentStore->city ?: $recentStore->user?->city }}</small>
                    <small class="text-muted">{{ $recentStore->active_ads_count }} {{ $recentStore->active_ads_count === 1 ? 'produto' : 'produtos' }}</small>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif

<!-- Section 2: 🏠 Imóveis em Sergipe -->
<div class="container mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center mb-2.5">
        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
            <i class="fa-solid fa-house text-primary"></i> Imóveis em Sergipe
        </h4>
        <a href="{{ route('module.real_estate') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todos os imóveis <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="position-relative overflow-hidden px-md-3">
        <div class="swiper swiper-category-ads rounded-3 p-1">
            <div class="swiper-wrapper">
                @php
                    $displayRealEstate = count($realEstateAds) ? $realEstateAds : $recentAds->where('module', 'real_estate')->take(6);
                @endphp
                @foreach($displayRealEstate as $ad)
                <div class="swiper-slide">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                            <span class="badge bg-primary position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Imóvel</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                <i class="fa-regular fa-bookmark text-primary"></i>
                            </button>
                            @if($ad->card_image)
                                <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                            @else
                                <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                    <i class="fa-solid fa-house fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title home-clickable-name text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                </div>
                                <div>
                                    <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->formatted_price }}</strong>
                                    <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 start-0 translate-middle-y z-3 swiper-cat-prev d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left text-dark small"></i>
        </button>
        <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 end-0 translate-middle-y z-3 swiper-cat-next d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Próximo">
            <i class="fa-solid fa-chevron-right text-dark small"></i>
        </button>
    </div>
</div>

<!-- Section 3: 🚗 Veículos em Destaque -->
<div class="container mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center mb-2.5">
        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
            <i class="fa-solid fa-car text-primary"></i> Veículos em Destaque
        </h4>
        <a href="{{ route('module.vehicles') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todos os veículos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="position-relative overflow-hidden px-md-3">
        <div class="swiper swiper-category-ads rounded-3 p-1">
            <div class="swiper-wrapper">
                @php
                    $displayVehicles = count($vehicleAds) ? $vehicleAds : $recentAds->where('module', 'vehicles')->take(6);
                @endphp
                @foreach($displayVehicles as $ad)
                <div class="swiper-slide">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                            <span class="badge bg-info text-dark position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Veículo</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                <i class="fa-regular fa-bookmark text-primary"></i>
                            </button>
                            @if($ad->card_image)
                                <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                            @else
                                <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                    <i class="fa-solid fa-car fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title home-clickable-name text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                </div>
                                <div>
                                    <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->formatted_price }}</strong>
                                    <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 start-0 translate-middle-y z-3 swiper-cat-prev d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left text-dark small"></i>
        </button>
        <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 end-0 translate-middle-y z-3 swiper-cat-next d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Próximo">
            <i class="fa-solid fa-chevron-right text-dark small"></i>
        </button>
    </div>
</div>

<!-- Section 4: 🏷️ Produtos & Eletrônicos -->
<div class="container mb-3 mb-md-4">
    <div class="d-flex justify-content-between align-items-center mb-2.5">
        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
            <i class="fa-solid fa-tag text-primary"></i> Produtos & Eletrônicos
        </h4>
        <a href="{{ route('module.products') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todos os produtos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="position-relative overflow-hidden px-md-3">
        <div class="swiper swiper-category-ads rounded-3 p-1">
            <div class="swiper-wrapper">
                @php
                    $displayProducts = count($productAds) ? $productAds : $recentAds->where('module', 'products')->take(6);
                @endphp
                @foreach($displayProducts as $ad)
                <div class="swiper-slide">
                    <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                        <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">Produto</span>
                            <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                <i class="fa-regular fa-bookmark text-primary"></i>
                            </button>
                            @if($ad->card_image)
                                <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                            @else
                                <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                    <i class="fa-solid fa-tag fs-2"></i>
                                </div>
                            @endif
                            <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title home-clickable-name text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                </div>
                                <div>
                                    <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->formatted_price }}</strong>
                                    <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 start-0 translate-middle-y z-3 swiper-cat-prev d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Anterior">
            <i class="fa-solid fa-chevron-left text-dark small"></i>
        </button>
        <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 end-0 translate-middle-y z-3 swiper-cat-next d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Próximo">
            <i class="fa-solid fa-chevron-right text-dark small"></i>
        </button>
    </div>
</div>

<!-- Section 5: 💼 Empregos & Agronegócio + Bloco de Planos -->
<div class="container mb-3 mb-md-4">
    <div class="row g-3 g-md-4">
        <!-- Esquerda: Empregos e Agro em Swiper -->
        <div class="col-12 col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2.5">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.25rem;">
                    <i class="fa-solid fa-briefcase text-primary"></i> Empregos & Agro
                </h4>
                <a href="{{ route('module.jobs') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver oportunidades <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="position-relative overflow-hidden px-md-3">
                <div class="swiper swiper-category-ads rounded-3 p-1">
                    <div class="swiper-wrapper">
                        @php
                            $displayJobAgro = count($jobAgroAds) ? $jobAgroAds : $recentAds->whereIn('module', ['jobs', 'agro'])->take(6);
                        @endphp
                        @foreach($displayJobAgro as $ad)
                        <div class="swiper-slide">
                            <a href="{{ route('ad.show', $ad->slug) }}" class="text-decoration-none text-dark">
                                <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">{{ strtoupper($ad->module) }}</span>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 z-1 p-1 text-muted shadow-sm" aria-label="Favoritar" title="Salvar Anúncio">
                                        <i class="fa-regular fa-bookmark text-primary"></i>
                                    </button>
                                    @if($ad->card_image)
                                        <img src="{{ asset($ad->card_image) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 120px; object-fit: cover;">
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-light text-muted" style="height: 120px;">
                                            <i class="fa-solid fa-briefcase fs-2"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title home-clickable-name text-truncate mb-1" style="font-size: 0.82rem;">{{ $ad->title }}</h6>
                                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ \Illuminate\Support\Str::limit($ad->description, 28) }}</small>
                                        </div>
                                        <div>
                                            <strong class="text-primary fs-6 d-block" style="font-size: 0.9rem !important;">{{ $ad->formatted_price }}</strong>
                                            <small class="text-muted" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot me-1"></i>{{ $ad->city ?? 'Aracaju, SE' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 start-0 translate-middle-y z-3 swiper-cat-prev d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Anterior">
                    <i class="fa-solid fa-chevron-left text-dark small"></i>
                </button>
                <button type="button" class="btn btn-white btn-sm rounded-circle shadow position-absolute top-50 end-0 translate-middle-y z-3 swiper-cat-next d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; background: #fff; border: 1px solid rgba(0,0,0,0.12);" aria-label="Próximo">
                    <i class="fa-solid fa-chevron-right text-dark small"></i>
                </button>
            </div>
        </div>

        <!-- Direita: Card Quer Anunciar (Planos de Anúncio) -->
        <style>
            .card-planos-cta {
                background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%);
                border: 1px solid rgba(13, 110, 253, 0.2) !important;
            }
            .card-planos-cta h5 {
                color: #212529 !important;
            }
            [data-bs-theme="dark"] .card-planos-cta {
                background: linear-gradient(135deg, rgba(13,110,253,0.1) 0%, rgba(25,135,84,0.05) 100%);
                border: 1px solid rgba(13, 110, 253, 0.3) !important;
            }
            [data-bs-theme="dark"] .card-planos-cta h5 {
                color: #f8f9fa !important;
            }
        </style>
        <div class="col-12 col-lg-4">
            <div class="p-4 rounded-4 shadow-sm border h-100 d-flex flex-column justify-content-between card-planos-cta">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-1 rounded-pill small fw-bold">Planos de Anúncio</span>
                    <h5 class="fw-bold mb-2">Quer anunciar sua empresa ou produto?</h5>
                    <p class="text-muted small mb-4">Escolha o plano ideal para você e alcance milhares de clientes em todo o estado de Sergipe.</p>
                    <a href="{{ route('page.plans') }}" class="btn btn-primary fw-bold w-100 rounded-3 py-2 mb-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-gem"></i> Conheça nossos Planos
                    </a>
                </div>
                <div class="d-flex flex-column gap-2 small text-secondary border-top pt-3 border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> <strong>Planos Prata, Ouro e Diamante</strong></div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Destaque no topo das buscas</div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Botão direto de WhatsApp e Ligação</div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Suporte prioritário dedicado</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grupos locais por cidade -->
@auth
@if($homeCityGroups->isNotEmpty())
<section class="container mb-3 mb-md-4" aria-labelledby="home-city-groups-title">
    <div class="home-city-groups">
        <header class="home-city-groups-header">
            <div class="home-city-groups-pin" aria-hidden="true"><i class="fa-solid fa-map-location-dot"></i></div>
            <div>
                <h2 class="home-city-groups-title" id="home-city-groups-title">Nossos grupos em Sergipe por cidade</h2>
                <p class="home-city-groups-subtitle">Entre no grupo da sua cidade e fique conectado com oportunidades locais.</p>
            </div>
        </header>

        <div class="home-city-groups-rail" aria-label="Grupos disponíveis por cidade">
            @foreach($homeCityGroups as $group)
                @php
                    $groupCover = str_starts_with($group['cover'], 'http') ? $group['cover'] : asset($group['cover']);
                    $externalGroupLink = str_starts_with($group['link'], 'http');
                @endphp
                <article class="home-city-group-card {{ $group['enabled'] ? 'is-active' : 'is-inactive' }}" data-city-group-slot="{{ $group['slot'] }}" data-city-group-active="{{ $group['enabled'] ? 'true' : 'false' }}" style="--city-group-background: url('{{ $groupCover }}');">
                    <div class="home-city-group-cover">
                        <img src="{{ $groupCover }}" alt="Capa do grupo de {{ $group['city'] }}" loading="lazy">
                    </div>
                    <h3 class="home-city-group-name">{{ $group['city'] }}</h3>
                    <p class="home-city-group-type">Grupo local</p>
                    <div class="home-city-group-status"><i class="fa-solid {{ $group['enabled'] ? 'fa-user-group' : 'fa-circle-pause' }}" aria-hidden="true"></i> {{ $group['enabled'] ? 'Grupo disponível' : 'Não ativo' }}</div>
                    @if($group['enabled'])
                        <a class="home-city-group-enter" href="{{ $group['link'] }}" data-city-group-enter data-group-city="{{ $group['city'] }}" data-group-gentilic="{{ $group['gentilic'] }}" @if($externalGroupLink) target="_blank" rel="noopener noreferrer" @endif>Entrar no grupo</a>
                    @else
                        <span class="home-city-group-enter is-disabled" aria-disabled="true">Indisponível</span>
                    @endif
                </article>
            @endforeach
        </div>

        <p class="home-city-groups-note">
            <i class="fa-regular fa-bell" aria-hidden="true"></i>
            <span>Grupos disponíveis no momento. O site atende todo o estado; os grupos são comunidades locais complementares.</span>
        </p>
    </div>
</section>

<div class="home-city-group-confirmation" data-city-group-confirmation hidden>
    <section class="home-city-group-confirmation-dialog" role="dialog" aria-modal="true" aria-labelledby="city-group-confirmation-title">
        <div class="home-city-group-confirmation-icon" aria-hidden="true"><i class="fa-brands fa-whatsapp"></i></div>
        <h3 class="home-city-group-confirmation-title" id="city-group-confirmation-title">Você realmente é <span data-group-confirmation-gentilic></span>?</h3>
        <p class="home-city-group-confirmation-text">Este grupo é destinado às pessoas de <strong data-group-confirmation-city></strong>.</p>
        <p class="home-city-group-confirmation-text">Caso não more nessa cidade, não entre apenas por curiosidade. Se ainda quiser participar, informe no grupo o seu intuito: acompanhar novidades, visitar parentes ou porque está de mudança para a cidade.</p>
        <div class="home-city-group-confirmation-actions">
            <a class="home-city-group-confirmation-join" href="#" data-group-confirmation-join>Participar</a>
            <button class="home-city-group-confirmation-close" type="button" data-group-confirmation-close>Fechar</button>
        </div>
    </section>
</div>
@endif
@endauth

<!-- Section Banner do Aplicativo -->
<div class="container mb-3 mb-md-4">
    <div class="rounded-4 p-4 p-md-5 text-white position-relative overflow-hidden shadow-lg" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border: 1px solid rgba(255, 255, 255, 0.15);">
        <div class="row align-items-center">
            <div class="col-12 col-md-7 mb-4 mb-md-0 z-1">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary bg-opacity-25 p-2 rounded-3 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fa-solid fa-mobile-screen-button fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-white" style="font-size: clamp(1.2rem, 3vw, 1.8rem);">Leve o Conectado em Sergipe com você.</h3>
                </div>
                <p class="text-light opacity-75 mb-4" style="max-width: 500px;">Baixe nosso app e encontre oportunidades onde estiver.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#" class="btn btn-dark btn-lg border border-secondary border-opacity-50 rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                        <i class="fa-brands fa-google-play fs-3 text-success"></i>
                        <div class="text-start lh-1">
                            <small class="d-block text-muted" style="font-size: 0.65rem;">DISPONÍVEL NO</small>
                            <strong class="text-white small">Google Play</strong>
                        </div>
                    </a>
                    <a href="#" class="btn btn-dark btn-lg border border-secondary border-opacity-50 rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                        <i class="fa-brands fa-apple fs-3 text-white"></i>
                        <div class="text-start lh-1">
                            <small class="d-block text-muted" style="font-size: 0.65rem;">BAIXAR NA</small>
                            <strong class="text-white small">App Store</strong>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-5 text-center d-none d-md-block z-1">
                <div class="position-relative d-inline-block">
                    <i class="fa-solid fa-mobile-retro text-primary opacity-50" style="font-size: 10rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Resultados da Busca / Categoria -->
@if($isSearch)
<div class="container mt-4 mb-5" id="resultados-busca">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">
                        @if($hasActiveFilters)
                            <i class="fa-solid fa-magnifying-glass text-primary me-2"></i>
                            Resultados da Pesquisa
                        @else
                            <i class="fa-solid {{ $module === 'real_estate' ? 'fa-house' : ($module === 'vehicles' ? 'fa-car' : 'fa-list-ul') }} text-primary me-2"></i>
                            {{ $moduleTitle ? $moduleTitle . ' em Sergipe' : 'Todos os Anúncios' }}
                        @endif
                    </h3>
                    <p class="text-muted mb-0">
                        @if($hasActiveFilters)
                            Encontrados <strong>{{ count($searchResults) }}</strong> resultado(s) {{ !empty($city) ? 'em ' . $city : 'no estado de Sergipe' }}
                        @else
                            Mostrando <strong>{{ count($searchResults) }}</strong> {{ $moduleTitle ? strtolower($moduleTitle) : 'anúncio(s)' }} disponível(eis) {{ !empty($city) ? 'em ' . $city : 'no estado de Sergipe' }}
                        @endif
                    </p>
                </div>
                @if($hasActiveFilters)
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-xmark me-1"></i> Limpar Filtros</a>
                @endif
            </div>

            @if(count($searchResults) === 0)
                <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4">
                    <i class="fa-solid fa-folder-open text-muted fs-1 mb-3"></i>
                    <h5 class="fw-bold text-dark">Nenhum anúncio encontrado</h5>
                    <p class="text-muted small">Tente buscar por outras palavras-chave ou alterar a cidade selecionada.</p>
                </div>
            @else
                <div class="row g-3 g-md-4">
                    @foreach($searchResults as $item)
                    <div class="col-6 col-md-6 col-lg-3">
                        <a href="{{ $item->module === 'services' ? route('provider.show', $item->slug) : route('ad.show', $item->slug) }}" class="text-decoration-none text-dark">
                            <div class="card card-premium h-100 border-0 rounded-4 shadow-sm overflow-hidden">
                                <div class="card-img-wrapper position-relative">
                                    @if($item->card_image)
                                        <img src="{{ asset($item->card_image) }}" class="card-img-top" alt="{{ $item->title }}" style="height: 160px; object-fit: cover;">
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-dark bg-opacity-25 text-muted" style="height: 160px;">
                                            <i class="fa-solid {{ $item->module === 'real_estate' ? 'fa-house' : ($item->module === 'vehicles' ? 'fa-car' : 'fa-tag') }} fs-1 text-primary"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        @php
                                            $moduleBadges = [
                                                'services' => 'PERFIL PROFISSIONAL',
                                                'real_estate' => 'IMÓVEIS',
                                                'vehicles' => 'VEÍCULOS',
                                                'products' => 'PRODUTOS',
                                                'jobs' => 'EMPREGOS',
                                                'agro' => 'AGRO',
                                            ];
                                        @endphp
                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-2 py-1 rounded-pill small fw-semibold">
                                            {{ $moduleBadges[$item->module] ?? strtoupper($item->module) }}
                                        </span>
                                        <h5 class="card-title home-clickable-name fs-6 text-truncate mb-1">{{ $item->title }}</h5>
                                        <p class="card-text text-muted small text-truncate">{{ $item->city }}</p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        @if($item->module === 'services')
                                            <span class="fw-bold text-primary small">Ver perfil profissional</span>
                                        @else
                                            <span class="fw-bold text-primary fs-5">{{ $item->formatted_price }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endif

@auth
    @if(empty($module))
        <nav class="home-auth-mobile-bottom-nav" aria-label="Navegação principal mobile">
            <a href="{{ route('home') }}" class="is-active" aria-current="page"><i class="fa-solid fa-house"></i><span>Início</span></a>
            <a href="{{ route('module.services') }}"><i class="fa-solid fa-screwdriver-wrench"></i><span>Serviços</span></a>
            <a href="{{ route('ad.create') }}" class="is-primary"><i class="fa-solid fa-plus"></i><span>Anunciar</span></a>
            <a href="{{ route('stores.index') }}"><i class="fa-solid fa-store"></i><span>Lojas</span></a>
            <a href="{{ route('user.panel') }}"><i class="fa-regular fa-user"></i><span>Conta</span></a>
        </nav>
    @endif
@endauth

</div>
@endsection

@push('scripts')
<script>
    @if(empty($module))
    const swiperHero = new Swiper('.swiper-hero', {
        loop: true,
        speed: 1400,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        autoplay: {
            delay: 8000,
            disableOnInteraction: false,
        },
        allowTouchMove: true,
        navigation: { nextEl: '.home-hero-next', prevEl: '.home-hero-prev' },
        on: {
            init: function () {
                // Sinaliza para a splash screen que o Swiper Hero está pronto
                window.dispatchEvent(new CustomEvent('swiper-hero-ready'));
            }
        }
    });

    const swiperProviders = new Swiper('.swiper-providers', {
        direction: 'vertical',
        slidesPerView: 4,
        spaceBetween: document.body.classList.contains('home-guest') ? 3 : 4,
        speed: 400,
        loop: true,
        autoplay: {
            delay: 7500,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        mousewheel: {
            forceToAxis: true,
            releaseOnEdges: true,
        },
        allowTouchMove: true,
        navigation: {
            prevEl: '.swiper-providers-prev',
            nextEl: '.swiper-providers-next',
        }
    });

    const prevBtn = document.querySelector('.swiper-providers-prev');
    const nextBtn = document.querySelector('.swiper-providers-next');

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            swiperProviders.slidePrev();
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            swiperProviders.slideNext();
        });
    }

    const providersContainer = document.querySelector('.swiper-providers-container');
    if (providersContainer) {
        providersContainer.addEventListener('wheel', (e) => {
            e.preventDefault();
            if (e.deltaY > 0) {
                swiperProviders.slideNext();
            } else if (e.deltaY < 0) {
                swiperProviders.slidePrev();
            }
        }, { passive: false });
    }

    const swiperFeatured = new Swiper('.swiper-featured-ads', {
        slidesPerView: 2.5,
        spaceBetween: 10,
        loop: true,
        speed: 650,
        autoplay: {
            delay: 4500,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        navigation: {
            prevEl: '.swiper-featured-prev',
            nextEl: '.swiper-featured-next',
        },
        breakpoints: {
            576: { slidesPerView: 2, spaceBetween: 12 },
            768: { slidesPerView: 3, spaceBetween: 14 },
            992: { slidesPerView: 3, spaceBetween: 14 },
            1200: { slidesPerView: 4, spaceBetween: 14 },
        },
    });

    document.querySelectorAll('.swiper-category-ads').forEach((el) => {
        const parent = el.parentElement;
        const prevBtn = parent ? parent.querySelector('.swiper-cat-prev') : null;
        const nextBtn = parent ? parent.querySelector('.swiper-cat-next') : null;
        const compact = el.classList.contains('swiper-category-compact');

        new Swiper(el, {
            slidesPerView: 2,
            spaceBetween: 10,
            speed: 800,
            loop: true,
            autoplay: {
                delay: 7500,
                disableOnInteraction: false,
            },
            navigation: (prevBtn && nextBtn) ? { prevEl: prevBtn, nextEl: nextBtn } : false,
            breakpoints: {
                576: { slidesPerView: 2, spaceBetween: 12 },
                768: { slidesPerView: 3, spaceBetween: 14 },
                992: { slidesPerView: compact ? 3 : 4, spaceBetween: 14 },
                1200: { slidesPerView: compact ? 3 : 5, spaceBetween: 14 },
            },
        });
    });

    const heroPlansCard = document.getElementById('home-hero-plans-card');
    const heroPlansClose = document.querySelector('[data-close-home-plans]');
    heroPlansClose?.addEventListener('click', () => {
        if (heroPlansCard) heroPlansCard.hidden = true;
    });
    @endif

    @if($module === 'real_estate')
    const swiperRealEstateHero = new Swiper('.swiper-real-estate-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.real-estate-swiper-next', prevEl: '.real-estate-swiper-prev' }
    });
    @endif

    @if($module === 'vehicles')
    const swiperVehiclesHero = new Swiper('.swiper-vehicles-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.vehicles-swiper-next', prevEl: '.vehicles-swiper-prev' }
    });
    @endif

    @if($module === 'products')
    const swiperProductsHero = new Swiper('.swiper-products-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.products-swiper-next', prevEl: '.products-swiper-prev' }
    });
    @endif

    @if($module === 'jobs')
    const swiperJobsHero = new Swiper('.swiper-jobs-hero', {
        loop: true,
        autoplay: { delay: 5000 },
        navigation: { nextEl: '.jobs-swiper-next', prevEl: '.jobs-swiper-prev' }
    });
    @endif

    (() => {
        const storageKey = 'conectado-search-filter';
        const searchForm = document.getElementById('home-search-form');
        const categoryFilter = document.getElementById('home-search-category-filter');
        const moduleValue = document.getElementById('home-search-module-value');
        const serviceCategoryValue = document.getElementById('home-search-service-category-value');
        const queryInput = document.getElementById('home-search-query');
        const citySelect = document.getElementById('home-search-city');
        const microphoneButton = document.getElementById('home-search-microphone');
        const voiceStatus = document.getElementById('home-voice-status');
        const suggestionsBox = document.getElementById('home-search-suggestions');
        const locationButton = document.getElementById('home-use-location');
        const locationStatus = document.getElementById('home-location-status');
        const locationButtonLabel = locationButton?.querySelector('[data-location-button-label]');
        const locationButtonDetail = locationButton?.querySelector('[data-location-button-detail]');
        const locationStoreEndpoint = @json(route('location.store'));
        const locationDestroyEndpoint = @json(route('location.destroy'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        let locationPreferenceEnabled = @json((bool) session('location_filter.enabled', false));
        const municipalityCoordinates = @json(\App\Core\SergipeCities::getCoordinates());
        const quickNav = document.querySelector('.quick-search-model-one-nav');
        const quickNavPages = quickNav
            ? Array.from(quickNav.querySelectorAll('[data-quick-nav-page]'))
            : [];

        if (
            !searchForm
            || !categoryFilter
            || !moduleValue
            || !serviceCategoryValue
            || !queryInput
            || !citySelect
            || !microphoneButton
            || !voiceStatus
            || !suggestionsBox
            || !locationButton
            || !locationStatus
        ) {
            return;
        }

        const smartSearchSetting = searchForm.dataset.smartSearch;
        let smartSearchEnabled = smartSearchSetting === '1';

        if (smartSearchSetting === 'guest') {
            try {
                smartSearchEnabled = localStorage.getItem('conectado-smart-search-enabled') !== '0';
            } catch (error) {
                smartSearchEnabled = true;
            }
        }

        const readSavedFilter = () => {
            try {
                return localStorage.getItem(storageKey);
            } catch (error) {
                return null;
            }
        };

        const saveFilter = (value) => {
            try {
                localStorage.setItem(storageKey, value);
            } catch (error) {
                // A busca continua funcionando se o navegador bloquear o armazenamento.
            }
        };

        const syncFilterValues = () => {
            const selectedValue = categoryFilter.value;
            moduleValue.value = '';
            serviceCategoryValue.value = '';

            if (selectedValue.startsWith('module:')) {
                moduleValue.value = selectedValue.slice('module:'.length);
            } else if (selectedValue.startsWith('service:')) {
                moduleValue.value = 'services';
                serviceCategoryValue.value = selectedValue.slice('service:'.length);
            }
        };

        const currentModule = moduleValue.value;
        const savedFilter = readSavedFilter();

        if (!currentModule && savedFilter && categoryFilter.querySelector(`option[value="${CSS.escape(savedFilter)}"]`)) {
            categoryFilter.value = savedFilter;
        }

        syncFilterValues();

        let automaticSearchTimer = null;
        const automaticSearchDelay = 20000;
        const scheduleAutomaticSearch = () => {
            if (!smartSearchEnabled) {
                return;
            }

            if (automaticSearchTimer) {
                window.clearTimeout(automaticSearchTimer);
            }

            automaticSearchTimer = window.setTimeout(() => {
                syncFilterValues();
                searchForm.requestSubmit();
            }, automaticSearchDelay);
        };

        categoryFilter.addEventListener('change', () => {
            syncFilterValues();
            saveFilter(categoryFilter.value);
            scheduleAutomaticSearch();
        });

        let applyingDetectedCity = false;

        const updateSearchPlaceholder = () => {
            const selectedCity = citySelect.value.trim();
            queryInput.placeholder = `O que você procura em ${selectedCity || 'Sergipe'}?`;
        };

        updateSearchPlaceholder();

        citySelect.addEventListener('change', () => {
            updateSearchPlaceholder();

            if (!applyingDetectedCity) {
                if (locationPreferenceEnabled) {
                    fetch(locationDestroyEndpoint, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    }).catch(() => null);
                    locationPreferenceEnabled = false;
                }

                try {
                    localStorage.removeItem('conectado-location-enabled');
                    localStorage.removeItem('conectado-detected-city');
                } catch (error) {
                    // A seleção manual continua funcionando sem armazenamento.
                }

                locationStatus.textContent = '';
                locationStatus.className = 'quick-search-location-status';
            }

            scheduleAutomaticSearch();
        });

        searchForm.addEventListener('submit', () => {
            if (automaticSearchTimer) {
                window.clearTimeout(automaticSearchTimer);
            }

            syncFilterValues();
        });

        let suggestions = [];
        let activeSuggestionIndex = -1;
        let suggestionsTimer = null;
        let suggestionsRequest = null;

        const closeSuggestions = () => {
            suggestionsBox.hidden = true;
            suggestionsBox.replaceChildren();
            queryInput.setAttribute('aria-expanded', 'false');
            activeSuggestionIndex = -1;
        };

        const activateSuggestion = (index) => {
            const suggestionButtons = Array.from(suggestionsBox.querySelectorAll('.quick-search-suggestion'));

            if (!suggestionButtons.length) {
                return;
            }

            activeSuggestionIndex = (index + suggestionButtons.length) % suggestionButtons.length;
            suggestionButtons.forEach((button, buttonIndex) => {
                button.classList.toggle('is-active', buttonIndex === activeSuggestionIndex);
            });
            suggestionButtons[activeSuggestionIndex].scrollIntoView({ block: 'nearest' });
        };

        const openSuggestion = (suggestion) => {
            if (!suggestion?.url) {
                return;
            }

            queryInput.value = suggestion.label;
            window.location.assign(suggestion.url);
        };

        const renderSuggestions = () => {
            suggestionsBox.replaceChildren();
            activeSuggestionIndex = -1;

            if (!suggestions.length) {
                const emptyMessage = document.createElement('p');
                emptyMessage.className = 'quick-search-suggestions-empty';
                emptyMessage.textContent = 'Nenhum resultado próximo encontrado.';
                suggestionsBox.appendChild(emptyMessage);
            } else {
                suggestions.forEach((suggestion, index) => {
                    const button = document.createElement('button');
                    const icon = document.createElement('i');
                    const content = document.createElement('span');
                    const label = document.createElement('strong');
                    const meta = document.createElement('small');

                    button.type = 'button';
                    button.className = 'quick-search-suggestion';
                    button.setAttribute('role', 'option');
                    icon.className = 'fa-solid fa-magnifying-glass';
                    label.textContent = suggestion.label;
                    meta.textContent = suggestion.meta || 'Sugestão';
                    content.append(label, meta);
                    button.append(icon, content);
                    button.addEventListener('mouseenter', () => activateSuggestion(index));
                    button.addEventListener('click', () => openSuggestion(suggestion));
                    suggestionsBox.appendChild(button);
                });
            }

            suggestionsBox.hidden = false;
            queryInput.setAttribute('aria-expanded', 'true');
        };

        const loadSuggestions = () => {
            if (!smartSearchEnabled) {
                closeSuggestions();
                return;
            }

            const searchTerm = queryInput.value.trim();
            if (searchTerm.length < 2) {
                closeSuggestions();
                return;
            }

            if (suggestionsRequest) {
                suggestionsRequest.abort();
            }

            suggestionsRequest = new AbortController();
            const url = new URL(searchForm.dataset.suggestionsUrl, window.location.origin);
            url.searchParams.set('q', searchTerm);

            fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: suggestionsRequest.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Não foi possível carregar as sugestões.');
                    }

                    return response.json();
                })
                .then((data) => {
                    suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];
                    renderSuggestions();
                })
                .catch((error) => {
                    if (error.name !== 'AbortError') {
                        closeSuggestions();
                    }
                });
        };

        const scheduleSuggestions = () => {
            if (suggestionsTimer) {
                window.clearTimeout(suggestionsTimer);
            }

            suggestionsTimer = window.setTimeout(loadSuggestions, 250);
        };

        queryInput.addEventListener('focus', loadSuggestions);
        queryInput.addEventListener('input', scheduleSuggestions);
        queryInput.addEventListener('keydown', (event) => {
            if (suggestionsBox.hidden || !suggestions.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activateSuggestion(activeSuggestionIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activateSuggestion(activeSuggestionIndex - 1);
            } else if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                event.preventDefault();
                openSuggestion(suggestions[activeSuggestionIndex]);
            } else if (event.key === 'Escape') {
                closeSuggestions();
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.quick-search-model-one-query')) {
                closeSuggestions();
            }
        });

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const normalizeVoiceTerm = (value) => value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('pt-BR')
            .trim();
        const voiceServiceCategories = Array.from(categoryFilter.options)
            .filter((option) => option.value.startsWith('service:'))
            .map((option) => ({
                option,
                normalizedName: normalizeVoiceTerm(option.textContent),
            }))
            .sort((first, second) => second.normalizedName.length - first.normalizedName.length);
        const findSpokenServiceCategory = (transcript) => {
            const normalizedTranscript = normalizeVoiceTerm(transcript);

            return voiceServiceCategories.find(({ normalizedName }) => (
                normalizedTranscript === normalizedName
                || normalizedTranscript.includes(normalizedName)
            ))?.option ?? null;
        };
        let voiceStatusTimer = null;
        const setVoiceStatus = (message, state = '', autoHide = false) => {
            window.clearTimeout(voiceStatusTimer);
            voiceStatus.textContent = message;
            voiceStatus.className = 'quick-search-voice-status';
            voiceStatus.hidden = !message;

            if (state) {
                voiceStatus.classList.add(`is-${state}`);
            }

            if (message && autoHide) {
                voiceStatusTimer = window.setTimeout(() => {
                    voiceStatus.hidden = true;
                    voiceStatus.textContent = '';
                }, 5000);
            }
        };

        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            let recognitionRunning = false;
            let microphonePermissionConfirmed = false;
            recognition.lang = 'pt-BR';
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            microphoneButton.addEventListener('click', async () => {
                if (!window.isSecureContext) {
                    setVoiceStatus('A busca por voz precisa de uma conexão segura (HTTPS).', 'error');
                    return;
                }

                if (recognitionRunning) {
                    recognition.stop();
                    return;
                }

                closeSuggestions();

                try {
                    if (!microphonePermissionConfirmed && navigator.mediaDevices?.getUserMedia) {
                        setVoiceStatus('Aguardando permissão para usar o microfone...', 'loading');
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        stream.getTracks().forEach((track) => track.stop());
                        microphonePermissionConfirmed = true;
                    }

                    recognition.start();
                } catch (error) {
                    const permissionDenied = error?.name === 'NotAllowedError' || error?.name === 'SecurityError';
                    setVoiceStatus(
                        permissionDenied
                            ? 'O acesso ao microfone está bloqueado. Libere a permissão do site no navegador.'
                            : 'Não foi possível iniciar o microfone. Verifique se ele está disponível.',
                        'error'
                    );
                }
            });
            recognition.addEventListener('start', () => {
                recognitionRunning = true;
                microphoneButton.classList.add('is-listening');
                microphoneButton.setAttribute('aria-label', 'Ouvindo sua busca');
                microphoneButton.title = 'Ouvindo... toque novamente para parar.';
                setVoiceStatus('Ouvindo... diga o que deseja procurar.', 'listening');
            });
            recognition.addEventListener('end', () => {
                recognitionRunning = false;
                microphoneButton.classList.remove('is-listening');
                microphoneButton.setAttribute('aria-label', 'Buscar usando a voz');
                microphoneButton.title = 'Buscar usando a voz';
            });
            recognition.addEventListener('result', (event) => {
                const transcript = event.results[0][0].transcript.trim();

                if (transcript) {
                    const spokenCategory = findSpokenServiceCategory(transcript);

                    if (spokenCategory) {
                        categoryFilter.value = spokenCategory.value;
                        queryInput.value = spokenCategory.textContent.trim();
                        syncFilterValues();
                    } else {
                        queryInput.value = transcript;
                    }

                    closeSuggestions();
                    setVoiceStatus(
                        spokenCategory
                            ? `Categoria reconhecida: “${spokenCategory.textContent.trim()}”. Buscando profissionais...`
                            : `Entendi: “${transcript}”. Buscando...`,
                        'success'
                    );
                    window.setTimeout(() => searchForm.requestSubmit(), 400);
                }
            });
            recognition.addEventListener('error', (event) => {
                const messages = {
                    'not-allowed': 'Permita o acesso ao microfone nas configurações do navegador.',
                    'service-not-allowed': 'A busca por voz foi bloqueada pelo navegador.',
                    'audio-capture': 'Nenhum microfone disponível foi encontrado.',
                    'no-speech': 'Não ouvi nenhuma fala. Toque no microfone e tente novamente.',
                    'network': 'A busca por voz está sem conexão. Verifique a internet e tente novamente.',
                    'aborted': '',
                };
                const message = messages[event.error] ?? 'Não foi possível reconhecer sua voz. Tente novamente.';
                if (message) {
                    setVoiceStatus(message, 'error');
                }
            });
            recognition.addEventListener('nomatch', () => {
                setVoiceStatus('Não consegui entender a fala. Tente novamente mais perto do microfone.', 'error');
            });
        } else {
            microphoneButton.title = 'Busca por voz não disponível neste navegador.';
            microphoneButton.setAttribute('aria-label', microphoneButton.title);
            microphoneButton.addEventListener('click', () => {
                setVoiceStatus('Este navegador não oferece busca por voz. No celular, tente usar o Chrome atualizado.', 'error');
            });
        }

        const setLocationStatus = (message, state = '') => {
            locationStatus.textContent = message;
            locationStatus.className = 'quick-search-location-status';

            if (state) {
                locationStatus.classList.add(`is-${state}`);
            }
        };

        const openLocationSearch = () => {
            queryInput.value = '';
            categoryFilter.value = '';
            moduleValue.value = '';
            serviceCategoryValue.value = '';

            try {
                localStorage.removeItem(storageKey);
                localStorage.removeItem('conectado-location-enabled');
                localStorage.removeItem('conectado-detected-city');
            } catch (error) {
                // A localização continua funcionando sem armazenamento.
            }

            const destination = new URL(searchForm.action, window.location.origin);
            destination.search = '';
            window.location.assign(destination.toString());
        };

        const storeLocationPreference = async (city) => {
            const response = await fetch(locationStoreEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ city }),
            });

            if (!response.ok) {
                throw new Error('Não foi possível salvar a localização.');
            }

            locationPreferenceEnabled = true;
        };

        const disableLocationPreference = async () => {
            const response = await fetch(locationDestroyEndpoint, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error('Não foi possível desativar a localização.');
            }

            locationPreferenceEnabled = false;
            const destination = new URL(window.location.href);
            destination.searchParams.delete('city');
            window.location.assign(destination.toString());
        };

        const toRadians = (degrees) => degrees * (Math.PI / 180);

        const distanceInKilometers = (latitudeA, longitudeA, latitudeB, longitudeB) => {
            const earthRadius = 6371;
            const latitudeDistance = toRadians(latitudeB - latitudeA);
            const longitudeDistance = toRadians(longitudeB - longitudeA);
            const calculation = Math.sin(latitudeDistance / 2) ** 2
                + Math.cos(toRadians(latitudeA))
                * Math.cos(toRadians(latitudeB))
                * Math.sin(longitudeDistance / 2) ** 2;

            return 2 * earthRadius * Math.asin(Math.sqrt(calculation));
        };

        const findNearestMunicipality = (latitude, longitude) => {
            let nearest = null;

            Object.entries(municipalityCoordinates).forEach(([name, coordinates]) => {
                const distance = distanceInKilometers(
                    latitude,
                    longitude,
                    Number(coordinates.latitude),
                    Number(coordinates.longitude),
                );

                if (!nearest || distance < nearest.distance) {
                    nearest = { name, distance };
                }
            });

            return nearest;
        };

        const isInsideSergipeArea = (latitude, longitude) => (
            latitude >= -11.65
            && latitude <= -9.45
            && longitude >= -38.35
            && longitude <= -36.35
        );

        const locateCurrentCity = () => {
            if (!window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                setLocationStatus('A localização precisa de uma conexão segura (HTTPS).', 'error');
                return;
            }

            if (!navigator.geolocation) {
                setLocationStatus('Este navegador não oferece localização automática.', 'error');
                locationButton.disabled = true;
                return;
            }

            locationButton.disabled = true;
            locationButton.classList.add('is-loading');
            setLocationStatus('Identificando sua cidade...', 'loading');

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    if (!isInsideSergipeArea(latitude, longitude)) {
                        setLocationStatus('Sua localização parece estar fora de Sergipe. Escolha a cidade manualmente.', 'error');
                        locationButton.disabled = false;
                        locationButton.classList.remove('is-loading');
                        return;
                    }

                    const nearestMunicipality = findNearestMunicipality(latitude, longitude);
                    const matchingOption = nearestMunicipality
                        ? Array.from(citySelect.options).find((option) => option.value === nearestMunicipality.name)
                        : null;

                    if (!nearestMunicipality || !matchingOption) {
                        setLocationStatus('Não foi possível relacionar sua localização a uma cidade.', 'error');
                        locationButton.disabled = false;
                        locationButton.classList.remove('is-loading');
                        return;
                    }

                    applyingDetectedCity = true;
                    citySelect.value = nearestMunicipality.name;
                    updateSearchPlaceholder();
                    applyingDetectedCity = false;

                    try {
                        await storeLocationPreference(nearestMunicipality.name);
                        setLocationStatus(`Localização ativa: ${nearestMunicipality.name}.`, 'success');
                        locationButton.classList.add('is-active');
                        locationButton.setAttribute('aria-pressed', 'true');
                        if (locationButtonLabel) {
                            locationButtonLabel.textContent = 'Desativar localização';
                        }
                        if (locationButtonDetail) {
                            locationButtonDetail.textContent = `Resultados filtrados para ${nearestMunicipality.name}.`;
                        }
                        window.setTimeout(openLocationSearch, 450);
                    } catch (error) {
                        setLocationStatus(error.message, 'error');
                        locationButton.disabled = false;
                        locationButton.classList.remove('is-loading');
                    }
                },
                (error) => {
                    const messages = {
                        1: 'Permissão de localização negada. Você ainda pode escolher a cidade manualmente.',
                        2: 'Sua localização não está disponível agora. Tente novamente.',
                        3: 'A localização demorou demais para responder. Tente novamente.',
                    };

                    setLocationStatus(messages[error.code] || 'Não foi possível identificar sua localização.', 'error');
                    locationButton.disabled = false;
                    locationButton.classList.remove('is-loading');
                },
                {
                    enableHighAccuracy: false,
                    timeout: 12000,
                    maximumAge: 300000,
                },
            );
        };

        locationButton.addEventListener('click', async () => {
            if (locationPreferenceEnabled) {
                locationButton.disabled = true;
                setLocationStatus('Desativando localização...', 'loading');
                try {
                    await disableLocationPreference();
                } catch (error) {
                    setLocationStatus(error.message, 'error');
                    locationButton.disabled = false;
                }
                return;
            }

            locateCurrentCity();
        });

        if (quickNavPages.length) {
            let activeQuickNavPage = Math.floor(Math.random() * quickNavPages.length);
            const activeItemByPage = quickNavPages.map(() => 0);

            const activateQuickNavPage = (pageIndex, advanceItem = false) => {
                activeQuickNavPage = pageIndex % quickNavPages.length;

                quickNavPages.forEach((page, currentPageIndex) => {
                    const isCurrentPage = currentPageIndex === activeQuickNavPage;
                    const pageItems = Array.from(page.querySelectorAll('[data-quick-nav]'));

                    page.hidden = !isCurrentPage;
                    page.classList.toggle('is-active', isCurrentPage);

                    if (isCurrentPage && advanceItem && pageItems.length) {
                        activeItemByPage[currentPageIndex] = (activeItemByPage[currentPageIndex] + 1) % pageItems.length;
                    }

                    pageItems.forEach((item, itemIndex) => {
                        const isHighlighted = isCurrentPage && itemIndex === activeItemByPage[currentPageIndex];
                        item.classList.toggle('active', isHighlighted);

                        if (isHighlighted) {
                            item.setAttribute('aria-current', 'true');
                        } else {
                            item.removeAttribute('aria-current');
                        }
                    });
                });

                quickNav.setAttribute(
                    'aria-label',
                    quickNavPages[activeQuickNavPage].dataset.pageLabel || 'Acesso rápido às categorias'
                );
                quickNav.scrollTo({ left: 0, behavior: 'smooth' });
            };

            requestAnimationFrame(() => activateQuickNavPage(activeQuickNavPage));
            window.setInterval(() => {
                activateQuickNavPage(activeQuickNavPage + 1, true);
            }, 10000);
        }
    })();
</script>
@auth
<script>
    (() => {
        const confirmation = document.querySelector('[data-city-group-confirmation]');
        if (!confirmation) return;

        const gentilic = confirmation.querySelector('[data-group-confirmation-gentilic]');
        const city = confirmation.querySelector('[data-group-confirmation-city]');
        const join = confirmation.querySelector('[data-group-confirmation-join]');
        const close = confirmation.querySelector('[data-group-confirmation-close]');
        let trigger = null;

        const closeConfirmation = () => {
            confirmation.hidden = true;
            document.body.style.overflow = '';
            trigger?.focus();
        };

        document.querySelectorAll('[data-city-group-enter]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                trigger = button;
                gentilic.textContent = button.dataset.groupGentilic || `morador(a) de ${button.dataset.groupCity}`;
                city.textContent = button.dataset.groupCity || 'sua cidade';
                join.href = button.href;

                if (button.target === '_blank') {
                    join.target = '_blank';
                    join.rel = 'noopener noreferrer';
                } else {
                    join.removeAttribute('target');
                    join.removeAttribute('rel');
                }

                confirmation.hidden = false;
                document.body.style.overflow = 'hidden';
                close.focus();
            });
        });

        close.addEventListener('click', closeConfirmation);
        join.addEventListener('click', () => {
            confirmation.hidden = true;
            document.body.style.overflow = '';
        });
        confirmation.addEventListener('click', (event) => {
            if (event.target === confirmation) closeConfirmation();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !confirmation.hidden) closeConfirmation();
        });
    })();
</script>
@endauth
@endpush
