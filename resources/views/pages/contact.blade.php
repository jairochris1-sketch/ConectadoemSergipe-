@extends('layouts.app')

@section('title', 'Fale Conosco - Conectado em Sergipe')

@section('content')
<div class="container py-5 position-relative">
    <div class="mb-3">
        <a href="{{ url('/') }}" class="btn btn-sm btn-light rounded-pill shadow-sm" style="font-size: 0.9rem; font-weight: 600; color: #1d4ed8; padding: 0.4rem 1rem;">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-2">Atendimento</span>
                        <h2 class="fw-bold text-dark">Fale Conosco</h2>
                        <p class="text-muted small">Tem dúvidas, sugestões ou precisa de ajuda? Envie uma mensagem.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success rounded-3 mb-4">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            Confira os campos destacados e tente novamente.
                        </div>
                    @endif

                    <form action="{{ route('page.contact.send') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Seu Nome *</label>
                            <input type="text" class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Ex: Maria Santos">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Seu E-mail *</label>
                            <input type="email" class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="seuemail@exemplo.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label fw-semibold">Assunto *</label>
                            <input type="text" class="form-control form-control-lg rounded-3 @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required maxlength="255" placeholder="Ex: Dúvida sobre planos ou anúncio">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label fw-semibold">Mensagem *</label>
                            <textarea class="form-control rounded-3 @error('message') is-invalid @enderror" id="message" name="message" rows="4" required maxlength="5000" placeholder="Escreva aqui a sua mensagem...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fa-paper-plane fa-regular me-2"></i> Enviar Mensagem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
