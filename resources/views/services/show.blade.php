@extends('layouts.app')

@section('title', $provider->title . ' - Prestador de Serviços em Sergipe')

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $provider->title . ' - Prestador de Serviços em Sergipe',
        'socialDescription' => \Illuminate\Support\Str::limit(strip_tags($provider->description), 160),
        'socialUrl' => route('provider.show', $provider->slug),
        'socialImage' => asset($provider->banner ?: $provider->logo ?: $provider->mainImage?->image_path ?: \App\Support\CityImage::for($provider->city)),
        'socialType' => 'profile',
    ])
@endpush

@section('content')
@php
    $coverImage = $provider->banner ?: \App\Support\CityImage::for($provider->city);
    $avatarImage = $provider->logo ?: ($provider->mainImage?->image_path);
    $portfolioImages = $provider->images
        ->sortByDesc('is_main')
        ->pluck('image_path')
        ->prepend($provider->card_image)
        ->filter()
        ->unique()
        ->values();
    $publicPhone = $provider->publicPhone();
    $whatsapp = preg_replace('/\D+/', '', $provider->publicWhatsapp() ?? '');
    $whatsappNumber = $whatsapp
        ? (str_starts_with($whatsapp, '55') ? $whatsapp : '55' . $whatsapp)
        : null;
    $whatsappMessage = urlencode("Olá, encontrei seu perfil profissional no Conectado em Sergipe: {$provider->title}");
    $dayLabels = [
        'monday' => 'Segunda',
        'segunda' => 'Segunda',
        'tuesday' => 'Terça',
        'terca' => 'Terça',
        'terça' => 'Terça',
        'wednesday' => 'Quarta',
        'quarta' => 'Quarta',
        'thursday' => 'Quinta',
        'quinta' => 'Quinta',
        'friday' => 'Sexta',
        'sexta' => 'Sexta',
        'saturday' => 'Sábado',
        'sabado' => 'Sábado',
        'sábado' => 'Sábado',
        'sunday' => 'Domingo',
        'domingo' => 'Domingo',
    ];
    $businessHours = collect($provider->business_hours ?? [])
        ->filter(fn ($times) => is_array($times) && isset($times['open'], $times['close']));
    $instagramValue = trim((string) ($provider->instagram ?: $provider->user?->instagram));
    $facebookValue = trim((string) ($provider->facebook ?: $provider->user?->facebook));
    $instagramUrl = $instagramValue
        ? (preg_match('#^https?://#i', $instagramValue)
            ? $instagramValue
            : 'https://instagram.com/' . ltrim($instagramValue, '@/'))
        : null;
    $facebookUrl = $facebookValue
        ? (preg_match('#^https?://#i', $facebookValue)
            ? $facebookValue
            : 'https://facebook.com/' . ltrim($facebookValue, '@/'))
        : null;
    $providerPublicAddress = trim((string) $provider->public_address);
    $providerDirectionsUrl = $providerPublicAddress !== ''
        ? 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode(implode(', ', array_filter([
            $providerPublicAddress,
            $provider->city,
            'SE',
            'Brasil',
        ])))
        : null;
@endphp

<section class="provider-profile-page">
    @if(session('claim_success'))
        <div class="container pt-3">
            <div class="alert alert-success rounded-3 mb-0">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('claim_success') }}
            </div>
        </div>
    @endif
    <div class="container provider-cover-frame">
        <div class="provider-cover">
            <img src="{{ asset($coverImage) }}" alt="Capa de {{ $provider->city }}">
            <div class="provider-cover-shade"></div>
            <div class="provider-cover-content">
                <a href="{{ route('module.services') }}" class="provider-back-button">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container provider-profile-container">
        <article class="provider-profile-card">
            <header class="provider-profile-header">
                <div class="provider-avatar">
                    @if($avatarImage)
                        <img src="{{ asset($avatarImage) }}" alt="{{ $provider->title }}">
                    @else
                        <span>{{ strtoupper(substr($provider->title, 0, 1)) }}</span>
                    @endif
                </div>

                <div class="provider-profile-identity">
                    <div class="provider-badges">
                        <span class="provider-category-badge">{{ $provider->display_category }}</span>
                        @if($provider->profile_kind === 'service_company')
                            <span class="provider-category-badge"><i class="fa-solid fa-building me-1"></i>Empresa de serviços</span>
                        @endif
                        <span class="provider-verified-badge {{ $provider->is_claimed ? '' : 'provider-unclaimed-badge' }}">
                            <i class="fa-solid {{ $provider->is_claimed ? 'fa-circle-check' : 'fa-circle-info' }}"></i>
                            {{ $provider->is_claimed ? 'Perfil reivindicado' : 'Perfil não reivindicado' }}
                        </span>
                    </div>

                    <h1>{{ $provider->title }}</h1>

                    <div class="provider-profile-facts">
                        <a href="#avaliacoes" class="provider-rating" aria-label="Ver avaliações">
                            <span class="provider-stars" aria-hidden="true">
                                @for($star = 1; $star <= 5; $star++)
                                    <i class="{{ $star <= round($reviewData['average']) ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                @endfor
                            </span>
                            @if($reviewData['count'] > 0)
                                <strong>{{ number_format($reviewData['average'], 1, ',', '.') }}</strong>
                            @endif
                            <span>({{ $reviewData['count'] }} {{ $reviewData['count'] === 1 ? 'avaliação' : 'avaliações' }})</span>
                        </a>

                        <span class="provider-location">
                            <i class="fa-solid fa-location-dot"></i>
                            {{ $provider->city }}{{ $provider->region ? ' — ' . $provider->region : ', SE' }}
                        </span>
                    </div>
                </div>

                <nav class="provider-action-list" aria-label="Contato com o profissional">
                    @if($provider->is_claimed)
                        <a href="{{ route('chat.index', ['with' => $provider->user_id]) }}" class="provider-action provider-action-primary">
                            <i class="fa-regular fa-message"></i>
                            <span>Mensagens</span>
                        </a>
                    @endif

                    @if($whatsappNumber)
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener" class="provider-action provider-action-primary">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </a>
                    @endif

                    @if($publicPhone)
                        <a href="tel:{{ $publicPhone }}" class="provider-action provider-action-secondary">
                            <i class="fa-solid fa-phone"></i>
                            <span>Ligar</span>
                        </a>
                    @endif

                    <button type="button" class="provider-action provider-action-secondary" data-social-share data-share-title="{{ $provider->title }}" data-share-text="Conheça este perfil profissional no Conectado em Sergipe: {{ $provider->title }}" data-share-url="{{ route('provider.show', $provider->slug) }}">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>Compartilhar</span>
                    </button>
                </nav>
            </header>

            <div class="provider-profile-overview">
                <section class="provider-about">
                    <span class="provider-section-eyebrow">Sobre</span>
                    <h2>Conheça o trabalho.</h2>
                    <p>{{ $provider->description }}</p>

                    <div class="provider-profile-meta-footer">
                        <div>
                            @if($provider->is_claimed)
                                <span>Responsável pelo perfil</span>
                                <strong>{{ $provider->user->name }}</strong>
                                @if($provider->user->username)
                                    <small>{{ '@' . $provider->user->username }}</small>
                                @endif
                            @else
                                <span>Origem do perfil</span>
                                <strong>Cadastrado pela plataforma</strong>
                                <small>Aguardando o responsável</small>
                            @endif
                        </div>

                        @if($instagramUrl || $facebookUrl)
                            <div class="provider-social-links" aria-label="Redes sociais">
                                @if($instagramUrl)
                                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" aria-label="Instagram">
                                        <i class="fa-brands fa-instagram"></i>
                                        <span>Instagram</span>
                                    </a>
                                @endif
                                @if($facebookUrl)
                                    <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" aria-label="Facebook">
                                        <i class="fa-brands fa-facebook-f"></i>
                                        <span>Facebook</span>
                                    </a>
                                @endif
                            </div>
                        @endif

                        <span class="provider-profile-views">
                            <i class="fa-regular fa-eye"></i>
                            {{ $provider->views }} visualizações
                        </span>
                    </div>
                </section>

                <aside class="provider-hours">
                    <h2><i class="fa-regular fa-clock"></i>Horário</h2>

                    @if($businessHours->isNotEmpty())
                        <div class="provider-hours-list">
                            @foreach($businessHours as $dayKey => $times)
                                <div class="provider-hours-row">
                                    <span>{{ $dayLabels[mb_strtolower($dayKey)] ?? ucfirst($dayKey) }}</span>
                                    <strong>{{ $times['open'] }} às {{ $times['close'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="provider-hours-empty">
                            <i class="fa-regular fa-calendar"></i>
                            <div>
                                <strong>Horários não informados</strong>
                                <span>Consulte a disponibilidade pelo chat, WhatsApp ou telefone.</span>
                            </div>
                        </div>
                    @endif

                    @if(! $provider->is_claimed && $provider->claiming_enabled)
                        <section class="provider-claim-card" aria-labelledby="provider-claim-title">
                            <h2 id="provider-claim-title">Você é responsável por este perfil?</h2>
                            <p>Reivindique-o gratuitamente para atualizar informações, adicionar fotos e responder avaliações.</p>
                            @if($currentUserPendingClaim)
                                <a href="{{ route('provider.claim.create', $provider) }}" class="provider-claim-button provider-claim-button-pending">
                                    <i class="fa-regular fa-clock"></i> Solicitação em análise
                                </a>
                            @else
                                <a href="{{ route('provider.claim.create', $provider) }}" class="provider-claim-button">
                                    <i class="fa-solid fa-hand-pointer"></i> Reivindicar este perfil
                                </a>
                            @endif
                        </section>
                    @endif

                    @if($providerDirectionsUrl)
                        <section class="provider-public-address" aria-labelledby="provider-public-address-title">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            <div>
                                <strong id="provider-public-address-title">Local de atendimento</strong>
                                <span>{{ $providerPublicAddress }}</span>
                                <a href="{{ $providerDirectionsUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Criar rota até {{ $provider->title }} no Google Maps">
                                    <i class="fa-solid fa-diamond-turn-right" aria-hidden="true"></i> Como chegar
                                </a>
                            </div>
                        </section>
                    @endif

                    <div class="provider-report-button">
                        @include('reports._button-and-modal', ['reportable' => $provider])
                    </div>
                </aside>
            </div>
        </article>
    </div>
</section>

<div class="container provider-profile-lower-frame">
    <div class="provider-profile-lower">
        @include('services._airbnb-gallery', [
            'provider' => $provider,
            'images' => $portfolioImages,
        ])

        @include('reviews._section', ['reviewable' => $provider, 'reviewData' => $reviewData])

        @if($ownerStores->isNotEmpty())
            <section class="provider-owned-stores mt-5" aria-labelledby="provider-owned-stores-title">
                <div class="provider-owned-stores-heading">
                    <div>
                        <span>Vitrine do profissional</span>
                        <h2 id="provider-owned-stores-title">Conheça também {{ $ownerStores->count() === 1 ? 'a loja' : 'as lojas' }} de {{ $provider->title }}</h2>
                    </div>
                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                </div>

                <div class="provider-owned-stores-grid">
                    @foreach($ownerStores as $ownerStore)
                        @php
                            $ownerStoreCover = $ownerStore->banner ?: $ownerStore->logo;
                            $ownerStoreCity = $ownerStore->city ?: $provider->city;
                        @endphp
                        <article class="provider-owned-store-card">
                            <a href="{{ route('store.show', $ownerStore->slug) }}" class="provider-owned-store-cover" aria-label="Abrir a loja {{ $ownerStore->name }}">
                                @if($ownerStoreCover)
                                    <img src="{{ asset($ownerStoreCover) }}" alt="" loading="lazy">
                                @else
                                    <span><i class="fa-solid fa-store"></i></span>
                                @endif
                            </a>
                            <div class="provider-owned-store-body">
                                <span class="provider-owned-store-label"><i class="fa-solid fa-circle-check"></i> Loja do profissional</span>
                                <h3><a href="{{ route('store.show', $ownerStore->slug) }}">{{ $ownerStore->name }}</a></h3>
                                <p>{{ \Illuminate\Support\Str::limit($ownerStore->description ?: 'Conheça os produtos e novidades desta loja.', 110) }}</p>
                                <div class="provider-owned-store-meta">
                                    <span><i class="fa-solid fa-location-dot"></i> {{ $ownerStoreCity }}/SE</span>
                                    <span><i class="fa-solid fa-cube"></i> {{ $ownerStore->active_ads_count }} {{ $ownerStore->active_ads_count === 1 ? 'item' : 'itens' }}</span>
                                </div>
                                <a href="{{ route('store.show', $ownerStore->slug) }}" class="provider-owned-store-open">Ver loja <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($relatedProviders->isNotEmpty())
            <section class="mt-5">
                <h2 class="h4 fw-bold mb-4">Outros profissionais em {{ $provider->city }}</h2>
                <div class="row g-4">
                    @foreach($relatedProviders as $relatedProvider)
                        <div class="col-12 col-md-6 col-xl-3">
                            @include('services._card', ['provider' => $relatedProvider])
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>

<style>
    .provider-profile-page {
        background: var(--background);
        color: var(--foreground);
        transition: background-color .2s ease, color .2s ease;
    }

    .provider-cover-frame,
    .provider-profile-container,
    .provider-profile-lower-frame {
        width: 100%;
        max-width: 1080px !important;
    }

    .provider-public-address {
        display: flex;
        gap: .75rem;
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--muted-bg);
    }

    .provider-public-address div,
    .provider-public-address strong,
    .provider-public-address span {
        display: block;
    }

    .provider-owned-stores {
        padding: 1.25rem;
        border: 1px solid var(--border);
        border-radius: 18px;
    }

    .provider-owned-stores-grid {
        display: grid;
        gap: 1rem;
    }

    .provider-owned-store-card {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 1rem;
    }

    .provider-owned-store-cover img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
    }

    .provider-cover {
        position: relative;
        height: clamp(125px, 11vw, 175px);
        overflow: hidden;
        border-radius: 0;
    }

    .provider-cover-frame {
        padding-top: 0;
        margin-top: 0;
    }

    .provider-cover > img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 42%;
    }

    .provider-cover-shade {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, .12), rgba(15, 23, 42, .26));
    }

    .provider-cover-content {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-start;
        padding: 1rem;
    }

    .provider-back-button {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        min-height: 42px;
        padding: .55rem 1rem;
        color: #0f172a;
        background: #fff;
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: 999px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .16);
        font-weight: 700;
        text-decoration: none;
    }

    .provider-profile-container {
        position: relative;
        margin-top: -28px;
        padding-bottom: 0;
        z-index: 2;
    }

    .provider-profile-card {
        background: var(--card);
        color: var(--foreground);
        border: 1px solid var(--border);
        border-bottom: 0;
        border-radius: 0;
        box-shadow: none;
        padding: clamp(1.5rem, 3vw, 2.75rem);
    }

    .provider-profile-header {
        display: grid;
        grid-template-columns: minmax(180px, 199px) minmax(0, 1fr) 180px;
        gap: clamp(1.5rem, 2.8vw, 2.5rem);
        align-items: center;
    }

    .provider-avatar {
        width: 100%;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: -62px;
        overflow: hidden;
        color: var(--primary);
        background: var(--muted-bg);
        border: 5px solid var(--card);
        border-radius: 25px;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .16);
        font-size: 4rem;
        font-weight: 800;
    }

    .provider-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .provider-profile-identity {
        min-width: 0;
    }

    .provider-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-bottom: 1.1rem;
    }

    .provider-category-badge,
    .provider-verified-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        min-height: 30px;
        padding: .35rem .8rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 800;
        line-height: 1;
    }

    .provider-category-badge {
        color: var(--primary);
        background: color-mix(in srgb, var(--primary) 10%, transparent);
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .provider-verified-badge {
        color: #118a4e;
        background: rgba(22, 163, 74, .1);
        border: 1px solid rgba(22, 163, 74, .18);
    }

    .provider-profile-identity h1 {
        max-width: 850px;
        margin: 0 0 .75rem;
        color: var(--foreground);
        font-size: clamp(1.4rem, 2.2vw, 1.85rem);
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1.15;
        overflow-wrap: anywhere;
    }

    .provider-profile-facts {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .8rem 1.5rem;
        color: var(--muted);
    }

    .provider-rating,
    .provider-location {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--muted);
        font-size: .96rem;
        text-decoration: none;
    }

    .provider-stars {
        display: inline-flex;
        gap: .12rem;
        color: #ffb000;
    }

    .provider-rating strong {
        color: var(--foreground);
    }

    .provider-location i {
        color: var(--primary);
    }

    .provider-action-list {
        display: grid;
        gap: .6rem;
    }

    .provider-action {
        width: 100%;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: .6rem;
        padding: .55rem 1rem;
        border: 1px solid var(--border);
        border-radius: 999px;
        font: inherit;
        font-size: .88rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease;
    }

    .provider-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 18px rgba(37, 99, 235, .15);
    }

    .provider-action-primary {
        color: #fff;
        background: var(--primary);
        border-color: var(--primary);
    }

    .provider-action-primary:hover {
        color: #fff;
        background: color-mix(in srgb, var(--primary) 88%, #000);
    }

    .provider-action-secondary {
        color: var(--foreground);
        background: var(--card);
    }

    .provider-profile-overview {
        display: grid;
        grid-template-columns: minmax(0, 1.75fr) minmax(290px, .8fr);
        gap: clamp(2rem, 5vw, 4.5rem);
        margin-top: 2.25rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border);
    }

    .provider-section-eyebrow {
        display: block;
        margin-bottom: .55rem;
        color: var(--primary);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .provider-about h2,
    .provider-hours h2 {
        margin: 0 0 1.3rem;
        color: var(--foreground);
        font-size: clamp(1.35rem, 2vw, 1.8rem);
        font-weight: 800;
        letter-spacing: -.025em;
    }

    .provider-about > p {
        max-width: 850px;
        margin: 0;
        color: var(--foreground);
        font-size: 1.02rem;
        line-height: 1.8;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .provider-profile-meta-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1.25rem;
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        color: var(--muted);
        border-top: 1px solid var(--border);
        font-size: .86rem;
    }

    .provider-profile-meta-footer > div:first-child {
        display: grid;
        gap: .12rem;
    }

    .provider-profile-meta-footer strong {
        color: var(--foreground);
    }

    .provider-profile-meta-footer small {
        color: var(--primary);
    }

    .provider-social-links {
        display: flex;
        gap: .5rem;
    }

    .provider-social-links a {
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        padding: 0 .75rem;
        color: var(--primary);
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
    }

    .provider-profile-views {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .provider-hours {
        min-width: 0;
    }

    .provider-hours h2 {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .provider-hours-list {
        display: grid;
        gap: .75rem;
    }

    .provider-hours-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        color: var(--foreground);
        font-size: .92rem;
    }

    .provider-hours-row strong {
        text-align: right;
    }

    .provider-hours-empty {
        display: flex;
        align-items: flex-start;
        gap: .8rem;
        padding: 1rem;
        color: var(--muted);
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    .provider-hours-empty > i {
        margin-top: .15rem;
        color: var(--primary);
        font-size: 1.15rem;
    }

    .provider-hours-empty div {
        display: grid;
        gap: .25rem;
    }

    .provider-hours-empty strong {
        color: var(--foreground);
        font-size: .92rem;
    }

    .provider-hours-empty span {
        font-size: .82rem;
        line-height: 1.45;
    }

    .provider-report-button {
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .provider-profile-lower {
        padding: 0 clamp(1.5rem, 3vw, 2.75rem) 3rem;
        color: var(--foreground);
        background: var(--card);
        border: 1px solid var(--border);
        border-top: 0;
        border-radius: 0;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
    }

    .provider-profile-lower .provider-airbnb-card {
        margin: 0 !important;
        padding: 2rem 0 !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .provider-profile-lower .reviews-section {
        margin-top: 0 !important;
        padding-top: 2rem;
        border-top: 1px solid var(--border);
    }

    .provider-profile-lower .reviews-summary-card {
        padding: 0 !important;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .provider-profile-lower .reviews-section > .d-flex.my-4 {
        margin: 1.5rem 0 !important;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border);
    }

    .provider-profile-lower .review-card,
    .provider-profile-lower .reviews-list > .text-center {
        margin-bottom: 0 !important;
        padding: 1.5rem 0 !important;
        background: transparent !important;
        border: 0 !important;
        border-top: 1px solid var(--border) !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    @media (max-width: 1199.98px) {
        .provider-profile-header {
            grid-template-columns: 184px minmax(0, 1fr);
        }

        .provider-action-list {
            grid-column: 1 / -1;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .provider-avatar {
            margin-top: -48px;
        }
    }

    @media (max-width: 991.98px) {
        .provider-profile-container {
            margin-top: -20px;
        }

        .provider-profile-header {
            grid-template-columns: 170px minmax(0, 1fr);
        }

        .provider-avatar {
            margin-top: -40px;
            border-radius: 22px;
        }

        .provider-profile-overview {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }

    @media (max-width: 767.98px) {
        .provider-cover {
            height: 112px;
        }

        .provider-cover-content {
            padding: .65rem;
        }

        .provider-cover-frame {
            padding-top: 0;
        }

        .provider-back-button {
            min-height: 38px;
            padding: .45rem .8rem;
            font-size: .86rem;
        }

        .provider-profile-container {
            padding-right: .75rem;
            padding-left: .75rem;
        }

        .provider-profile-card {
            padding: 1.25rem;
            border-radius: 0;
        }

        .provider-profile-header {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }

        .provider-avatar {
            width: 132px;
            margin: -58px auto 0;
            border-width: 4px;
            border-radius: 20px;
        }

        .provider-badges,
        .provider-profile-facts {
            justify-content: center;
        }

        .provider-profile-identity h1 {
            font-size: clamp(1.25rem, 5.5vw, 1.55rem);
        }

        .provider-action-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .5rem;
        }

        .provider-action {
            min-height: 40px;
            justify-content: center;
            padding: .45rem .75rem;
            font-size: .82rem;
        }

        .provider-profile-overview {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            text-align: left;
        }

        .provider-profile-meta-footer {
            align-items: flex-start;
        }

        .provider-profile-lower {
            padding-right: 1.25rem;
            padding-left: 1.25rem;
            border-radius: 0;
        }
    }

    @media (max-width: 399.98px) {
        .provider-action-list {
            grid-template-columns: 1fr;
        }

        .provider-profile-facts {
            align-items: flex-start;
            flex-direction: column;
        }

        .provider-rating,
        .provider-location {
            width: 100%;
            justify-content: center;
        }
    }
</style>

@push('scripts')
@if($reviewData['count'] >= 5)
@php
    $providerReviewSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfessionalService',
        'name' => $provider->title,
        'url' => route('provider.show', $provider->slug),
        'image' => asset($avatarImage ?: $coverImage),
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => $provider->city,
            'addressRegion' => 'SE',
            'addressCountry' => 'BR',
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => $reviewData['average'],
            'reviewCount' => $reviewData['count'],
            'bestRating' => 5,
            'worstRating' => 1,
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($providerReviewSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush
@endsection
