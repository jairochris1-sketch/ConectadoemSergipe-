@extends('layouts.app')

@section('title', ($ad->module === 'services' ? 'Perfil profissional criado' : 'Anúncio publicado') . ' - Conectado em Sergipe')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden text-center">
                <div class="card-body p-4 p-md-5">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 88px; height: 88px;">
                        <i class="fa-solid fa-circle-check fs-1"></i>
                    </div>

                    @if($ad->module === 'services')
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 mb-3">
                            Perfil profissional
                        </span>
                        <h1 class="fw-bold text-dark fs-2 mb-3">Seu perfil profissional foi criado!</h1>
                        <p class="text-muted fs-5 mb-2">
                            Seu perfil profissional de Prestador de Serviços no Conectado em Sergipe foi criado.
                        </p>
                        <p class="text-muted mb-4">
                            Mostre para seus clientes e compartilhe nas suas redes sociais.
                        </p>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 mb-3">
                            Publicação concluída
                        </span>
                        <h1 class="fw-bold text-dark fs-2 mb-3">Anúncio publicado!</h1>
                        <p class="text-muted fs-5 mb-4">
                            Seu anúncio já está disponível no Conectado em Sergipe.
                        </p>
                    @endif

                    <div class="bg-light border rounded-4 p-3 mb-4">
                        <div class="text-muted small mb-1">Publicado com sucesso</div>
                        <div class="fw-bold text-dark">{{ $ad->title }}</div>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-center gap-3">
                        <a href="{{ route('ad.create') }}" class="btn btn-success btn-lg rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-plus me-2"></i>
                            Criar outro anúncio grátis
                        </a>
                        <a href="{{ route('user.panel') }}" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-rectangle-list me-2"></i>
                            Ver meus anúncios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
