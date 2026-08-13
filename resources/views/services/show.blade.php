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

    $currentUser = auth()->user();
    $isOwnerOrAdmin = $currentUser && ($currentUser->id === $provider->user_id || $currentUser->role === 'admin');
    $ownerHasPaidPlan = $provider->user?->hasPaidPlan() ?? false;
    $canEditCover = $isOwnerOrAdmin && $ownerHasPaidPlan;
@endphp

<section class="provider-profile-page">
    @if(session('claim_success'))
        <div class="container pt-3">
            <div class="alert alert-success rounded-3 mb-0">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('claim_success') }}
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div class="container pt-3">
            <div class="alert alert-warning rounded-3 mb-0 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="container pt-3">
            <div class="alert alert-danger rounded-3 mb-0 shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            </div>
        </div>
    @endif
    <div class="container provider-cover-frame">
        <div class="provider-cover" id="providerCoverContainer">
            <img src="{{ asset($coverImage) }}" id="providerCoverImg" alt="Capa de {{ $provider->city }}" style="object-position: center {{ $provider->cover_position_y ?? 50 }}%;">
            <div class="provider-cover-shade"></div>
            <div class="provider-cover-content">
                <a href="{{ route('module.services') }}" class="provider-back-button">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>

                <div class="provider-cover-actions">
                    <button type="button" class="btn-edit-cover shadow-sm" onclick="handleEditCoverBtnClick()" title="Editar ou ajustar foto de capa">
                        <i class="fa-solid fa-camera me-1"></i> Editar capa
                    </button>
                </div>
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
                        @if(! $provider->is_claimed && $provider->claiming_enabled)
                            <span class="provider-category-badge" style="background: var(--warning-bg, #fff3cd); color: var(--warning-text, #856404); border-color: var(--warning-border, #ffeeba);">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Perfil não reivindicado
                            </span>
                        @endif
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
                    @if($provider->booking_enabled && \App\Support\ServiceBookingCatalog::eligible($provider))
                        <a href="{{ auth()->check() ? route('service-booking.book', $provider) : route('login', ['redirect' => route('service-booking.book', $provider)]) }}" class="provider-action provider-action-primary">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>Agendar horário</span>
                        </a>
                    @endif
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
                        @if($provider->is_claimed)
                            <div>
                                <span>Responsável pelo perfil</span>
                                <strong>{{ $provider->user->name }}</strong>
                                @if($provider->user->username && !str_contains($provider->user->email, '@cliente.conectadoemsergipe.com.br'))
                                    <small>{{ '@' . $provider->user->username }}</small>
                                @endif
                            </div>
                        @endif

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
        height: clamp(240px, 20vw, 320px);
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
        margin-top: -140px;
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
        border: 2px solid var(--card);
        border-radius: 18px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .12);
        font-size: 4rem;
        font-weight: 800;
    }

    .provider-cover-actions {
        margin-left: auto;
        display: flex;
        align-items: center;
    }

    .btn-edit-cover {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: rgba(15, 23, 42, 0.78);
        backdrop-filter: blur(8px);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 99px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-edit-cover:hover {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(255, 255, 255, 0.6);
        transform: translateY(-1px);
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
            height: 220px;
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
            margin-top: -105px;
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
            border-width: 2px;
            border-radius: 16px;
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

<!-- Modal Alerta de Upgrade de Plano (Edição de Capa) -->
<div class="modal fade" id="coverUpgradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-primary bg-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-crown me-2"></i> Recurso do Plano Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                    <i class="fa-solid fa-camera fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Personalização da Capa do Perfil</h4>
                <p class="text-muted small mb-4">
                    A alteração da foto de capa e o <strong>ajuste de posição vertical (estilo Facebook)</strong> estão disponíveis a partir do <strong>Plano Pago (Start, PRO ou Ouro)</strong>.
                </p>
                <div class="alert alert-light border rounded-3 text-start mb-4 small">
                    <div class="mb-1"><i class="fa-solid fa-circle-check text-success me-2"></i> Adicione foto de capa personalizada em alta resolução</div>
                    <div class="mb-1"><i class="fa-solid fa-circle-check text-success me-2"></i> Ajuste livremente a posição (mover para cima e para baixo)</div>
                    <div><i class="fa-solid fa-circle-check text-success me-2"></i> Ganhe mais visibilidade e destaque na busca e página inicial</div>
                </div>
                <a href="{{ route('page.plans') }}" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-sm">
                    <i class="fa-solid fa-rocket me-2"></i> Conhecer Planos Pagos
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar e Reposicionar Capa -->
<div class="modal fade" id="editCoverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-sliders text-primary me-2"></i> Ajustar Posição da Capa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label fw-semibold mb-2">Mover imagem para cima ou para baixo (estilo Facebook)</label>
                <div class="cover-reposition-box rounded-3 overflow-hidden border mb-3 position-relative" style="height: 220px; background: #000; cursor: ns-resize;">
                    <img id="coverPreviewImg" src="{{ asset($coverImage) }}" alt="Prévia da capa" style="width: 100%; height: 100%; object-fit: cover; object-position: center {{ $provider->cover_position_y ?? 50 }}%;">
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 text-center text-white bg-dark bg-opacity-75 small">
                        <i class="fa-solid fa-arrows-up-down me-1"></i> Posição Vertical Atual: <strong id="positionYLabel">{{ $provider->cover_position_y ?? 50 }}</strong>%
                    </div>
                </div>

                <div class="mb-4">
                    <input type="range" class="form-range" id="coverRangeY" min="0" max="100" value="{{ $provider->cover_position_y ?? 50 }}" oninput="updateCoverPositionY(this.value)">
                    <div class="d-flex justify-content-between text-muted small fw-semibold">
                        <span><i class="fa-solid fa-arrow-up"></i> Topo (0%)</span>
                        <span>Centro (50%)</span>
                        <span>Base (100%) <i class="fa-solid fa-arrow-down"></i></span>
                    </div>
                </div>

                @if($isOwnerOrAdmin)
                <div class="border-top pt-3 mt-3">
                    @php
                        $ownerPlan = $provider->user?->normalizedSubscriptionPlan() ?? 'free';
                        $monthlyChanges = $provider->monthly_cover_changes;
                    @endphp
                    @if($ownerPlan === 'start')
                        <div class="alert {{ $monthlyChanges >= 2 ? 'alert-warning' : 'alert-info' }} rounded-3 small mb-3">
                            <div class="fw-bold mb-1">
                                <i class="fa-solid fa-crown text-warning me-1"></i> Plano Start — Limite Mensal de Capas
                            </div>
                            @if($monthlyChanges >= 2)
                                <div>⚠️ <strong>Atenção:</strong> Você realizou <strong>{{ $monthlyChanges }} de 3</strong> alterações de capa permitidas para este mês. Resta apenas <strong>{{ max(0, 3 - $monthlyChanges) }}</strong> alteração até a renovação no próximo mês.</div>
                            @else
                                <div>No Plano Start é permitida a alteração de foto de capa até <strong>3 vezes por mês</strong>. (Realizadas este mês: {{ $monthlyChanges }} de 3).</div>
                            @endif
                        </div>
                    @endif
                    <label for="coverUploadInput" class="form-label fw-semibold">Trocar Foto de Capa (Envie um novo arquivo se desejar)</label>
                    <form action="{{ route('ad.update', $provider->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="title" value="{{ $provider->title }}">
                        <input type="hidden" name="description" value="{{ $provider->description }}">
                        <input type="hidden" name="city" value="{{ $provider->city }}">
                        <input type="hidden" name="module" value="services">
                        <div class="input-group">
                            <input type="file" class="form-control rounded-start-3" id="coverUploadInput" name="banner" accept="image/*">
                            <button type="submit" class="btn btn-outline-primary rounded-end-3 fw-bold">
                                <i class="fa-solid fa-upload me-1"></i> Enviar Nova Imagem
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="saveCoverPositionAjax()">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Posição
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const canEditCover = @json($canEditCover);

    function handleEditCoverBtnClick() {
        if (!canEditCover) {
            const modal = new bootstrap.Modal(document.getElementById('coverUpgradeModal'));
            modal.show();
        } else {
            const modal = new bootstrap.Modal(document.getElementById('editCoverModal'));
            modal.show();
        }
    }

    function updateCoverPositionY(val) {
        const preview = document.getElementById('coverPreviewImg');
        const mainImg = document.getElementById('providerCoverImg');
        const label = document.getElementById('positionYLabel');
        if (preview) preview.style.objectPosition = `center ${val}%`;
        if (mainImg) mainImg.style.objectPosition = `center ${val}%`;
        if (label) label.textContent = val;
    }

    function saveCoverPositionAjax() {
        const val = document.getElementById('coverRangeY')?.value || 50;
        fetch("{{ route('ad.cover_position', $provider->id) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ cover_position_y: parseInt(val) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('editCoverModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                alert(data.message || 'Posição da capa salva com sucesso!');
            } else {
                alert(data.message || 'Ocorreu um erro ao salvar a posição.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Não foi possível salvar a posição da capa.');
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const box = document.querySelector('.cover-reposition-box');
        const range = document.getElementById('coverRangeY');
        if (!box || !range) return;

        let isDragging = false;
        let startY = 0;
        let startVal = 50;

        box.addEventListener('mousedown', (e) => {
            isDragging = true;
            startY = e.clientY;
            startVal = parseInt(range.value, 10);
            e.preventDefault();
        });

        window.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const deltaY = e.clientY - startY;
            let newVal = startVal + Math.round((deltaY / box.clientHeight) * 100);
            newVal = Math.max(0, Math.min(100, newVal));
            range.value = newVal;
            updateCoverPositionY(newVal);
        });

        window.addEventListener('mouseup', () => {
            isDragging = false;
        });

        box.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                isDragging = true;
                startY = e.touches[0].clientY;
                startVal = parseInt(range.value, 10);
            }
        });

        window.addEventListener('touchmove', (e) => {
            if (!isDragging || e.touches.length !== 1) return;
            const deltaY = e.touches[0].clientY - startY;
            let newVal = startVal + Math.round((deltaY / box.clientHeight) * 100);
            newVal = Math.max(0, Math.min(100, newVal));
            range.value = newVal;
            updateCoverPositionY(newVal);
        });

        window.addEventListener('touchend', () => {
            isDragging = false;
        });
    });
</script>
@endpush
@endsection
