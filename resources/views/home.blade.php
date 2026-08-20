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
    .hero-search-card-container {
        margin-top: -145px;
    }
    .home-provider-heading-row {
        grid-template-columns: minmax(0, 1fr) auto;
        white-space: normal;
    }
    .home-content-section {
        margin-bottom: 1.75rem !important;
    }
    @media (min-width: 768px) {
        .home-content-section {
            margin-bottom: 2.25rem !important;
        }
    }
    @media (min-width: 1200px) {
        .home-content-section {
            margin-bottom: 2.75rem !important;
        }
    }
    /* ── Lojas: 5 colunas no desktop ── */
    @media (min-width: 992px) {
        .col-lg-home-store {
            flex: 0 0 auto;
            width: 20%;
        }
    }
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
            height: 190px !important;
            min-height: 190px !important;
            max-height: 190px !important;
        }
        .hero-slide-container-responsive {
            padding: 18px 16px 24px 16px !important;
            text-align: left !important;
            align-items: flex-start !important;
            justify-content: flex-start !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .hero-slide-container-responsive h1 {
            font-size: clamp(1.1rem, 4.6vw, 1.35rem) !important;
            font-weight: 800 !important;
            margin-bottom: 6px !important;
            line-height: 1.22 !important;
            text-align: left !important;
            max-width: 84% !important;
            color: #ffffff !important;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.75) !important;
        }
        .hero-slide-container-responsive p {
            max-width: 88% !important;
            font-size: clamp(0.75rem, 3.1vw, 0.84rem) !important;
            line-height: 1.3 !important;
            text-align: left !important;
            margin: 0 !important;
            opacity: 0.92 !important;
            color: rgba(255, 255, 255, 0.92) !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.75) !important;
        }
        .home-hero-navigation {
            width: 32px !important;
            height: 32px !important;
            font-size: 0.75rem !important;
            top: 48% !important;
            transform: translateY(-50%) !important;
            background: rgba(0, 0, 0, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 50% !important;
            opacity: 0.85;
        }
        .home-hero-prev { left: 8px !important; }
        .home-hero-next { right: 8px !important; }
        .home-hero-copy {
            max-width: 88% !important;
            text-align: left !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .home-hero-cta {
            display: none !important;
        }
        .hero-search-card-container,
        body.home-guest .hero-search-card-container,
        body.home-authenticated .hero-search-card-container {
            margin-top: -36px !important;
            margin-bottom: 10px !important;
            padding: 0 10px !important;
        }
        .home-search-panel {
            padding: 5px 6px !important;
            background: rgba(15, 23, 42, 0.94) !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, 0.16) !important;
        }
        #home-search-form {
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            gap: 6px !important;
            margin-bottom: 4px !important;
        }
        .hero-search-input-box {
            min-height: 34px !important;
            padding-top: 3px !important;
            padding-bottom: 3px !important;
        }
        .home-search-query-field {
            grid-column: 1 / 2 !important;
            grid-row: 1 / 2 !important;
            border-radius: 8px 0 0 8px !important;
            margin: 0 !important;
        }
        .home-search-submit {
            grid-column: 2 / 3 !important;
            grid-row: 1 / 2 !important;
            width: auto !important;
            border-radius: 0 8px 8px 0 !important;
            padding: 0 14px !important;
            background-color: #0057d9 !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 34px !important;
        }
        .home-search-filter-row {
            grid-column: 1 / 3 !important;
            grid-row: 2 / 3 !important;
            width: 100% !important;
            margin: 0 !important;
            gap: 6px !important;
        }
        .home-search-panel .form-select {
            background-image: none !important;
            padding-right: 0 !important;
            font-size: 0.8rem !important;
        }
        #home-use-location {
            display: none !important;
        }
    }
    /* VLibras: apenas botão flutuante discreto */
    [vw] [vw-access-button] {
        width: 42px !important;
        height: 42px !important;
        overflow: hidden !important;
        border-radius: 8px 0 0 8px !important;
        transition: width 0.25s ease !important;
    }
    [vw] [vw-access-button]:hover,
    [vw] [vw-access-button]:focus-within {
        width: auto !important;
    }
    @media (min-width: 768px) and (max-width: 991.98px) {
        .hero-swiper-slide-responsive {
            height: 220px !important;
            min-height: 220px !important;
            max-height: 230px !important;
        }
        .hero-slide-container-responsive {
            padding-top: 15px !important;
            padding-bottom: 45px !important;
        }
        .hero-search-card-container,
        body.home-guest .hero-search-card-container,
        body.home-authenticated .hero-search-card-container {
            margin-top: -75px !important;
            margin-bottom: 14px !important;
        }
    }
    @media (min-width: 992px) {
        .home-hero-prev { left: clamp(12px, 1.8vw, 32px); }
        .home-hero-next { right: clamp(12px, 1.8vw, 32px); }
        .hero-swiper-slide-responsive {
            min-height: 310px;
            max-height: 380px;
        }
        .hero-slide-container-responsive {
            padding-top: 20px;
            padding-bottom: 100px;
        }
        .hero-search-card-container,
        body.home-guest .hero-search-card-container,
        body.home-authenticated .hero-search-card-container {
            margin-top: -140px;
            margin-bottom: 34px;
        }
    }
    /* Fix: em monitores 1366x768 o texto fica atrás da caixa de busca */
    @media (min-width: 992px) and (max-height: 800px) {
        .hero-swiper-slide-responsive {
            min-height: 270px !important;
            max-height: 320px !important;
        }
        .hero-slide-container-responsive {
            padding-top: 20px !important;
            padding-bottom: 95px !important;
        }
        .hero-search-card-container,
        body.home-guest .hero-search-card-container,
        body.home-authenticated .hero-search-card-container {
            margin-top: -135px !important;
            margin-bottom: 34px !important;
        }
    }
    .home-highlights-layout {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    @media (min-width: 1400px) {
        .home-provider-heading-row {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start !important;
            column-gap: 12px;
        }
        .home-provider-heading-row h4 {
            min-width: 0;
            line-height: 1.25;
            white-space: normal;
            font-size: 1rem !important;
        }
        .home-provider-heading-row > a {
            grid-column: 2;
            margin-top: 2px;
            white-space: nowrap;
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
    .home-search-shortcuts {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        white-space: nowrap !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-x;
        gap: 12px !important;
    }
    .home-search-shortcuts::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
    .home-search-shortcuts a {
        flex: 0 0 auto !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        white-space: nowrap !important;
        transition: opacity 0.15s ease, color 0.15s ease;
    }
    .home-search-shortcuts a:hover {
        opacity: 1 !important;
        color: #ffffff !important;
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
    /* Cards de Lojas */
    .home-store-card {
        background: var(--card);
        border-color: var(--border) !important;
    }
    .home-store-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(13, 110, 253, 0.12) !important;
        border-color: #0d6efd !important;
    }
    [data-bs-theme="dark"] .home-store-card,
    html[data-theme="dark"] .home-store-card {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.08) !important;
    }
    [data-bs-theme="dark"] .home-store-card:hover,
    html[data-theme="dark"] .home-store-card:hover {
        border-color: #3b82f6 !important;
        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.2) !important;
    }
    [data-bs-theme="dark"] .home-store-card strong,
    html[data-theme="dark"] .home-store-card strong {
        color: #f8fafc !important;
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
    [data-bs-theme="dark"] a:visited .home-clickable-name,
    html[data-theme="dark"] .home-clickable-name,
    html[data-theme="dark"] a:visited .home-clickable-name {
        color: #7db5f1;
    }
    [data-bs-theme="dark"] a:hover .home-clickable-name,
    [data-bs-theme="dark"] a:focus-visible .home-clickable-name,
    html[data-theme="dark"] a:hover .home-clickable-name,
    html[data-theme="dark"] a:focus-visible .home-clickable-name {
        color: #a9cefa;
    }
    .home-provider-category,
    .home-provider-city,
    .home-card-city,
    .home-featured-city {
        color: #4b5563;
        font-weight: 500;
    }
    [data-bs-theme="dark"] .home-provider-category,
    html[data-theme="dark"] .home-provider-category,
    [data-bs-theme="dark"] .home-auth-provider-copy span,
    html[data-theme="dark"] .home-auth-provider-copy span {
        color: #cbd5e1 !important;
    }
    [data-bs-theme="dark"] .home-provider-city,
    html[data-theme="dark"] .home-provider-city,
    [data-bs-theme="dark"] .home-auth-provider-copy small em,
    html[data-theme="dark"] .home-auth-provider-copy small em,
    [data-bs-theme="dark"] .home-featured-city,
    html[data-theme="dark"] .home-featured-city,
    [data-bs-theme="dark"] .home-card-city,
    html[data-theme="dark"] .home-card-city {
        color: #cbd5e1 !important;
    }
    [data-bs-theme="dark"] .home-auth-provider-copy strong,
    html[data-theme="dark"] .home-auth-provider-copy strong {
        color: #93c5fd !important;
    }
    [data-bs-theme="dark"] .swiper-providers .swiper-slide > div,
    html[data-theme="dark"] .swiper-providers .swiper-slide > div,
    [data-bs-theme="dark"] .home-auth-provider-card,
    html[data-theme="dark"] .home-auth-provider-card {
        background: #1e293b !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .swiper-providers-prev,
    [data-bs-theme="dark"] .swiper-providers-next,
    html[data-theme="dark"] .swiper-providers-prev,
    html[data-theme="dark"] .swiper-providers-next {
        background: #334155 !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
        color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .swiper-providers-prev i,
    [data-bs-theme="dark"] .swiper-providers-next i,
    html[data-theme="dark"] .swiper-providers-prev i,
    html[data-theme="dark"] .swiper-providers-next i {
        color: #f8fafc !important;
    }
    .card-premium > button[data-favorite-button] {
        width: 24px;
        height: 24px;
        min-width: 24px;
        padding: 0 !important;
        border-radius: 50% !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .card-premium > button[data-favorite-button] > i {
        font-size: .72rem;
    }
    .card-premium > button[data-favorite-button].is-favorite {
        background: #eaf3ff;
        border-color: rgba(13, 110, 253, .28);
    }
    .favorite-folder-modal {
        position: fixed;
        z-index: 1100;
        inset: 0;
        display: grid;
        place-items: center;
        padding: 18px;
        background: rgba(8, 18, 36, .68);
        backdrop-filter: blur(5px);
    }
    .favorite-folder-modal[hidden] { display: none !important; }
    .favorite-folder-dialog {
        width: min(100%, 420px);
        padding: 22px;
        border: 1px solid #d9e4f2;
        border-radius: 18px;
        color: #17243a;
        background: #fff;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .28);
    }
    .favorite-folder-dialog-header { display: flex; align-items: flex-start; gap: 12px; }
    .favorite-folder-dialog-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        color: #0d6efd;
        background: #eaf3ff;
    }
    .favorite-folder-dialog h2 { margin: 0 34px 3px 0; font-size: 1.08rem; font-weight: 800; }
    .favorite-folder-dialog-header p { margin: 0; color: #64748b; font-size: .78rem; line-height: 1.4; }
    .favorite-folder-close {
        width: 30px;
        height: 30px;
        margin-left: auto;
        border: 0;
        border-radius: 50%;
        color: #526178;
        background: #edf2f7;
    }
    .favorite-folder-field { margin-top: 18px; }
    .favorite-folder-usage { margin: 14px 0 -5px; color: #64748b; font-size: .7rem; }
    .favorite-folder-usage strong { color: #0d6efd; }
    .favorite-folder-field[hidden] { display: none !important; }
    .favorite-folder-field label { display: block; margin-bottom: 6px; font-size: .78rem; font-weight: 800; }
    .favorite-folder-field input,
    .favorite-folder-field select {
        width: 100%;
        min-height: 44px;
        padding: 9px 12px;
        border: 1px solid #cbd7e6;
        border-radius: 11px;
        color: #17243a;
        background: #fff;
        outline: none;
    }
    .favorite-folder-field input:focus,
    .favorite-folder-field select:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13, 110, 253, .12); }
    .favorite-folder-feedback { min-height: 20px; margin: 9px 0 0; color: #c92a2a; font-size: .72rem; }
    .favorite-folder-actions { display: flex; align-items: center; gap: 8px; margin-top: 15px; }
    .favorite-folder-actions button { min-height: 40px; padding: 8px 14px; border-radius: 10px; font-size: .78rem; font-weight: 800; }
    .favorite-folder-remove { margin-right: auto; border: 0; color: #c92a2a; background: #fff0f1; }
    .favorite-folder-cancel { border: 1px solid #cad5e2; color: #42526a; background: #fff; }
    .favorite-folder-save { border: 0; color: #fff; background: #0d6efd; }
    .favorite-folder-save:disabled { opacity: .65; }
    .favorite-toast {
        position: fixed;
        z-index: 1110;
        right: 18px;
        bottom: 18px;
        max-width: min(360px, calc(100vw - 36px));
        padding: 11px 15px;
        border-radius: 11px;
        color: #fff;
        background: #146c43;
        box-shadow: 0 14px 36px rgba(0, 0, 0, .25);
        font-size: .78rem;
        font-weight: 700;
    }
    .favorite-toast[hidden] { display: none !important; }
    html[data-theme="dark"] .favorite-folder-dialog,
    [data-bs-theme="dark"] .favorite-folder-dialog { color: #f5f8fc; border-color: #2d405a; background: #0d192b; }
    html[data-theme="dark"] .favorite-folder-dialog-header p,
    [data-bs-theme="dark"] .favorite-folder-dialog-header p { color: #aebed7; }
    html[data-theme="dark"] .favorite-folder-field input,
    html[data-theme="dark"] .favorite-folder-field select,
    [data-bs-theme="dark"] .favorite-folder-field input,
    [data-bs-theme="dark"] .favorite-folder-field select { color: #f5f8fc; border-color: #354963; background: #14243a; }
    .home-auth-mobile-bottom-nav,
    .home-auth-mobile-providers {
        display: none;
    }
    .home-paid-provider-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .home-paid-provider-card {
        min-width: 0;
        height: 100%;
        overflow: hidden;
        border: 1px solid #dce4ee;
        border-radius: 18px;
        background: var(--card, #fff);
        box-shadow: 0 9px 24px rgba(15, 23, 42, .08);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .home-paid-provider-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 34px rgba(15, 23, 42, .14);
    }
    .home-paid-provider-media {
        position: relative;
        height: 118px;
        overflow: hidden;
        background: linear-gradient(135deg, #0a2342, #0d6efd);
    }
    .home-paid-provider-media img { width: 100%; height: 100%; object-fit: cover; }
    .home-paid-provider-media-placeholder { display: grid; place-items: center; height: 100%; color: #fff; font-size: 2rem; }
    .home-paid-provider-badges {
        position: absolute;
        z-index: 2;
        inset: 8px 8px auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4px;
        pointer-events: none;
    }
    .home-paid-provider-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 0;
        min-height: 22px;
        padding: 4px 6px;
        border-radius: 8px;
        font-size: .5rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: .015em;
        text-transform: uppercase;
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(2, 12, 33, .18);
    }
    .home-paid-provider-badge i { margin-right: 3px !important; font-size: .48rem; }
    .home-paid-provider-badge.is-featured { flex: 0 1 auto; color: #fff; background: #1666e8; }
    .home-paid-provider-badge.is-type { color: #14213a; background: rgba(255, 255, 255, .94); }
    .home-paid-provider-content { padding: 12px 12px 10px; }
    .home-paid-provider-category {
        display: block;
        margin-bottom: 5px;
        color: #075be8;
        font-size: .61rem;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
    }
    .home-paid-provider-name {
        display: -webkit-box;
        min-height: 2.55em;
        margin: 0;
        overflow: hidden;
        color: var(--foreground, #14213a);
        font-size: .86rem;
        font-weight: 800;
        line-height: 1.28;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .home-paid-provider-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        padding: 9px 12px;
        border-top: 1px solid #e5ebf2;
        color: #53657b;
        font-size: .62rem;
        font-weight: 700;
    }
    .home-paid-provider-city { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .home-paid-provider-stars { flex: 0 0 auto; color: #f5b800; font-size: .58rem; letter-spacing: -1px; }
    html[data-theme="dark"] .home-paid-provider-card,
    [data-bs-theme="dark"] .home-paid-provider-card {
        border-color: transparent;
        background: #0b172a;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .28);
    }
    html[data-theme="dark"] .home-paid-provider-footer,
    [data-bs-theme="dark"] .home-paid-provider-footer { border-top-color: rgba(148, 163, 184, .16); }
    @media (max-width: 359.98px) {
        .home-paid-provider-badge { padding-inline: 5px; font-size: .45rem; }
        .home-paid-provider-badge i { font-size: .43rem; }
    }
    @media (min-width: 768px) {
        .home-paid-provider-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .home-paid-provider-media { height: 150px; }
        .home-paid-provider-content { padding: 14px 14px 12px; }
        .home-paid-provider-name { font-size: .94rem; }
        .home-paid-provider-footer { padding: 10px 14px; }
        .home-paid-provider-badges { inset: 10px 10px auto; gap: 6px; }
        .home-paid-provider-badge { min-height: 24px; padding: 4px 8px; font-size: .55rem; }
        .home-paid-provider-badge i { font-size: .52rem; }
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
    /* Nova vitrine dos grupos locais */
    .home-city-groups {
        padding: clamp(20px, 3vw, 34px);
        border: 0;
        border-radius: 24px;
        color: #fff;
        background:
            radial-gradient(circle at 86% 10%, rgba(50, 179, 255, .3), transparent 28%),
            linear-gradient(135deg, #071b3b 0%, #0a3d82 56%, #075be8 100%);
        box-shadow: 0 22px 55px rgba(7, 52, 113, .2);
    }
    .home-city-groups::after {
        content: '';
        position: absolute;
        width: 220px;
        height: 220px;
        right: -90px;
        bottom: -130px;
        border: 36px solid rgba(255, 255, 255, .07);
        border-radius: 50%;
        pointer-events: none;
    }
    .home-city-groups-header {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }
    .home-city-groups-pin {
        width: 50px;
        height: 50px;
        flex-basis: 50px;
        border: 1px solid rgba(255, 255, 255, .28);
        color: #fff;
        background: rgba(255, 255, 255, .13);
        box-shadow: 0 10px 30px rgba(0, 0, 0, .14);
    }
    .home-city-groups-eyebrow {
        display: block;
        margin-bottom: 4px;
        color: #7dd3fc;
        font-size: .64rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .home-city-groups-title { color: #fff; font-size: clamp(1.1rem, 2vw, 1.55rem); }
    .home-city-groups-subtitle { max-width: 620px; color: rgba(255, 255, 255, .76); font-size: .78rem; }
    .home-city-groups-summary { display: flex; gap: 8px; }
    .home-city-groups-summary span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 32px;
        padding: 6px 10px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .1);
        font-size: .67rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .home-city-groups-rail {
        position: relative;
        z-index: 1;
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 210px;
        grid-template-rows: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 2px 2px 12px;
        scroll-snap-type: x mandatory;
        scrollbar-color: rgba(255, 255, 255, .55) rgba(255, 255, 255, .12);
    }
    .home-city-group-card {
        width: auto;
        min-width: 0;
        padding: 0;
        text-align: left;
        border: 1px solid rgba(255, 255, 255, .24);
        border-radius: 17px;
        background: #fff;
        box-shadow: 0 13px 30px rgba(2, 12, 33, .2);
    }
    .home-city-group-card::before { display: none; }
    .home-city-group-cover {
        width: 100%;
        height: 96px;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 16px 16px 0 0;
        background: #dbeafe;
        box-shadow: none;
    }
    .home-city-group-cover img { border-radius: 0; }
    .home-city-group-body { padding: 11px 12px 12px; }
    .home-city-group-name { min-height: auto; margin-bottom: 2px; font-size: .84rem; }
    .home-city-group-type { margin-bottom: 7px; }
    .home-city-group-status { justify-content: flex-start; margin-bottom: 8px; }
    .home-city-group-enter { min-height: 34px; border-radius: 9px; }
    .home-city-groups-note {
        position: relative;
        z-index: 1;
        justify-content: flex-start;
        margin-top: 14px;
        color: rgba(255, 255, 255, .72);
    }
    .home-city-groups-note i { color: #7dd3fc; }
    html[data-theme="dark"] .home-city-groups,
    [data-bs-theme="dark"] .home-city-groups { background: radial-gradient(circle at 86% 10%, rgba(50, 179, 255, .22), transparent 28%), linear-gradient(135deg, #031027, #082f66 58%, #064cad); }
    html[data-theme="dark"] .home-city-groups-title,
    [data-bs-theme="dark"] .home-city-groups-title { color: #fff; }
    html[data-theme="dark"] .home-city-groups-subtitle,
    [data-bs-theme="dark"] .home-city-groups-subtitle,
    html[data-theme="dark"] .home-city-groups-note,
    [data-bs-theme="dark"] .home-city-groups-note { color: rgba(255, 255, 255, .75); }
    @media (max-width: 767.98px) {
        .home-city-groups { margin-inline: -4px; padding: 18px 12px; border-radius: 18px; }
        .home-city-groups-header { grid-template-columns: auto minmax(0, 1fr); align-items: start; gap: 10px; margin-bottom: 16px; }
        .home-city-groups-summary { grid-column: 1 / -1; padding-left: 48px; }
        .home-city-groups-summary span { min-height: 28px; padding: 5px 9px; font-size: .61rem; }
        .home-city-groups-rail { grid-auto-columns: min(72vw, 220px); grid-template-rows: 1fr; }
        .home-city-group-cover { height: 92px; }
    }
    @auth
    @media (max-width: 767.98px) {
        body.home-authenticated {
            padding-bottom: 70px;
            background: var(--background);
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
            height: 190px !important;
            min-height: 190px !important;
            max-height: 190px !important;
        }
        body.home-authenticated .hero-slide-container-responsive {
            justify-content: flex-start !important;
            align-items: flex-start !important;
            text-align: left !important;
            display: flex !important;
            flex-direction: column !important;
            padding: 18px 16px 24px 16px !important;
        }
        body.home-authenticated .hero-slide-container-responsive h1 {
            display: block !important;
            font-size: clamp(1.1rem, 4.6vw, 1.35rem) !important;
            font-weight: 800 !important;
            margin-bottom: 6px !important;
            color: #ffffff !important;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.75) !important;
            text-align: left !important;
            line-height: 1.22 !important;
            max-width: 84% !important;
        }
        body.home-authenticated .hero-slide-container-responsive p {
            display: block !important;
            font-size: clamp(0.75rem, 3.1vw, 0.84rem) !important;
            line-height: 1.3 !important;
            color: rgba(255, 255, 255, 0.92) !important;
            max-width: 88% !important;
            margin: 0 !important;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.75) !important;
            text-align: left !important;
        }
        body.home-authenticated .home-hero-copy {
            text-align: left !important;
            width: 100% !important;
            max-width: 88% !important;
            padding: 0 !important;
        }
        body.home-authenticated .home-hero-navigation {
            top: 48% !important;
            transform: translateY(-50%) !important;
            opacity: 0.85;
            width: 32px !important;
            height: 32px !important;
            font-size: 0.75rem !important;
            background: rgba(0, 0, 0, 0.4) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 50% !important;
        }
        body.home-authenticated .hero-search-card-container {
            width: 100%;
            margin-top: -36px !important;
            margin-bottom: 10px !important;
            padding: 0 10px;
        }
        body.home-authenticated .home-search-panel {
            padding: 10px 12px !important;
            background: rgba(15, 23, 42, 0.94) !important;
            border: 1px solid rgba(255, 255, 255, 0.16) !important;
            border-radius: 18px !important;
            box-shadow: 0 6px 20px rgba(5, 14, 29, .24) !important;
        }
        body.home-authenticated #home-search-form {
            display: grid !important;
            grid-template-columns: 1fr auto !important;
            gap: 8px !important;
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
        @media (max-width: 991.98px) {
            body.home-authenticated .home-provider-desktop-column {
                display: none !important;
            }
            body.home-authenticated .home-auth-mobile-providers {
                display: block;
                margin-top: 14px;
                padding-right: 12px;
                padding-left: 12px;
            }
        }
        @media (min-width: 992px) {
            body.home-authenticated .home-provider-desktop-column {
                display: block !important;
            }
            body.home-authenticated .home-auth-mobile-providers {
                display: none !important;
            }
        }
        .home-auth-provider-list {
            display: grid;
            gap: 6px;
        }
        .home-auth-provider-card {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto;
            align-items: center;
            gap: 8px;
            min-height: 76px;
            height: 100%;
            padding: 5px 8px;
            color: var(--foreground);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
        }
        .home-auth-provider-photo,
        .home-auth-provider-photo-placeholder {
            width: 64px;
            height: 64px;
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
            font-size: .82rem;
            line-height: 1.15;
        }
        .home-auth-provider-copy span {
            margin: 1px 0 2px;
            color: #4b5563;
            font-weight: 500;
            font-size: .7rem;
            line-height: 1.15;
        }
        .home-auth-provider-copy small {
            color: #f59f00;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1.15;
        }
        .home-auth-provider-copy small em {
            margin-left: 4px;
            color: #4b5563;
            font-weight: 500;
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
<div class="row mx-0 mb-0 home-main-hero">
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
                            'description' => 'Conectando prestadores de serviços, lojas, produtos e oportunidades nos 75 municípios.',
                        ],
                        [
                            'title' => 'Tudo o que você procura em Sergipe, em um só lugar.',
                            'description' => 'Serviços, lojas, produtos, veículos, imóveis e oportunidades nos 75 municípios.',
                        ],
                        [
                            'title' => 'Sergipe inteiro mais perto de você.',
                            'description' => 'Encontre serviços, lojas, produtos e oportunidades em um só lugar.',
                        ],
                        [
                            'title' => 'Valorize quem é daqui.',
                            'description' => 'Descubra profissionais, lojas e negócios que fazem Sergipe acontecer todos os dias.',
                        ],
                        [
                            'title' => 'Assine nossos planos e saia na frente.',
                            'description' => 'Dê mais visibilidade aos seus serviços e produtos e aumente suas oportunidades.',
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
                            'description' => 'Encontre eletricistas, pintores, mecânicos, diaristas e profissionais da sua região.',
                        ],
                        [
                            'title' => 'Descubra lojas e negócios locais.',
                            'description' => 'Veja produtos, conheça novas opções e compre de quem movimenta a economia.',
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
                            'title' => 'Seu próximo cliente a uma busca de distância.',
                            'description' => 'Mais do que anunciar: esteja presente onde seus clientes procuram.',
                        ],
                        [
                            'title' => 'Mais destaque. Mais presença. Mais oportunidades.',
                            'description' => 'Escolha um plano e aumente suas chances de ser encontrado por quem procura.',
                            'cta_label' => 'Ver planos',
                            'cta_url' => route('page.plans'),
                        ],
                        [
                            'title' => 'Conecte-se. Divulgue. Venda. Cresça em Sergipe.',
                            'description' => 'Conectamos pessoas, profissionais e negócios em todo o estado de Sergipe.',
                        ],
                    ];
                @endphp
                @foreach($heroBanners as $index => $banner)
                @php
                    $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner);
                    $heroMessage = $heroMessages[$index % count($heroMessages)];
                @endphp
                <div class="swiper-slide hero-swiper-slide-responsive d-flex flex-column justify-content-center align-items-center px-3 px-lg-0"
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
                                        {{ $heroMessage['cta_label'] }}
                                    </a>
                                @endif
                            </div>
                            @if($loop->first)
                                <div id="home-hero-plans-card" class="home-hero-plans-card d-none d-md-flex align-items-center rounded-pill px-3 py-1.5 ms-auto shadow-sm" style="position: relative; padding-right: 28px; background: rgba(37, 99, 235, 0.85); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 0.82rem;">
                                    <a href="{{ route('page.plans') }}" class="d-flex align-items-center text-decoration-none text-white gap-2">
                                        <i class="fa-solid fa-gem text-warning" style="font-size: 0.95rem;"></i>
                                        <span class="fw-bold text-nowrap">Planos Premium</span>
                                    </a>
                                    <button type="button" data-close-home-plans aria-label="Fechar card de planos" title="Fechar" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; padding: 0; border: none; border-radius: 50%; color: #fff; background: rgba(0,0,0,.25); display: flex; align-items: center; justify-content: center; font-size: 0.65rem;">
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
            <div class="rounded-4 shadow-lg p-2 p-md-2.5 px-xl-3 py-xl-2.5 mx-auto home-search-panel" style="background: rgba(15, 23, 42, 0.94); backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, 0.15);">
                <form
                    id="home-search-form"
                    action="{{ route('home') }}"
                    method="GET"
                    data-suggestions-url="{{ route('search.suggestions') }}"
                    class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-1.5 gap-lg-2.5 w-100 mb-1 mb-md-2"
                >
                    <input type="hidden" id="home-search-module-value" name="module" value="{{ $module }}">
                    <input type="hidden" id="home-search-service-category-value" name="service_category" value="">

                    <!-- Campo Pesquisa -->
                    <div class="position-relative d-flex align-items-center bg-white rounded-3 px-2.5 py-1 w-100 hero-search-input-box home-search-query-field" style="flex: 2.5; min-height: 34px;">
                        <i class="fa-solid fa-magnifying-glass text-muted me-2" style="font-size: 0.82rem;"></i>
                        <input
                            id="home-search-query"
                            class="form-control bg-transparent border-0 shadow-none p-0 text-dark"
                            type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="O que você procura em {{ !empty($city) ? $city : 'Sergipe' }}?"
                            autocomplete="off"
                            style="font-size: 0.8rem;"
                        >
                        <button type="button" id="home-search-microphone" class="btn btn-link text-muted p-0 ms-2 text-decoration-none" title="Buscar por voz">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <div id="home-search-suggestions" class="quick-search-suggestions" hidden></div>
                    </div>

                    <!-- Linha Mobile: Cidade & Categoria -->
                    <div class="d-flex gap-2 w-100 home-search-filter-row" style="flex: 3;">
                        <!-- Cidade -->
                        <div class="position-relative d-flex align-items-center bg-white rounded-3 px-2 px-md-3 py-1 py-md-2 w-50 hero-search-input-box overflow-hidden">
                            <i class="fa-solid fa-location-dot text-danger me-1 flex-shrink-0"></i>
                            <select id="home-search-city" name="city" class="form-select bg-transparent border-0 shadow-none py-0 ps-0 pe-4 text-dark fw-semibold text-truncate" style="font-size: 0.84rem; width: 100%; min-width: 0;">
                                <option value="" {{ empty($city) ? 'selected' : '' }}>Todas as cidades</option>
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}" {{ $city === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                @endforeach
                            </select>
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

                <!-- Chips de Categorias e Serviços (Categorias Primeiro, Módulos Depois) -->
                <div class="d-flex align-items-center gap-2 gap-md-3 pt-1.5 pb-0.5 px-1 border-top border-secondary border-opacity-25 overflow-x-auto text-nowrap scrollbar-none home-search-shortcuts" style="scrollbar-width: none;">
                    @if(!empty($serviceSearchCategories))
                        @foreach($serviceSearchCategories as $svcCategory)
                            @php
                                $svcName = is_array($svcCategory) ? ($svcCategory['name'] ?? '') : (string) $svcCategory;
                                $svcIcon = is_array($svcCategory) ? ($svcCategory['icon'] ?? 'fa-wrench') : 'fa-wrench';
                            @endphp
                            @if(!empty($svcName))
                                <a href="{{ route('module.services', ['category' => $svcName]) }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                                    <i class="fa-solid {{ $svcIcon }} text-primary"></i> {{ $svcName }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                    <a href="{{ route('module.services') }}" class="text-decoration-none text-light opacity-90 fw-semibold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-wrench text-primary"></i> Serviços
                    </a>
                    <a href="{{ route('module.products') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-tag text-primary"></i> Produtos
                    </a>
                    <a href="{{ route('module.real_estate') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-building text-primary"></i> Imóveis
                    </a>
                    <a href="{{ route('module.vehicles') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-car text-primary"></i> Veículos
                    </a>
                    <a href="{{ route('module.jobs') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-briefcase text-primary"></i> Empregos
                    </a>
                    <a href="{{ route('module.agro') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-leaf text-primary"></i> Agro
                    </a>
                    <a href="{{ route('culture.index') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-palette text-primary"></i> Arte e Cultura
                    </a>
                    <a href="{{ route('stores.index') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        <i class="fa-solid fa-store text-primary"></i> Lojas
                    </a>
                    <a href="{{ route('module.services') }}" class="text-decoration-none text-light opacity-90 fw-medium d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="font-size: 0.82rem;">
                        Ver todos os serviços
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Section Destaques para você (Carrossel em movimento) + Profissionais em destaque -->
@if(!$isSearch && empty($module))
<div class="container home-content-section home-highlights-layout">
    <div class="row g-3 g-md-4">
        <!-- Destaques para você (Prestadores + Anúncios em Destaque) -->
        <div class="col-12 home-featured-column">
            <div class="d-flex justify-content-between align-items-center mb-2 gap-2 position-relative" style="z-index: 5;">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2 text-truncate" style="font-size: 1.15rem;">
                    Destaques para você
                </h4>
                <a href="{{ route('highlights.index', ['category' => 'services']) }}" class="text-primary text-decoration-none small fw-bold text-nowrap flex-shrink-0 position-relative" style="z-index: 5; padding: 2px 6px;">
                    <span class="d-none d-sm-inline">Ver todos os destaques</span><span class="d-inline d-sm-none">Ver todos</span>
                </a>
            </div>

            <style>
                .home-highlights-layout h4,
                #home-stores-title {
                    font-size: 1.15rem !important;
                    font-weight: 700 !important;
                }
                .home-provider-heading-row h4 {
                    font-size: 0.96rem !important;
                    font-weight: 700 !important;
                }
                .swiper-marquee-esteira .swiper-wrapper {
                    -webkit-transition-timing-function: linear !important;
                    -o-transition-timing-function: linear !important;
                    transition-timing-function: linear !important;
                }
                .swiper-providers, .swiper-providers .swiper-wrapper, .swiper-providers .swiper-slide {
                    touch-action: auto !important;
                }
                .swiper-providers-container,
                body.home-guest .swiper-providers-container,
                body.home-authenticated .swiper-providers-container {
                    height: 310px !important;
                    padding-top: 0 !important;
                    padding-bottom: 0 !important;
                    overflow: visible !important;
                }
                .swiper-providers {
                    height: 100%;
                    overflow: hidden !important;
                    border-radius: 16px;
                }
                .swiper-providers .swiper-slide > div {
                    height: 96px !important;
                    min-height: 96px !important;
                    max-height: 96px !important;
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
                .swiper-featured-ads .card-media,
                .swiper-featured-ads .card-media-hybrid,
                .swiper-featured-ads .card-img-placeholder {
                    height: 155px !important;
                    width: 100% !important;
                }
                .swiper-featured-ads .card-media-main,
                .swiper-featured-ads img {
                    object-fit: cover !important;
                    transform: none !important;
                    width: 100% !important;
                    height: 100% !important;
                }
                .swiper-featured-ads .card-body {
                    flex: 1 1 auto !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                    min-width: 0 !important;
                }
                .swiper-featured-city {
                    color: #41536a !important;
                    font-weight: 700;
                }
                @media (min-width: 992px) {
                    .swiper-featured-ads .card-premium {
                        height: 220px !important;
                    }
                    .swiper-featured-ads .card-media,
                    .swiper-featured-ads .card-media-hybrid,
                    .swiper-featured-ads .home-featured-card-image,
                    .swiper-featured-ads .card-img-placeholder {
                        height: 134px !important;
                        flex: 0 0 134px !important;
                    }
                    .swiper-featured-ads .card-body {
                        padding: 0.4rem 0.7rem !important;
                        overflow: hidden;
                    }
                    .swiper-featured-ads .card-title {
                        font-size: 0.8rem !important;
                        line-height: 1.05 !important;
                    }
                    .swiper-featured-ads .card-body small {
                        font-size: 0.68rem !important;
                        line-height: 1.1 !important;
                    }
                    .swiper-featured-ads .card-body .text-muted.mb-2 {
                        margin-bottom: 0.2rem !important;
                    }
                }
                html[data-theme="dark"] .home-featured-city,
                [data-bs-theme="dark"] .home-featured-city {
                    color: #c4d0df !important;
                }
                @media (max-width: 767.98px) {
                    .home-highlights-layout h4,
                    .home-provider-heading-row h4 {
                        font-size: 0.95rem !important;
                        margin-bottom: 0 !important;
                    }
                    .home-highlights-layout h4 i,
                    .home-provider-heading-row h4 i {
                        font-size: 0.9rem !important;
                    }
                    .home-highlights-layout a.small,
                    .home-provider-heading-row a.small {
                        font-size: 0.74rem !important;
                    }
                    .swiper-featured-ads .swiper-slide {
                        width: 48% !important;
                        max-width: 48% !important;
                        flex: 0 0 48% !important;
                    }
                    .swiper-featured-ads .card-media,
                    .swiper-featured-ads .card-media-hybrid,
                    .swiper-featured-ads .home-featured-card-image,
                    .swiper-featured-ads .card-img-placeholder {
                        height: 140px !important;
                    }
                    .swiper-featured-ads .card-body {
                        padding: 0.55rem 0.65rem !important;
                    }
                    .swiper-featured-ads .card-title {
                        font-size: 0.84rem !important;
                        margin-bottom: 2px !important;
                    }
                    .swiper-featured-ads .card-body small {
                        font-size: 0.70rem !important;
                    }
                    .swiper-featured-ads .card-body strong.text-primary {
                        font-size: 0.88rem !important;
                    }
                    .home-provider-heading-row {
                        margin-bottom: 0.75rem !important;
                    }
                    .swiper-providers-container,
                    body.home-guest .swiper-providers-container {
                        height: 290px !important;
                        padding-top: 4px !important;
                        padding-bottom: 4px !important;
                    }
                    .swiper-providers .swiper-slide {
                        height: auto !important;
                        display: flex !important;
                    }
                    .swiper-providers .swiper-slide > div {
                        height: 92px !important;
                        min-height: 92px !important;
                        max-height: 92px !important;
                        width: 100% !important;
                        padding: 0.4rem 0.75rem !important;
                    }
                    .swiper-providers .swiper-slide img,
                    .swiper-providers .swiper-slide .rounded-3.bg-primary {
                        width: 76px !important;
                        height: 76px !important;
                        min-width: 76px !important;
                        border-radius: 12px !important;
                    }
                    .swiper-providers .home-clickable-name {
                        font-size: 0.86rem !important;
                        line-height: 1.2 !important;
                    }
                    .swiper-providers small {
                        font-size: 0.72rem !important;
                        line-height: 1.2 !important;
                    }
                    .swiper-providers .btn-sm.rounded-circle {
                        width: 32px !important;
                        height: 32px !important;
                        font-size: 0.80rem !important;
                    }
                    .swiper-providers-prev {
                        margin-top: -10px !important;
                    }
                    .swiper-providers-next {
                        margin-bottom: -10px !important;
                    }
                }
            </style>
            <div class="position-relative overflow-hidden px-0">
                <div class="swiper swiper-featured-ads swiper-marquee-esteira rounded-3 p-1">
                    <div class="swiper-wrapper">
                        @php
                            $loopProviders = $featuredForYou->isNotEmpty() ? $featuredForYou : $serviceProviders;
                            if ($loopProviders->isNotEmpty() && $loopProviders->count() < 12) {
                                $loopProviders = $loopProviders
                                    ->concat($loopProviders)
                                    ->concat($loopProviders)
                                    ->concat($loopProviders);
                            }
                        @endphp

                        @foreach($loopProviders as $provider)
                        @php
                            $providerProfileImage = $provider->logo ?: $provider->user?->avatar;
                        @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('provider.show', $provider->slug) }}" class="text-decoration-none text-dark">
                                <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card); @if($provider->is_plan_featured) border-top: 3px solid #0d6efd !important; @endif">
                                    @if($provider->is_plan_featured)
                                        <x-featured-badge :provider="$provider" class="position-absolute top-0 start-0 m-2 px-2 py-0.5" style="font-size: 0.65rem; z-index: 10 !important;" />
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary position-absolute top-0 start-0 m-2 px-2 py-0.5 rounded-pill" style="font-size: 0.65rem; z-index: 10 !important;">
                                            <i class="fa-solid fa-screwdriver-wrench me-1"></i>Prestador
                                        </span>
                                    @endif
                                    @if($providerProfileImage)
                                        <div class="card-media-hybrid" style="height: 120px;">
                                            <img src="{{ asset($providerProfileImage) }}" class="card-media-bg" aria-hidden="true" alt="">
                                            <img src="{{ asset($providerProfileImage) }}" class="card-img-top home-featured-card-image" alt="Foto de {{ $provider->title }}" loading="lazy">
                                        </div>
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center" style="height: 120px; background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                                            <i class="fa-solid fa-user-tie text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <h6 class="card-title home-clickable-name text-truncate mb-1" style="font-size: 0.82rem;">{{ $provider->title }}</h6>
                                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.7rem;">{{ $provider->display_category ?? 'Serviço profissional' }}</small>
                                        </div>
                                        <div>
                                            <small class="text-warning fw-bold d-block mb-0.5" style="font-size: 0.72rem;">⭐ 4,9 (128)</small>
                                            <small class="home-featured-city text-truncate d-block" style="font-size: 0.68rem;"><i class="fa-solid fa-location-dot"></i> {{ $provider->city ?? 'Aracaju, SE' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ─────────────────────────────────────────────────────────────
     SEÇÃO 1: CATEGORIAS DE SERVIÇOS EM DESTAQUE (ATALHOS DIRETOS)
────────────────────────────────────────────────────────────── --}}
<section class="container home-content-section" aria-labelledby="home-service-categories-title">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 id="home-service-categories-title" class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size:1.15rem;">
            <i class="fa-solid fa-screwdriver-wrench text-primary"></i> Categorias de Serviços Populares
        </h2>
        <a href="{{ route('module.services') }}" class="text-primary text-decoration-none small fw-bold">
            Ver todas as categorias
        </a>
    </div>

    @php
        $popularServiceCategories = [
            ['name' => 'Eletricista', 'icon' => 'fa-bolt', 'color' => '#f59e0b', 'bg' => '#fef3c7', 'desc' => 'Instalações e reparos elétricos'],
            ['name' => 'Encanador', 'icon' => 'fa-faucet-drip', 'color' => '#0284c7', 'bg' => '#e0f2fe', 'desc' => 'Vazamentos e hidráulica'],
            ['name' => 'Diarista', 'icon' => 'fa-broom', 'color' => '#10b981', 'bg' => '#d1fae5', 'desc' => 'Limpeza e organização'],
            ['name' => 'Pintor', 'icon' => 'fa-paint-roller', 'color' => '#8b5cf6', 'bg' => '#ede9fe', 'desc' => 'Pinturas e acabamentos'],
            ['name' => 'Mecânico', 'icon' => 'fa-screwdriver-wrench', 'color' => '#ef4444', 'bg' => '#fee2e2', 'desc' => 'Auto mecânica e revisões'],
            ['name' => 'Marceneiro', 'icon' => 'fa-hammer', 'color' => '#d97706', 'bg' => '#fef3c7', 'desc' => 'Montagem e móveis sob medida'],
            ['name' => 'TI / Informática', 'icon' => 'fa-computer', 'color' => '#3b82f6', 'bg' => '#dbeafe', 'desc' => 'Suporte técnico e redes'],
            ['name' => 'Frete e Mudanças', 'icon' => 'fa-truck-moving', 'color' => '#059669', 'bg' => '#d1fae5', 'desc' => 'Transportes e carretos'],
            ['name' => 'Pedreiro', 'icon' => 'fa-trowel-bricks', 'color' => '#ea580c', 'bg' => '#ffedd5', 'desc' => 'Obras, reformas e pisos'],
            ['name' => 'Técnico de Informática', 'icon' => 'fa-snowflake', 'color' => '#06b6d4', 'bg' => '#cffafe', 'desc' => 'Ar-condicionado e refrigeração'],
        ];
    @endphp

    <div class="row g-2 g-md-3">
        @foreach($popularServiceCategories as $cat)
            <div class="col-6 col-md-4 col-lg-home-store">
                <a href="{{ route('module.services', ['category' => $cat['name']]) }}" class="text-decoration-none">
                    <div class="card h-100 rounded-4 p-3 border shadow-sm home-store-card" style="transition: transform .18s, box-shadow .18s; background: var(--card);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:{{ $cat['bg'] }};flex-shrink:0;">
                                <i class="fa-solid {{ $cat['icon'] }}" style="color:{{ $cat['color'] }};font-size:1.15rem;"></i>
                            </span>
                            <strong class="home-clickable-name text-truncate" style="font-size:.88rem; color: var(--foreground, #1e293b);">{{ $cat['name'] }}</strong>
                        </div>
                        <small class="text-muted text-truncate d-block" style="font-size: 0.72rem;">{{ $cat['desc'] }}</small>
                        <span class="badge mt-2 rounded-pill" style="font-size:.62rem; background: rgba(7, 91, 232, 0.10); color: #075be8; width:fit-content;">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>Buscar profissionais
                        </span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>

{{-- ─────────────────────────────────────────────────────────────
     SEÇÃO 2: PROFISSIONAIS RECOMENDADOS EM SERGIPE + CARD PLANOS
────────────────────────────────────────────────────────────── --}}
<div class="container home-content-section">
    <div class="row g-3 g-md-4">
        <!-- Esquerda: Grade de Prestadores de Serviço em destaque -->
        <div class="col-12 col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.15rem;">
                    <i class="fa-solid fa-user-check text-primary"></i> Prestadores de Serviço em destaque
                </h4>
                <a href="{{ route('module.services') }}" class="text-primary text-decoration-none small fw-bold">
                    Ver todos os profissionais
                </a>
            </div>

            @if($serviceProviders->isNotEmpty())
                <div class="home-paid-provider-grid">
                    @foreach($serviceProviders as $provider)
                        @php
                            $providerImage = $provider->logo ?: ($provider->user?->avatar ?: ($provider->card_image ?: $provider->mainImage?->image_path));
                        @endphp
                        <a href="{{ route('provider.show', $provider->slug) }}" class="text-decoration-none text-dark">
                            <article class="home-paid-provider-card">
                                <div class="home-paid-provider-media">
                                    <div class="home-paid-provider-badges">
                                        <span class="home-paid-provider-badge is-featured"><i class="fa-solid fa-star me-1"></i>Em destaque</span>
                                        <span class="home-paid-provider-badge is-type">Prestador</span>
                                    </div>
                                    @if($providerImage)
                                        <img src="{{ asset($providerImage) }}" alt="Foto de {{ $provider->title }}" loading="lazy">
                                    @else
                                        <div class="home-paid-provider-media-placeholder"><i class="fa-solid fa-user-gear"></i></div>
                                    @endif
                                </div>
                                <div class="home-paid-provider-content">
                                    <span class="home-paid-provider-category">{{ $provider->display_category ?? 'Serviço profissional' }}</span>
                                    <h3 class="home-paid-provider-name">{{ $provider->title }}</h3>
                                </div>
                                <footer class="home-paid-provider-footer">
                                    <span class="home-paid-provider-city"><i class="fa-solid fa-location-dot me-1"></i>{{ $provider->city ?? 'Aracaju' }}</span>
                                    <span class="home-paid-provider-stars" aria-label="5 estrelas"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                                </footer>
                            </article>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-4 border bg-body p-4 text-center shadow-sm">
                    <p class="text-muted mb-2">Ainda não há prestadores com destaque pago disponíveis.</p>
                    <a href="{{ route('page.plans') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3">Conhecer os planos</a>
                </div>
            @endif
        </div>

        <!-- Direita: Card Quer Anunciar Seus Serviços Profissionais -->
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
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-1 rounded-pill small fw-bold">Divulgue seus Serviços</span>
                    <h5 class="fw-bold mb-2">Você é um prestador de serviços em Sergipe?</h5>
                    <p class="text-muted small mb-4">Crie seu perfil profissional, apareça em destaque na sua cidade e receba solicitações de orçamento direto no seu WhatsApp.</p>
                    <a href="{{ route('ad.create') }}" class="btn btn-primary fw-bold w-100 rounded-3 py-2 mb-4 shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-plus-circle"></i> Cadastrar Meu Perfil Profissional
                    </a>
                </div>
                <div class="d-flex flex-column gap-2 small text-secondary border-top pt-3 border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> <strong>Página de perfil exclusiva com galeria</strong></div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Botão direto de WhatsApp e Ligação</div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Avaliações e recomendações de clientes</div>
                    <div class="d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check text-success fs-6"></i> Cobertura em todos os 75 municípios</div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="container home-content-section" aria-labelledby="home-liberal-professionals-title">
    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
        <h2 id="home-liberal-professionals-title" class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 1.15rem;">
            <i class="fa-solid fa-user-tie text-primary"></i> Profissional Liberal em Destaque
        </h2>
        <a href="{{ route('module.services', ['profile_kind' => 'liberal_professional']) }}" class="text-primary text-decoration-none small fw-bold text-nowrap">
            Ver todos
        </a>
    </div>

    @if($liberalProfessionals->isNotEmpty())
        <div class="position-relative overflow-hidden px-md-3">
            <div class="swiper swiper-category-ads rounded-3 p-1">
                <div class="swiper-wrapper">
                    @foreach($liberalProfessionals as $provider)
                        @php
                            $liberalImg = $provider->logo ?: ($provider->user?->avatar ?: ($provider->card_image ?: $provider->mainImage?->image_path));
                        @endphp
                        <div class="swiper-slide">
                            <a href="{{ route('provider.show', $provider->slug) }}" class="text-decoration-none text-dark">
                                <div class="card card-premium h-100 border rounded-4 shadow-sm overflow-hidden position-relative" style="background: var(--card);">
                                    @if($provider->is_plan_featured)
                                        <x-featured-badge :provider="$provider" class="position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5" style="font-size: 0.60rem;" />
                                    @else
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-2 z-1 px-2 py-0.5 rounded-pill" style="font-size: 0.60rem;">Profissional liberal</span>
                                    @endif
                                    @if($liberalImg)
                                        <div class="card-media-hybrid" style="height: 130px;">
                                            <img src="{{ asset($liberalImg) }}" class="card-media-bg" aria-hidden="true" alt="">
                                            <img src="{{ asset($liberalImg) }}" class="card-media-main" alt="Foto de {{ $provider->title }}" loading="lazy">
                                        </div>
                                    @else
                                        <div class="card-img-placeholder d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="height: 130px;">
                                            <i class="fa-solid fa-user-tie fs-2"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2.5 p-md-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <h3 class="card-title home-clickable-name text-truncate mb-1 fw-bold" style="font-size: 0.86rem;">{{ $provider->title }}</h3>
                                            <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.72rem;">{{ $provider->display_category ?? 'Profissional liberal' }}</small>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small class="text-warning fw-bold" style="font-size: 0.72rem;">⭐ 4,9 (128)</small>
                                            <small class="city-badge" style="font-size: 0.65rem;"><i class="fa-solid fa-location-dot"></i>{{ $provider->city ?? 'Aracaju, SE' }}</small>
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
    @else
        <div class="rounded-4 border bg-body p-4 text-center shadow-sm">
            <i class="fa-solid fa-user-tie text-primary fs-3 mb-2"></i>
            <p class="text-muted mb-3">Os primeiros profissionais liberais cadastrados aparecerão aqui.</p>
            <a href="{{ route('ad.create', ['module' => 'services', 'profile_kind' => 'liberal_professional']) }}" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                Cadastrar perfil profissional
            </a>
        </div>
    @endif
</section>

<!-- Grupos locais por cidade -->
@auth
@if($homeCityGroups->isNotEmpty())
<section class="container home-content-section" aria-labelledby="home-city-groups-title">
    <div class="home-city-groups">
        <header class="home-city-groups-header">
            <div class="home-city-groups-pin" aria-hidden="true"><i class="fa-solid fa-map-location-dot"></i></div>
            <div>
                <span class="home-city-groups-eyebrow">Comunidades locais</span>
                <h2 class="home-city-groups-title" id="home-city-groups-title">Nossos grupos em Sergipe por cidade</h2>
                <p class="home-city-groups-subtitle">Encontre sua cidade, participe da comunidade e acompanhe oportunidades, avisos e novidades perto de você.</p>
            </div>
            <div class="home-city-groups-summary" aria-label="Resumo dos grupos">
                <span><i class="fa-solid fa-map"></i>75 cidades</span>
                <span><i class="fa-brands fa-whatsapp"></i>Comunidades locais</span>
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
                    <div class="home-city-group-body">
                        <h3 class="home-city-group-name">{{ $group['city'] }}</h3>
                        <p class="home-city-group-type">Comunidade da cidade</p>
                        <div class="home-city-group-status"><i class="fa-solid {{ $group['enabled'] ? 'fa-user-group' : 'fa-circle-pause' }}" aria-hidden="true"></i> {{ $group['enabled'] ? 'Grupo disponível' : 'Não ativo' }}</div>
                        @if($group['enabled'])
                            <a class="home-city-group-enter" href="{{ $group['link'] }}" data-city-group-enter data-group-city="{{ $group['city'] }}" data-group-gentilic="{{ $group['gentilic'] }}" @if($externalGroupLink) target="_blank" rel="noopener noreferrer" @endif>Participar pelo WhatsApp</a>
                        @else
                            <span class="home-city-group-enter is-disabled" aria-disabled="true">Indisponível</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <p class="home-city-groups-note">
            <i class="fa-regular fa-bell" aria-hidden="true"></i>
            <span>Os grupos complementam o site e são organizados por cidade. Confirme que você mora ou tem interesse real na comunidade antes de participar.</span>
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
<div class="container home-content-section">
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

@auth
<div class="favorite-folder-modal" data-favorite-folder-modal hidden>
    <section class="favorite-folder-dialog" role="dialog" aria-modal="true" aria-labelledby="favorite-folder-title">
        <header class="favorite-folder-dialog-header">
            <div class="favorite-folder-dialog-icon" aria-hidden="true"><i class="fa-solid fa-folder-plus"></i></div>
            <div>
                <h2 id="favorite-folder-title">Salvar nos favoritos</h2>
                <p>Organize este anúncio em uma pasta. Você pode salvar até {{ $favoriteLimit }} anúncios.</p>
            </div>
            <button type="button" class="favorite-folder-close" data-favorite-folder-close aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
        </header>

        <form data-favorite-folder-form>
            <p class="favorite-folder-usage"><strong data-favorite-count>{{ $favoriteCount }}</strong> de {{ $favoriteLimit }} favoritos utilizados</p>
            <div class="favorite-folder-field" data-favorite-folder-select-field @if($favoriteFolders->isEmpty()) hidden @endif>
                <label for="favorite-folder-select">Escolha uma pasta</label>
                <select id="favorite-folder-select" data-favorite-folder-select>
                    @foreach($favoriteFolders as $folder)
                        <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                    @endforeach
                    <option value="new">+ Criar nova pasta</option>
                </select>
            </div>

            <div class="favorite-folder-field" data-favorite-folder-name-field @if($favoriteFolders->isNotEmpty()) hidden @endif>
                <label for="favorite-folder-name">Nome da pasta</label>
                <input id="favorite-folder-name" name="folder_name" type="text" maxlength="60" placeholder="Ex.: Quero conhecer" autocomplete="off" data-favorite-folder-name>
            </div>

            <p class="favorite-folder-feedback" data-favorite-folder-feedback role="alert"></p>

            <div class="favorite-folder-actions">
                <button type="button" class="favorite-folder-remove" data-favorite-folder-remove hidden>Remover</button>
                <button type="button" class="favorite-folder-cancel" data-favorite-folder-close>Cancelar</button>
                <button type="submit" class="favorite-folder-save" data-favorite-folder-save>Salvar</button>
            </div>
        </form>
    </section>
</div>
<div class="favorite-toast" data-favorite-toast role="status" aria-live="polite" hidden></div>
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
        slidesPerView: 3,
        spaceBetween: 9,
        speed: 600,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
            pauseOnMouseEnter: false,
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

    if (document.querySelector('.swiper-auth-mobile-providers')) {
        new Swiper('.swiper-auth-mobile-providers', {
            direction: 'vertical',
            slidesPerView: 4,
            spaceBetween: 6,
            speed: 600,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: false,
            },
            allowTouchMove: true,
        });
    }

    const swiperFeatured = new Swiper('.swiper-featured-ads', {
        slidesPerView: 2.42,
        spaceBetween: 7,
        loop: true,
        loopAdditionalSlides: 4,
        allowTouchMove: true,
        speed: 6800,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        breakpoints: {
            576: { slidesPerView: 2.4, spaceBetween: 8, speed: 7000 },
            768: { slidesPerView: 3, spaceBetween: 14, speed: 7500 },
            992: { slidesPerView: 4, spaceBetween: 14, speed: 7500 },
            1200: { slidesPerView: 5, spaceBetween: 14, speed: 8500 },
            1400: { slidesPerView: 6, spaceBetween: 14, speed: 9000 },
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
<script>
    (() => {
        const isAuthenticated = @json(auth()->check());
        const modal = document.querySelector('[data-favorite-folder-modal]');
        const form = modal?.querySelector('[data-favorite-folder-form]');
        const selectField = modal?.querySelector('[data-favorite-folder-select-field]');
        const select = modal?.querySelector('[data-favorite-folder-select]');
        const nameField = modal?.querySelector('[data-favorite-folder-name-field]');
        const nameInput = modal?.querySelector('[data-favorite-folder-name]');
        const feedback = modal?.querySelector('[data-favorite-folder-feedback]');
        const removeButton = modal?.querySelector('[data-favorite-folder-remove]');
        const saveButton = modal?.querySelector('[data-favorite-folder-save]');
        const toast = document.querySelector('[data-favorite-toast]');
        const favoriteCount = modal?.querySelector('[data-favorite-count]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        let activeButton = null;
        let toastTimer = null;

        const showToast = (message, error = false) => {
            if (!toast) return;
            window.clearTimeout(toastTimer);
            toast.textContent = message;
            toast.style.background = error ? '#c92a2a' : '#146c43';
            toast.hidden = false;
            toastTimer = window.setTimeout(() => { toast.hidden = true; }, 3200);
        };

        const updateNameField = () => {
            if (!select || !nameField || !nameInput) return;
            const creatingFolder = select.value === 'new';
            nameField.hidden = !creatingFolder;
            nameInput.required = creatingFolder;
            if (creatingFolder) nameInput.focus();
        };

        const closeModal = () => {
            if (!modal) return;
            modal.hidden = true;
            document.body.style.overflow = '';
            activeButton?.focus();
        };

        const openModal = (button) => {
            if (!modal || !form || !select || !nameInput || !feedback || !removeButton) return;
            activeButton = button;
            feedback.textContent = '';
            nameInput.value = '';
            const folderId = button.dataset.favoriteFolderId || '';
            const existingOption = folderId
                ? Array.from(select.options).find((option) => option.value === folderId)
                : null;
            const firstFolder = Array.from(select.options).find((option) => option.value !== 'new');

            if (existingOption) {
                select.value = folderId;
            } else if (firstFolder) {
                select.value = firstFolder.value;
            } else {
                select.value = 'new';
            }

            selectField.hidden = !firstFolder;
            removeButton.hidden = button.getAttribute('aria-pressed') !== 'true';
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            updateNameField();

            if (select.value === 'new') nameInput.focus();
            else select.focus();
        };

        const updateFavoriteButtons = (adId, favorite, folderId = '') => {
            document.querySelectorAll(`[data-favorite-button][data-ad-id="${CSS.escape(String(adId))}"]`).forEach((button) => {
                button.classList.toggle('is-favorite', favorite);
                button.setAttribute('aria-pressed', favorite ? 'true' : 'false');
                button.setAttribute('aria-label', favorite ? 'Organizar favorito' : 'Favoritar');
                button.title = favorite ? 'Organizar favorito' : 'Salvar anúncio';
                button.dataset.favoriteFolderId = favorite ? String(folderId) : '';
                const icon = button.querySelector('i');
                icon?.classList.toggle('fa-solid', favorite);
                icon?.classList.toggle('fa-regular', !favorite);
            });
        };

        const request = async (url, method, body = null) => {
            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: body ? JSON.stringify(body) : null,
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validationMessage = data.errors
                    ? Object.values(data.errors).flat()[0]
                    : null;
                throw new Error(validationMessage || data.message || 'Não foi possível atualizar o favorito.');
            }

            return data;
        };

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-favorite-button]');
            if (!button) return;
            event.preventDefault();
            event.stopPropagation();

            if (!isAuthenticated) {
                window.location.assign(button.dataset.loginUrl);
                return;
            }

            openModal(button);
        });

        select?.addEventListener('change', updateNameField);
        modal?.querySelectorAll('[data-favorite-folder-close]').forEach((button) => button.addEventListener('click', closeModal));
        modal?.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal && !modal.hidden) closeModal();
        });

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!activeButton || !select || !nameInput || !feedback || !saveButton) return;

            const creatingFolder = select.value === 'new';
            const folderName = nameInput.value.trim();
            if (creatingFolder && !folderName) {
                feedback.textContent = 'Digite o nome da pasta.';
                nameInput.focus();
                return;
            }

            feedback.textContent = '';
            saveButton.disabled = true;
            saveButton.textContent = 'Salvando...';

            try {
                const wasFavorite = activeButton.getAttribute('aria-pressed') === 'true';
                const data = await request(activeButton.dataset.storeEndpoint, 'POST', creatingFolder
                    ? { folder_name: folderName }
                    : { folder_id: Number(select.value) });
                const adId = activeButton.dataset.adId;

                if (creatingFolder && !Array.from(select.options).some((option) => option.value === String(data.folder.id))) {
                    const option = document.createElement('option');
                    option.value = String(data.folder.id);
                    option.textContent = data.folder.name;
                    select.insertBefore(option, select.querySelector('option[value="new"]'));
                    selectField.hidden = false;
                }

                updateFavoriteButtons(adId, true, data.folder.id);
                if (!wasFavorite && favoriteCount) favoriteCount.textContent = String(Number(favoriteCount.textContent) + 1);
                closeModal();
                showToast(data.message);
            } catch (error) {
                feedback.textContent = error.message;
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = 'Salvar';
            }
        });

        removeButton?.addEventListener('click', async () => {
            if (!activeButton || !feedback || !removeButton) return;
            feedback.textContent = '';
            removeButton.disabled = true;

            try {
                const data = await request(activeButton.dataset.destroyEndpoint, 'DELETE');
                const adId = activeButton.dataset.adId;
                updateFavoriteButtons(adId, false);
                if (favoriteCount) favoriteCount.textContent = String(Math.max(0, Number(favoriteCount.textContent) - 1));
                closeModal();
                showToast(data.message);
            } catch (error) {
                feedback.textContent = error.message;
            } finally {
                removeButton.disabled = false;
            }
        });
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
