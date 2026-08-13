@extends('layouts.app')

@section('title', 'Recuperar Senha - Conectado em Sergipe')

@section('content')
<div class="container-fluid p-0 min-vh-100 bg-white">
    <div class="row g-0 min-vh-100">
        
        <!-- COLUNA DA ESQUERDA: FORMULÁRIO DE RECUPERAÇÃO COMPACTO E ELEGANTE -->
        <div class="col-12 col-lg-5 d-flex flex-column justify-content-center p-4 p-md-5 bg-white">
            <div class="w-100 mx-auto" style="max-width: 400px;">
                
                <!-- Logo -->
                <div class="mb-4 text-center">
                    <a href="{{ route('home') }}" class="d-inline-block" aria-label="Ir para a página inicial">
                        <img src="{{ asset('images/2mapa-sergipe-conectado-azul.png') }}" class="auth-theme-brand-logo auth-theme-brand-logo-light" alt="Conectado em Sergipe">
                        <img src="{{ asset('images/1mapa-sergipe-conectado.png') }}" class="auth-theme-brand-logo auth-theme-brand-logo-dark" alt="Conectado em Sergipe">
                    </a>
                </div>

                <!-- Título e Subtítulo -->
                <div class="mb-3">
                    <h3 class="fw-bold text-dark mb-1">Recuperar <span class="text-primary">sua senha</span></h3>
                    <p class="text-muted small mb-0">Digite seu celular, e-mail ou @usuário para continuar.</p>
                </div>

                @if(session('status'))
                    <div class="alert alert-success rounded-3 mb-3 p-2.5 small border-0 bg-success bg-opacity-10 text-success">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 0.8rem;">Solicitação recebida</strong>
                                <span>{{ session('status') }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-3 p-2.5 small">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulário de Esqueci minha Senha -->
                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="login" class="form-label fw-semibold text-dark small mb-1">Celular, e-mail ou @usuário</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3"><i class="fa-regular fa-user"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 rounded-end-3 py-2 text-dark" style="font-size: 0.9rem;" id="login" name="login" value="{{ old('login') }}" required autocomplete="username" placeholder="Celular, e-mail ou @usuario">
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Localizar Minha Conta
                        </button>
                    </div>
                </form>

                <!-- Botão de Voltar ao Login -->
                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4 py-2 fw-semibold" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-arrow-left me-1.5"></i> Voltar para o Login
                    </a>
                </div>

            </div>
        </div>

        <!-- COLUNA DA DIREITA: PAINEL DE DESTAQUE HERO -->
        <div class="col-lg-7 d-none d-lg-block position-relative overflow-hidden bg-dark">
            @include('auth._city-slideshow')

            <svg class="position-absolute top-0 start-0 w-100 h-100 opacity-20" xmlns="http://www.w3.org/2000/svg">
                <polyline points="200,150 400,250 350,450 650,300 700,500" fill="none" stroke="#60a5fa" stroke-width="2" stroke-dasharray="6,6" />
                <circle cx="200" cy="150" r="10" fill="#60a5fa" />
                <circle cx="400" cy="250" r="12" fill="#3b82f6" />
                <circle cx="350" cy="450" r="10" fill="#60a5fa" />
                <circle cx="650" cy="300" r="14" fill="#2563eb" />
                <circle cx="700" cy="500" r="10" fill="#60a5fa" />
            </svg>

            <!-- Cards Flutuantes -->
            <div class="position-absolute" style="top: 12%; left: 15%;">
                <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border" style="width: 220px; animation: float 6s ease-in-out infinite;">
                    <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=100&auto=format&fit=crop" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px;">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 140px;">Apartamento</h6>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Aracaju, SE</small>
                        <span class="fw-bold text-primary small">R$ 350.000</span>
                    </div>
                </div>
            </div>

            <div class="position-absolute" style="top: 22%; right: 12%;">
                <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border" style="width: 210px; animation: float 7s ease-in-out infinite 1s;">
                    <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-briefcase fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 130px;">Vaga p/ Mecânico</h6>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">N. Sra. do Socorro</small>
                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.65rem;">Em aberto</span>
                    </div>
                </div>
            </div>

            <div class="position-absolute" style="top: 42%; left: 10%;">
                <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border" style="width: 210px; animation: float 5s ease-in-out infinite 0.5s;">
                    <img src="https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=100&auto=format&fit=crop" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px;">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 130px;">Corolla 2022</h6>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Estância, SE</small>
                        <span class="fw-bold text-primary small">R$ 118.900</span>
                    </div>
                </div>
            </div>

            <div class="position-absolute" style="top: 60%; left: 8%;">
                <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border" style="width: 220px; animation: float 8s ease-in-out infinite 1.5s;">
                    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=100&auto=format&fit=crop" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px;">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 140px;">Fazenda à venda</h6>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Lagarto, SE</small>
                        <span class="fw-bold text-success small">R$ 2.800.000</span>
                    </div>
                </div>
            </div>

            <div class="position-absolute" style="bottom: 15%; left: 16%;">
                <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border" style="width: 200px; animation: float 6.5s ease-in-out infinite 2s;">
                    <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=100&auto=format&fit=crop" class="rounded-3 object-fit-cover" style="width: 48px; height: 48px;">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark small text-truncate" style="max-width: 120px;">iPhone 15</h6>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Itabaiana, SE</small>
                        <span class="fw-bold text-primary small">R$ 4.999</span>
                    </div>
                </div>
            </div>

            @include('auth._message_balloon')

        </div>
    </div>
</div>

<style>
.auth-theme-brand-logo {
    display: block;
    width: 156px;
    height: auto;
}

.auth-theme-brand-logo-dark {
    display: none;
}

html[data-theme="dark"] .auth-theme-brand-logo-light {
    display: none;
}

html[data-theme="dark"] .auth-theme-brand-logo-dark {
    display: block;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>
@endsection
