{{-- ============================================================
     Hero de Imóveis — Banner + Pílula de busca FORA do Swiper
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

<div class="real-estate-hero-container position-relative mb-4" style="border-radius: 0; overflow: hidden;">

    {{-- ===== SWIPER — apenas imagens ===== --}}
    <div class="swiper swiper-real-estate-hero" style="height: 420px; border-radius: 0;">
        <div class="swiper-wrapper">
            @foreach($realEstateBanners as $banner)
            @php $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner); @endphp
            <div class="swiper-slide real-estate-hero-slide"
                 style="background: url('{{ $bannerUrl }}') center/cover no-repeat; height: 420px; border-radius: 0;"></div>
            @endforeach
        </div>

        @if(count($realEstateBanners) > 1)
            <div class="swiper-button-next real-estate-swiper-next" style="color:#fff; width:40px; height:40px; background:rgba(0,0,0,0.35); border-radius:50%; right:20px;"></div>
            <div class="swiper-button-prev real-estate-swiper-prev" style="color:#fff; width:40px; height:40px; background:rgba(0,0,0,0.35); border-radius:50%; left:20px;"></div>
        @endif
    </div>

    {{-- ===== OVERLAY (gradiente + conteúdo) — posicionado SOBRE o Swiper ===== --}}
    <div class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-center px-3 py-4"
         style="background: linear-gradient(rgba(10,18,38,0.72), rgba(10,18,38,0.85)); z-index: 10;">

        {{-- Badge Portal Imobiliário --}}
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill text-white fw-bold mb-3 shadow"
             style="background-color: #2563eb; font-size: 0.85rem;">
            <i class="fa-solid fa-house" style="font-size: 0.8rem;"></i>
            <span>Portal Imobiliário</span>
        </div>

        {{-- Título e Subtítulo --}}
        <h1 class="fw-bold text-white mb-2 display-6" style="text-shadow: 0 2px 8px rgba(0,0,0,0.6);">
            Encontre seu novo lar
        </h1>
        <p class="text-white mb-4 mx-auto" style="max-width: 600px; opacity: 0.88; text-shadow: 0 1px 4px rgba(0,0,0,0.5);">
            Compre, venda ou alugue imóveis em Sergipe com segurança.
        </p>

        {{-- ===== Pílula de Busca ===== --}}
        <div style="max-width: 920px; width: 100%;">
            <form action="{{ route('module.real_estate') }}" method="GET"
                  class="real-estate-search-pill d-flex flex-wrap flex-md-nowrap align-items-center p-2 rounded-pill shadow-lg border border-secondary border-opacity-25">

                {{-- Comprar / Alugar --}}
                <div class="real-estate-pill-item flex-grow-0 min-w-140 position-relative border-end-md w-100 w-md-auto">
                    <select name="intent" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:120px;">
                        <option value="comprar" {{ request('intent') === 'comprar' ? 'selected' : '' }}>Comprar</option>
                        <option value="alugar" {{ request('intent') === 'alugar' ? 'selected' : '' }}>Alugar</option>
                    </select>
                </div>

                {{-- Tipo de Imóvel --}}
                <div class="real-estate-pill-item flex-grow-0 min-w-160 position-relative border-end-md w-100 w-md-auto">
                    <select name="type" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:140px;">
                        <option value="">Tipo de Imóvel</option>
                        <option value="casa" {{ request('type') === 'casa' ? 'selected' : '' }}>Casa</option>
                        <option value="apartamento" {{ request('type') === 'apartamento' ? 'selected' : '' }}>Apartamento</option>
                        <option value="terreno" {{ request('type') === 'terreno' ? 'selected' : '' }}>Terreno / Lote</option>
                        <option value="comercial" {{ request('type') === 'comercial' ? 'selected' : '' }}>Comercial</option>
                        <option value="rural" {{ request('type') === 'rural' ? 'selected' : '' }}>Sítio / Chácara</option>
                    </select>
                </div>

                {{-- Cidade --}}
                <div class="real-estate-pill-item flex-grow-0 min-w-140 position-relative border-end-md w-100 w-md-auto">
                    <select name="city" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold" style="min-width:140px;">
                        <option value="">Cidade</option>
                        @foreach($sergipeCities as $cname)
                            <option value="{{ $cname }}" {{ request('city') === $cname ? 'selected' : '' }}>{{ $cname }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bairro ou palavra-chave --}}
                <div class="real-estate-pill-item flex-grow-1 position-relative w-100">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Bairro ou busca..."
                           class="form-control bg-transparent text-white border-0 shadow-none py-2 px-3">
                </div>

                {{-- Botão Buscar --}}
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

</div>{{-- /real-estate-hero-container --}}
