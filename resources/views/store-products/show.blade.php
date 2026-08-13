@extends('layouts.app')

@section('title', $seoData['title'])

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $seoData['title'],
        'socialDescription' => $seoData['description'],
        'socialUrl' => $seoData['canonical'],
        'socialImage' => $seoData['image'],
        'socialType' => 'product',
    ])
    <meta name="robots" content="index, follow">
    <meta property="product:price:amount" content="{{ number_format($product->effective_price, 2, '.', '') }}">
    <meta property="product:price:currency" content="BRL">
    <script type="application/ld+json">{!! json_encode($seoData['jsonLd'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($seoData['breadcrumbJsonLd'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/store-product.css') }}?v=1.0">
@endpush

@section('content')
<main class="product-page">
    <div class="product-page-shell">
        <nav class="product-breadcrumb" aria-label="Navegação estrutural">
            <a href="{{ route('home') }}"><i class="fa-solid fa-house me-1"></i>Início</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route('stores.index') }}">Lojas</a>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route('store.show', $store->slug) }}">{{ $store->name }}</a>
            @if($product->category)
                @foreach($product->category->category_trail as $catBranch)
                    <i class="fa-solid fa-chevron-right"></i>
                    <a href="{{ route('module.products', ['category' => $catBranch->slug]) }}">{{ $catBranch->name }}</a>
                @endforeach
            @endif
            <i class="fa-solid fa-chevron-right"></i>
            <span class="text-truncate" style="max-width: 280px;">{{ $product->title }}</span>
        </nav>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->has('cart'))<div class="alert alert-warning">{{ $errors->first('cart') }}</div>@endif
        @if($errors->any() && !$errors->has('cart'))<div class="alert alert-warning">{{ $errors->first() }}</div>@endif

        <div class="product-main-grid">
            <section class="product-gallery" aria-label="Galeria do produto">
                @php
                    $gallery = $product->images->pluck('image_path')
                        ->prepend($product->card_image)
                        ->filter()
                        ->unique()
                        ->values();
                    if ($gallery->isEmpty()) $gallery->push('images/logo.png');
                @endphp
                <div class="product-main-image">
                    <img id="product-main-image" src="{{ asset($gallery->first()) }}" alt="{{ $product->title }}">
                </div>
                @if($gallery->count() > 1)
                    <div class="product-thumbnails">
                        @foreach($gallery as $image)
                            <button type="button" data-product-image="{{ asset($image) }}" @class(['is-active' => $loop->first])>
                                <img src="{{ asset($image) }}" alt="Imagem {{ $loop->iteration }} de {{ $product->title }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="product-purchase-card">
                <span class="product-category">{{ $product->display_category }}</span>
                <h1>{{ $product->title }}</h1>
                <div class="product-rating">
                    <span><i class="fa-solid fa-star"></i> {{ $reviewData['count'] ? number_format($reviewData['average'], 1, ',', '.') : 'Novo' }}</span>
                    <small>{{ $reviewData['count'] }} {{ $reviewData['count'] === 1 ? 'avaliação' : 'avaliações' }}</small>
                    <small><i class="fa-solid fa-bag-shopping"></i> {{ $salesCount }} {{ $salesCount === 1 ? 'unidade vendida' : 'unidades vendidas' }}</small>
                </div>
                <p class="product-location"><i class="fa-solid fa-location-dot"></i> {{ $product->city }}/SE</p>

                @if($product->effective_price > 0)
                    <div class="product-price">
                        @if($product->sale_price !== null && (float) $product->sale_price < (float) $product->price)
                            <small><s>R$ {{ number_format((float) $product->price, 2, ',', '.') }}</s></small>
                        @endif
                        <span data-product-price>R$ {{ number_format($product->effective_price, 2, ',', '.') }}</span>
                        <small>Pagamento combinado após a confirmação do pedido. Nenhuma cobrança é feita agora.</small>
                    </div>
                @else
                    <div class="product-price is-negotiable">Preço a combinar</div>
                @endif
                <div class="product-stock">
                    <div data-product-stock-status role="status" aria-live="polite">
                        @if($product->is_out_of_stock)
                            <strong class="text-danger">Produto esgotado</strong>
                        @elseif($product->is_low_stock)
                            <strong class="text-warning">Últimas {{ $product->stock_quantity }} unidades</strong>
                        @else
                            <span class="text-success">Disponível</span>
                        @endif
                    </div>
                    @if($product->sku)<small>SKU: {{ $product->sku }}</small>@endif
                </div>

                <p class="product-description-short">{{ \Illuminate\Support\Str::limit($product->description, 220) }}</p>

                @if($product->effective_price > 0 && !$product->is_out_of_stock)
                    <form action="{{ route('cart.add', $product) }}" method="POST" class="product-buy-form" data-configurable-product data-base-price="{{ $product->effective_price }}" data-product-stock="{{ $product->stock_quantity }}" data-product-track-stock="{{ $product->track_stock ? '1' : '0' }}" data-allow-backorders="{{ $product->allow_backorders ? '1' : '0' }}" data-low-stock-threshold="{{ $product->low_stock_threshold }}">
                        @csrf
                        @if($product->activeVariations->isNotEmpty())
                            <label for="product-variation">Escolha uma opção</label>
                            <select id="product-variation" name="variation_id" required>
                                <option value="">Selecione</option>
                                @foreach($product->activeVariations as $variation)
                                    <option
                                        value="{{ $variation->id }}"
                                        data-price="{{ $variation->price !== null ? (float) $variation->price : $product->effective_price + (float) $variation->price_adjustment }}"
                                        data-image="{{ $variation->image ? asset($variation->image) : '' }}"
                                        data-stock="{{ $variation->stock_quantity }}"
                                        data-track-stock="{{ $variation->track_stock ? '1' : '0' }}"
                                        @disabled($variation->track_stock && !$product->allow_backorders && $variation->stock_quantity < 1)
                                    >
                                        {{ $variation->name }}
                                        @if($variation->track_stock && $variation->stock_quantity < 1) — esgotado @endif
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @if($product->activeAddons->isNotEmpty())
                            <fieldset class="product-options">
                                <legend>Adicionais</legend>
                                @foreach($product->activeAddons as $addon)
                                    <label>
                                        <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" data-addon-price="{{ (float) $addon->price }}">
                                        {{ $addon->name }} (+ R$ {{ number_format((float) $addon->price, 2, ',', '.') }})
                                    </label>
                                @endforeach
                            </fieldset>
                        @endif
                        <label for="product-note">Observação</label>
                        <textarea id="product-note" name="note" rows="2" maxlength="500" placeholder="Ex.: retirar cebola, embalagem para presente"></textarea>
                        <label for="product-quantity">Quantidade</label>
                        <input id="product-quantity" type="number" name="quantity" min="{{ $product->minimum_quantity }}" max="{{ $product->track_stock && !$product->allow_backorders ? max(1, $product->stock_quantity) : 99 }}" value="{{ $product->minimum_quantity }}" required>
                        <div class="product-buy-actions">
                            <button type="submit" class="is-secondary"><i class="fa-solid fa-cart-plus"></i> Adicionar ao carrinho</button>
                            <button type="submit" name="buy_now" value="1" class="is-primary"><i class="fa-solid fa-bolt"></i> Comprar agora</button>
                        </div>
                    </form>
                @endif

                <div class="product-delivery-info">
                    <strong class="text-{{ $businessStatus['state'] === 'open' ? 'success' : ($businessStatus['state'] === 'closed' ? 'danger' : 'secondary') }}">
                        <i class="fa-regular fa-clock"></i> {{ $businessStatus['label'] }} · {{ $businessStatus['detail'] }}
                    </strong>
                    @if($store->delivery_available)
                        <span><i class="fa-solid fa-truck"></i> Entrega disponível
                            @if((float) $store->delivery_fee > 0) a partir de R$ {{ number_format((float) $store->delivery_fee, 2, ',', '.') }} @endif
                        </span>
                        @if($store->free_delivery_threshold)
                            <span><i class="fa-solid fa-gift"></i> Entrega grátis acima de R$ {{ number_format((float) $store->free_delivery_threshold, 2, ',', '.') }}</span>
                        @endif
                        @if($store->delivery_min_minutes)
                            <span><i class="fa-solid fa-stopwatch"></i> Prazo estimado: {{ $store->delivery_min_minutes }}–{{ $store->delivery_max_minutes ?: $store->delivery_min_minutes }} minutos</span>
                        @endif
                    @endif
                    @if($store->pickup_available)
                        <span><i class="fa-solid fa-store"></i> Retirada disponível{{ $store->pickup_address ? ' em '.$store->pickup_address : '' }}</span>
                    @endif
                    @if((float) $store->minimum_order > 0)
                        <span><i class="fa-solid fa-receipt"></i> Pedido mínimo da loja: R$ {{ number_format((float) $store->minimum_order, 2, ',', '.') }}</span>
                    @endif
                </div>

                <button type="button" class="product-share" data-social-share data-share-url="{{ $seoData['canonical'] }}" data-share-title="{{ $product->title }}" data-share-text="Confira {{ $product->title }} da {{ $store->name }} no Conectado em Sergipe.">
                    <i class="fa-solid fa-share-nodes"></i> Compartilhar produto
                </button>
                @auth
                    <form action="{{ route('products.favorite.toggle', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="product-share">
                            <i class="{{ $isFavorite ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
                            {{ $isFavorite ? 'Remover dos favoritos' : 'Adicionar aos favoritos' }}
                        </button>
                    </form>
                @else
                    <a class="product-share product-login-link" href="{{ route('login') }}"><i class="fa-regular fa-heart"></i> Entre para favoritar</a>
                @endauth

                <div class="product-store-summary">
                    @if($store->logo)<img src="{{ asset($store->logo) }}" alt="">@endif
                    <div>
                        <small>Vendido por</small>
                        <strong>{{ $store->name }}</strong>
                        <span>{{ $store->city }}/{{ $store->state }}</span>
                    </div>
                    <a href="{{ route('store.show', $store->slug) }}">Ver loja</a>
                </div>
            </section>
        </div>

        <section class="product-content-card">
            <h2>Descrição do produto</h2>
            <p>{!! nl2br(e($product->description)) !!}</p>
        </section>

        @if($videoEmbedUrl || $product->video_url)
            <section class="product-content-card">
                <h2>Vídeo do produto</h2>
                @if($videoEmbedUrl)
                    <div class="product-video"><iframe src="{{ $videoEmbedUrl }}" title="Vídeo de {{ $product->title }}" loading="lazy" allowfullscreen></iframe></div>
                @else
                    <a href="{{ $product->video_url }}" target="_blank" rel="noopener noreferrer">Assistir vídeo do produto</a>
                @endif
            </section>
        @endif

        @if(collect($product->technical_specs)->isNotEmpty())
            <section class="product-content-card">
                <h2>Ficha técnica</h2>
                <dl class="product-specs">
                    @foreach($product->technical_specs as $label => $value)
                        <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </section>
        @endif

        @if($reviewData['count'])
            <section class="product-content-card">
                <h2>Avaliações</h2>
                <div class="product-review-summary">
                    <strong>{{ number_format($reviewData['average'], 1, ',', '.') }}</strong>
                    <span><i class="fa-solid fa-star"></i> {{ $reviewData['count'] }} avaliações verificadas</span>
                </div>
                @foreach($reviewData['reviews']->take(5) as $review)
                    <article class="product-review">
                        <strong>{{ $review->user?->name ?: 'Cliente' }}</strong>
                        <span>{{ str_repeat('★', $review->rating) }}</span>
                        <p>{{ $review->comment }}</p>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="product-content-card" id="perguntas">
            <h2>Perguntas sobre o produto</h2>
            @auth
                @if(auth()->id() !== $product->user_id)
                    <form action="{{ route('products.questions.store', $product) }}" method="POST" class="product-question-form">
                        @csrf
                        <label for="product-question">Tire sua dúvida com a loja</label>
                        <textarea id="product-question" name="question" rows="3" maxlength="1000" required></textarea>
                        <button type="submit">Enviar pergunta</button>
                    </form>
                @endif
            @else
                <p><a href="{{ route('login') }}">Entre na sua conta</a> para enviar uma pergunta.</p>
            @endauth

            @forelse($product->questions->where('active', true) as $question)
                <article class="product-question">
                    <strong>{{ $question->user?->name ?: 'Cliente' }}</strong>
                    <p>{{ $question->question }}</p>
                    @if($question->answer)
                        <div><strong>{{ $store->name }} respondeu:</strong><p>{{ $question->answer }}</p></div>
                    @elseif(auth()->id() === $product->user_id || auth()->user()?->role === 'admin')
                        <form action="{{ route('products.questions.answer', $question) }}" method="POST">
                            @csrf
                            <textarea name="answer" rows="2" maxlength="2000" required></textarea>
                            <button type="submit">Responder</button>
                        </form>
                    @else
                        <small>Aguardando resposta da loja.</small>
                    @endif
                </article>
            @empty
                <p class="product-empty-copy">Ainda não há perguntas. Seja o primeiro a perguntar.</p>
            @endforelse
        </section>

        @if($relatedProducts->isNotEmpty())
            <section class="product-related">
                <h2>Outros produtos da {{ $store->name }}</h2>
                <div class="product-related-grid">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('store.products.show', [$store, $related]) }}">
                            <div>
                                @if($related->card_image)<img src="{{ asset($related->card_image) }}" alt="">@else<i class="fa-solid fa-image"></i>@endif
                            </div>
                            <strong>{{ $related->title }}</strong>
                            <span>R$ {{ number_format($related->effective_price, 2, ',', '.') }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-product-image]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('product-main-image').src = button.dataset.productImage;
            document.querySelectorAll('[data-product-image]').forEach((item) => item.classList.remove('is-active'));
            button.classList.add('is-active');
        });
    });

    document.querySelectorAll('[data-configurable-product]').forEach((form) => {
        const purchaseCard = form.closest('.product-purchase-card');
        const price = purchaseCard.querySelector('[data-product-price]');
        const stockStatus = purchaseCard.querySelector('[data-product-stock-status]');
        const quantity = form.querySelector('[name="quantity"]');
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        const updateProduct = () => {
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
            let total = Number(variation?.dataset.price || form.dataset.basePrice);

            if (variation?.dataset.image) document.getElementById('product-main-image').src = variation.dataset.image;
            form.querySelectorAll('[data-addon-price]:checked').forEach((addon) => total += Number(addon.dataset.addonPrice));
            if (price) price.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            if (!hasSelectedVariation) {
                stockStatus.innerHTML = '<span class="text-secondary">Selecione uma opção para consultar o estoque.</span>';
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
            const lowStock = trackStock && stock <= Number(form.dataset.lowStockThreshold);
            submitButtons.forEach((button) => button.disabled = unavailable);
            stockStatus.innerHTML = unavailable
                ? '<strong class="text-danger">Opção esgotada</strong>'
                : (lowStock
                    ? `<strong class="text-warning">Últimas ${stock} unidades</strong>`
                    : '<span class="text-success">Disponível</span>');
        };
        form.addEventListener('change', updateProduct);
        updateProduct();
    });

</script>
@endpush
