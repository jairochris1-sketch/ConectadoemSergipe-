@php
    $feedAdUrl = $feedAd->module === 'services'
        ? route('provider.show', $feedAd->slug)
        : route('ad.show', $feedAd->slug);
    $feedAdImage = $feedAd->card_image ? asset($feedAd->card_image) : null;
    $feedAdSponsored = (bool) $feedAd->feed_is_sponsored;
    $feedModuleLabels = [
        'services' => 'Serviços', 'products' => 'Produtos', 'real_estate' => 'Imóveis',
        'vehicles' => 'Veículos', 'jobs' => 'Empregos', 'agro' => 'Agro',
    ];
@endphp
<article class="card community-card community-recommended-ad"
         data-feed-ad-card
         data-feed-ad-event-url="{{ route('feed.ads.event', $feedAd) }}"
         data-feed-ad-mode="{{ $feedMode }}"
         data-feed-ad-city="{{ request('city') }}">
    <header class="community-ad-header">
        <span class="community-ad-label {{ $feedAdSponsored ? 'is-sponsored' : '' }}">
            <i class="fa-solid {{ $feedAdSponsored ? 'fa-bullhorn' : 'fa-star' }}" aria-hidden="true"></i>
            {{ $feedAdSponsored ? 'Patrocinado' : 'Recomendado' }}
        </span>
        <button type="button" class="community-ad-dismiss" data-feed-ad-dismiss title="Não tenho interesse" aria-label="Não tenho interesse neste anúncio">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </header>

    <a href="{{ $feedAdUrl }}" class="community-ad-link" data-feed-ad-link>
        @if($feedAdImage)
            <img src="{{ $feedAdImage }}" class="community-ad-image" alt="{{ $feedAd->title }}" loading="lazy">
        @else
            <div class="community-ad-placeholder" aria-hidden="true"><i class="fa-solid fa-rectangle-ad"></i></div>
        @endif

        <div class="community-ad-content">
            <span class="community-ad-module">{{ $feedAd->category?->name ?: ($feedModuleLabels[$feedAd->module] ?? 'Anúncio') }}</span>
            <h2>{{ $feedAd->title }}</h2>
            <p>{{ \Illuminate\Support\Str::limit($feedAd->description, 125) }}</p>
            <div class="community-ad-footer">
                <span class="community-ad-city"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $feedAd->city ?: 'Sergipe' }}</span>
                <span class="community-ad-footer-actions">
                    @if((float) $feedAd->price > 0)
                        <strong>R$ {{ number_format((float) $feedAd->effective_price, 2, ',', '.') }}</strong>
                    @endif
                    <span class="community-ad-cta">Ver anúncio <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </span>
            </div>
        </div>
    </a>

    <details class="community-ad-explanation">
        <summary>Por que estou vendo isso?</summary>
        <p>{{ $feedAd->feed_reason }}. A recomendação considera somente sinais do próprio Conectado em Sergipe e possui repetição limitada.</p>
    </details>
</article>
