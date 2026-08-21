@extends('layouts.app')

@section('title', 'Acesse sua conta - Conectado em Sergipe')

@section('content')
<div class="container-fluid p-0 min-vh-100 bg-white">
    <div class="row g-0 min-vh-100">
        
        <!-- COLUNA DA ESQUERDA: FORMULÁRIO DE LOGIN COMPACTO E ELEGANTE -->
        <div class="col-12 col-lg-5 d-flex flex-column justify-content-center p-4 p-md-5 bg-white">
            <div class="w-100 mx-auto" style="max-width: 400px;">
                
                <!-- Logo -->
                <div class="mb-4 text-center">
                    <a href="{{ route('home') }}" class="d-inline-block" aria-label="Ir para a página inicial">
                        <img src="{{ asset('images/2mapa-sergipe-conectado-azul.png') }}" class="auth-login-brand-logo auth-login-brand-logo-light" alt="Conectado em Sergipe">
                        <img src="{{ asset('images/1mapa-sergipe-conectado.png') }}" class="auth-login-brand-logo auth-login-brand-logo-dark" alt="Conectado em Sergipe">
                    </a>
                </div>

                <!-- Título e Subtítulo -->
                <div class="mb-3 text-center">
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
                <form action="{{ route('login') }}" method="POST" data-login-form>
                    @csrf
                    
                    <div class="mb-3">
                        <label for="login" class="visually-hidden">Número de celular, nome de usuário ou email</label>
                        <div class="auth-access-control" data-access-copy-control>
                            <input type="text" class="form-control auth-login-field" id="login" name="login" value="{{ old('login') }}" required autocomplete="username" placeholder="Número de celular, nome de usuário ou email" data-access-copy-field>
                            <div class="auth-password-actions">
                                <button type="button" class="auth-password-action" data-access-copy aria-label="Copiar acesso" title="Copiar acesso">
                                    <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="visually-hidden">Senha</label>
                        <div class="auth-password-control" data-password-control>
                            <input type="password" class="form-control auth-login-field" id="password" name="password" required autocomplete="current-password" placeholder="Senha" data-password-field>
                            <div class="auth-password-actions">
                                <button type="button" class="auth-password-action" data-password-toggle aria-label="Mostrar senha" title="Mostrar senha">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="auth-password-action" data-password-copy aria-label="Copiar senha" title="Copiar senha">
                                    <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary auth-login-submit fw-bold shadow-sm" data-login-submit>
                            <span data-login-submit-label>Entrar</span>
                            <span class="spinner-border spinner-border-sm d-none" data-login-submit-spinner aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="text-center mb-4">
                        <a href="{{ route('password.request') }}" class="auth-login-forgot-link text-decoration-none fw-bold">Esqueceu a senha?</a>
                    </div>

                    @if(\App\Models\Setting::get('google_login_enabled', '1') === '1')
                        @include('auth._google_button')
                    @endif
                </form>

                <!-- Seção de Cadastro Próxima e Proporcionada -->
                <div class="text-center pt-2">
                    <span class="text-muted small">Não tem uma conta? </span>
                    <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none small">Criar conta</a>
                </div>

                <div class="auth-login-benefits" aria-label="Vantagens do Conectado em Sergipe">
                    <div class="auth-login-benefit">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <strong>Seguro</strong>
                        <small>Seus dados<br>protegidos</small>
                    </div>
                    <div class="auth-login-benefit">
                        <i class="fa-regular fa-heart" aria-hidden="true"></i>
                        <strong>Prático</strong>
                        <small>Tudo que você<br>precisa em um só lugar</small>
                    </div>
                    <div class="auth-login-benefit">
                        <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                        <strong>Rápido</strong>
                        <small>Acesso fácil e<br>experiência leve</small>
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

            @include('auth._message_balloon')

        </div>
    </div>
</div>

<style>
.auth-login-brand-logo {
    display: block;
    width: 156px;
    height: auto;
}

.auth-login-brand-logo-dark {
    display: none;
}

.auth-login-field {
    min-height: 66px;
    padding: 0 18px;
    color: var(--foreground);
    background: transparent;
    border: 1px solid #8a94a3;
    border-radius: 18px !important;
    box-shadow: none;
    font-size: 1rem;
    font-weight: 500;
}

.auth-login-field::placeholder {
    color: #667085;
    opacity: 1;
}

.auth-login-field:focus {
    color: var(--foreground);
    background: transparent;
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, .16);
}

.auth-login-submit {
    min-height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border: 0;
    border-radius: 999px;
    font-size: 1rem;
}

.auth-login-forgot-link {
    color: var(--foreground);
    font-size: .95rem;
}

.auth-login-forgot-link:hover,
.auth-login-forgot-link:focus-visible {
    color: #0d6efd;
}

.auth-login-benefits {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid var(--border);
    text-align: center;
}

.auth-login-benefit {
    display: flex;
    min-width: 0;
    align-items: center;
    flex-direction: column;
}

.auth-login-benefit > i {
    margin-bottom: 8px;
    color: #0d6efd;
    font-size: 1.45rem;
}

.auth-login-benefit > strong {
    color: var(--foreground);
    font-size: .78rem;
}

.auth-login-benefit > small {
    margin-top: 3px;
    color: #667085;
    font-size: .61rem;
    line-height: 1.35;
}

html[data-theme="dark"] .auth-login-field {
    color: #f8fafc;
    background: rgba(255, 255, 255, .015);
    border-color: #565b65;
}

html[data-theme="dark"] .auth-login-field::placeholder {
    color: #aeb8c8;
}

html[data-theme="dark"] .auth-login-benefit > small {
    color: #aeb8c8;
}

html[data-theme="dark"] .auth-login-brand-logo-light {
    display: none;
}

html[data-theme="dark"] .auth-login-brand-logo-dark {
    display: block;
}

@media (max-width: 380px) {
    .auth-login-benefits {
        gap: 6px;
    }

    .auth-login-benefit > small {
        font-size: .56rem;
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>
@include('auth._password_controls')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-login-form]');
    const submitButton = form?.querySelector('[data-login-submit]');
    const submitLabel = submitButton?.querySelector('[data-login-submit-label]');
    const submitSpinner = submitButton?.querySelector('[data-login-submit-spinner]');

    if (!form || !submitButton || !submitLabel || !submitSpinner) return;

    const resetSubmit = () => {
        submitButton.disabled = false;
        submitButton.removeAttribute('aria-busy');
        submitLabel.textContent = 'Entrar';
        submitSpinner.classList.add('d-none');
    };

    form.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
        submitLabel.textContent = 'Entrando…';
        submitSpinner.classList.remove('d-none');
    });

    window.addEventListener('pageshow', resetSubmit);
});
</script>
@endpush
@endsection
