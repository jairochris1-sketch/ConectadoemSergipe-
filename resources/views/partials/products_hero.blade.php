{{-- Hero de Produtos — mesmo padrão visual de veículos e imóveis --}}
@php
    $sergipeCities = [
        'Aracaju','Nossa Senhora do Socorro','Lagarto','Itabaiana','São Cristóvão',
        'Estância','Tobias Barreto','Simão Dias','Propriá','Nossa Senhora da Glória',
        'Itabaianinha','Boquim','Poço Verde','Riachão do Dantas','Umbaúba',
        'Canindé de São Francisco','Aquidabã','Japaratuba','Maruim','Laranjeiras',
        'Pacatuba','Ilha das Flores','Neópolis','Cedro de São João',
        'Monte Alegre de Sergipe','Gararu','Porto da Folha','Nossa Senhora de Lourdes',
    ];
    $productsHeroBanners = $productsBanners ?? $heroBanners ?? ['images/banner-1.jpg'];
    $productTypes = [
        ['label' => 'Todos', 'value' => null, 'icon' => 'fa-layer-group'],
        ['label' => 'Celulares', 'value' => 'Celulares', 'icon' => 'fa-mobile-screen'],
        ['label' => 'Informática', 'value' => 'Informática', 'icon' => 'fa-laptop'],
        ['label' => 'Eletrônicos', 'value' => 'Eletrônicos', 'icon' => 'fa-tv'],
        ['label' => 'Móveis', 'value' => 'Móveis', 'icon' => 'fa-couch'],
        ['label' => 'Roupas', 'value' => 'Roupas', 'icon' => 'fa-shirt'],
    ];
@endphp

<div class="products-hero-container position-relative mb-4" style="border-radius: 0; overflow: hidden;">
    <div class="swiper swiper-products-hero" style="height: 420px; border-radius: 0;">
        <div class="swiper-wrapper">
            @foreach($productsHeroBanners as $banner)
                @php $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner); @endphp
                <div class="swiper-slide products-hero-slide"
                     style="background: url('{{ $bannerUrl }}') center/cover no-repeat; height: 420px; border-radius: 0;"></div>
            @endforeach
        </div>

        @if(count($productsHeroBanners) > 1)
            <div class="swiper-button-next products-swiper-next" style="color:#fff; width:40px; height:40px; background:rgba(0,0,0,0.35); border-radius:50%; right:20px;"></div>
            <div class="swiper-button-prev products-swiper-prev" style="color:#fff; width:40px; height:40px; background:rgba(0,0,0,0.35); border-radius:50%; left:20px;"></div>
        @endif
    </div>

    <div class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-center px-3 py-4"
         style="background: linear-gradient(rgba(10,18,38,0.72), rgba(10,18,38,0.85)); z-index: 10;">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill text-white fw-bold mb-3 shadow"
             style="background-color: #2563eb; font-size: 0.85rem;">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Produtos em Sergipe</span>
        </div>

        <h1 class="fw-bold text-white mb-2 display-6" style="text-shadow: 0 2px 8px rgba(0,0,0,0.6);">
            Encontre produtos perto de você.
        </h1>
        <p class="text-white mb-4 mx-auto" style="max-width: 600px; opacity: 0.88; text-shadow: 0 1px 4px rgba(0,0,0,0.5);">
            Compre celulares, eletrônicos, móveis e ofertas locais em Sergipe.
        </p>

        <div style="max-width: 920px; width: 100%;">
            <form action="{{ route('module.products') }}" method="GET"
                  class="products-search-pill d-flex flex-wrap flex-md-nowrap align-items-center p-2 rounded-pill shadow-lg border border-secondary border-opacity-25">
                <div class="products-pill-item flex-grow-0 min-w-160 position-relative border-end-md w-100 w-md-auto">
                    <select name="type" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:150px;">
                        <option value="">Categoria</option>
                        @foreach(['Celulares','Informática','Eletrônicos','Móveis','Roupas','Eletrodomésticos','Esporte'] as $productType)
                            <option value="{{ $productType }}" {{ request('type') === $productType ? 'selected' : '' }}>{{ $productType }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="products-pill-item flex-grow-0 min-w-140 position-relative border-end-md w-100 w-md-auto">
                    <select name="city" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:140px;">
                        <option value="">Cidade</option>
                        @foreach($sergipeCities as $cname)
                            <option value="{{ $cname }}" {{ request('city') === $cname ? 'selected' : '' }}>{{ $cname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="products-pill-item flex-grow-1 position-relative w-100">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Produto ou palavra-chave..."
                           class="form-control bg-transparent text-white border-0 shadow-none py-2 px-3">
                </div>

                <div class="w-100 w-md-auto ms-md-2 mt-2 mt-md-0">
                    <button type="submit" class="btn rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 w-100 shadow-sm"
                            style="background-color:#2563eb; color:#fff; border:none; min-width:120px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Buscar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 products-category-rail">
        @foreach($productTypes as $productType)
            <a href="{{ $productType['value'] ? route('module.products', ['type' => $productType['value']]) : route('module.products') }}"
               class="products-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ $productType['value'] ? (request('type') === $productType['value'] ? 'active-pill' : '') : (!request()->anyFilled(['q','city','type']) ? 'active-pill' : '') }}">
                <i class="fa-solid {{ $productType['icon'] }}"></i>
                <span>{{ $productType['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
