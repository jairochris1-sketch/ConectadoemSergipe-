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
                <form action="{{ route('register') }}" method="POST" id="registration-form">
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

                        @php
                            $suggestions = session('username_suggestions', []);
                        @endphp
                        <div id="username-suggestions-wrapper" class="username-suggestions-wrapper mt-2 p-2.5 rounded-3 border {{ empty($suggestions) ? 'd-none' : '' }}" aria-live="polite">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="small fw-semibold d-flex align-items-center gap-1.5 username-suggestions-title" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-lightbulb text-warning" aria-hidden="true"></i>
                                    Sugestões disponíveis para você:
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-1.5" id="username-suggestions-list">
                                @foreach($suggestions as $suggestion)
                                    <button type="button" class="btn btn-sm username-suggestion-btn py-1 px-2.5 rounded-pill d-inline-flex align-items-center gap-1" data-suggested-username="{{ $suggestion }}" style="font-size: 0.78rem;">
                                        <span class="text-muted fw-normal">@</span><span class="fw-bold">{{ $suggestion }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-1.5 username-suggestions-hint" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-arrow-pointer me-1" aria-hidden="true"></i>Clique em uma sugestão para preencher automaticamente.
                            </small>
                        </div>
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

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <div class="position-relative auth-password-input-group">
                                <input type="password" class="form-control bg-light rounded-3 py-2 text-dark auth-password-field" style="font-size: 0.85rem;" id="password" name="password" required autocomplete="new-password" placeholder="Senha">
                                <button type="button" class="auth-toggle-password-btn" data-target="password" aria-label="Mostrar senha" title="Mostrar senha" tabindex="-1">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="position-relative auth-password-input-group">
                                <input type="password" class="form-control bg-light rounded-3 py-2 text-dark auth-password-field" style="font-size: 0.85rem;" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Confirmação">
                                <button type="button" class="auth-toggle-password-btn" data-target="password_confirmation" aria-label="Mostrar confirmação da senha" title="Mostrar confirmação da senha" tabindex="-1">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="password-secure-notice" class="password-secure-notice d-none mb-3" role="alert" aria-live="polite">
                        <div class="d-flex align-items-center gap-2 p-2.5 rounded-3">
                            <i class="fa-solid fa-triangle-exclamation flex-shrink-0 text-warning" aria-hidden="true"></i>
                            <span class="small fw-semibold">
                                Atenção! Memorize ou guarde sua senha em um local seguro.
                            </span>
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
                        <div class="text-danger small mt-1 d-none" id="terms-client-error" role="alert">
                            Marque a opção acima para aceitar os Termos de Uso antes de criar sua conta.
                        </div>
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

.auth-password-input-group {
    position: relative;
    width: 100%;
}

.auth-password-field {
    padding-right: 36px !important;
}

.auth-toggle-password-btn {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    z-index: 4;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    color: #6b7280;
    background: transparent;
    border: 0;
    border-radius: 6px;
    cursor: pointer;
    transition: color 0.15s ease, background-color 0.15s ease;
}

.auth-toggle-password-btn:hover,
.auth-toggle-password-btn:focus-visible {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.08);
    outline: none;
}

html[data-theme="dark"] .auth-toggle-password-btn {
    color: #94a3b8;
}

html[data-theme="dark"] .auth-toggle-password-btn:hover,
html[data-theme="dark"] .auth-toggle-password-btn:focus-visible {
    color: #60a5fa;
    background-color: rgba(96, 165, 250, 0.12);
}

.password-secure-notice {
    animation: fadeInSecureNotice 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.password-secure-notice > div {
    background-color: #fff9e6;
    border: 1px solid #ffeeba;
    color: #856404;
    font-size: 0.8rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

html[data-theme="dark"] .password-secure-notice > div {
    background-color: rgba(255, 193, 7, 0.12);
    border-color: rgba(255, 193, 7, 0.28);
    color: #ffd24d;
}

.username-suggestions-wrapper {
    background-color: #f8fafc;
    border-color: #e2e8f0 !important;
    animation: fadeInSecureNotice 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.username-suggestions-title {
    color: #334155;
}

.username-suggestion-btn {
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
    background: #ffffff !important;
    cursor: pointer;
    transition: all 0.18s ease;
}

.username-suggestion-btn:hover,
.username-suggestion-btn:focus-visible {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(13, 110, 253, 0.2);
}

.username-suggestion-btn:hover .text-muted,
.username-suggestion-btn:focus-visible .text-muted {
    color: rgba(255, 255, 255, 0.85) !important;
}

.username-suggestion-btn.is-selected {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
}

.username-suggestion-btn.is-selected .text-muted {
    color: rgba(255, 255, 255, 0.85) !important;
}

html[data-theme="dark"] .username-suggestions-wrapper {
    background-color: #1e293b;
    border-color: #334155 !important;
}

html[data-theme="dark"] .username-suggestions-title {
    color: #e2e8f0;
}

html[data-theme="dark"] .username-suggestion-btn {
    border-color: #475569 !important;
    color: #e2e8f0 !important;
    background: #0f172a !important;
}

html[data-theme="dark"] .username-suggestion-btn:hover,
html[data-theme="dark"] .username-suggestion-btn:focus-visible,
html[data-theme="dark"] .username-suggestion-btn.is-selected {
    background: #2563eb !important;
    border-color: #3b82f6 !important;
    color: #ffffff !important;
}

html[data-theme="dark"] .username-suggestions-hint {
    color: #94a3b8 !important;
}

@keyframes fadeInSecureNotice {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const termsCheckbox = document.getElementById('terms_accepted');
    const registrationForm = document.getElementById('registration-form');
    const termsClientError = document.getElementById('terms-client-error');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const secureNotice = document.getElementById('password-secure-notice');
    const usernameInput = document.getElementById('username');
    const suggestionsWrapper = document.getElementById('username-suggestions-wrapper');
    const suggestionsList = document.getElementById('username-suggestions-list');

    if (termsCheckbox && registrationForm) {
        termsCheckbox.addEventListener('change', () => {
            if (!termsCheckbox.checked) return;

            termsCheckbox.setCustomValidity('');
            termsClientError?.classList.add('d-none');
        });

        registrationForm.addEventListener('submit', (event) => {
            if (termsCheckbox.checked) return;

            event.preventDefault();
            termsCheckbox.setCustomValidity('Aceite os Termos de Uso para criar sua conta.');
            termsClientError?.classList.remove('d-none');
            termsCheckbox.focus();
            termsCheckbox.reportValidity();
        });
    }

    // 1. Alerta de memorizar/guardar senha (some após 7 segundos)
    let secureNoticeTimeout = null;

    if (passwordInput && confirmInput && secureNotice) {
        const updatePasswordNotice = () => {
            const password = passwordInput.value;
            const confirmation = confirmInput.value;

            if (confirmation.length > 0 && password.length > 0 && confirmation === password) {
                secureNotice.classList.remove('d-none');

                if (secureNoticeTimeout) {
                    clearTimeout(secureNoticeTimeout);
                }

                secureNoticeTimeout = setTimeout(() => {
                    secureNotice.classList.add('d-none');
                }, 7000);
            } else {
                if (secureNoticeTimeout) {
                    clearTimeout(secureNoticeTimeout);
                }
                secureNotice.classList.add('d-none');
            }
        };

        passwordInput.addEventListener('input', updatePasswordNotice);
        confirmInput.addEventListener('input', updatePasswordNotice);
        confirmInput.addEventListener('change', updatePasswordNotice);
    }

    // 2. Olhinho para revelar/ocultar senha (volta a ocultar após 7 segundos)
    const togglePasswordButtons = document.querySelectorAll('.auth-toggle-password-btn');
    const autoHideTimeouts = {};

    togglePasswordButtons.forEach((button) => {
        const targetId = button.getAttribute('data-target');
        const targetInput = document.getElementById(targetId);
        const icon = button.querySelector('i');

        if (!targetInput || !icon) return;

        button.addEventListener('click', () => {
            const isCurrentlyPassword = targetInput.type === 'password';

            if (isCurrentlyPassword) {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                button.setAttribute('aria-label', 'Ocultar senha');
                button.setAttribute('title', 'Ocultar senha');

                if (autoHideTimeouts[targetId]) {
                    clearTimeout(autoHideTimeouts[targetId]);
                }

                autoHideTimeouts[targetId] = setTimeout(() => {
                    targetInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    button.setAttribute('aria-label', 'Mostrar senha');
                    button.setAttribute('title', 'Mostrar senha');
                }, 7000);
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                button.setAttribute('aria-label', 'Mostrar senha');
                button.setAttribute('title', 'Mostrar senha');

                if (autoHideTimeouts[targetId]) {
                    clearTimeout(autoHideTimeouts[targetId]);
                }
            }
        });
    });

    // 3. Sugestões de username
    if (usernameInput && suggestionsWrapper && suggestionsList) {
        suggestionsList.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-suggested-username]');
            if (!btn) return;

            const selectedUser = btn.getAttribute('data-suggested-username');
            if (selectedUser) {
                usernameInput.value = selectedUser;
                usernameInput.classList.remove('is-invalid');

                const errorFeedback = document.getElementById('username-error');
                if (errorFeedback) {
                    errorFeedback.style.display = 'none';
                }

                // Fecha a caixa de sugestões automaticamente
                suggestionsWrapper.classList.add('d-none');

                usernameInput.focus();
            }
        });
    }
});
</script>
@endsection
