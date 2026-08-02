{{-- ============================================================
     Hero de Veículos — Banner + Pílula de busca FORA do Swiper
     ============================================================ --}}
@php
    $sergipeCities = [
        'Aracaju','Nossa Senhora do Socorro','Lagarto','Itabaiana','São Cristóvão',
        'Estância','Tobias Barreto','Simão Dias','Propriá','Nossa Senhora da Glória',
        'Itabaianinha','Boquim','Poço Verde','Riachão do Dantas','Umbaúba',
        'Canindé de São Francisco','Aquidabã','Japaratuba','Maruim','Laranjeiras',
        'Pacatuba','Ilha das Flores','Neópolis','Cedro de São João',
        'Monte Alegre de Sergipe','Gararu','Porto da Folha','Nossa Senhora de Lourdes',
    ];
@endphp

<div class="vehicles-hero-container position-relative mb-4" style="border-radius: 0; overflow: hidden;">

    {{-- ===== SWIPER — apenas imagens (sem conteúdo dentro do slide) ===== --}}
    <div class="swiper swiper-vehicles-hero" style="height: 420px; border-radius: 0;">
        <div class="swiper-wrapper">
            @foreach($vehiclesBanners as $banner)
            @php $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner); @endphp
            <div class="swiper-slide vehicles-hero-slide"
                 style="background: url('{{ $bannerUrl }}') center/cover no-repeat; height: 420px; border-radius: 0;"></div>
            @endforeach
        </div>

        @if(count($vehiclesBanners) > 1)
            <div class="swiper-button-next vehicles-swiper-next" style="color:#fff; width:40px; height:40px; background:rgba(0,0,0,0.35); border-radius:50%; right:20px;"></div>
            <div class="swiper-button-prev vehicles-swiper-prev" style="color:#fff; width:40px; height:40px; background:rgba(0,0,0,0.35); border-radius:50%; left:20px;"></div>
        @endif
    </div>

    {{-- ===== OVERLAY (gradiente + conteúdo) — posicionado SOBRE o Swiper ===== --}}
    <div class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-center px-3 py-4"
         style="background: linear-gradient(rgba(10,18,38,0.72), rgba(10,18,38,0.85)); z-index: 10;">

        {{-- Badge --}}
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill text-white fw-bold mb-3 shadow"
             style="background-color: #2563eb; font-size: 0.85rem;">
            <i class="fa-solid fa-car"></i>
            <span>Veículos em Sergipe</span>
        </div>

        {{-- Título --}}
        <h1 class="fw-bold text-white mb-2 display-6" style="text-shadow: 0 2px 8px rgba(0,0,0,0.6);">
            Seu próximo veículo está aqui.
        </h1>
        <p class="text-white mb-4 mx-auto" style="max-width: 600px; opacity: 0.88; text-shadow: 0 1px 4px rgba(0,0,0,0.5);">
            Encontre carros, motos, caminhões e muito mais em Sergipe.
        </p>

        {{-- ===== Pílula de Busca ===== --}}
        <div style="max-width: 920px; width: 100%;">
            <form action="{{ route('module.vehicles') }}" method="GET"
                  class="vehicles-search-pill d-flex flex-wrap flex-md-nowrap align-items-center p-2 rounded-pill shadow-lg border border-secondary border-opacity-25">

                {{-- Marca --}}
                <div class="vehicles-pill-item flex-grow-0 min-w-140 position-relative border-end-md w-100 w-md-auto">
                    <select name="brand" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:130px;">
                        <option value="">Marca</option>
                        @foreach(['Chevrolet','Fiat','Volkswagen','Toyota','Hyundai','Honda','Jeep','Nissan','Ford','Renault','BMW','Mercedes-Benz','Yamaha','Kawasaki','Suzuki','Harley-Davidson'] as $b)
                            <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ano --}}
                <div class="vehicles-pill-item flex-grow-0 min-w-140 position-relative border-end-md w-100 w-md-auto">
                    <select name="year" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:100px;">
                        <option value="">Ano</option>
                        @foreach(range(date('Y'), 2000) as $y)
                            <option value="{{ $y }}" {{ (string)request('year') === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Cidade --}}
                <div class="vehicles-pill-item flex-grow-0 min-w-140 position-relative border-end-md w-100 w-md-auto">
                    <select name="city" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:140px;">
                        <option value="">Cidade</option>
                        @foreach($sergipeCities as $cname)
                            <option value="{{ $cname }}" {{ request('city') === $cname ? 'selected' : '' }}>{{ $cname }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Modelo / palavra-chave --}}
                <div class="vehicles-pill-item flex-grow-1 position-relative w-100">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Modelo ou palavra-chave..."
                           class="form-control bg-transparent text-white border-0 shadow-none py-2 px-3">
                </div>

                {{-- Botão Buscar (azul) --}}
                <div class="w-100 w-md-auto ms-md-2 mt-2 mt-md-0">
                    <button type="submit" class="btn rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 w-100 shadow-sm"
                            style="background-color:#2563eb; color:#fff; border:none; min-width:120px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Buscar</span>
                    </button>
                </div>
            </form>
        </div>

    </div>{{-- /overlay --}}

</div>{{-- /vehicles-hero-container --}}

{{-- Sub-categorias de Veículos --}}
<div class="container mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 vehicles-category-rail">
        <a href="{{ route('module.vehicles') }}"
           class="vehicles-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ !request()->anyFilled(['q','brand','year','city','type']) ? 'active-pill' : '' }}">
            <i class="fa-solid fa-layer-group"></i> <span>Todos</span>
        </a>
        <a href="{{ route('module.vehicles', ['type' => 'Carro']) }}"
           class="vehicles-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ request('type') === 'Carro' ? 'active-pill' : '' }}">
            <i class="fa-solid fa-car"></i> <span>Carros</span>
        </a>
        <a href="{{ route('module.vehicles', ['type' => 'Moto']) }}"
           class="vehicles-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ request('type') === 'Moto' ? 'active-pill' : '' }}">
            <i class="fa-solid fa-motorcycle"></i> <span>Motos</span>
        </a>
        <a href="{{ route('module.vehicles', ['type' => 'Caminhão']) }}"
           class="vehicles-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ request('type') === 'Caminhão' ? 'active-pill' : '' }}">
            <i class="fa-solid fa-truck-front"></i> <span>Caminhões</span>
        </a>
        <a href="{{ route('module.vehicles', ['type' => 'Náutica']) }}"
           class="vehicles-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ request('type') === 'Náutica' ? 'active-pill' : '' }}">
            <i class="fa-solid fa-anchor"></i> <span>Náutica</span>
        </a>
        <a href="{{ route('module.vehicles', ['type' => 'Peças']) }}"
           class="vehicles-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ request('type') === 'Peças' ? 'active-pill' : '' }}">
            <i class="fa-solid fa-gears"></i> <span>Peças e Acessórios</span>
        </a>
    </div>
</div>
