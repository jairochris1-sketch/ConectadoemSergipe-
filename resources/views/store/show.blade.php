@extends('layouts.app')

@section('title', $store->name . ' - Loja no Conectado em Sergipe')

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $store->name . ' - Loja no Conectado em Sergipe',
        'socialDescription' => \Illuminate\Support\Str::limit(strip_tags($store->description ?: "Conheça a {$store->name}, seus produtos e ofertas no Conectado em Sergipe."), 160),
        'socialUrl' => route('store.show', $store->slug),
        'socialImage' => asset($store->banner ?: $store->logo ?: 'images/logo-hero.png'),
    ])
@endpush

@section('content')
@php
    $storeWhatsapp = preg_replace('/\D+/', '', $store->whatsapp ?: $store->user?->whatsapp ?: '');
    $storePhone = preg_replace('/\D+/', '', $store->phone ?: $store->user?->phone ?: '');
    $storeCity = $store->city ?: $store->user?->city ?: 'Sergipe';
    $storeState = $store->state ?: 'SE';
    $storeCategory = $store->category ?: 'Comércio local';
    $reviewCount = (int) $reviewData['count'];
    $reviewAverage = (float) $reviewData['average'];
    $isOwner = auth()->check() && auth()->id() === $store->user_id;
    $isFeaturedStore = $store->isCurrentlyFeatured();
    $storeBanners = collect([$store->banner])
        ->merge($store->media->where('type', 'banner')->pluck('path'))
        ->filter()
        ->unique()
        ->values();
    if ($storeBanners->isEmpty()) {
        $storeBanners->push('images/hero_banner.jpg');
    }
    $storeBannerUrls = $storeBanners->map(fn ($path) => asset($path))->values();
    $storeGallery = $store->media->where('type', 'gallery')->values();
    $whatsappMessage = rawurlencode("Olá! Encontrei a loja {$store->name} no Conectado em Sergipe.");
    $instagramUrl = $store->instagram
        ? (str_starts_with($store->instagram, 'http') ? $store->instagram : 'https://instagram.com/' . ltrim($store->instagram, '@'))
        : null;
    $currentStoreDay = now('America/Fortaleza')->dayOfWeek;
@endphp

<main class="storefront-page">
    @if(!$store->active || !$store->isModerationApproved())
        <div class="storefront-shell pt-3">
            <div class="alert alert-warning rounded-4 mb-0">
                <i class="fa-solid fa-eye-slash me-1"></i>
                @if(!$store->isModerationApproved())
                    Esta loja está oculta pela moderação e somente o proprietário ou a administração podem visualizá-la.
                    @if($isOwner && $store->moderation_note)
                        <span class="d-block mt-1"><strong>Observação:</strong> {{ $store->moderation_note }}</span>
                    @endif
                @else
                    Esta loja está desativada e somente você ou a administração podem visualizá-la.
                @endif
            </div>
        </div>
    @endif

    <div class="storefront-shell">
        @if($errors->has('cart'))
            <div class="alert alert-warning rounded-4 mt-3 mb-0">{{ $errors->first('cart') }}</div>
        @endif
        <nav class="storefront-breadcrumb" aria-label="Navegação estrutural">
            <a href="{{ route('stores.index') }}"><i class="fa-solid fa-arrow-left"></i> Lojas</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span>{{ $store->name }}</span>
        </nav>

        <section class="storefront-hero" aria-labelledby="storefront-title">
            <div
                class="storefront-hero-background"
                id="storefront-hero-background"
                style="background-image: url('{{ $storeBannerUrls->first() }}')"
            ></div>
            <div class="storefront-hero-overlay"></div>
            <div class="storefront-owner-tools">
                @if($isOwner)
                    <a href="{{ route('store.manage', $store) }}">
                        <i class="fa-solid fa-pen"></i> Gerenciar loja
                    </a>
                    <a href="{{ route('seller.orders.index', $store) }}">
                        <i class="fa-solid fa-box"></i> Pedidos
                    </a>
                @else
                    @auth
                        <button
                            type="button"
                            class="storefront-follow-button {{ $isFollowing ? 'is-following' : '' }}"
                            data-store-follow
                            data-store-id="{{ $store->id }}"
                            data-endpoint="{{ route('store.follow.toggle', $store) }}"
                            data-label-idle="Seguir"
                            data-label-following="Seguindo"
                            aria-pressed="{{ $isFollowing ? 'true' : 'false' }}"
                        >
                            <i class="{{ $isFollowing ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                            <span data-store-follow-label>{{ $isFollowing ? 'Seguindo' : 'Seguir' }}</span>
                            <small data-store-follow-count>{{ $store->followers_count }}</small>
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="storefront-follow-button">
                            <i class="fa-regular fa-heart"></i>
                            Seguir
                            <small>{{ $store->followers_count }}</small>
                        </a>
                    @endauth
                @endif
                <button type="button" id="store-share-button" data-social-share data-store-event="share_click" data-share-title="{{ $store->name }}" data-share-text="Conheça a loja {{ $store->name }} no Conectado em Sergipe." data-share-url="{{ route('store.show', $store->slug) }}">
                    <i class="fa-solid fa-share-nodes"></i> Compartilhar
                </button>
            </div>

            <div class="storefront-identity">
                <div class="storefront-logo">
                    <img src="{{ asset($store->logo ?: 'images/logo.png') }}" alt="Logo da {{ $store->name }}">
                </div>
                <div class="storefront-identity-copy">
                    <span class="storefront-category"><i class="fa-solid fa-store"></i> {{ $storeCategory }}</span>
                    @if($isFeaturedStore)
                        <span class="storefront-featured"><i class="fa-solid fa-star"></i> Loja em destaque</span>
                    @endif
                    <h1 id="storefront-title">{{ $store->name }}</h1>
                    <div class="storefront-location">
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $storeCity }}{{ $storeState ? '/' . $storeState : '' }}
                    </div>
                    <a href="#avaliacoes" class="storefront-rating">
                        <i class="fa-solid fa-star"></i>
                        @if($reviewCount)
                            <strong>{{ number_format($reviewAverage, 1, ',', '.') }}</strong>
                            <span>{{ $reviewCount }} {{ $reviewCount === 1 ? 'avaliação' : 'avaliações' }}</span>
                        @else
                            <span>Loja ainda sem avaliações</span>
                        @endif
                    </a>
                </div>
            </div>
        </section>

        <section class="storefront-stats" aria-label="Resumo da loja">
            <div>
                <span class="storefront-stat-icon"><i class="fa-solid fa-box-open"></i></span>
                <p><strong>{{ $storeProductsCount }}</strong><small>{{ $storeProductsCount === 1 ? 'produto disponível' : 'produtos disponíveis' }}</small></p>
            </div>
            <div>
                <span class="storefront-stat-icon"><i class="fa-solid fa-location-dot"></i></span>
                <p><strong>{{ $storeCity }}</strong><small>comércio local de Sergipe</small></p>
            </div>
            <div>
                <span class="storefront-stat-icon"><i class="fa-solid fa-shield-halved"></i></span>
                <p><strong>Contato direto</strong><small>negocie com a própria loja</small></p>
            </div>
        </section>

        @if($storeGallery->isNotEmpty())
            <section class="storefront-gallery" aria-labelledby="storefront-gallery-title">
                <div class="storefront-gallery-heading">
                    <div>
                        <span>GALERIA</span>
                        <h2 id="storefront-gallery-title">Conheça melhor a loja</h2>
                    </div>
                    <strong>{{ $storeGallery->count() }} {{ $storeGallery->count() === 1 ? 'foto' : 'fotos' }}</strong>
                </div>
                <div class="storefront-gallery-grid">
                    @foreach($storeGallery as $galleryImage)
                        <button type="button" data-store-gallery-index="{{ $loop->index }}">
                            <img
                                src="{{ asset($galleryImage->path) }}"
                                alt="Foto {{ $loop->iteration }} da {{ $store->name }}"
                                loading="lazy"
                            >
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="storefront-layout">
            <div class="storefront-main">
                @if($storePromotions->isNotEmpty())
                    <section class="storefront-promotions" aria-labelledby="storefront-promotions-title">
                        <div class="storefront-section-heading">
                            <div>
                                <span>OFERTAS</span>
                                <h2 id="storefront-promotions-title">Cupons e promoções</h2>
                                <p>Confira as condições e apresente o código diretamente à loja.</p>
                            </div>
                            <strong>{{ $storePromotions->count() }} {{ $storePromotions->count() === 1 ? 'oferta' : 'ofertas' }}</strong>
                        </div>

                        <div class="storefront-promotions-grid">
                            @foreach($storePromotions as $promotion)
                                <article class="storefront-promotion-card">
                                    <div class="storefront-promotion-discount">
                                        <i class="fa-solid fa-tag"></i>
                                        <strong>{{ $promotion->discount_label }}</strong>
                                    </div>
                                    <div class="storefront-promotion-content">
                                        <h3>{{ $promotion->title }}</h3>
                                        @if($promotion->description)
                                            <p>{{ $promotion->description }}</p>
                                        @endif
                                        <small>
                                            <i class="fa-regular fa-clock"></i>
                                            Válida até {{ $promotion->ends_at->format('d/m/Y \à\s H:i') }}
                                        </small>
                                        @if($promotion->terms)
                                            <details>
                                                <summary>Ver regras</summary>
                                                <p>{{ $promotion->terms }}</p>
                                            </details>
                                        @endif
                                    </div>
                                    @if($promotion->coupon_code)
                                        <button
                                            type="button"
                                            class="storefront-coupon-copy"
                                            data-coupon-copy="{{ $promotion->coupon_code }}"
                                            aria-label="Copiar cupom {{ $promotion->coupon_code }}"
                                        >
                                            <span>Cupom</span>
                                            <strong>{{ $promotion->coupon_code }}</strong>
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="storefront-catalog" aria-labelledby="storefront-products-title">
                    <div class="storefront-section-heading">
                        <div>
                            <span>CATÁLOGO</span>
                            <h2 id="storefront-products-title">Produtos da loja</h2>
                            <p>Encontre os produtos publicados por {{ $store->name }}.</p>
                        </div>
                        <strong>{{ $ads->total() }} {{ $ads->total() === 1 ? 'resultado' : 'resultados' }}</strong>
                    </div>

                    <form method="GET" action="{{ route('store.show', $store->slug) }}" class="storefront-search" id="storefront-search-form">
                        @if(request('reviews_sort'))
                            <input type="hidden" name="reviews_sort" value="{{ request('reviews_sort') }}">
                        @endif
                        <label class="storefront-search-query">
                            <span class="visually-hidden">Buscar produtos nesta loja</span>
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Buscar produtos nesta loja...">
                        </label>
                        <label>
                            <span class="visually-hidden">Filtrar por categoria</span>
                            <select name="category">
                                <option value="">Todas as categorias</option>
                                @foreach($productCategories as $productCategory)
                                    <option value="{{ $productCategory }}" @selected($category === $productCategory)>{{ $productCategory }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span class="visually-hidden">Ordenar produtos</span>
                            <select name="sort">
                                <option value="recent" @selected($sort === 'recent')>Mais recentes</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>Menor preço</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>Maior preço</option>
                            </select>
                        </label>
                        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                    </form>

                    @if($q || $category || $sort !== 'recent')
                        <div class="storefront-filter-summary">
                            <span>Filtros aplicados</span>
                            @if($q)<strong>Busca: “{{ $q }}”</strong>@endif
                            @if($category)<strong>{{ $category }}</strong>@endif
                            <a href="{{ route('store.show', $store->slug) }}#produtos">Limpar</a>
                        </div>
                    @endif

                    <div id="produtos">
                        @if($ads->isEmpty())
                            <div class="storefront-empty">
                                <span><i class="fa-solid fa-box-open"></i></span>
                                @if($storeProductsCount > 0)
                                    <h3>Nenhum produto encontrado</h3>
                                    <p>Tente outro termo ou remova os filtros aplicados.</p>
                                    <a href="{{ route('store.show', $store->slug) }}#produtos">Ver todos os produtos</a>
                                @else
                                    <h3>Esta loja ainda não publicou produtos</h3>
                                    <p>Você pode conhecer a loja e voltar em breve para conferir as novidades.</p>
                                    @if($storeWhatsapp)
                                        <a href="https://wa.me/55{{ $storeWhatsapp }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" data-store-event="whatsapp_click">Perguntar pelo WhatsApp</a>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="storefront-products-grid">
                                @foreach($ads as $item)
                                    @php
                                        $productUrl = route('store.products.show', [$store, $item]);
                                        $effectiveDisplayMode = $item->effectiveDisplayMode();
                                    @endphp
                                    <article class="storefront-product-card">
                                        <a href="{{ $productUrl }}" class="storefront-product-image" data-store-event="product_click" data-ad-id="{{ $item->id }}" @if($effectiveDisplayMode === 'catalog') data-quick-product="quick-product-{{ $item->id }}" @endif>
                                            @if($item->card_image)
                                                <img src="{{ asset($item->card_image) }}" alt="{{ $item->title }}" loading="lazy">
                                            @else
                                                <span><i class="fa-solid fa-image"></i></span>
                                            @endif
                                            <small>
                                                {{ $effectiveDisplayMode === 'catalog' ? 'Compra rápida' : $item->display_category }}
                                            </small>
                                        </a>
                                        <div class="storefront-product-body">
                                            <a href="{{ $productUrl }}" data-store-event="product_click" data-ad-id="{{ $item->id }}" @if($effectiveDisplayMode === 'catalog') data-quick-product="quick-product-{{ $item->id }}" @endif><h3>{{ $item->title }}</h3></a>
                                            <p><i class="fa-solid fa-location-dot"></i> {{ $item->city ?: $storeCity }}</p>
                                            @if($item->is_out_of_stock)
                                                <small class="text-danger fw-bold">Esgotado</small>
                                            @elseif($item->is_low_stock)
                                                <small class="text-warning fw-bold">Últimas unidades</small>
                                            @endif
                                            <div class="storefront-product-footer">
                                                <strong>
                                                    @if(($item->price_type ?? 'fixed') === 'negotiable')
                                                        Preço a combinar
                                                    @else
                                                        @if($item->sale_price !== null && (float) $item->sale_price < (float) $item->price)
                                                            <small><s>R$ {{ number_format((float) $item->price, 2, ',', '.') }}</s></small>
                                                        @endif
                                                        R$ {{ number_format($item->effective_price, 2, ',', '.') }}
                                                    @endif
                                                </strong>
                                                <div class="storefront-product-actions">
                                                    <a href="{{ $productUrl }}" aria-label="Ver {{ $item->title }}" data-store-event="product_click" data-ad-id="{{ $item->id }}" @if($effectiveDisplayMode === 'catalog') data-quick-product="quick-product-{{ $item->id }}" @endif>
                                                        {{ $effectiveDisplayMode === 'catalog' ? ($item->activeVariations->isNotEmpty() || $item->activeAddons->isNotEmpty() ? 'Escolher opções' : 'Ver rápido') : 'Ver detalhes' }}
                                                    </a>
                                                    @if($effectiveDisplayMode === 'catalog' && !$item->is_out_of_stock && $item->activeVariations->isEmpty() && $item->activeAddons->isEmpty() && ($item->price_type ?? 'fixed') !== 'negotiable' && $item->effective_price > 0)
                                                        <form action="{{ route('cart.add', $item) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="quantity" value="1">
                                                            <button type="submit" aria-label="Adicionar {{ $item->title }} ao carrinho">
                                                                <i class="fa-solid fa-cart-plus"></i> Adicionar
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                    @if($effectiveDisplayMode === 'catalog')
                                        <dialog class="storefront-quick-product" id="quick-product-{{ $item->id }}" data-product-url="{{ $productUrl }}" aria-labelledby="quick-product-title-{{ $item->id }}">
                                            <button type="button" class="storefront-quick-close" data-quick-close aria-label="Fechar">×</button>
                                            <div class="storefront-quick-grid">
                                                <div class="storefront-quick-media">
                                                    <div class="storefront-quick-image">
                                                        @if($item->card_image)<img src="{{ asset($item->card_image) }}" alt="{{ $item->title }}" data-quick-image>@else<i class="fa-solid fa-image"></i>@endif
                                                    </div>
                                                    @if($item->images->count() > 1)
                                                        <div class="storefront-quick-thumbnails" aria-label="Galeria de {{ $item->title }}">
                                                            @foreach($item->images as $image)
                                                                <button type="button" data-quick-thumbnail data-image="{{ asset($image->image_path) }}" @class(['is-active' => $image->image_path === $item->card_image]) aria-label="Exibir imagem {{ $loop->iteration }} de {{ $item->title }}">
                                                                    <img src="{{ asset($image->image_path) }}" alt="" loading="lazy">
                                                                </button>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <small>{{ $item->display_category }}</small>
                                                    <h2 id="quick-product-title-{{ $item->id }}">{{ $item->title }}</h2>
                                                    <div class="storefront-quick-rating" aria-label="Avaliações do produto">
                                                        <i class="fa-solid fa-star"></i>
                                                        @if($item->approved_reviews_count)
                                                            <strong>{{ number_format((float) $item->approved_reviews_average, 1, ',', '.') }}</strong>
                                                            <span>({{ $item->approved_reviews_count }} {{ $item->approved_reviews_count === 1 ? 'avaliação' : 'avaliações' }})</span>
                                                        @else
                                                            <span>Ainda sem avaliações</span>
                                                        @endif
                                                    </div>
                                                    <p>{{ \Illuminate\Support\Str::limit($item->description, 220) }}</p>
                                                    <strong class="storefront-quick-price" data-quick-price>R$ {{ number_format($item->effective_price, 2, ',', '.') }}</strong>
                                                    @if($item->is_out_of_stock)
                                                        <p class="storefront-quick-stock is-out" role="status">Produto esgotado</p>
                                                    @else
                                                        <form action="{{ route('cart.add', $item) }}" method="POST" data-quick-form data-base-price="{{ $item->effective_price }}" data-product-stock="{{ $item->stock_quantity }}" data-product-track-stock="{{ $item->track_stock ? '1' : '0' }}" data-allow-backorders="{{ $item->allow_backorders ? '1' : '0' }}" data-default-image="{{ $item->card_image ? asset($item->card_image) : '' }}">
                                                            @csrf
                                                            @if($item->activeVariations->isNotEmpty())
                                                                <label for="quick-variation-{{ $item->id }}">Opção
                                                                    <select id="quick-variation-{{ $item->id }}" name="variation_id" required>
                                                                        <option value="">Selecione</option>
                                                                        @foreach($item->activeVariations as $variation)
                                                                            <option value="{{ $variation->id }}" data-price="{{ $variation->price !== null ? (float) $variation->price : $item->effective_price + (float) $variation->price_adjustment }}" data-image="{{ $variation->image ? asset($variation->image) : '' }}" data-stock="{{ $variation->stock_quantity }}" data-track-stock="{{ $variation->track_stock ? '1' : '0' }}" @disabled($variation->track_stock && !$item->allow_backorders && $variation->stock_quantity < 1)>
                                                                                {{ $variation->name }}{{ $variation->track_stock && $variation->stock_quantity < 1 ? ' — esgotado' : '' }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </label>
                                                            @endif
                                                            @foreach($item->activeAddons as $addon)
                                                                <label class="storefront-quick-addon">
                                                                    <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" data-addon-price="{{ (float) $addon->price }}">
                                                                    {{ $addon->name }} (+ R$ {{ number_format((float) $addon->price, 2, ',', '.') }})
                                                                </label>
                                                            @endforeach
                                                            <label for="quick-note-{{ $item->id }}">Observação<textarea id="quick-note-{{ $item->id }}" name="note" rows="2" maxlength="500"></textarea></label>
                                                            <label for="quick-quantity-{{ $item->id }}">Quantidade<input id="quick-quantity-{{ $item->id }}" type="number" name="quantity" min="{{ $item->minimum_quantity }}" max="{{ $item->track_stock && !$item->allow_backorders ? max(1, $item->stock_quantity) : 99 }}" value="{{ $item->minimum_quantity }}"></label>
                                                            <p class="storefront-quick-stock" data-quick-stock role="status" aria-live="polite"></p>
                                                            <div class="storefront-quick-actions">
                                                                <button type="submit"><i class="fa-solid fa-cart-plus"></i> Adicionar ao carrinho</button>
                                                                <button type="submit" name="buy_now" value="1"><i class="fa-solid fa-bolt"></i> Comprar agora</button>
                                                            </div>
                                                        </form>
                                                    @endif
                                                    <div class="storefront-quick-delivery" aria-label="Opções de entrega e retirada">
                                                        <strong><i class="fa-regular fa-clock"></i> {{ $businessStatus['label'] }} · {{ $businessStatus['detail'] }}</strong>
                                                        @if($store->delivery_available)
                                                            <span>
                                                                <i class="fa-solid fa-truck"></i> Entrega disponível
                                                                @if((float) $store->delivery_fee > 0)
                                                                    a partir de R$ {{ number_format((float) $store->delivery_fee, 2, ',', '.') }}
                                                                @endif
                                                            </span>
                                                            @if($store->delivery_min_minutes)
                                                                <span><i class="fa-solid fa-stopwatch"></i> Prazo de {{ $store->delivery_min_minutes }} a {{ $store->delivery_max_minutes ?: $store->delivery_min_minutes }} minutos</span>
                                                            @endif
                                                            @if($store->free_delivery_threshold)
                                                                <span><i class="fa-solid fa-gift"></i> Entrega grátis acima de R$ {{ number_format((float) $store->free_delivery_threshold, 2, ',', '.') }}</span>
                                                            @endif
                                                        @endif
                                                        @if($store->pickup_available)
                                                            <span><i class="fa-solid fa-store"></i> Retirada disponível{{ $store->pickup_address ? ' em '.$store->pickup_address : '' }}</span>
                                                        @endif
                                                        @if((float) $store->minimum_order > 0)
                                                            <span><i class="fa-solid fa-receipt"></i> Pedido mínimo: R$ {{ number_format((float) $store->minimum_order, 2, ',', '.') }}</span>
                                                        @endif
                                                    </div>
                                                    <a class="storefront-quick-full" href="{{ $productUrl }}">Ver página completa</a>
                                                </div>
                                            </div>
                                        </dialog>
                                    @endif
                                @endforeach
                            </div>

                            @if($ads->hasPages())
                                <div class="storefront-pagination">
                                    {{ $ads->onEachSide(1)->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </section>
            </div>

            <aside class="storefront-sidebar" aria-label="Informações e contato da loja">
                <section class="storefront-info-card">
                    <span class="storefront-card-eyebrow">SOBRE</span>
                    <h2>Conheça a loja</h2>
                    <p>{{ $store->description ?: 'Esta loja ainda não adicionou uma descrição detalhada.' }}</p>
                    <dl>
                        <div><dt>Categoria</dt><dd>{{ $storeCategory }}</dd></div>
                        <div><dt>Localização</dt><dd>{{ $storeCity }}/{{ $storeState }}</dd></div>
                    </dl>
                </section>

                <section class="storefront-hours-card">
                    <span class="storefront-card-eyebrow">FUNCIONAMENTO</span>
                    <h2>Horário da loja</h2>
                    <div class="storefront-open-status is-{{ $businessStatus['state'] }}">
                        <span><i class="fa-solid fa-circle"></i> {{ $businessStatus['label'] }}</span>
                        <small>{{ $businessStatus['detail'] }}</small>
                    </div>

                    @if($store->businessHours->isNotEmpty())
                        <div class="storefront-hours-list">
                            @foreach($weeklyBusinessHours as $schedule)
                                <div @class(['is-today' => $schedule['day'] === $currentStoreDay])>
                                    <span>{{ $schedule['label'] }}</span>
                                    <strong>{{ $schedule['hours']?->display_hours ?? 'Não informado' }}</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="storefront-contact-card">
                    <span class="storefront-card-eyebrow">CONTATO</span>
                    <h2>Fale com a loja</h2>
                    <p>Consulte disponibilidade, condições e detalhes diretamente com o vendedor.</p>
                    <div class="storefront-contact-actions">
                        @if($storeWhatsapp)
                            <a class="is-whatsapp" href="https://wa.me/55{{ $storeWhatsapp }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" data-store-event="whatsapp_click">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                        @if($storePhone)
                            <a href="tel:+55{{ $storePhone }}" data-store-event="phone_click"><i class="fa-solid fa-phone"></i> Ligar</a>
                        @endif
                        @if($instagramUrl)
                            <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" data-store-event="instagram_click"><i class="fa-brands fa-instagram"></i> Instagram</a>
                        @endif
                        @if($store->website)
                            <a href="{{ $store->website }}" target="_blank" rel="noopener noreferrer" data-store-event="website_click"><i class="fa-solid fa-globe"></i> Site da loja</a>
                        @endif
                    </div>
                    <small><i class="fa-solid fa-circle-info"></i> Combine pagamentos e entregas com segurança.</small>
                    @if(!$isOwner)
                        <div class="storefront-report-action">
                            @include('reports._button-and-modal', ['reportable' => $store])
                        </div>
                    @endif
                </section>
            </aside>
        </div>

        <section class="storefront-reviews">
            @include('reviews._section', ['reviewable' => $store, 'reviewData' => $reviewData])
        </section>
    </div>
</main>

@if($storeGallery->isNotEmpty())
    <dialog class="storefront-gallery-dialog" id="storefront-gallery-dialog">
        <button type="button" class="storefront-gallery-close" data-store-gallery-close aria-label="Fechar">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <button type="button" class="storefront-gallery-nav is-previous" data-store-gallery-previous aria-label="Foto anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <img id="storefront-gallery-dialog-image" src="" alt="">
        <button type="button" class="storefront-gallery-nav is-next" data-store-gallery-next aria-label="Próxima foto">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        <span id="storefront-gallery-counter"></span>
    </dialog>
@endif

<div class="store-share-feedback" id="store-share-feedback" role="status" aria-live="polite"></div>

@push('styles')
<style>
    .storefront-page {
        min-height: 70vh;
        padding: 0 0 4rem;
        color: var(--foreground);
        background: var(--background);
    }
    .storefront-shell {
        width: min(1440px, calc(100% - 32px));
        margin: 0 auto;
    }
    .storefront-breadcrumb {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-width: 0;
        padding: 1rem 2px;
        color: var(--muted-foreground);
        font-size: .82rem;
    }
    .storefront-breadcrumb a {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: #1265f5;
        font-weight: 700;
        text-decoration: none;
    }
    .storefront-breadcrumb span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .storefront-breadcrumb > i { font-size: .6rem; }
    .storefront-hero {
        position: relative;
        min-height: 330px;
        overflow: hidden;
        isolation: isolate;
        background: #071630;
        border: 1px solid var(--border);
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .12);
    }
    .storefront-hero-background {
        position: absolute;
        z-index: -2;
        inset: 0;
        background-position: center;
        background-size: cover;
        opacity: 1;
        transform: scale(1.01);
        transition: opacity .45s ease;
    }
    .storefront-hero-background.is-changing { opacity: 0; }
    .storefront-hero-overlay {
        position: absolute;
        z-index: -1;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(5, 17, 43, .94) 0%, rgba(7, 24, 57, .76) 44%, rgba(7, 22, 48, .2) 100%),
            linear-gradient(0deg, rgba(5, 14, 34, .55), transparent 65%);
    }
    .storefront-owner-tools {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        gap: .6rem;
    }
    .storefront-owner-tools a,
    .storefront-owner-tools button {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        min-height: 40px;
        padding: 0 15px;
        color: #fff;
        background: rgba(7, 20, 45, .58);
        border: 1px solid rgba(255, 255, 255, .38);
        border-radius: 999px;
        backdrop-filter: blur(9px);
        font-size: .82rem;
        font-weight: 700;
        text-decoration: none;
    }
    .storefront-owner-tools button { cursor: pointer; }
    .storefront-owner-tools .storefront-follow-button {
        color: #fff;
        text-decoration: none;
    }
    .storefront-owner-tools .storefront-follow-button.is-following {
        color: #fff;
        background: rgba(217, 39, 85, .82);
        border-color: rgba(255, 255, 255, .55);
    }
    .storefront-follow-button small {
        min-width: 18px;
        height: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        background: rgba(255, 255, 255, .16);
        border-radius: 999px;
        font-size: .58rem;
    }
    .storefront-identity {
        position: absolute;
        right: 38px;
        bottom: 32px;
        left: 38px;
        display: flex;
        align-items: center;
        gap: 22px;
        color: #fff;
    }
    .storefront-logo {
        width: 138px;
        height: 138px;
        flex: 0 0 138px;
        padding: 8px;
        overflow: hidden;
        background: #fff;
        border: 3px solid rgba(255, 255, 255, .9);
        border-radius: 24px;
        box-shadow: 0 18px 36px rgba(0, 0, 0, .3);
    }
    .storefront-logo img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: contain;
        border-radius: 17px;
    }
    .storefront-identity-copy { min-width: 0; }
    .storefront-category {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .55rem;
        padding: 6px 11px;
        color: #d9e8ff;
        background: rgba(18, 101, 245, .36);
        border: 1px solid rgba(126, 174, 255, .4);
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .storefront-featured {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        margin: 0 0 .55rem .35rem;
        padding: 6px 11px;
        color: #fff2bd;
        background: rgba(151, 101, 0, .58);
        border: 1px solid rgba(255, 211, 74, .52);
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .storefront-identity h1 {
        max-width: 760px;
        margin: 0 0 .45rem;
        color: #fff;
        font-size: clamp(2rem, 4vw, 3.35rem);
        font-weight: 850;
        letter-spacing: -.045em;
        line-height: 1.02;
        overflow-wrap: anywhere;
    }
    .storefront-location {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .55rem;
        color: #dbe5f6;
        font-size: .9rem;
    }
    .storefront-rating {
        display: inline-flex;
        align-items: center;
        gap: .38rem;
        color: #fff;
        font-size: .84rem;
        text-decoration: none;
    }
    .storefront-rating i { color: #ffbd15; }
    .storefront-rating span { color: #dbe5f6; }
    .storefront-stats {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        margin: -1px 24px 0;
        background: var(--card);
        border: 1px solid var(--border);
        border-top: 0;
        border-radius: 0 0 18px 18px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .07);
    }
    .storefront-stats > div {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .8rem;
        min-width: 0;
        padding: 16px;
    }
    .storefront-stats > div + div { border-left: 1px solid var(--border); }
    .storefront-stat-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        flex: 0 0 38px;
        color: #1265f5;
        background: color-mix(in srgb, #1265f5 10%, var(--card));
        border-radius: 11px;
    }
    .storefront-stats p { display: grid; min-width: 0; margin: 0; line-height: 1.2; }
    .storefront-stats strong { overflow: hidden; font-size: .88rem; text-overflow: ellipsis; white-space: nowrap; }
    .storefront-stats small { margin-top: 3px; color: var(--muted-foreground); font-size: .7rem; }
    .storefront-gallery {
        margin-top: 22px;
        padding: 20px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
    }
    .storefront-gallery-heading {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 14px;
    }
    .storefront-gallery-heading span { color: #1265f5; font-size: .64rem; font-weight: 850; letter-spacing: .08em; }
    .storefront-gallery-heading h2 { margin: 2px 0 0; color: var(--foreground); font-size: 1.1rem; font-weight: 850; }
    .storefront-gallery-heading > strong { color: var(--muted-foreground); font-size: .7rem; }
    .storefront-gallery-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 9px;
    }
    .storefront-gallery-grid button {
        min-width: 0;
        padding: 0;
        overflow: hidden;
        background: var(--muted-bg);
        border: 0;
        border-radius: 11px;
        cursor: pointer;
    }
    .storefront-gallery-grid img {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        transition: transform .25s ease;
    }
    .storefront-gallery-grid button:hover img { transform: scale(1.035); }
    .storefront-gallery-dialog {
        width: min(1040px, calc(100% - 28px));
        max-width: none;
        padding: 0;
        overflow: visible;
        background: transparent;
        border: 0;
    }
    .storefront-gallery-dialog::backdrop { background: rgba(3, 9, 23, .9); backdrop-filter: blur(8px); }
    .storefront-gallery-dialog > img {
        width: 100%;
        max-height: 82vh;
        display: block;
        object-fit: contain;
        background: #050b17;
        border-radius: 16px;
    }
    .storefront-gallery-close,
    .storefront-gallery-nav {
        position: absolute;
        z-index: 2;
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        color: #fff;
        background: rgba(7, 20, 45, .78);
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 50%;
    }
    .storefront-gallery-close { top: 12px; right: 12px; }
    .storefront-gallery-nav { top: 50%; transform: translateY(-50%); }
    .storefront-gallery-nav.is-previous { left: 12px; }
    .storefront-gallery-nav.is-next { right: 12px; }
    .storefront-gallery-dialog > span {
        position: absolute;
        right: 50%;
        bottom: 12px;
        padding: 5px 10px;
        color: #fff;
        background: rgba(7, 20, 45, .78);
        border-radius: 999px;
        font-size: .68rem;
        transform: translateX(50%);
    }
    .storefront-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        align-items: start;
        gap: 24px;
        margin-top: 30px;
    }
    .storefront-main {
        min-width: 0;
        display: grid;
        align-content: start;
        gap: 18px;
    }
    .storefront-promotions {
        padding: 22px;
        color: var(--foreground);
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: var(--shadow-sm);
    }
    .storefront-promotions-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 11px;
        margin-top: 17px;
    }
    .storefront-promotion-card {
        min-width: 0;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 12px;
        padding: 14px;
        background: linear-gradient(135deg, color-mix(in srgb, #1265f5 8%, var(--card)), var(--card));
        border: 1px solid color-mix(in srgb, #1265f5 22%, var(--border));
        border-radius: 14px;
    }
    .storefront-promotion-discount {
        width: 72px;
        min-height: 72px;
        display: grid;
        place-items: center;
        align-content: center;
        gap: 3px;
        color: #fff;
        background: linear-gradient(145deg, #1265f5, #084ac3);
        border-radius: 12px;
        text-align: center;
    }
    .storefront-promotion-discount i { font-size: .72rem; }
    .storefront-promotion-discount strong { font-size: .72rem; line-height: 1.1; }
    .storefront-promotion-content { min-width: 0; }
    .storefront-promotion-content h3 {
        margin: 0;
        color: var(--foreground);
        font-size: .82rem;
        font-weight: 850;
    }
    .storefront-promotion-content > p,
    .storefront-promotion-content details p {
        margin: 5px 0 0;
        color: var(--muted-foreground);
        font-size: .66rem;
        line-height: 1.45;
    }
    .storefront-promotion-content > small {
        display: block;
        margin-top: 7px;
        color: var(--muted-foreground);
        font-size: .58rem;
    }
    .storefront-promotion-content details { margin-top: 5px; }
    .storefront-promotion-content summary {
        color: #1265f5;
        font-size: .58rem;
        font-weight: 750;
        cursor: pointer;
    }
    .storefront-coupon-copy {
        grid-column: 1 / -1;
        min-height: 39px;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        padding: 0 11px;
        color: var(--foreground);
        background: var(--card);
        border: 1px dashed #1265f5;
        border-radius: 10px;
        cursor: pointer;
    }
    .storefront-coupon-copy span { color: var(--muted-foreground); font-size: .58rem; }
    .storefront-coupon-copy strong {
        overflow: hidden;
        color: #1265f5;
        font-size: .72rem;
        letter-spacing: .06em;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .storefront-coupon-copy i { color: #1265f5; }
    .storefront-catalog,
    .storefront-info-card,
    .storefront-hours-card,
    .storefront-contact-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .045);
    }
    .storefront-catalog { padding: 24px; }
    .storefront-section-heading {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 20px;
    }
    .storefront-section-heading span,
    .storefront-card-eyebrow {
        color: #1265f5;
        font-size: .7rem;
        font-weight: 850;
        letter-spacing: .08em;
    }
    .storefront-section-heading h2,
    .storefront-info-card h2,
    .storefront-hours-card h2,
    .storefront-contact-card h2 {
        margin: 3px 0;
        color: var(--foreground);
        font-size: 1.35rem;
        font-weight: 850;
    }
    .storefront-section-heading p {
        margin: 0;
        color: var(--muted-foreground);
        font-size: .83rem;
    }
    .storefront-section-heading > strong {
        flex: 0 0 auto;
        color: #1265f5;
        font-size: .78rem;
    }
    .storefront-search {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) 190px 155px auto;
        gap: 9px;
        margin-bottom: 17px;
    }
    .storefront-search label {
        position: relative;
        min-width: 0;
        margin: 0;
    }
    .storefront-search-query > i {
        position: absolute;
        top: 50%;
        left: 15px;
        color: var(--muted-foreground);
        transform: translateY(-50%);
    }
    .storefront-search input,
    .storefront-search select {
        width: 100%;
        height: 44px;
        padding: 0 13px;
        color: var(--foreground);
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        outline: none;
        font-size: .82rem;
    }
    .storefront-search input { padding-left: 41px; }
    .storefront-search input:focus,
    .storefront-search select:focus {
        border-color: #1265f5;
        box-shadow: 0 0 0 3px rgba(18, 101, 245, .1);
    }
    .storefront-search > button {
        height: 44px;
        padding: 0 17px;
        color: #fff;
        background: #1265f5;
        border: 0;
        border-radius: 12px;
        font-size: .82rem;
        font-weight: 800;
    }
    .storefront-filter-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
        margin-bottom: 17px;
        color: var(--muted-foreground);
        font-size: .72rem;
    }
    .storefront-filter-summary strong {
        padding: 5px 9px;
        color: var(--foreground);
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 999px;
    }
    .storefront-filter-summary a { margin-left: auto; color: #1265f5; font-weight: 700; }
    #produtos { scroll-margin-top: 24px; }
    .storefront-products-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }
    .storefront-product-card {
        min-width: 0;
        overflow: hidden;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 15px;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .storefront-product-card:hover {
        border-color: color-mix(in srgb, #1265f5 48%, var(--border));
        box-shadow: 0 12px 24px rgba(15, 23, 42, .09);
        transform: translateY(-3px);
    }
    .storefront-product-image {
        position: relative;
        height: 190px;
        display: grid;
        place-items: center;
        overflow: hidden;
        color: var(--muted-foreground);
        background: var(--muted-bg);
        text-decoration: none;
    }
    .storefront-product-image > img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        transition: transform .3s ease;
    }
    .storefront-product-card:hover .storefront-product-image > img { transform: scale(1.035); }
    .storefront-product-image > span { font-size: 2rem; }
    .storefront-product-image > small {
        position: absolute;
        top: 10px;
        left: 10px;
        max-width: calc(100% - 20px);
        padding: 5px 8px;
        overflow: hidden;
        color: #fff;
        background: rgba(8, 23, 50, .78);
        border-radius: 999px;
        font-size: .62rem;
        font-weight: 750;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .storefront-product-body { padding: 14px; }
    .storefront-product-body > a { color: var(--foreground); text-decoration: none; }
    .storefront-product-body h3 {
        min-height: 2.5em;
        margin: 0 0 8px;
        display: -webkit-box;
        overflow: hidden;
        font-size: .94rem;
        font-weight: 800;
        line-height: 1.25;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
    .storefront-product-body > p {
        margin: 0 0 12px;
        overflow: hidden;
        color: var(--muted-foreground);
        font-size: .7rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .storefront-product-body > p i { color: #1265f5; }
    .storefront-product-footer {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: .5rem;
        padding-top: 11px;
        border-top: 1px solid var(--border);
    }
    .storefront-product-footer strong {
        color: #1265f5;
        font-size: .92rem;
        line-height: 1.2;
    }
    .storefront-product-footer a {
        flex: 0 0 auto;
        color: var(--foreground);
        font-size: .68rem;
        font-weight: 750;
        text-decoration: none;
    }

    .storefront-product-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .45rem;
    }

    .storefront-product-actions form {
        margin: 0;
    }

    .storefront-product-actions button {
        border: 0;
        border-radius: 999px;
        padding: .5rem .7rem;
        color: #fff;
        background: #256c2b;
        font-size: .72rem;
        font-weight: 800;
    }
    .storefront-pagination { margin-top: 22px; }
    .storefront-empty {
        display: grid;
        place-items: center;
        min-height: 300px;
        padding: 35px;
        text-align: center;
        background: var(--muted-bg);
        border: 1px dashed var(--border);
        border-radius: 16px;
    }
    .storefront-empty > span {
        width: 62px;
        height: 62px;
        display: grid;
        place-items: center;
        margin-bottom: 12px;
        color: #1265f5;
        background: color-mix(in srgb, #1265f5 10%, var(--card));
        border-radius: 18px;
        font-size: 1.55rem;
    }
    .storefront-empty h3 { margin: 0; color: var(--foreground); font-size: 1rem; font-weight: 800; }
    .storefront-empty p { max-width: 430px; margin: 6px 0 15px; color: var(--muted-foreground); font-size: .8rem; }
    .storefront-empty a {
        padding: 9px 15px;
        color: #fff;
        background: #1265f5;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 750;
        text-decoration: none;
    }
    .storefront-sidebar {
        position: sticky;
        top: 18px;
        display: grid;
        gap: 16px;
    }
    .storefront-info-card,
    .storefront-hours-card,
    .storefront-contact-card { padding: 22px; }
    .storefront-info-card > p,
    .storefront-contact-card > p {
        margin: 10px 0 17px;
        color: var(--muted-foreground);
        font-size: .82rem;
        line-height: 1.65;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }
    .storefront-info-card dl { margin: 0; }
    .storefront-info-card dl > div {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 10px 0;
        border-top: 1px solid var(--border);
        font-size: .78rem;
    }
    .storefront-info-card dt { color: var(--muted-foreground); font-weight: 500; }
    .storefront-info-card dd { margin: 0; text-align: right; font-weight: 750; }
    .storefront-open-status {
        display: grid;
        gap: 3px;
        margin: 12px 0 14px;
        padding: 10px 11px;
        color: var(--muted-foreground);
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 11px;
    }
    .storefront-open-status span {
        color: var(--foreground);
        font-size: .72rem;
        font-weight: 800;
    }
    .storefront-open-status span i { margin-right: 4px; font-size: .48rem; }
    .storefront-open-status small { font-size: .6rem; }
    .storefront-open-status.is-open span,
    .storefront-open-status.is-open span i { color: #16834b; }
    .storefront-open-status.is-closed span,
    .storefront-open-status.is-closed span i { color: #c2414f; }
    .storefront-hours-list {
        display: grid;
        gap: 0;
    }
    .storefront-hours-list > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 7px 0;
        color: var(--muted-foreground);
        border-top: 1px solid var(--border);
        font-size: .62rem;
    }
    .storefront-hours-list > div strong {
        color: var(--foreground);
        font-size: .62rem;
        text-align: right;
    }
    .storefront-hours-list > div.is-today span,
    .storefront-hours-list > div.is-today strong {
        color: #1265f5;
        font-weight: 850;
    }
    .storefront-contact-actions { display: grid; gap: 9px; }
    .storefront-contact-actions a {
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .55rem;
        color: var(--foreground);
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        font-size: .78rem;
        font-weight: 800;
        text-decoration: none;
    }
    .storefront-contact-actions a.is-whatsapp {
        color: #fff;
        background: #179653;
        border-color: #179653;
    }
    .storefront-contact-card > small {
        display: flex;
        align-items: flex-start;
        gap: .45rem;
        margin-top: 14px;
        color: var(--muted-foreground);
        font-size: .68rem;
        line-height: 1.45;
    }
    .storefront-report-action {
        margin-top: 14px;
        padding-top: 10px;
        border-top: 1px solid var(--border);
    }
    .storefront-reviews .reviews-section { margin-top: 30px !important; }
    .store-share-feedback {
        position: fixed;
        z-index: 1090;
        right: 20px;
        bottom: 80px;
        padding: 10px 15px;
        color: #fff;
        background: #1265f5;
        border-radius: 999px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, .22);
        font-size: .78rem;
        font-weight: 700;
        opacity: 0;
        pointer-events: none;
        transform: translateY(8px);
        transition: .2s ease;
    }
    .store-share-feedback.is-visible { opacity: 1; transform: translateY(0); }
    .storefront-quick-product {
        width: min(920px, calc(100% - 32px));
        max-height: 88vh;
        padding: 0;
        border: 0;
        border-radius: 22px;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .32);
        overflow: auto;
    }
    .storefront-quick-product::backdrop { background: rgba(15, 23, 42, .64); backdrop-filter: blur(3px); }
    .storefront-quick-close {
        position: absolute;
        z-index: 2;
        top: 12px;
        right: 12px;
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 50%;
        background: #fff;
        font-size: 1.35rem;
    }
    .storefront-quick-grid { display: grid; grid-template-columns: minmax(280px, .9fr) minmax(320px, 1.1fr); }
    .storefront-quick-grid > div:last-child { padding: 34px; }
    .storefront-quick-media { min-width: 0; background: #f1f5f9; }
    .storefront-quick-image { min-height: 480px; display: grid; place-items: center; background: #f1f5f9; color: #94a3b8; font-size: 3rem; }
    .storefront-quick-image img { width: 100%; height: 100%; object-fit: cover; }
    .storefront-quick-thumbnails { display: flex; gap: 8px; padding: 10px; overflow-x: auto; }
    .storefront-quick-thumbnails button { flex: 0 0 62px; width: 62px; height: 62px; padding: 2px; border: 2px solid transparent; border-radius: 10px; background: #fff; overflow: hidden; }
    .storefront-quick-thumbnails button.is-active { border-color: #1265f5; }
    .storefront-quick-thumbnails img { width: 100%; height: 100%; border-radius: 6px; object-fit: cover; }
    .storefront-quick-rating { display: flex; align-items: center; gap: 5px; margin: 7px 0 12px; color: #64748b; font-size: .86rem; }
    .storefront-quick-rating i { color: #f59e0b; }
    .storefront-quick-rating strong { color: #334155; }
    .storefront-quick-price { display: block; margin: 18px 0; color: #1265f5; font-size: 1.65rem; }
    .storefront-quick-product form { display: grid; gap: 12px; }
    .storefront-quick-product form > label:not(.storefront-quick-addon) { display: grid; gap: 5px; font-weight: 700; }
    .storefront-quick-product select,
    .storefront-quick-product textarea,
    .storefront-quick-product input[type="number"] { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 10px; }
    .storefront-quick-addon { display: flex; gap: 8px; align-items: center; }
    .storefront-quick-stock { min-height: 20px; margin: 0; color: #16834b; font-size: .82rem; font-weight: 800; }
    .storefront-quick-stock.is-low { color: #b45309; }
    .storefront-quick-stock.is-out { color: #c2414f; }
    .storefront-quick-delivery { display: grid; gap: 7px; margin-top: 14px; padding: 12px; border-radius: 12px; background: #f8fafc; color: #475569; font-size: .78rem; }
    .storefront-quick-delivery strong { color: #334155; }
    .storefront-quick-delivery i { width: 17px; color: #1265f5; text-align: center; }
    .storefront-quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .storefront-quick-actions button,
    .storefront-quick-full { padding: 11px; border: 0; border-radius: 10px; background: #1265f5; color: #fff; font-weight: 800; text-align: center; text-decoration: none; }
    .storefront-quick-actions button:disabled { cursor: not-allowed; opacity: .55; }
    .storefront-quick-full { display: block; margin-top: 12px; background: #eef2ff; color: #1e40af; }
    @media (max-width: 1199.98px) {
        .storefront-layout { grid-template-columns: minmax(0, 1fr) 300px; }
        .storefront-products-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .storefront-search { grid-template-columns: minmax(200px, 1fr) 180px; }
        .storefront-search > button { grid-column: span 2; }
    }
    @media (max-width: 991.98px) {
        .storefront-layout { grid-template-columns: 1fr; }
        .storefront-sidebar {
            position: static;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767.98px) {
        .storefront-shell { width: min(100% - 20px, 1440px); }
        .storefront-breadcrumb { padding: .75rem 2px; }
        .storefront-hero { min-height: 300px; border-radius: 18px; }
        .storefront-owner-tools { top: 12px; right: 12px; }
        .storefront-owner-tools a,
        .storefront-owner-tools button { min-height: 36px; padding: 0 11px; font-size: .7rem; }
        .storefront-identity { right: 18px; bottom: 20px; left: 18px; gap: 14px; }
        .storefront-logo { width: 104px; height: 104px; flex-basis: 104px; border-radius: 19px; }
        .storefront-logo img { border-radius: 12px; }
        .storefront-identity h1 { font-size: clamp(1.55rem, 7vw, 2.25rem); }
        .storefront-stats { margin: -1px 8px 0; }
        .storefront-stats > div { padding: 12px 9px; }
        .storefront-stat-icon { display: none; }
        .storefront-stats p { text-align: center; }
        .storefront-stats strong { font-size: .75rem; }
        .storefront-stats small { font-size: .6rem; }
        .storefront-layout { margin-top: 20px; }
        .storefront-gallery { margin-top: 15px; padding: 14px; }
        .storefront-gallery-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .storefront-catalog { padding: 17px; border-radius: 17px; }
        .storefront-promotions { padding: 17px; border-radius: 17px; }
        .storefront-promotions-grid { grid-template-columns: 1fr; }
        .storefront-section-heading { align-items: start; }
        .storefront-section-heading h2 { font-size: 1.15rem; }
        .storefront-section-heading p { display: none; }
        .storefront-search { grid-template-columns: 1fr; }
        .storefront-search > button { grid-column: auto; }
        .storefront-sidebar { grid-template-columns: 1fr; }
        .storefront-quick-product {
            width: 100%;
            max-width: none;
            max-height: 92vh;
            margin: auto 0 0;
            border-radius: 22px 22px 0 0;
        }
        .storefront-quick-grid { grid-template-columns: 1fr; }
        .storefront-quick-image { min-height: 230px; max-height: 34vh; }
        .storefront-quick-grid > div:last-child { padding: 22px; }
        .storefront-quick-actions { position: sticky; bottom: 0; z-index: 1; padding: 8px 0; background: #fff; }
    }
    @media (max-width: 479.98px) {
        .storefront-hero { min-height: 285px; }
        .storefront-owner-tools a { width: 36px; padding: 0; justify-content: center; }
        .storefront-owner-tools a { font-size: 0; }
        .storefront-owner-tools a i { font-size: .75rem; }
        .storefront-identity { align-items: end; }
        .storefront-logo { width: 84px; height: 84px; flex-basis: 84px; padding: 5px; border-radius: 16px; }
        .storefront-category,
        .storefront-featured { padding: 4px 7px; font-size: .56rem; }
        .storefront-identity h1 { font-size: 1.42rem; }
        .storefront-location,
        .storefront-rating { font-size: .68rem; }
        .storefront-stats > div:nth-child(3) { display: none; }
        .storefront-stats { grid-template-columns: repeat(2, 1fr); }
        .storefront-gallery-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .storefront-products-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
        .storefront-product-image { height: 142px; }
        .storefront-product-body { padding: 10px; }
        .storefront-product-body h3 { font-size: .78rem; }
        .storefront-product-footer { align-items: start; flex-direction: column; }
        .storefront-product-footer strong { font-size: .76rem; }
        .storefront-product-footer a { font-size: .62rem; }
        .storefront-product-image > small { font-size: .52rem; }
        .storefront-promotion-card { padding: 11px; }
        .storefront-promotion-discount { width: 64px; min-height: 64px; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const feedback = document.getElementById('store-share-feedback');
        const eventEndpoint = @json(route('store.events.store', $store));
        const csrfToken = @json(csrf_token());
        const storeUrl = @json(route('store.show', $store->slug));

        const recordStoreEvent = (eventType, adId = null) => {
            const payload = { event_type: eventType };
            if (adId) payload.ad_id = Number(adId);

            return fetch(eventEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            }).catch(() => null);
        };

        recordStoreEvent('page_view');

        const closeQuickProduct = (dialog, restoreUrl = true) => {
            if (dialog?.open) dialog.close();
            if (restoreUrl) history.replaceState({}, '', storeUrl);
        };
        document.querySelectorAll('[data-quick-product]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const dialog = document.getElementById(link.dataset.quickProduct);
                if (!dialog?.showModal) return;
                event.preventDefault();
                dialog.showModal();
                history.pushState({ quickProduct: dialog.id }, '', dialog.dataset.productUrl);
            });
        });
        document.querySelectorAll('.storefront-quick-product').forEach((dialog) => {
            const form = dialog.querySelector('[data-quick-form]');
            const updateQuickProduct = () => {
                if (!form) return;

                const variationSelect = form.querySelector('[name="variation_id"]');
                const variation = variationSelect?.selectedOptions[0];
                const hasSelectedVariation = !variationSelect || Boolean(variation?.value);
                const allowBackorders = form.dataset.allowBackorders === '1';
                const trackStock = hasSelectedVariation && variationSelect
                    ? variation?.dataset.trackStock === '1'
                    : form.dataset.productTrackStock === '1';
                const stock = Number(hasSelectedVariation && variationSelect
                    ? variation?.dataset.stock
                    : form.dataset.productStock);
                const quantity = form.querySelector('[name="quantity"]');
                const stockMessage = form.querySelector('[data-quick-stock]');
                const submitButtons = form.querySelectorAll('button[type="submit"]');
                let total = Number(variation?.dataset.price || form.dataset.basePrice);

                form.querySelectorAll('[data-addon-price]:checked').forEach((addon) => total += Number(addon.dataset.addonPrice));
                dialog.querySelector('[data-quick-price]').textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                const selectedImage = variation?.dataset.image || form.dataset.defaultImage;
                const quickImage = dialog.querySelector('[data-quick-image]');
                if (selectedImage && quickImage) quickImage.src = selectedImage;

                if (!hasSelectedVariation) {
                    stockMessage.textContent = 'Selecione uma opção para consultar a disponibilidade.';
                    stockMessage.className = 'storefront-quick-stock';
                    submitButtons.forEach((button) => button.disabled = false);
                    return;
                }

                if (trackStock && !allowBackorders) {
                    quantity.max = Math.max(1, stock);
                    if (Number(quantity.value) > stock) quantity.value = Math.max(Number(quantity.min), stock);
                } else {
                    quantity.max = 99;
                }

                const unavailable = trackStock && !allowBackorders && stock < Number(quantity.min);
                submitButtons.forEach((button) => button.disabled = unavailable);
                stockMessage.className = `storefront-quick-stock${unavailable ? ' is-out' : (trackStock && stock <= 3 ? ' is-low' : '')}`;
                stockMessage.textContent = unavailable
                    ? 'Opção esgotada.'
                    : (trackStock ? `${stock} ${stock === 1 ? 'unidade disponível' : 'unidades disponíveis'}` : 'Disponível para compra');
            };

            dialog.querySelector('[data-quick-close]')?.addEventListener('click', () => closeQuickProduct(dialog));
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) closeQuickProduct(dialog);
            });
            dialog.addEventListener('close', () => {
                if (location.pathname !== new URL(storeUrl).pathname) {
                    history.replaceState({}, '', storeUrl);
                }
            });
            form?.addEventListener('change', updateQuickProduct);
            dialog.querySelectorAll('[data-quick-thumbnail]').forEach((thumbnail) => {
                thumbnail.addEventListener('click', () => {
                    const quickImage = dialog.querySelector('[data-quick-image]');
                    if (quickImage) quickImage.src = thumbnail.dataset.image;
                    dialog.querySelectorAll('[data-quick-thumbnail]').forEach((item) => item.classList.toggle('is-active', item === thumbnail));
                });
            });
            updateQuickProduct();
        });
        window.addEventListener('popstate', () => {
            document.querySelectorAll('.storefront-quick-product[open]').forEach((dialog) => closeQuickProduct(dialog, false));
        });

        document.querySelectorAll('[data-store-event]').forEach((element) => {
            element.addEventListener('click', () => {
                recordStoreEvent(element.dataset.storeEvent, element.dataset.adId || null);
            });
        });

        const showFeedback = (message) => {
            if (!feedback) return;
            feedback.textContent = message;
            feedback.classList.add('is-visible');
            window.setTimeout(() => feedback.classList.remove('is-visible'), 2200);
        };

        document.querySelectorAll('[data-coupon-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                const coupon = button.dataset.couponCopy;
                try {
                    await navigator.clipboard.writeText(coupon);
                    showFeedback(`Cupom ${coupon} copiado.`);
                } catch (error) {
                    const input = document.createElement('input');
                    input.value = coupon;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                    showFeedback(`Cupom ${coupon} copiado.`);
                }
            });
        });

        const bannerUrls = @json($storeBannerUrls);
        const heroBackground = document.getElementById('storefront-hero-background');
        if (
            heroBackground
            && bannerUrls.length > 1
            && !window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            let bannerIndex = 0;
            window.setInterval(() => {
                heroBackground.classList.add('is-changing');
                window.setTimeout(() => {
                    bannerIndex = (bannerIndex + 1) % bannerUrls.length;
                    heroBackground.style.backgroundImage = `url("${bannerUrls[bannerIndex]}")`;
                    heroBackground.classList.remove('is-changing');
                }, 450);
            }, 8000);
        }

        const galleryUrls = @json($storeGallery->map(fn ($media) => asset($media->path))->values());
        const galleryDialog = document.getElementById('storefront-gallery-dialog');
        const galleryDialogImage = document.getElementById('storefront-gallery-dialog-image');
        const galleryCounter = document.getElementById('storefront-gallery-counter');
        let galleryIndex = 0;

        const renderGalleryImage = () => {
            if (!galleryDialogImage || galleryUrls.length === 0) return;
            galleryDialogImage.src = galleryUrls[galleryIndex];
            galleryDialogImage.alt = `Foto ${galleryIndex + 1} de ${galleryUrls.length} da loja`;
            if (galleryCounter) {
                galleryCounter.textContent = `${galleryIndex + 1} de ${galleryUrls.length}`;
            }
        };

        document.querySelectorAll('[data-store-gallery-index]').forEach((button) => {
            button.addEventListener('click', () => {
                galleryIndex = Number(button.dataset.storeGalleryIndex) || 0;
                renderGalleryImage();
                galleryDialog?.showModal();
            });
        });

        document.querySelector('[data-store-gallery-close]')?.addEventListener('click', () => galleryDialog?.close());
        document.querySelector('[data-store-gallery-previous]')?.addEventListener('click', () => {
            galleryIndex = (galleryIndex - 1 + galleryUrls.length) % galleryUrls.length;
            renderGalleryImage();
        });
        document.querySelector('[data-store-gallery-next]')?.addEventListener('click', () => {
            galleryIndex = (galleryIndex + 1) % galleryUrls.length;
            renderGalleryImage();
        });
        galleryDialog?.addEventListener('click', (event) => {
            if (event.target === galleryDialog) galleryDialog.close();
        });
    });
</script>
@endpush
@endsection
