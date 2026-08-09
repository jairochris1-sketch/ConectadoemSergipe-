@php
    $sergipeCities = [
        'Aracaju','Nossa Senhora do Socorro','Lagarto','Itabaiana','São Cristóvão',
        'Estância','Tobias Barreto','Simão Dias','Propriá','Nossa Senhora da Glória',
        'Itabaianinha','Boquim','Poço Verde','Riachão do Dantas','Umbaúba',
        'Canindé de São Francisco','Aquidabã','Japaratuba','Maruim','Laranjeiras',
        'Pacatuba','Ilha das Flores','Neópolis','Cedro de São João',
        'Monte Alegre de Sergipe','Gararu','Porto da Folha','Nossa Senhora de Lourdes',
    ];
    $jobTypes = [
        ['label' => 'Todos', 'value' => null, 'icon' => 'fa-layer-group'],
        ['label' => 'Vagas de emprego', 'value' => 'Vagas de Emprego', 'icon' => 'fa-briefcase'],
        ['label' => 'Estágios', 'value' => 'Estágios', 'icon' => 'fa-graduation-cap'],
        ['label' => 'Currículos', 'value' => 'Currículos / Procurando', 'icon' => 'fa-file-lines'],
    ];
@endphp

<div class="jobs-hero-container position-relative mb-4 overflow-hidden">
    <div class="swiper swiper-jobs-hero">
        <div class="swiper-wrapper">
            @foreach($jobsBanners as $banner)
                @php $bannerUrl = str_starts_with($banner, 'http') ? $banner : asset($banner); @endphp
                <div class="swiper-slide jobs-hero-slide" style="background-image: url('{{ $bannerUrl }}');"></div>
            @endforeach
        </div>

        @if(count($jobsBanners) > 1)
            <div class="swiper-button-next jobs-swiper-next"></div>
            <div class="swiper-button-prev jobs-swiper-prev"></div>
        @endif
    </div>

    <div class="jobs-hero-overlay position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center text-center px-3 py-4">
        <div class="jobs-hero-badge d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill text-white fw-bold mb-3 shadow">
            <i class="fa-solid fa-briefcase"></i>
            <span>Empregos em Sergipe</span>
        </div>

        <h1 class="fw-bold text-white mb-2 display-6">Sua próxima oportunidade está aqui.</h1>
        <p class="text-white mb-4 mx-auto">Encontre vagas de emprego, estágios e oportunidades perto de você.</p>

        <div class="jobs-search-wrapper">
            <form action="{{ route('module.jobs') }}" method="GET" class="jobs-search-pill d-flex flex-wrap flex-md-nowrap align-items-center p-2 rounded-pill shadow-lg border">
                <div class="jobs-pill-item flex-grow-0 position-relative border-end-md w-100 w-md-auto">
                    <select name="type" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold">
                        <option value="">Tipo de oportunidade</option>
                        @foreach(array_slice($jobTypes, 1) as $jobType)
                            <option value="{{ $jobType['value'] }}" {{ request('type') === $jobType['value'] ? 'selected' : '' }}>{{ $jobType['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="jobs-pill-item flex-grow-0 position-relative border-end-md w-100 w-md-auto">
                    <select name="city" class="form-select bg-transparent text-white border-0 shadow-none py-2 px-3 fw-semibold">
                        <option value="">Cidade</option>
                        @foreach($sergipeCities as $cityName)
                            <option value="{{ $cityName }}" {{ request('city') === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="jobs-pill-item flex-grow-1 position-relative w-100">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cargo, empresa ou palavra-chave..." class="form-control bg-transparent text-white border-0 shadow-none py-2 px-3">
                </div>

                <div class="w-100 w-md-auto ms-md-2 mt-2 mt-md-0">
                    <button type="submit" class="btn jobs-search-button rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-2 w-100 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Buscar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 jobs-category-rail">
        @foreach($jobTypes as $jobType)
            <a href="{{ $jobType['value'] ? route('module.jobs', ['type' => $jobType['value']]) : route('module.jobs') }}"
               class="jobs-cat-pill d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill border text-white text-decoration-none fw-semibold shadow-sm {{ $jobType['value'] ? (request('type') === $jobType['value'] ? 'active-pill' : '') : (!request()->anyFilled(['q','city','type']) ? 'active-pill' : '') }}">
                <i class="fa-solid {{ $jobType['icon'] }}"></i>
                <span>{{ $jobType['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
