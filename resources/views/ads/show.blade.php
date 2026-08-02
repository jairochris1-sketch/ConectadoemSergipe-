@extends('layouts.app')

@section('title', $ad->title . ' - Conectado em Sergipe')

@push('meta')
    @include('components.social-meta', [
        'socialTitle' => $ad->title . ' - Conectado em Sergipe',
        'socialDescription' => \Illuminate\Support\Str::limit(strip_tags($ad->description), 160),
        'socialUrl' => route('ad.show', $ad->slug),
        'socialImage' => asset($ad->card_image ?: $ad->banner ?: $ad->logo ?: 'images/logo-hero.png'),
        'socialType' => 'article',
    ])
@endpush

@section('content')

@if($ad->banner)
<!-- Banner de Fundo do Prestador -->
<div class="position-relative bg-dark" style="height: 220px; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset($ad->banner) }}') center/cover no-repeat;">
    <div class="container h-100 d-flex align-items-end pb-4">
        <div class="d-flex align-items-center gap-3">
            @if($ad->logo)
                <div class="rounded-circle bg-white p-2 shadow" style="width: 90px; height: 90px;">
                    <img src="{{ asset($ad->logo) }}" class="w-100 h-100 object-fit-contain rounded-circle" alt="{{ $ad->title }}">
                </div>
            @endif
            <div class="text-white">
                <span class="badge bg-primary px-3 py-1 rounded-pill mb-1">{{ $ad->display_category }}</span>
                <h2 class="fw-bold mb-0 text-white">{{ $ad->title }}</h2>
            </div>
        </div>
    </div>
</div>
@endif

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $ad->title }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Coluna da Esquerda: Galeria e Descrição -->
        <div class="col-12 col-lg-8">
            <div class="bg-white rounded-4 shadow-sm border p-3 p-md-4 mb-4">
                
                <!-- Imagem Principal / Portfólio -->
                <div class="main-image-container rounded-3 overflow-hidden mb-3 text-center bg-light" style="max-height: 450px;">
                    @if($ad->card_image)
                        <img src="{{ asset($ad->card_image) }}" id="main-ad-image" class="img-fluid object-fit-contain w-100" style="max-height: 450px;" alt="{{ $ad->title }}">
                    @else
                        <img src="{{ asset('images/logo.png') }}" class="img-fluid p-5 object-fit-contain" style="max-height: 300px;" alt="{{ $ad->title }}">
                    @endif
                </div>

                <!-- Miniaturas de Imagens da Galeria -->
                @if($ad->images->count() > 1)
                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-images text-primary me-2"></i> Galeria de Trabalhos / Fotos</h6>
                <div class="d-flex gap-2 overflow-auto pb-2 mb-4">
                    @foreach($ad->images as $img)
                    <img src="{{ asset($img->image_path) }}" class="rounded-3 border cursor-pointer thumbnail-img" style="width: 90px; height: 70px; object-fit: cover;" onclick="document.getElementById('main-ad-image').src='{{ asset($img->image_path) }}'">
                    @endforeach
                </div>
                @endif

                <!-- Título, Preço e Tipo -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h1 class="fw-bold fs-3 text-dark mb-0">{{ $ad->title }}</h1>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fs-6">{{ $ad->display_category }}</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-4">
                    @if($ad->price > 0)
                        <span class="fs-2 fw-bold text-primary">R$ {{ number_format($ad->price, 2, ',', '.') }}</span>
                    @else
                        <span class="fs-4 fw-bold text-success">A Combinar</span>
                    @endif
                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><i class="fa-solid fa-location-dot me-1"></i> {{ $ad->city }}, Sergipe</span>
                    @if($ad->cnpj)
                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill"><i class="fa-solid fa-id-card me-1"></i> CNPJ: {{ $ad->cnpj }}</span>
                    @endif
                </div>

                <hr class="my-4">

                <!-- Descrição detalhada -->
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-align-left text-primary me-2"></i> Descrição Detalhada</h5>
                <p class="text-secondary lh-lg mb-4" style="white-space: pre-line;">{{ $ad->description }}</p>

                <!-- Horários de Funcionamento se houver -->
                @if(is_array($ad->business_hours) && count($ad->business_hours) > 0)
                <hr class="my-4">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-regular fa-clock text-primary me-2"></i> Horários de Atendimento</h5>
                <div class="row g-2">
                    @foreach($ad->business_hours as $dayKey => $times)
                        @if(isset($times['open']) && isset($times['close']))
                        <div class="col-6 col-sm-4 col-md-3">
                            <div class="bg-light p-2 rounded-3 text-center border">
                                <small class="fw-bold text-capitalize text-dark d-block">{{ $dayKey }}</small>
                                <small class="text-muted">{{ $times['open'] }} - {{ $times['close'] }}</small>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Coluna da Direita: Dados do Anunciante & Redes Sociais -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-4 shadow-sm border p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold text-dark mb-3">Contato & Responsável</h5>
                
                <div class="d-flex align-items-center gap-3 mb-4">
                    @if($ad->logo)
                        <img src="{{ asset($ad->logo) }}" class="rounded-circle shadow-sm object-fit-contain" style="width: 55px; height: 55px;">
                    @else
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 55px; height: 55px;">
                            {{ strtoupper(substr($ad->user->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">{{ $ad->user->name ?? 'Anunciante' }}</h6>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i> Perfil Verificado em SE</p>
                    </div>
                </div>

                @if($ad->user->whatsapp)
                <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $ad->user->whatsapp) }}?text=Olá,%20vi%20seu%20anúncio%20'{{ urlencode($ad->title) }}'%20no%20Conectado%20em%20Sergipe!" target="_blank" class="btn btn-success w-100 rounded-pill py-3 fw-bold mb-2 text-nowrap">
                    <i class="fa-brands fa-whatsapp me-2 fs-5"></i> Falar no WhatsApp
                </a>
                @endif

                @if($ad->user->phone)
                <a href="tel:{{ $ad->user->phone }}" class="btn btn-outline-primary w-100 rounded-pill py-2 fw-semibold mb-3">
                    <i class="fa-solid fa-phone me-2"></i> {{ $ad->user->phone }}
                </a>
                @endif

                <!-- Redes Sociais do Prestador -->
                @if($ad->instagram || $ad->facebook)
                <hr class="my-3">
                <h6 class="fw-bold text-dark mb-2">Redes Sociais</h6>
                <div class="d-flex gap-2">
                    @if($ad->instagram)
                    <a href="https://instagram.com/{{ ltrim($ad->instagram, '@') }}" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill flex-fill"><i class="fa-brands fa-instagram me-1"></i> Instagram</a>
                    @endif
                    @if($ad->facebook)
                    <a href="{{ $ad->facebook }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill flex-fill"><i class="fa-brands fa-facebook-f me-1"></i> Facebook</a>
                    @endif
                </div>
                @endif

                <div class="text-center mt-3">
                    @include('reports._button-and-modal', ['reportable' => $ad])
                </div>

                <hr class="my-3">
                <p class="text-muted small text-center mb-0"><i class="fa-regular fa-eye me-1"></i> {{ $ad->views }} visualizações deste anúncio</p>
            </div>
        </div>
    </div>
</div>
@endsection
