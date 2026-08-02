@extends('layouts.app')

@section('title', 'Acesse sua conta - Conectado em Sergipe')

@section('content')
<div class="container-fluid p-0 min-vh-100 bg-white">
    <div class="row g-0 min-vh-100">
        
        <!-- COLUNA DA ESQUERDA: FORMULÁRIO DE LOGIN COMPACTO E ELEGANTE -->
        <div class="col-12 col-lg-5 d-flex flex-column justify-content-center p-4 p-md-5 bg-white">
            <div class="w-100 mx-auto" style="max-width: 400px;">
                
                <!-- Logo -->
                <div class="mb-3 text-start">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/logo.png') }}" height="40" alt="Conectado em Sergipe">
                    </a>
                </div>

                <!-- Título e Subtítulo -->
                <div class="mb-3">
                    <h3 class="fw-bold text-dark mb-1">Acesse <span class="text-primary">sua conta</span></h3>
                    <p class="text-muted small mb-0">Use seu celular, e-mail ou @usuário para entrar.</p>
                </div>

                <!-- Alerta quando redirecionado ao tentar anunciar -->
                @if(session('info'))
                    <div class="alert alert-info rounded-3 mb-3 p-2.5 border-0 bg-info bg-opacity-10 text-info small">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <div>
                                <strong class="d-block text-dark" style="font-size: 0.8rem;">Aviso para Publicação:</strong>
                                <span>{{ session('info') }}</span>
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

                <!-- Formulário de Login -->
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <div class="mb-2.5">
                        <label for="login" class="form-label fw-semibold text-dark small mb-1">Celular, e-mail ou @usuário</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3"><i class="fa-regular fa-user"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 rounded-end-3 py-2 text-dark" style="font-size: 0.9rem;" id="login" name="login" value="{{ old('login') }}" required autocomplete="username" placeholder="(79) 99999-9999, email@exemplo.com ou @usuario">
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" class="form-control bg-light border-start-0 rounded-end-3 py-2 text-dark" style="font-size: 0.9rem;" id="password" name="password" required autocomplete="current-password" placeholder="••••••••••••">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label text-muted" style="font-size: 0.82rem;" for="remember">Lembrar de mim</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="text-primary text-decoration-none fw-semibold" style="font-size: 0.82rem;">Esqueci minha senha</a>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-sm" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-user me-2"></i> Entrar
                        </button>
                    </div>

                    <div class="text-center position-relative my-3">
                        <hr class="text-muted opacity-25 my-0">
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted" style="font-size: 0.75rem;">ou continue com</span>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold border text-dark" style="font-size: 0.85rem;">
                            <i class="fa-brands fa-google text-danger me-2"></i> Continuar com Google
                        </button>
                    </div>
                </form>

                <!-- Seção de Cadastro Próxima e Proporcionada -->
                <div class="text-center pt-2">
                    <span class="text-muted small">Não tem uma conta? </span>
                    <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none small">Criar conta</a>
                    <div class="mt-2">
                        <a href="{{ route('register') }}" class="btn btn-success btn-sm rounded-pill w-100 py-2 fw-bold shadow-sm text-white" style="font-size: 0.85rem;">
                            <i class="fa-solid fa-user-plus me-1.5"></i> Criar conta gratuita agora
                        </a>
                    </div>
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

            <!-- Banner Flutuante Central de Vidro (Glassmorphism) -->
            <div class="position-absolute top-50 start-50 translate-middle p-4 p-xl-5 text-white rounded-4 border border-white border-opacity-20 shadow-2xl" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(16px); width: 80%; max-width: 520px; z-index: 10;">
                <h3 class="fw-bold lh-base text-white mb-0 fs-3">
                    Conecte-se a <span class="text-info fw-bold">serviços</span>, <span class="text-info fw-bold">produtos</span>, <span class="text-info fw-bold">imóveis</span>, <span class="text-info fw-bold">veículos</span> e <span class="text-info fw-bold">oportunidades</span> em um único lugar.
                </h3>
            </div>

        </div>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>
@endsection
