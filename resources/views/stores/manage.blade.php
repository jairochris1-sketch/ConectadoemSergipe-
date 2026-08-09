@extends('layouts.app')

@section('title', ($store ? 'Gerenciar minha loja' : ($requestedCategory === 'Alimentação' ? 'Cadastrar restaurante' : 'Criar minha loja')) . ' - Conectado em Sergipe')

@section('content')
@php
    $editing = !is_null($store);
@endphp
<main class="store-management-page">
    <div class="container store-management-container">
        <div class="store-management-heading">
            <div>
                <a href="{{ route('user.panel') }}" class="store-management-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Voltar ao meu painel
                </a>
                <span class="store-management-eyebrow">
                    <i class="fa-solid fa-store"></i>
                    Minha loja
                </span>
                <h1>{{ $editing ? 'Gerenciar minha loja' : ($requestedCategory === 'Alimentação' ? 'Cadastre seu restaurante' : 'Crie sua vitrine comercial') }}</h1>
                <p>{{ $editing ? 'Atualize as informações que seus clientes encontram na vitrine.' : ($requestedCategory === 'Alimentação' ? 'Apresente seu cardápio, atendimento e opções de entrega aos clientes de Sergipe.' : 'Cadastre sua loja para apresentar seus produtos aos clientes de Sergipe.') }}</p>
            </div>

            @if($editing)
                <div class="d-flex flex-column gap-2 align-items-end">
                    <a href="{{ route('seller.orders.index', $store) }}" class="btn btn-success rounded-pill px-3 fw-bold">
                        <i class="fa-solid fa-box me-1"></i> Pedidos recebidos
                    </a>
                    <div class="store-management-status {{ !$store->isModerationApproved() ? 'is-suspended' : ($store->active ? 'is-active' : 'is-inactive') }}">
                        <i class="fa-solid {{ !$store->isModerationApproved() ? 'fa-shield-halved' : ($store->active ? 'fa-circle-check' : 'fa-circle-pause') }}"></i>
                        <span>
                            <strong>{{ !$store->isModerationApproved() ? 'Loja suspensa' : ($store->active ? 'Loja ativa' : 'Loja desativada') }}</strong>
                            <small>{{ !$store->isModerationApproved() ? 'Bloqueada pela moderação' : ($store->active ? 'Visível na vitrine pública' : 'Oculta da vitrine pública') }}</small>
                        </span>
                    </div>
                </div>
            @endif
        </div>

        @if(session('store_success'))
            <div class="alert alert-success rounded-4">{{ session('store_success') }}</div>
        @endif
        @if(session('store_warning'))
            <div class="alert alert-warning rounded-4">{{ session('store_warning') }}</div>
        @endif
        <div class="alert alert-info rounded-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <strong>Plano {{ $planLabel }}</strong>
                <span class="d-block small">
                    {{ $storeUsage }} de {{ $storeLimit === null ? 'ilimitadas' : $storeLimit }} lojas
                    @if($editing)
                        · {{ $store->products_count }} de {{ $productLimit === null ? 'ilimitados' : $productLimit }} produtos nesta loja
                    @endif
                </span>
                @if($editing && $store->isCurrentlyFeatured())
                    <span class="d-block small text-warning fw-bold mt-1">
                        <i class="fa-solid fa-star"></i>
                        Em destaque até {{ $store->featured_until?->format('d/m/Y') ?: 'a remoção administrativa' }}
                    </span>
                @elseif($editing && auth()->user()->canHaveFeaturedStore())
                    <span class="d-block small mt-1">Seu plano permite que esta loja seja selecionada para destaque.</span>
                @endif
            </div>
            @if($storeLimit !== null || $productLimit !== null)
                <a href="{{ route('page.plans') }}" class="btn btn-sm btn-primary rounded-pill px-3">Ver planos</a>
            @endif
        </div>
        @if($editing && !$store->isModerationApproved())
            <div class="alert alert-danger rounded-4">
                <strong><i class="fa-solid fa-shield-halved me-1"></i> Loja suspensa pela moderação.</strong>
                @if($store->moderation_note)
                    <span class="d-block mt-1">{{ $store->moderation_note }}</span>
                @endif
                <small class="d-block mt-1">Você pode corrigir os dados, mas somente a administração poderá liberar a loja novamente.</small>
            </div>
        @endif

        @if($editing)
            <section class="store-products-manager" aria-labelledby="store-products-manager-title">
                <div class="store-products-manager-heading">
                    <div>
                        <span class="store-management-eyebrow">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            Catálogo
                        </span>
                        <h2 id="store-products-manager-title">Produtos da loja</h2>
                        <p>Acompanhe preço, código e estoque sem sair do gerenciamento.</p>
                    </div>
                    <a href="{{ route('ad.create', ['module' => 'products', 'store_id' => $store->id]) }}" class="btn btn-primary rounded-pill px-3 fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> Novo produto
                    </a>
                </div>

                @if($storeProducts->isEmpty())
                    <div class="store-products-manager-empty">
                        <i class="fa-solid fa-box-open"></i>
                        <p>Esta loja ainda não possui produtos cadastrados.</p>
                    </div>
                @else
                    <div class="store-products-manager-list">
                        @foreach($storeProducts as $product)
                            @php
                                $stockLabel = !$product->track_stock
                                    ? 'Estoque livre'
                                    : ($product->is_out_of_stock ? 'Esgotado' : ($product->is_low_stock ? 'Estoque baixo' : $product->stock_quantity.' em estoque'));
                                $stockClass = !$product->track_stock
                                    ? 'is-untracked'
                                    : ($product->is_out_of_stock ? 'is-out' : ($product->is_low_stock ? 'is-low' : 'is-ok'));
                            @endphp
                            <article>
                                <div class="store-products-manager-image">
                                    @if($product->card_image)
                                        <img src="{{ asset($product->card_image) }}" alt="">
                                    @else
                                        <i class="fa-solid fa-image"></i>
                                    @endif
                                </div>
                                <div class="store-products-manager-info">
                                    <div>
                                        <h3>{{ $product->title }}</h3>
                                        <span class="store-products-status is-{{ $product->status }}">{{ $product->status === 'active' ? 'Ativo' : 'Inativo' }}</span>
                                    </div>
                                    <p>
                                        <strong>R$ {{ number_format($product->effective_price, 2, ',', '.') }}</strong>
                                        <span>SKU: {{ $product->sku ?: 'não informado' }}</span>
                                        <span>{{ $product->variations_count }} {{ $product->variations_count === 1 ? 'variação' : 'variações' }}</span>
                                        <span>{{ $product->active_addons_count }} {{ $product->active_addons_count === 1 ? 'adicional' : 'adicionais' }}</span>
                                    </p>
                                </div>
                                <span class="store-products-stock {{ $stockClass }}">{{ $stockLabel }}</span>
                                <div class="store-products-manager-actions">
                                    @if($product->status === 'active')
                                        <a href="{{ route('store.products.show', [$store, $product]) }}" aria-label="Ver {{ $product->title }}"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                    @endif
                                    <a href="{{ route('ad.edit', $product) }}" aria-label="Editar {{ $product->title }}"><i class="fa-solid fa-pen"></i></a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-4">
                <strong>Confira os campos da loja:</strong>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($editing && $analytics)
            @php
                $analyticsMetrics = [
                    ['key' => 'views', 'label' => 'Visualizações', 'icon' => 'fa-eye'],
                    ['key' => 'contacts', 'label' => 'Contatos', 'icon' => 'fa-comments'],
                    ['key' => 'product_clicks', 'label' => 'Produtos abertos', 'icon' => 'fa-box-open'],
                    ['key' => 'shares', 'label' => 'Compartilhamentos', 'icon' => 'fa-share-nodes'],
                ];
                $dailyMax = max(1, collect($analytics['daily_views'])->max('value') ?? 1);
                $contactMax = max(1, collect($analytics['contact_breakdown'])->max('value') ?? 1);
            @endphp
            <section class="store-analytics" aria-labelledby="store-analytics-title">
                <div class="store-analytics-heading">
                    <div>
                        <span class="store-management-eyebrow">
                            <i class="fa-solid fa-chart-line"></i>
                            Estatísticas da loja
                        </span>
                        <h2 id="store-analytics-title">Desempenho da sua vitrine</h2>
                        <p>{{ $analytics['period_label'] }} · acessos do proprietário não são contabilizados.</p>
                    </div>
                    <span class="store-analytics-plan">
                        <i class="fa-solid fa-gem"></i>
                        Plano {{ $planLabel }}
                    </span>
                </div>

                <div class="store-analytics-metrics">
                    @foreach($analyticsMetrics as $metric)
                        @php
                            $metricData = $analytics['summary'][$metric['key']];
                        @endphp
                        <article>
                            <span><i class="fa-solid {{ $metric['icon'] }}"></i></span>
                            <div>
                                <strong>{{ number_format($metricData['value'], 0, ',', '.') }}</strong>
                                <small>{{ $metric['label'] }}</small>
                            </div>
                            <em class="{{ $metricData['change'] < 0 ? 'is-negative' : ($metricData['change'] > 0 ? 'is-positive' : '') }}">
                                <i class="fa-solid {{ $metricData['change'] < 0 ? 'fa-arrow-down' : ($metricData['change'] > 0 ? 'fa-arrow-up' : 'fa-minus') }}"></i>
                                {{ abs($metricData['change']) }}%
                            </em>
                        </article>
                    @endforeach
                </div>

                <div class="store-analytics-grid">
                    <article class="store-analytics-card store-analytics-chart-card">
                        <header>
                            <div>
                                <h3>Visualizações por dia</h3>
                                <p>Exibição diária dos últimos {{ count($analytics['daily_views']) }} dias.</p>
                            </div>
                            <span>{{ number_format($analytics['conversion_rate'], 1, ',', '.') }}% conversão</span>
                        </header>
                        <div class="store-analytics-chart" aria-label="Gráfico de visualizações por dia" style="--analytics-columns: {{ count($analytics['daily_views']) }}">
                            @foreach($analytics['daily_views'] as $day)
                                <div title="{{ $day['label'] }}: {{ $day['value'] }} visualizações">
                                    <span style="height: {{ max(4, round(($day['value'] / $dailyMax) * 100)) }}%"></span>
                                    @if($loop->first || $loop->last || $loop->iteration % max(1, (int) ceil(count($analytics['daily_views']) / 5)) === 0)
                                        <small>{{ $day['label'] }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="store-analytics-card">
                        <header>
                            <div>
                                <h3>Canais de contato</h3>
                                <p>Onde os clientes demonstraram interesse.</p>
                            </div>
                        </header>
                        <div class="store-contact-breakdown">
                            @foreach($analytics['contact_breakdown'] as $channel)
                                <div>
                                    <span><i class="{{ $channel['icon'] }}"></i> {{ $channel['label'] }}</span>
                                    <div><i style="width: {{ round(($channel['value'] / $contactMax) * 100) }}%"></i></div>
                                    <strong>{{ $channel['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </div>

                <article class="store-analytics-card store-top-products">
                    <header>
                        <div>
                            <h3>Produtos com mais interesse</h3>
                            <p>Cliques recebidos a partir da vitrine da loja.</p>
                        </div>
                    </header>
                    @if($analytics['top_products']->isEmpty())
                        <p class="store-analytics-empty">Os produtos aparecerão aqui assim que receberem acessos pela loja.</p>
                    @else
                        <ol>
                            @foreach($analytics['top_products'] as $product)
                                <li>
                                    <span>{{ $loop->iteration }}</span>
                                    <a href="{{ route('ad.show', $product['slug']) }}">{{ $product['title'] }}</a>
                                    <strong>{{ $product['clicks'] }} {{ $product['clicks'] === 1 ? 'clique' : 'cliques' }}</strong>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>
            </section>
        @endif

        <form
            action="{{ $editing ? route('store.update_specific', $store) : route('store.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="store-management-form"
        >
            @csrf
            @if($editing) @method('PUT') @endif

            <section class="store-management-card">
                <div class="store-management-section-title">
                    <span><i class="fa-solid fa-image"></i></span>
                    <div>
                        <h2>Identidade visual</h2>
                        <p>Use imagens próprias e com boa qualidade.</p>
                    </div>
                </div>

                <div class="store-media-fields">
                    <div class="store-logo-field">
                        <div class="store-logo-header">
                            <div id="store-logo-preview" class="store-logo-preview">
                                @if($store?->logo)
                                    <img src="{{ asset($store->logo) }}" alt="Logo atual da loja">
                                @else
                                    <i class="fa-solid fa-store"></i>
                                @endif
                            </div>
                            <label for="store-logo-input" class="store-media-label">
                                <strong>Logo da loja</strong>
                                <span>Quadrado, JPG, PNG ou WEBP. Até 5 MB.</span>
                            </label>
                        </div>
                        <input id="store-logo-input" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
                        @if($store?->logo)
                            <label class="store-remove-media">
                                <input type="checkbox" name="remove_logo" value="1">
                                Remover logo atual
                            </label>
                        @endif
                    </div>

                    <div class="store-banner-field">
                        <div id="store-banner-preview" class="store-banner-preview">
                            @if($store?->banner)
                                <img src="{{ asset($store->banner) }}" alt="Banner atual da loja">
                            @else
                                <div><i class="fa-solid fa-panorama"></i><span>Prévia do banner</span></div>
                            @endif
                        </div>
                        <label for="store-banner-input" class="store-media-label">
                            <strong>Banner da loja</strong>
                            <span>Imagem horizontal, JPG, PNG ou WEBP. Até 8 MB.</span>
                        </label>
                        <input id="store-banner-input" type="file" name="banner" accept=".jpg,.jpeg,.png,.webp">
                        @if($store?->banner)
                            <label class="store-remove-media">
                                <input type="checkbox" name="remove_banner" value="1">
                                Remover banner atual
                            </label>
                        @endif
                    </div>
                </div>

                <div class="store-media-plan-summary">
                    <span>
                        <i class="fa-solid fa-panorama"></i>
                        Banners: {{ $bannerUsage }} de {{ $bannerLimit === null ? 'ilimitados' : $bannerLimit }}
                    </span>
                    <span>
                        <i class="fa-solid fa-images"></i>
                        Galeria: {{ $galleryUsage }} de {{ $galleryLimit === null ? 'ilimitadas' : $galleryLimit }} fotos
                    </span>
                </div>

                @if($editing && $store->media->isNotEmpty())
                    <div class="store-existing-media">
                        @foreach($store->media as $media)
                            <label>
                                <img src="{{ asset($media->path) }}" alt="">
                                <span>{{ $media->type === 'banner' ? 'Banner adicional' : 'Foto da galeria' }}</span>
                                <span class="store-existing-media-remove">
                                    <input type="checkbox" name="remove_media_ids[]" value="{{ $media->id }}">
                                    Excluir
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="store-extra-media-fields">
                    <label>
                        <strong>Banners adicionais</strong>
                        <span>
                            O banner principal também conta no limite.
                            Você pode selecionar vários arquivos.
                        </span>
                        <input
                            type="file"
                            name="additional_banners[]"
                            accept=".jpg,.jpeg,.png,.webp"
                            multiple
                        >
                    </label>
                    <label>
                        <strong>Galeria da loja</strong>
                        <span>Mostre o ambiente, produtos, equipe ou trabalhos realizados.</span>
                        <input
                            type="file"
                            name="gallery_images[]"
                            accept=".jpg,.jpeg,.png,.webp"
                            multiple
                        >
                    </label>
                </div>
            </section>

            <section class="store-management-card">
                <div class="store-management-section-title">
                    <span><i class="fa-solid fa-pen-to-square"></i></span>
                    <div>
                        <h2>Informações da loja</h2>
                        <p>Explique claramente o que sua empresa oferece.</p>
                    </div>
                </div>

                <div class="store-form-grid">
                    <label class="store-form-field store-form-field-wide">
                        <span>Nome da loja *</span>
                        <input type="text" name="name" value="{{ old('name', $store?->name) }}" maxlength="120" required>
                    </label>

                    <label class="store-form-field">
                        <span>Categoria *</span>
                        <select name="category" id="store-category" required>
                            <option value="">Escolha uma categoria</option>
                            @foreach($categories as $categoryOption)
                                <option value="{{ $categoryOption['name'] }}" @selected(old('category', $store?->category ?? $requestedCategory) === $categoryOption['name'])>
                                    {{ $categoryOption['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="store-form-field">
                        <span>Cidade *</span>
                        <select name="city" required>
                            <option value="">Escolha a cidade</option>
                            @foreach($cities as $cityName)
                                <option value="{{ $cityName }}" @selected(old('city', $store?->city ?: auth()->user()->city) === $cityName)>
                                    {{ $cityName }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="store-form-field store-form-field-wide">
                        <span>Descrição</span>
                        <textarea name="description" rows="5" maxlength="2000" placeholder="Conte sobre sua loja, produtos, diferenciais e atendimento.">{{ old('description', $store?->description) }}</textarea>
                        <small>Máximo de 2.000 caracteres.</small>
                    </label>

                    <div class="store-form-field store-form-field-wide">
                        <span>Formato padrão dos produtos</span>
                        @php
                            $selectedProductMode = old('product_display_mode', $store?->product_display_mode ?: 'individual');
                        @endphp
                        <div class="d-grid gap-2 mt-2">
                            <label class="border rounded-3 p-3 d-flex gap-3 align-items-start">
                                <input type="radio" name="product_display_mode" value="catalog" @checked($selectedProductMode === 'catalog')>
                                <span>
                                    <strong class="d-block">Catálogo rápido</strong>
                                    <small>Recomendado para restaurantes, pizzarias, hamburguerias, confeitarias e outros negócios de alimentação.</small>
                                </span>
                            </label>
                            <label class="border rounded-3 p-3 d-flex gap-3 align-items-start">
                                <input type="radio" name="product_display_mode" value="individual" @checked($selectedProductMode === 'individual')>
                                <span>
                                    <strong class="d-block">Página individual</strong>
                                    <small>Recomendado para roupas, perfumes, celulares, móveis, eletrônicos e produtos que precisam de mais detalhes.</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="store-form-field store-form-field-wide">
                        <span>Entrega e retirada</span>
                        <div class="d-flex flex-wrap gap-4 mt-2">
                            <label>
                                <input type="hidden" name="pickup_available" value="0">
                                <input type="checkbox" name="pickup_available" value="1" @checked(old('pickup_available', $store?->pickup_available ?? true))>
                                Retirada disponível
                            </label>
                            <label>
                                <input type="hidden" name="delivery_available" value="0">
                                <input type="checkbox" name="delivery_available" value="1" @checked(old('delivery_available', $store?->delivery_available ?? true))>
                                Entrega disponível
                            </label>
                        </div>
                    </div>

                    <label class="store-form-field">
                        <span>Taxa de entrega (R$)</span>
                        <input type="number" name="delivery_fee" min="0" step="0.01" value="{{ old('delivery_fee', $store?->delivery_fee ?? 0) }}">
                    </label>
                    <label class="store-form-field">
                        <span>Entrega grátis acima de (R$)</span>
                        <input type="number" name="free_delivery_threshold" min="0" step="0.01" value="{{ old('free_delivery_threshold', $store?->free_delivery_threshold) }}">
                    </label>
                    <label class="store-form-field">
                        <span>Prazo mínimo (minutos)</span>
                        <input type="number" name="delivery_min_minutes" min="1" value="{{ old('delivery_min_minutes', $store?->delivery_min_minutes) }}">
                    </label>
                    <label class="store-form-field">
                        <span>Prazo máximo (minutos)</span>
                        <input type="number" name="delivery_max_minutes" min="1" value="{{ old('delivery_max_minutes', $store?->delivery_max_minutes) }}">
                    </label>
                    <label class="store-form-field">
                        <span>Pedido mínimo (R$)</span>
                        <input type="number" name="minimum_order" min="0" step="0.01" value="{{ old('minimum_order', $store?->minimum_order ?? 0) }}">
                    </label>
                    <label class="store-form-field">
                        <span>Endereço para retirada</span>
                        <input type="text" name="pickup_address" maxlength="255" value="{{ old('pickup_address', $store?->pickup_address) }}">
                    </label>
                    <label class="store-form-field store-form-field-wide">
                        <span>Cidades atendidas</span>
                        <textarea name="delivery_cities_text" rows="2" placeholder="Uma por linha. Deixe vazio para aceitar qualquer cidade.">{{ old('delivery_cities_text', collect($store?->delivery_cities)->implode("\n")) }}</textarea>
                    </label>
                    <label class="store-form-field store-form-field-wide">
                        <span>Bairros atendidos</span>
                        <textarea name="delivery_neighborhoods_text" rows="2" placeholder="Um por linha.">{{ old('delivery_neighborhoods_text', collect($store?->delivery_neighborhoods)->implode("\n")) }}</textarea>
                    </label>
                    <label class="store-form-field store-form-field-wide">
                        <span>Taxas por bairro ou região</span>
                        <textarea name="delivery_region_fees_text" rows="3" placeholder="Atalaia | 8,00&#10;Farolândia | 10,00">{{ old('delivery_region_fees_text', collect($store?->delivery_region_fees)->map(fn ($item) => ($item['region'] ?? '').' | '.number_format((float) ($item['fee'] ?? 0), 2, ',', ''))->implode("\n")) }}</textarea>
                        <small>Use uma linha por região. A taxa específica substitui a taxa geral.</small>
                    </label>
                </div>
            </section>

            <section class="store-management-card">
                <div class="store-management-section-title">
                    <span><i class="fa-solid fa-address-card"></i></span>
                    <div>
                        <h2>Contato e presença digital</h2>
                        <p>O WhatsApp será o principal contato público da loja.</p>
                    </div>
                </div>

                <div class="store-form-grid">
                    <label class="store-form-field">
                        <span>WhatsApp *</span>
                        <input type="tel" name="whatsapp" value="{{ old('whatsapp', $store?->whatsapp ?: auth()->user()->whatsapp) }}" maxlength="20" placeholder="(79) 99999-9999" required>
                    </label>

                    <label class="store-form-field">
                        <span>Telefone</span>
                        <input type="tel" name="phone" value="{{ old('phone', $store?->phone ?: auth()->user()->phone) }}" maxlength="20" placeholder="(79) 3333-3333">
                    </label>

                    <label class="store-form-field">
                        <span>Instagram</span>
                        <input type="text" name="instagram" value="{{ old('instagram', $store?->instagram) }}" maxlength="120" placeholder="@nomedaloja">
                    </label>

                    <label class="store-form-field">
                        <span>Site</span>
                        <input type="url" name="website" value="{{ old('website', $store?->website) }}" maxlength="255" placeholder="https://sualoja.com.br">
                    </label>

                    <div class="store-form-field store-form-field-wide">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                            <span class="fw-semibold">Localização no Google Maps (Coordenadas, Link ou iFrame)</span>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 text-decoration-none fw-bold shadow-sm" onclick="document.getElementById('coords-help-dialog').showModal()" style="font-size: 0.82rem;">
                                <i class="fa-solid fa-circle-question me-1"></i> Como pegar o mapa?
                            </button>
                        </div>
                        <input type="text" id="map_location" name="map_location" value="{{ old('map_location', $store?->map_location) }}" maxlength="1000" placeholder="Ex: -10.9472, -37.0731 ou https://maps.google.com/... ou <iframe... >">
                        <small class="text-muted d-block mt-1">
                            <strong>Formatos aceitos:</strong> Coordenadas (ex: <code>-10.9472, -37.0731</code>), link direto do Google Maps ou código <code>&lt;iframe&gt;</code>. O sistema gerará o mapa automaticamente na sua loja.
                        </small>

                        <details class="mt-2 border rounded-3 p-3 bg-light" style="font-size: 0.85rem;">
                            <summary class="fw-semibold text-primary cursor-pointer" style="user-select: none;">
                                <i class="fa-solid fa-lightbulb text-warning me-1"></i> Ver opções e passo a passo (Coordenadas, Link ou iFrame)
                            </summary>
                            <div class="mt-3 pt-3 border-top">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <strong class="d-block text-dark mb-2"><i class="fa-solid fa-location-crosshairs text-primary me-1"></i> 1. Coordenadas:</strong>
                                        <ul class="ps-3 mb-0 text-muted" style="line-height: 1.5;">
                                            <li>Clique c/ botão direito no mapa ou segure o dedo no celular sobre sua loja.</li>
                                            <li>Copie os números da latitude e longitude (ex: <code>-10.9472, -37.0731</code>).</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="d-block text-dark mb-2"><i class="fa-solid fa-link text-success me-1"></i> 2. Link do Google Maps:</strong>
                                        <ul class="ps-3 mb-0 text-muted" style="line-height: 1.5;">
                                            <li>No Google Maps, clique no botão <strong>Compartilhar</strong>.</li>
                                            <li>Clique em <strong>Copiar link</strong> e cole o link direto aqui.</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="d-block text-dark mb-2"><i class="fa-solid fa-code text-secondary me-1"></i> 3. Código iFrame:</strong>
                                        <ul class="ps-3 mb-0 text-muted" style="line-height: 1.5;">
                                            <li>No Google Maps, clique em <strong>Compartilhar</strong> &gt; <strong>Incorporar um mapa</strong>.</li>
                                            <li>Clique em <strong>Copiar HTML</strong> (código <code>&lt;iframe...&gt;</code>) e cole aqui.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </section>

            <div class="store-management-submit">
                <a href="{{ route('user.panel') }}">Cancelar</a>
                <button type="submit">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ $editing ? 'Salvar alterações' : 'Criar minha loja' }}
                </button>
            </div>
        </form>

        @if($editing)
            <section class="store-promotions-manager" id="promocoes">
                <div class="store-promotions-heading">
                    <div>
                        <span class="store-management-eyebrow">
                            <i class="fa-solid fa-tags"></i>
                            Cupons e promoções
                        </span>
                        <h2>Ofertas da sua loja</h2>
                        <p>Divulgue descontos com período definido. O código do cupom é opcional.</p>
                    </div>
                    <span class="store-promotions-usage">
                        {{ $activePromotionsUsage }} de {{ $promotionLimit === null ? 'ilimitadas' : $promotionLimit }} ativas
                    </span>
                </div>

                <form action="{{ route('store.promotions.store', $store) }}" method="POST" class="store-promotion-form">
                    @csrf
                    <div class="store-promotion-form-heading">
                        <span><i class="fa-solid fa-plus"></i></span>
                        <div>
                            <h3>Nova promoção</h3>
                            <p>Informe o benefício e até quando ele será válido.</p>
                        </div>
                    </div>

                    <div class="store-promotion-fields">
                        <label class="is-wide">
                            <span>Título *</span>
                            <input type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Ex.: Semana da beleza" required>
                        </label>
                        <label>
                            <span>Código do cupom</span>
                            <input type="text" name="coupon_code" maxlength="40" value="{{ old('coupon_code') }}" placeholder="SERGIPE10">
                        </label>
                        <label>
                            <span>Tipo do desconto *</span>
                            <select name="discount_type" required>
                                <option value="percentage" @selected(old('discount_type', 'percentage') === 'percentage')>Porcentagem (%)</option>
                                <option value="fixed" @selected(old('discount_type') === 'fixed')>Valor em reais (R$)</option>
                            </select>
                        </label>
                        <label>
                            <span>Valor do desconto *</span>
                            <input type="number" name="discount_value" min="0.01" max="999999.99" step="0.01" value="{{ old('discount_value') }}" placeholder="10" required>
                        </label>
                        <label>
                            <span>Começa em</span>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}">
                        </label>
                        <label>
                            <span>Termina em *</span>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required>
                        </label>
                        <label class="is-wide">
                            <span>Descrição</span>
                            <textarea name="description" rows="2" maxlength="500" placeholder="Explique o que está em promoção.">{{ old('description') }}</textarea>
                        </label>
                        <label class="is-wide">
                            <span>Regras da oferta</span>
                            <textarea name="terms" rows="2" maxlength="500" placeholder="Ex.: válido para compras acima de R$ 100.">{{ old('terms') }}</textarea>
                        </label>
                    </div>

                    <div class="store-promotion-form-footer">
                        <label>
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1" @checked(old('active', '1') === '1')>
                            Publicar ao salvar
                        </label>
                        <button type="submit"><i class="fa-solid fa-tag"></i> Criar promoção</button>
                    </div>
                </form>

                <div class="store-promotions-list">
                    @forelse($store->promotions as $promotion)
                        <article class="store-promotion-manage-card">
                            <header>
                                <div>
                                    <span class="store-promotion-status is-{{ strtolower($promotion->status_label) }}">{{ $promotion->status_label }}</span>
                                    <h3>{{ $promotion->title }}</h3>
                                    <p>
                                        <strong>{{ $promotion->discount_label }}</strong>
                                        @if($promotion->coupon_code)
                                            · código <code>{{ $promotion->coupon_code }}</code>
                                        @endif
                                    </p>
                                </div>
                                <span>Até {{ $promotion->ends_at->format('d/m/Y H:i') }}</span>
                            </header>

                            <details>
                                <summary><i class="fa-solid fa-pen"></i> Editar promoção</summary>
                                <form action="{{ route('store.promotions.update', [$store, $promotion]) }}" method="POST" class="store-promotion-form is-editing">
                                    @csrf
                                    @method('PUT')
                                    <div class="store-promotion-fields">
                                        <label class="is-wide">
                                            <span>Título *</span>
                                            <input type="text" name="title" maxlength="120" value="{{ $promotion->title }}" required>
                                        </label>
                                        <label>
                                            <span>Código</span>
                                            <input type="text" name="coupon_code" maxlength="40" value="{{ $promotion->coupon_code }}">
                                        </label>
                                        <label>
                                            <span>Tipo *</span>
                                            <select name="discount_type" required>
                                                <option value="percentage" @selected($promotion->discount_type === 'percentage')>Porcentagem (%)</option>
                                                <option value="fixed" @selected($promotion->discount_type === 'fixed')>Valor em reais (R$)</option>
                                            </select>
                                        </label>
                                        <label>
                                            <span>Valor *</span>
                                            <input type="number" name="discount_value" min="0.01" max="999999.99" step="0.01" value="{{ $promotion->discount_value }}" required>
                                        </label>
                                        <label>
                                            <span>Começa em</span>
                                            <input type="datetime-local" name="starts_at" value="{{ $promotion->starts_at?->format('Y-m-d\TH:i') }}">
                                        </label>
                                        <label>
                                            <span>Termina em *</span>
                                            <input type="datetime-local" name="ends_at" value="{{ $promotion->ends_at->format('Y-m-d\TH:i') }}" required>
                                        </label>
                                        <label class="is-wide">
                                            <span>Descrição</span>
                                            <textarea name="description" rows="2" maxlength="500">{{ $promotion->description }}</textarea>
                                        </label>
                                        <label class="is-wide">
                                            <span>Regras</span>
                                            <textarea name="terms" rows="2" maxlength="500">{{ $promotion->terms }}</textarea>
                                        </label>
                                    </div>
                                    <div class="store-promotion-form-footer">
                                        <label>
                                            <input type="hidden" name="active" value="0">
                                            <input type="checkbox" name="active" value="1" @checked($promotion->active)>
                                            Promoção ativa
                                        </label>
                                        <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar promoção</button>
                                    </div>
                                </form>
                            </details>

                            <footer>
                                <form action="{{ route('store.promotions.toggle', [$store, $promotion]) }}" method="POST">
                                    @csrf
                                    <button type="submit">
                                        <i class="fa-solid {{ $promotion->active ? 'fa-pause' : 'fa-play' }}"></i>
                                        {{ $promotion->active ? 'Pausar' : 'Ativar' }}
                                    </button>
                                </form>
                                <form action="{{ route('store.promotions.destroy', [$store, $promotion]) }}" method="POST" onsubmit="return confirm('Excluir esta promoção?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="is-danger"><i class="fa-solid fa-trash"></i> Excluir</button>
                                </form>
                            </footer>
                        </article>
                    @empty
                        <div class="store-promotions-empty">
                            <i class="fa-solid fa-ticket"></i>
                            <strong>Nenhuma promoção cadastrada</strong>
                            <span>Crie a primeira oferta da sua loja usando o formulário acima.</span>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="store-hours-manager" id="horarios">
                <div class="store-hours-heading">
                    <div>
                        <span class="store-management-eyebrow">
                            <i class="fa-regular fa-clock"></i>
                            Horário de funcionamento
                        </span>
                        <h2>Quando sua loja atende?</h2>
                        <p>Os visitantes verão automaticamente se a loja está aberta ou fechada.</p>
                    </div>
                    <span>Horário de Sergipe</span>
                </div>

                <form action="{{ route('store.business_hours.update', $store) }}" method="POST" class="store-hours-form">
                    @csrf
                    @method('PUT')
                    <div class="store-hours-labels" aria-hidden="true">
                        <span>Dia</span>
                        <span>Abertura</span>
                        <span>Fechamento</span>
                        <span>Opções</span>
                    </div>

                    <div class="store-hours-list">
                        @foreach($weekdays as $dayNumber => $dayLabel)
                            @php
                                $businessHour = $businessHoursByDay->get($dayNumber);
                                $isClosed = $businessHour ? $businessHour->is_closed : true;
                                $is24Hours = $businessHour?->is_24_hours ?? false;
                            @endphp
                            <div class="store-hours-row" data-business-hours-row>
                                <input type="hidden" name="hours[{{ $loop->index }}][day_of_week]" value="{{ $dayNumber }}">
                                <strong>{{ $dayLabel }}</strong>
                                <label>
                                    <span>Abertura</span>
                                    <input
                                        type="time"
                                        name="hours[{{ $loop->index }}][opens_at]"
                                        value="{{ old("hours.{$loop->index}.opens_at", $businessHour?->opens_at ? substr($businessHour->opens_at, 0, 5) : '08:00') }}"
                                        data-business-opens
                                    >
                                </label>
                                <label>
                                    <span>Fechamento</span>
                                    <input
                                        type="time"
                                        name="hours[{{ $loop->index }}][closes_at]"
                                        value="{{ old("hours.{$loop->index}.closes_at", $businessHour?->closes_at ? substr($businessHour->closes_at, 0, 5) : '18:00') }}"
                                        data-business-closes
                                    >
                                </label>
                                <div class="store-hours-options">
                                    <label>
                                        <input type="hidden" name="hours[{{ $loop->index }}][is_24_hours]" value="0">
                                        <input
                                            type="checkbox"
                                            name="hours[{{ $loop->index }}][is_24_hours]"
                                            value="1"
                                            data-business-24
                                            @checked(old("hours.{$loop->index}.is_24_hours", $is24Hours))
                                        >
                                        24 horas
                                    </label>
                                    <label>
                                        <input type="hidden" name="hours[{{ $loop->index }}][is_closed]" value="0">
                                        <input
                                            type="checkbox"
                                            name="hours[{{ $loop->index }}][is_closed]"
                                            value="1"
                                            data-business-closed
                                            @checked(old("hours.{$loop->index}.is_closed", $isClosed))
                                        >
                                        Fechado
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="store-hours-footer">
                        <small><i class="fa-solid fa-moon"></i> Horários como 18:00–02:00 são aceitos para atendimento após a meia-noite.</small>
                        <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar horários</button>
                    </div>
                </form>
            </section>

            <section class="store-management-actions">
                <div>
                    <h2>Controles da loja</h2>
                    <p>Desativar oculta temporariamente. Excluir remove definitivamente o cadastro da loja.</p>
                </div>
                <div class="store-management-action-buttons">
                    <a href="{{ route('store.show', $store->slug) }}">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Ver loja
                    </a>
                    @if($store->isModerationApproved())
                        <form action="{{ route('store.toggle_specific', $store) }}" method="POST">
                            @csrf
                            <button type="submit" class="{{ $store->active ? 'is-warning' : 'is-success' }}">
                                <i class="fa-solid {{ $store->active ? 'fa-pause' : 'fa-play' }}"></i>
                                {{ $store->active ? 'Desativar loja' : 'Ativar loja' }}
                            </button>
                        </form>
                    @else
                        <button type="button" class="is-danger" disabled>
                            <i class="fa-solid fa-lock"></i>
                            Aguardando moderação
                        </button>
                    @endif
                    <form action="{{ route('store.destroy_specific', $store) }}" method="POST" onsubmit="return confirm('Excluir definitivamente sua loja? Os anúncios serão preservados, mas sairão da loja.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="is-danger">
                            <i class="fa-solid fa-trash"></i>
                            Excluir loja
                        </button>
                    </form>
                </div>
            </section>
        @endif
    </div>

    <!-- Modal de Instruções sobre Localização e Mapa -->
    <dialog id="coords-help-dialog" class="border-0 rounded-4 shadow-lg p-0" style="max-width: 620px; width: 92%; backdrop-filter: blur(4px);">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white p-3 rounded-top-4 d-flex align-items-center justify-content-between">
                <h5 class="modal-title m-0 fs-6 fw-bold">
                    <i class="fa-solid fa-map-location-dot me-2"></i> Como adicionar o Mapa da sua Loja
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('coords-help-dialog').close()" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4" style="font-size: 0.9rem;">
                <p class="text-muted mb-3">
                    O sistema aceita <strong>3 formas simples</strong> para exibir o mapa interativo e rota GPS na página da sua loja:
                </p>

                <ul class="nav nav-pills nav-fill mb-3 gap-2 p-1 bg-light rounded-3" id="coordsModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-2 py-2 fw-bold" id="coords-tab" data-bs-toggle="tab" data-bs-target="#coords-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-location-crosshairs me-1"></i> Coordenadas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-2 py-2 fw-bold" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-link me-1"></i> Link Direto
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-2 py-2 fw-bold" id="iframe-tab" data-bs-toggle="tab" data-bs-target="#iframe-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-code me-1"></i> Código iFrame
                        </button>
                    </li>
                </ul>

                <div class="tab-content border rounded-3 p-3 bg-body" id="coordsModalTabsContent">
                    <!-- Aba Coordenadas -->
                    <div class="tab-pane fade show active" id="coords-tab-pane" role="tabpanel">
                        <strong class="d-block text-dark mb-2"><i class="fa-solid fa-circle-check text-primary me-1"></i> Opção 1: Coordenadas (Latitude e Longitude)</strong>
                        <ol class="ps-3 mb-0 text-secondary" style="line-height: 1.6;">
                            <li>Acesse o <a href="https://maps.google.com" target="_blank" rel="noopener" class="fw-bold text-primary text-decoration-underline">Google Maps <i class="fa-solid fa-arrow-up-right-from-square small"></i></a>.</li>
                            <li><strong>No PC:</strong> Clique com o <u>botão direito</u> no local da loja e clique na 1ª opção (ex: <code>-10.9472, -37.0731</code>) para copiar.</li>
                            <li><strong>No Celular:</strong> Segure o dedo sobre a loja no app do Maps até surgir o pino vermelho e toque nas coordenadas para copiar.</li>
                            <li>Cole os números no campo do formulário.</li>
                        </ol>
                    </div>

                    <!-- Aba Link Direto -->
                    <div class="tab-pane fade" id="link-tab-pane" role="tabpanel">
                        <strong class="d-block text-dark mb-2"><i class="fa-solid fa-circle-check text-success me-1"></i> Opção 2: Link do Google Maps</strong>
                        <ol class="ps-3 mb-0 text-secondary" style="line-height: 1.6;">
                            <li>Pesquise sua loja no <a href="https://maps.google.com" target="_blank" rel="noopener" class="fw-bold text-primary text-decoration-underline">Google Maps</a>.</li>
                            <li>Clique no botão <strong>Compartilhar</strong>.</li>
                            <li>Clique em <strong>Copiar link</strong>.</li>
                            <li>Cole o link copiado (ex: <code>https://maps.app.goo.gl/...</code>) no campo do formulário.</li>
                        </ol>
                    </div>

                    <!-- Aba iFrame -->
                    <div class="tab-pane fade" id="iframe-tab-pane" role="tabpanel">
                        <strong class="d-block text-dark mb-2"><i class="fa-solid fa-circle-check text-secondary me-1"></i> Opção 3: Código iFrame (Incorporar Mapa)</strong>
                        <ol class="ps-3 mb-0 text-secondary" style="line-height: 1.6;">
                            <li>No <a href="https://maps.google.com" target="_blank" rel="noopener" class="fw-bold text-primary text-decoration-underline">Google Maps</a>, clique em <strong>Compartilhar</strong>.</li>
                            <li>Acesse a aba <strong>Incorporar um mapa</strong>.</li>
                            <li>Clique no botão <strong>Copiar HTML</strong>.</li>
                            <li>Cole o código gerado (ex: <code>&lt;iframe src="..."&gt;&lt;/iframe&gt;</code>) diretamente no campo do formulário.</li>
                        </ol>
                    </div>
                </div>

                <div class="alert alert-success border-0 mt-3 mb-0 d-flex align-items-center gap-2 py-2 px-3 rounded-3">
                    <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                    <small><strong>Pronto!</strong> Qualquer um dos 3 métodos funcionará perfeitamente e exibirá o mapa na sua loja.</small>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 rounded-bottom-4 d-flex justify-content-end">
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="document.getElementById('coords-help-dialog').close()">
                    Entendi!
                </button>
            </div>
        </div>
    </dialog>
</main>
@endsection

@push('scripts')
<script>
    (() => {
        const category = document.getElementById('store-category');
        const modes = document.querySelectorAll('[name="product_display_mode"]');
        if (!category || !modes.length || @json($editing)) return;

        let manuallyChanged = false;
        modes.forEach((radio) => radio.addEventListener('change', () => manuallyChanged = true));
        category.addEventListener('change', () => {
            if (manuallyChanged) return;
            const suggested = category.value === 'Alimentação' ? 'catalog' : 'individual';
            document.querySelector(`[name="product_display_mode"][value="${suggested}"]`)?.click();
            manuallyChanged = false;
        });
    })();

    (() => {
        const previews = [
            {
                input: document.getElementById('store-logo-input'),
                preview: document.getElementById('store-logo-preview'),
                className: 'store-logo-preview-image',
                alt: 'Prévia do novo logo',
            },
            {
                input: document.getElementById('store-banner-input'),
                preview: document.getElementById('store-banner-preview'),
                className: 'store-banner-preview-image',
                alt: 'Prévia do novo banner',
            },
        ];

        previews.forEach(({ input, preview, className, alt }) => {
            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', () => {
                const file = input.files?.[0];

                if (!file) {
                    return;
                }

                const image = document.createElement('img');
                const objectUrl = URL.createObjectURL(file);
                image.src = objectUrl;
                image.alt = alt;
                image.className = className;
                image.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
                preview.replaceChildren(image);
            });
        });

        document.querySelectorAll('[data-business-hours-row]').forEach((row) => {
            const opensInput = row.querySelector('[data-business-opens]');
            const closesInput = row.querySelector('[data-business-closes]');
            const allDayInput = row.querySelector('[data-business-24]');
            const closedInput = row.querySelector('[data-business-closed]');

            const syncBusinessHourRow = (changedInput = null) => {
                if (changedInput === allDayInput && allDayInput.checked) {
                    closedInput.checked = false;
                }
                if (changedInput === closedInput && closedInput.checked) {
                    allDayInput.checked = false;
                }

                const disableTimes = allDayInput.checked || closedInput.checked;
                opensInput.disabled = disableTimes;
                closesInput.disabled = disableTimes;
                row.classList.toggle('is-disabled', disableTimes);
            };

            allDayInput.addEventListener('change', () => syncBusinessHourRow(allDayInput));
            closedInput.addEventListener('change', () => syncBusinessHourRow(closedInput));
            syncBusinessHourRow();
        });
    })();
</script>
@endpush
