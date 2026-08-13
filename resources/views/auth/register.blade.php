@extends('layouts.app')

@section('title', 'Criar Conta Gratuita - Conectado em Sergipe')

@section('content')
<div class="container-fluid p-0 min-vh-100 bg-white">
    <div class="row g-0 min-vh-100">
        
        <!-- COLUNA DA ESQUERDA: FORMULÁRIO DE CADASTRO COMPACTO E ELEGANTE -->
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
                    <h3 class="fw-bold text-dark mb-1">Crie sua <span class="text-primary">conta gratuita</span></h3>
                    <p class="text-muted small mb-0">Preencha seus dados para publicar anúncios em Sergipe.</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-3 p-2.5 small">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulário de Cadastro -->
                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    
                    <div class="mb-2.5">
                        <input type="text" class="form-control bg-light rounded-3 py-2 text-dark" style="font-size: 0.9rem;" id="name" name="name" value="{{ old('name') }}" required placeholder="Seu nome completo">
                    </div>

                    <div class="mb-2.5">
                        <input type="text" class="form-control bg-light rounded-3 py-2 text-dark @error('username') is-invalid @enderror" style="font-size: 0.9rem;" id="username" name="username" value="{{ old('username') }}" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9._]+" autocomplete="username" placeholder="Nome de usuário" aria-describedby="username-error">
                        @error('username')
                            <div class="text-danger small mt-1" id="username-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-2.5">
                        <input type="email" class="form-control bg-light rounded-3 py-2 text-dark" style="font-size: 0.9rem;" id="email" name="email" value="{{ old('email') }}" required placeholder="Seu e-mail principal">
                    </div>

                    <div class="row g-2 mb-2.5">
                        <div class="col-6">
                            <input type="tel" class="form-control bg-light rounded-3 py-2 text-dark" style="font-size: 0.85rem;" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="WhatsApp obrigatório">
                        </div>
                        <div class="col-6">
                            <select class="form-select auth-register-select-clean bg-light rounded-3 py-2 text-secondary" style="font-size: 0.85rem;" id="city" name="city">
                                @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                    <option value="{{ $cityName }}" {{ $cityName === 'Aracaju' ? 'selected' : '' }}>{{ $cityName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="password" class="form-control bg-light rounded-3 py-2 text-dark" style="font-size: 0.85rem;" id="password" name="password" required autocomplete="new-password" placeholder="Senha">
                        </div>
                        <div class="col-6">
                            <input type="password" class="form-control bg-light rounded-3 py-2 text-dark" style="font-size: 0.85rem;" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Confirmação">
                        </div>
                    </div>

                    <div class="form-check auth-register-terms mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="terms_accepted" name="terms_accepted" required {{ old('terms_accepted') ? 'checked' : '' }}>
                        <label class="form-check-label" for="terms_accepted">
                            Li e aceito os
                            <a href="{{ route('page.terms') }}" target="_blank" rel="noopener" class="fw-bold text-primary text-decoration-none">Termos de Uso</a>
                            do site.
                        </label>
                        @error('terms_accepted')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary auth-register-submit fw-bold shadow-sm" data-register-submit>
                            <i class="fa-solid fa-user-check me-2" aria-hidden="true"></i> Criar agora
                        </button>
                    </div>
                </form>

                @if(\App\Models\Setting::get('google_login_enabled', '1') === '1')
                    @include('auth._google_button')
                @endif

                <!-- Retorno para o Login -->
                <div class="text-center pt-2">
                    <span class="text-muted small">Já possui uma conta? </span>
                    <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none small">Entrar</a>
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

.auth-register-submit {
    min-height: 50px;
    color: #fff !important;
    background: #0d6efd !important;
    border: 0 !important;
    border-radius: 999px !important;
    font-size: .95rem;
}

.auth-register-submit:hover,
.auth-register-submit:focus-visible {
    background: #0b5ed7 !important;
}

.auth-register-select-clean {
    padding-right: .75rem !important;
    background-image: none !important;
}

.auth-register-terms {
    color: var(--foreground);
    font-size: .78rem;
    line-height: 1.4;
}

.auth-register-terms .form-check-input {
    margin-top: .18rem;
}

.auth-register-submit:disabled {
    cursor: not-allowed;
    opacity: .55;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const termsCheckbox = document.getElementById('terms_accepted');
    const submitButton = document.querySelector('[data-register-submit]');

    if (!termsCheckbox || !submitButton) return;

    const updateSubmitState = () => {
        submitButton.disabled = !termsCheckbox.checked;
        submitButton.setAttribute('aria-disabled', submitButton.disabled ? 'true' : 'false');
    };

    termsCheckbox.addEventListener('change', updateSubmitState);
    updateSubmitState();
});
</script>
@endsection
