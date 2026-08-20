@extends('layouts.app')

@section('title', $provider->title . ' - Profissional Liberal em Sergipe')

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $provider->title . ' - Profissional Liberal em Sergipe',
        'socialDescription' => \Illuminate\Support\Str::limit(strip_tags($provider->description), 160),
        'socialUrl' => route('provider.show', $provider->slug),
        'socialImage' => asset($provider->banner ?: $provider->logo ?: $provider->mainImage?->image_path ?: \App\Support\CityImage::for($provider->city)),
        'socialType' => 'profile',
    ])
@endpush

@section('content')
@php
    $details = (array) data_get($provider->technical_specs, 'liberal_profile', []);
    $coverImage = $provider->banner ?: \App\Support\CityImage::for($provider->city);
    $avatarImage = $provider->logo ?: $provider->mainImage?->image_path;
    $portfolioImages = $provider->images
        ->sortByDesc('is_main')
        ->pluck('image_path')
        ->prepend($provider->card_image)
        ->filter()
        ->unique()
        ->take(5)
        ->values();
    $specialties = collect($details['specialties'] ?? [])
        ->filter(fn ($item) => filled(is_array($item) ? ($item['title'] ?? null) : $item))
        ->values();
    $credentialVerified = (bool) ($details['credential_verified'] ?? false);
    $credentialLabel = $details['credential'] ?? null;
    $education = $details['education'] ?? null;
    $educationInstitution = $details['education_institution'] ?? null;
    $headline = $details['headline'] ?? 'Atendimento profissional com experiência, ética e compromisso.';
    $serviceArea = $details['service_area'] ?? ('Atendimento em ' . $provider->city . ' e região.');
    $publicPhone = $provider->publicPhone();
    $whatsapp = preg_replace('/\D+/', '', $provider->publicWhatsapp() ?? '');
    $whatsappNumber = $whatsapp ? (str_starts_with($whatsapp, '55') ? $whatsapp : '55' . $whatsapp) : null;
    $whatsappMessage = urlencode("Olá, encontrei seu perfil profissional no Conectado em Sergipe: {$provider->title}");
    $businessHours = collect($provider->business_hours ?? [])
        ->filter(fn ($times) => is_array($times) && isset($times['open'], $times['close']));
    $dayLabels = [
        'monday' => 'Segunda', 'segunda' => 'Segunda',
        'tuesday' => 'Terça', 'terca' => 'Terça', 'terça' => 'Terça',
        'wednesday' => 'Quarta', 'quarta' => 'Quarta',
        'thursday' => 'Quinta', 'quinta' => 'Quinta',
        'friday' => 'Sexta', 'sexta' => 'Sexta',
        'saturday' => 'Sábado', 'sabado' => 'Sábado', 'sábado' => 'Sábado',
        'sunday' => 'Domingo', 'domingo' => 'Domingo',
    ];
    $providerAddress = trim((string) $provider->public_address);
    $directionsUrl = $providerAddress !== ''
        ? 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode(implode(', ', array_filter([$providerAddress, $provider->city, 'SE', 'Brasil'])))
        : null;
    $currentUser = auth()->user();
    $isOwnerOrAdmin = $currentUser && ($currentUser->id === $provider->user_id || $currentUser->role === 'admin');
@endphp

<main class="liberal-profile-page">
    <div class="container liberal-profile-shell">
        <section class="liberal-profile-hero" aria-labelledby="liberal-profile-title">
            <div class="liberal-profile-cover">
                <img src="{{ asset($coverImage) }}" alt="Capa do perfil de {{ $provider->title }}" loading="eager">
                <span class="liberal-profile-cover-pattern" aria-hidden="true"></span>
                <a href="{{ route('module.services', ['profile_kind' => 'liberal_professional']) }}" class="liberal-profile-back">
                    <i class="fa-solid fa-arrow-left"></i> Voltar para profissionais liberais
                </a>
                @if($isOwnerOrAdmin)
                    <a href="{{ route('ad.edit', $provider) }}" class="liberal-profile-edit"><i class="fa-solid fa-pen"></i> Editar perfil</a>
                @endif
            </div>

            <div class="liberal-profile-identity">
                <div class="liberal-profile-avatar">
                    @if($avatarImage)
                        <img src="{{ asset($avatarImage) }}" alt="{{ $provider->title }}">
                    @else
                        <span><i class="fa-solid fa-user-graduate"></i></span>
                    @endif
                    <i class="liberal-profile-online" aria-label="Perfil ativo"></i>
                </div>

                <div class="liberal-profile-copy">
                    <div class="liberal-profile-badges">
                        <span class="is-category">{{ $provider->display_category ?? 'Profissional liberal' }}</span>
                        <span class="{{ $credentialVerified ? 'is-verified' : 'is-informed' }}">
                            <i class="fa-solid {{ $credentialVerified ? 'fa-circle-check' : 'fa-id-card' }}"></i>
                            {{ $credentialVerified ? 'Credencial verificada' : 'Credencial informada' }}
                        </span>
                    </div>
                    <h1 id="liberal-profile-title">{{ $provider->title }}</h1>
                    <div class="liberal-profile-facts">
                        <span><i class="fa-solid fa-location-dot"></i>{{ $provider->city }}, Sergipe</span>
                        <a href="#avaliacoes">
                            <span class="liberal-profile-stars">★★★★★</span>
                            <strong>{{ $reviewData['count'] ? number_format($reviewData['average'], 1, ',', '.') : 'Novo' }}</strong>
                            <small>({{ $reviewData['count'] }} {{ $reviewData['count'] === 1 ? 'avaliação' : 'avaliações' }})</small>
                        </a>
                        <span><i class="fa-regular fa-eye"></i>{{ $provider->views }} visualizações</span>
                    </div>
                </div>

                <nav class="liberal-profile-actions" aria-label="Contato profissional">
                    @if($provider->is_claimed)
                        <a href="{{ route('chat.index', ['with' => $provider->user_id]) }}" class="is-chat"><i class="fa-regular fa-paper-plane"></i>Iniciar chat</a>
                    @endif
                    @if($whatsappNumber)
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener" class="is-whatsapp"><i class="fa-brands fa-whatsapp"></i>WhatsApp</a>
                    @endif
                    @if(! $provider->is_claimed && $provider->claiming_enabled)
                        <a href="{{ route('provider.claim.create', $provider) }}" class="is-chat"><i class="fa-solid fa-user-check"></i>Reivindicar perfil</a>
                    @endif
                </nav>
            </div>
        </section>

        <div class="liberal-profile-layout">
            <div class="liberal-profile-main">
                <section class="liberal-profile-panel liberal-profile-about">
                    <span class="liberal-profile-eyebrow">Sobre o profissional</span>
                    <h2>{{ $headline }}</h2>
                    <p>{{ $provider->description }}</p>

                    @if($specialties->isNotEmpty())
                        <div class="liberal-profile-specialties">
                            @foreach($specialties as $index => $specialty)
                                @php
                                    $specialtyData = is_array($specialty) ? $specialty : ['title' => $specialty];
                                @endphp
                                <article>
                                    <i class="fa-solid {{ $index % 2 === 0 ? 'fa-scale-balanced' : 'fa-briefcase' }}"></i>
                                    <div>
                                        <h3>{{ $specialtyData['title'] }}</h3>
                                        @if(!empty($specialtyData['description']))<p>{{ $specialtyData['description'] }}</p>@endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="liberal-profile-panel">
                    <span class="liberal-profile-eyebrow">Verificação profissional</span>
                    <h2>Documentação e registros</h2>
                    <div class="liberal-profile-documents">
                        <article>
                            <i class="fa-solid fa-id-card"></i>
                            <div>
                                <h3>{{ $credentialLabel ?: 'Registro profissional não informado' }}</h3>
                                <p>{{ $details['credential_issuer'] ?? 'A informação do conselho profissional deve ser confirmada diretamente com o profissional.' }}</p>
                                @if(!empty($details['credential_url']))
                                    <a href="{{ $details['credential_url'] }}" target="_blank" rel="noopener noreferrer">Consultar registro oficial <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                @endif
                            </div>
                            <span class="{{ $credentialVerified ? 'is-valid' : '' }}">{{ $credentialVerified ? 'Verificado' : 'Informado' }}</span>
                        </article>
                        <article>
                            <i class="fa-solid fa-graduation-cap"></i>
                            <div>
                                <h3>{{ $education ?: 'Formação não informada' }}</h3>
                                @if($educationInstitution)<p>{{ $educationInstitution }}</p>@endif
                            </div>
                        </article>
                    </div>
                </section>

                @if($portfolioImages->isNotEmpty())
                    <section class="liberal-profile-panel">
                        <span class="liberal-profile-eyebrow">Galeria</span>
                        <h2>Escritório e estrutura</h2>
                        <div class="liberal-profile-gallery">
                            @foreach($portfolioImages as $image)
                                <img src="{{ asset($image) }}" alt="Galeria de {{ $provider->title }}" loading="lazy">
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="liberal-profile-sidebar">
                <section class="liberal-profile-panel liberal-profile-availability">
                    <i class="fa-regular fa-calendar-check"></i>
                    <h2>Disponibilidade</h2>
                    <p>{{ $serviceArea }}</p>
                    @if($businessHours->isNotEmpty())
                        <div>
                            @foreach($businessHours as $day => $times)
                                <span><b>{{ $dayLabels[mb_strtolower($day)] ?? ucfirst($day) }}</b><strong>{{ $times['open'] }}–{{ $times['close'] }}</strong></span>
                            @endforeach
                        </div>
                    @else
                        <div><span><b>Horários</b><strong>Sob consulta</strong></span></div>
                    @endif
                    @if($provider->booking_enabled && \App\Support\ServiceBookingCatalog::eligible($provider))
                        <a href="{{ auth()->check() ? route('service-booking.book', $provider) : route('login', ['redirect' => route('service-booking.book', $provider)]) }}">Agendar atendimento</a>
                    @elseif($whatsappNumber)
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener">Consultar disponibilidade</a>
                    @endif
                </section>

                <section class="liberal-profile-panel liberal-profile-location">
                    <h2>Onde estamos</h2>
                    @if($providerAddress)
                        <p><i class="fa-solid fa-location-dot"></i><span><b>Endereço</b>{{ $providerAddress }} — {{ $provider->city }}/SE</span></p>
                    @endif
                    @if($publicPhone)
                        <p><i class="fa-solid fa-phone"></i><span><b>Telefone</b>{{ $publicPhone }}</span></p>
                    @endif
                    @if($directionsUrl)
                        <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-diamond-turn-right"></i>Abrir no Google Maps</a>
                    @endif
                </section>

                <section class="liberal-profile-trust {{ $credentialVerified ? 'is-verified' : '' }}">
                    <i class="fa-solid fa-user-shield"></i>
                    <h2>{{ $credentialVerified ? 'Perfil verificado pelo Conectado' : 'Dados profissionais informados' }}</h2>
                    <p>{{ $credentialVerified ? 'A documentação profissional apresentada foi analisada pela plataforma.' : 'Confirme o registro no conselho profissional antes da contratação.' }}</p>
                </section>

                <div class="liberal-profile-report">@include('reports._button-and-modal', ['reportable' => $provider])</div>
            </aside>
        </div>

        <section class="liberal-profile-reviews" id="avaliacoes">
            @include('reviews._section', ['reviewable' => $provider, 'reviewData' => $reviewData])
        </section>
    </div>
</main>
@endsection

@push('styles')
<style>
    .liberal-profile-page { padding: 36px 0 70px; color: var(--foreground); background: var(--background); }
    .liberal-profile-shell { max-width: 1180px; }
    .liberal-profile-hero { overflow: hidden; margin-bottom: 34px; border: 1px solid var(--border); border-radius: 28px; background: var(--card); box-shadow: 0 18px 45px rgba(15, 23, 42, .08); }
    .liberal-profile-cover { position: relative; height: 270px; overflow: hidden; background: linear-gradient(135deg, #173c9b, #075be8); }
    .liberal-profile-cover > img { width: 100%; height: 100%; object-fit: cover; opacity: .18; }
    .liberal-profile-cover-pattern { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(22, 64, 170, .84), rgba(7, 91, 232, .9)); }
    .liberal-profile-back, .liberal-profile-edit { position: absolute; z-index: 2; top: 26px; display: inline-flex; align-items: center; gap: 8px; min-height: 38px; padding: 8px 15px; border-radius: 9px; color: #fff; background: rgba(255, 255, 255, .16); font-size: .74rem; font-weight: 800; text-decoration: none; backdrop-filter: blur(8px); }
    .liberal-profile-back { left: 28px; }
    .liberal-profile-edit { right: 28px; }
    .liberal-profile-back:hover, .liberal-profile-edit:hover { color: #fff; background: rgba(255, 255, 255, .24); }
    .liberal-profile-identity { position: relative; display: grid; grid-template-columns: 190px minmax(0, 1fr) auto; gap: 25px; align-items: end; min-height: 165px; padding: 24px 42px 28px; }
    .liberal-profile-avatar { position: relative; width: 176px; height: 176px; margin-top: -112px; padding: 7px; border-radius: 34px; background: var(--card); box-shadow: 0 15px 36px rgba(15, 23, 42, .2); }
    .liberal-profile-avatar img, .liberal-profile-avatar span { width: 100%; height: 100%; display: grid; place-items: center; object-fit: cover; border-radius: 28px; background: #0f172a; color: #fff; font-size: 3rem; }
    .liberal-profile-online { position: absolute; right: 5px; bottom: 11px; width: 24px; height: 24px; border: 5px solid var(--card); border-radius: 50%; background: #22c55e; }
    .liberal-profile-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 9px; }
    .liberal-profile-badges span { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 999px; font-size: .61rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
    .liberal-profile-badges .is-category { color: #fff; background: #2463eb; }
    .liberal-profile-badges .is-verified { color: #07814a; background: #dcfce7; }
    .liberal-profile-badges .is-informed { color: #975a00; background: #fff4cc; }
    .liberal-profile-copy h1 { margin: 0 0 10px; font-size: clamp(1.7rem, 3.2vw, 2.55rem); font-weight: 900; letter-spacing: -.04em; }
    .liberal-profile-facts { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 20px; color: var(--muted-foreground); font-size: .72rem; font-weight: 700; }
    .liberal-profile-facts span, .liberal-profile-facts a { display: inline-flex; align-items: center; gap: 6px; color: inherit; text-decoration: none; }
    .liberal-profile-facts i { color: #2463eb; }
    .liberal-profile-stars { color: #f5b800 !important; letter-spacing: -1px; }
    .liberal-profile-actions { display: grid; gap: 10px; min-width: 175px; }
    .liberal-profile-actions a { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 10px 18px; border-radius: 14px; color: #fff; font-size: .76rem; font-weight: 900; text-decoration: none; box-shadow: 0 10px 22px rgba(37, 99, 235, .2); }
    .liberal-profile-actions .is-chat { background: #2563eb; }
    .liberal-profile-actions .is-whatsapp { background: #079968; }
    .liberal-profile-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(280px, .92fr); gap: 28px; align-items: start; }
    .liberal-profile-main, .liberal-profile-sidebar { display: grid; gap: 28px; }
    .liberal-profile-panel { padding: clamp(24px, 4vw, 46px); border: 1px solid var(--border); border-radius: 28px; background: var(--card); box-shadow: 0 14px 38px rgba(15, 23, 42, .055); }
    .liberal-profile-eyebrow { display: block; margin-bottom: 13px; color: #2563eb; font-size: .65rem; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
    .liberal-profile-panel h2 { margin: 0 0 16px; font-size: clamp(1.3rem, 2.3vw, 1.75rem); font-weight: 900; letter-spacing: -.03em; }
    .liberal-profile-about > p { color: var(--muted-foreground); line-height: 1.7; }
    .liberal-profile-specialties { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 26px; }
    .liberal-profile-specialties article { display: flex; gap: 13px; min-height: 118px; padding: 20px; border: 1px solid #d8e8ff; border-radius: 18px; background: #eff6ff; }
    .liberal-profile-specialties article:nth-child(even) { border-color: #cef2df; background: #ecfdf5; }
    .liberal-profile-specialties i { width: 34px; height: 34px; display: grid; flex: 0 0 34px; place-items: center; border-radius: 10px; color: #2563eb; background: #fff; }
    .liberal-profile-specialties article:nth-child(even) i { color: #059669; }
    .liberal-profile-specialties h3, .liberal-profile-documents h3 { margin: 0 0 5px; font-size: .86rem; font-weight: 900; }
    .liberal-profile-specialties p, .liberal-profile-documents p { margin: 0; color: #64748b; font-size: .71rem; line-height: 1.45; }
    .liberal-profile-documents { display: grid; gap: 16px; }
    .liberal-profile-documents article { display: grid; grid-template-columns: 44px minmax(0, 1fr) auto; gap: 14px; align-items: start; padding: 20px; border-radius: 17px; background: var(--muted); }
    .liberal-profile-documents article > i { width: 40px; height: 40px; display: grid; place-items: center; border-radius: 12px; color: #2563eb; background: var(--card); }
    .liberal-profile-documents a { display: inline-block; margin-top: 8px; color: #2563eb; font-size: .68rem; font-weight: 800; text-decoration: none; }
    .liberal-profile-documents article > span { padding: 5px 8px; border-radius: 999px; color: #975a00; background: #fff4cc; font-size: .55rem; font-weight: 900; text-transform: uppercase; }
    .liberal-profile-documents article > span.is-valid { color: #07814a; background: #dcfce7; }
    .liberal-profile-gallery { display: grid; grid-template-columns: 1.5fr repeat(2, 1fr); gap: 14px; }
    .liberal-profile-gallery img { width: 100%; height: 145px; object-fit: cover; border-radius: 18px; }
    .liberal-profile-gallery img:first-child { height: 205px; grid-row: span 2; }
    .liberal-profile-availability { text-align: center; }
    .liberal-profile-availability > i { width: 58px; height: 58px; display: grid; place-items: center; margin: 0 auto 18px; border-radius: 17px; color: #2563eb; background: #eff6ff; font-size: 1.4rem; }
    .liberal-profile-availability > p { color: var(--muted-foreground); font-size: .73rem; line-height: 1.5; }
    .liberal-profile-availability > div { display: grid; gap: 9px; margin: 19px 0; }
    .liberal-profile-availability > div span { display: flex; justify-content: space-between; gap: 10px; padding: 12px; border-radius: 11px; background: var(--muted); font-size: .68rem; }
    .liberal-profile-availability > div strong { color: #2563eb; }
    .liberal-profile-availability > a { display: flex; align-items: center; justify-content: center; min-height: 45px; border: 2px solid #2563eb; border-radius: 14px; color: #2563eb; font-size: .74rem; font-weight: 900; text-decoration: none; }
    .liberal-profile-location { color: #fff; background: #0d1a33; }
    .liberal-profile-location h2 { color: #fff; }
    .liberal-profile-location p { display: flex; gap: 12px; margin-bottom: 16px; color: #c7d2e5; font-size: .69rem; line-height: 1.5; }
    .liberal-profile-location p > i { width: 32px; height: 32px; display: grid; flex: 0 0 32px; place-items: center; border-radius: 50%; color: #60a5fa; background: rgba(96, 165, 250, .12); }
    .liberal-profile-location p span, .liberal-profile-location p b { display: block; }
    .liberal-profile-location p b { margin-bottom: 3px; color: #60a5fa; font-size: .57rem; letter-spacing: .08em; text-transform: uppercase; }
    .liberal-profile-location > a { display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 45px; margin-top: 22px; border-radius: 13px; color: #0f172a; background: #fff; font-size: .7rem; font-weight: 900; text-decoration: none; }
    .liberal-profile-trust { padding: 34px 24px; border-radius: 28px; color: #334155; text-align: center; background: #e2e8f0; }
    .liberal-profile-trust.is-verified { color: #fff; background: linear-gradient(145deg, #2563eb, #075be8); box-shadow: 0 16px 34px rgba(37, 99, 235, .22); }
    .liberal-profile-trust > i { margin-bottom: 15px; font-size: 2.5rem; }
    .liberal-profile-trust h2 { font-size: 1.15rem; font-weight: 900; }
    .liberal-profile-trust p { margin: 0; font-size: .72rem; line-height: 1.55; opacity: .82; }
    .liberal-profile-report { display: flex; justify-content: center; }
    .liberal-profile-reviews { margin-top: 40px; }
    [data-bs-theme="dark"] .liberal-profile-specialties article, html[data-theme="dark"] .liberal-profile-specialties article { border-color: #243d61; background: #10213b; }
    [data-bs-theme="dark"] .liberal-profile-specialties article:nth-child(even), html[data-theme="dark"] .liberal-profile-specialties article:nth-child(even) { border-color: #1d4b42; background: #0d2826; }
    @media (max-width: 991.98px) {
        .liberal-profile-identity { grid-template-columns: 150px minmax(0, 1fr); padding-inline: 28px; }
        .liberal-profile-avatar { width: 142px; height: 142px; margin-top: -82px; border-radius: 28px; }
        .liberal-profile-avatar img, .liberal-profile-avatar span { border-radius: 22px; }
        .liberal-profile-actions { grid-column: 1 / -1; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .liberal-profile-layout { grid-template-columns: 1fr; }
        .liberal-profile-sidebar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .liberal-profile-report { grid-column: 1 / -1; }
    }
    @media (max-width: 575.98px) {
        .liberal-profile-page { padding-top: 12px; }
        .liberal-profile-shell { padding-inline: 10px; }
        .liberal-profile-hero, .liberal-profile-panel, .liberal-profile-trust { border-radius: 20px; }
        .liberal-profile-cover { height: 210px; }
        .liberal-profile-back, .liberal-profile-edit { top: 14px; min-height: 34px; padding: 7px 10px; font-size: .62rem; }
        .liberal-profile-back { left: 14px; }
        .liberal-profile-edit { right: 14px; }
        .liberal-profile-identity { display: block; padding: 0 18px 22px; text-align: center; }
        .liberal-profile-avatar { width: 124px; height: 124px; margin: -62px auto 15px; }
        .liberal-profile-badges, .liberal-profile-facts { justify-content: center; }
        .liberal-profile-copy h1 { font-size: 1.65rem; }
        .liberal-profile-actions { grid-template-columns: 1fr; margin-top: 18px; }
        .liberal-profile-main, .liberal-profile-sidebar { gap: 16px; }
        .liberal-profile-sidebar { grid-template-columns: 1fr; }
        .liberal-profile-panel { padding: 24px 20px; }
        .liberal-profile-specialties { grid-template-columns: 1fr; }
        .liberal-profile-documents article { grid-template-columns: 38px minmax(0, 1fr); padding: 16px; }
        .liberal-profile-documents article > span { grid-column: 2; justify-self: start; }
        .liberal-profile-gallery { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .liberal-profile-gallery img, .liberal-profile-gallery img:first-child { height: 125px; grid-row: auto; }
        .liberal-profile-gallery img:first-child { grid-column: 1 / -1; height: 180px; }
    }
</style>
@endpush
