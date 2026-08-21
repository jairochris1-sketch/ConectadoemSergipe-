@extends('layouts.app')

@section('title', 'Conta criada - Conectado em Sergipe')

@section('content')
<main class="registration-success-page">
    <section
        class="registration-success-card"
        id="registration-success"
        data-animation-src="{{ asset('animations/account-created.json') }}"
        data-login-url="{{ route('login') }}"
        data-redirect-delay="4200"
        aria-labelledby="registration-success-title"
    >
        <div class="registration-success-animation" aria-hidden="true">
            <canvas data-registration-animation width="220" height="220"></canvas>
            <span class="registration-success-fallback"><i class="fa-solid fa-check"></i></span>
        </div>

        <p class="registration-success-eyebrow">Cadastro concluído</p>
        <h1 id="registration-success-title">Sua conta foi criada!</h1>
        <p>Agora você será direcionado para entrar com seu e-mail, telefone ou nome de usuário.</p>

        <div class="registration-success-progress" aria-hidden="true"><span></span></div>
        <p class="registration-success-status" role="status">Redirecionando para o login...</p>

        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
            Ir para o login agora
        </a>
    </section>
</main>

<style>
.registration-success-page {
    min-height: calc(100vh - 80px);
    display: grid;
    place-items: center;
    padding: 32px 16px;
    background: radial-gradient(circle at top, rgba(37, 99, 235, .12), transparent 46%), var(--background, #f8fafc);
}
.registration-success-card {
    width: min(100%, 520px);
    padding: clamp(28px, 6vw, 48px);
    text-align: center;
    background: var(--card, #fff);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 28px;
    box-shadow: 0 24px 64px rgba(15, 23, 42, .14);
}
.registration-success-animation {
    position: relative;
    width: 190px;
    height: 190px;
    margin: -8px auto 8px;
}
.registration-success-animation canvas { width: 100%; height: 100%; }
.registration-success-fallback {
    position: absolute;
    inset: 29px;
    display: none;
    place-items: center;
    color: #fff;
    font-size: 3.4rem;
    background: #16a34a;
    border-radius: 50%;
}
.registration-success-animation.is-fallback canvas { display: none; }
.registration-success-animation.is-fallback .registration-success-fallback { display: grid; }
.registration-success-eyebrow {
    margin: 0 0 8px;
    color: #16a34a;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}
.registration-success-card h1 { margin-bottom: 12px; color: var(--foreground, #0f172a); font-size: clamp(1.8rem, 6vw, 2.5rem); font-weight: 800; }
.registration-success-card > p:not(.registration-success-eyebrow):not(.registration-success-status) { color: var(--muted-foreground, #64748b); line-height: 1.65; }
.registration-success-progress { height: 7px; margin: 24px 0 10px; overflow: hidden; background: #dbeafe; border-radius: 999px; }
.registration-success-progress span { display: block; width: 100%; height: 100%; background: linear-gradient(90deg, #2563eb, #16a34a); transform-origin: left; animation: registrationRedirect 4.2s linear forwards; }
.registration-success-status { margin-bottom: 20px; color: var(--muted-foreground, #64748b); font-size: .88rem; }
@keyframes registrationRedirect { from { transform: scaleX(0); } to { transform: scaleX(1); } }
@media (prefers-reduced-motion: reduce) {
    .registration-success-animation canvas { display: none; }
    .registration-success-fallback { display: grid; }
    .registration-success-progress span { animation: none; }
}
</style>
@endsection

@push('scripts')
    @vite('resources/js/registration-success.js')
@endpush
