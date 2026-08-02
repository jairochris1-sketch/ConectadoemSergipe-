@extends('layouts.app')

@section('title', 'Planos e Preços - Conectado em Sergipe')

@push('meta')
<meta name="description" content="Escolha o plano ideal para o seu negócio no Conectado em Sergipe. Mais visibilidade, mais clientes e mais resultados para você crescer em Sergipe.">
@endpush

@push('styles')
<style>
/* ─── Layout Global da Página de Planos ──────────────────────────────────── */
.plans-page-wrapper {
    background: #092c7d;
    background: linear-gradient(180deg, #072675 0%, #0d3aa9 450px, #f4f6fb 450px, #f4f6fb 100%);
    min-height: 100vh;
    padding-bottom: 60px;
}

.plans-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    color: #ffffff;
    text-decoration: none;
    padding: 8px 18px;
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 50px;
    background: rgba(255,255,255,0.1);
    transition: all 0.2s ease;
}
.plans-back-btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: #ffffff;
    color: #ffffff;
}

/* ─── Hero Header ────────────────────────────────────────────────────────── */
.plans-hero-section {
    padding: 30px 0 60px;
    position: relative;
}
.plans-hero-tag {
    color: #90b5ff;
    letter-spacing: 1.5px;
    font-weight: 700;
    font-size: 0.78rem;
    text-transform: uppercase;
    margin-bottom: 12px;
    display: block;
}
.plans-hero-title {
    font-size: clamp(2rem, 3.8vw, 2.7rem);
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}
.plans-hero-lead {
    font-size: 1.05rem;
    color: #dbeafe;
    opacity: 0.9;
    max-width: 620px;
    margin: 0 auto;
}

/* ─── Card de Plano ───────────────────────────────────────────────────────── */
.plan-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 28px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 1px solid #e2e8f0;
}
.plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
}
.plan-card.is-pro {
    border: 2px solid #081d52;
    box-shadow: 0 10px 36px rgba(8, 29, 82, 0.22);
}

/* Topo do Card (Faixa Colorida) */
.plan-card-header-bar {
    padding: 14px 20px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 800;
    font-size: 0.92rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.plan-card-header-bar.free {
    background: #0a2d78;
    justify-content: center;
}
.plan-card-header-bar.start {
    background: #1a56db;
}
.plan-card-header-bar.pro {
    background: #081d52;
}
.plan-card-header-bar.premium {
    background: #1a56db;
}

.plan-card-badge {
    background: #2b66ee;
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 6px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.plan-card-badge.pro-badge {
    background: #1a56db;
}

/* Conteúdo Interno do Card */
.plan-card-body {
    padding: 24px 22px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.plan-card-headline {
    font-size: 1.18rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin-bottom: 6px;
}
.plan-card-desc {
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.5;
    min-height: 48px;
    margin-bottom: 16px;
}

/* Bloco de Preço */
.plan-card-price-block {
    display: flex;
    align-items: baseline;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid #f1f5f9;
}
.plan-card-price-currency {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0d3aa9;
    margin-right: 2px;
}
.plan-card-price-amount {
    font-size: 2.3rem;
    font-weight: 900;
    color: #0d3aa9;
    line-height: 1;
    letter-spacing: -1px;
}
.plan-card-price-period {
    font-size: 0.8rem;
    color: #64748b;
    margin-left: 4px;
    font-weight: 500;
}

/* Lista de Benefícios */
.plan-card-features {
    list-style: none;
    padding: 0;
    margin: 0 0 24px 0;
    flex: 1;
}
.plan-card-features li {
    font-size: 0.82rem;
    color: #334155;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.45;
}
.plan-card-features li.blocked-feature {
    color: #94a3b8;
}
.icon-check-blue {
    color: #1a56db;
    font-size: 0.85rem;
    margin-top: 2px;
    flex-shrink: 0;
}
.icon-cross-grey {
    color: #cbd5e1;
    font-size: 0.85rem;
    margin-top: 2px;
    flex-shrink: 0;
}

/* Botões CTA */
.plan-cta-btn {
    width: 100%;
    padding: 12px 20px;
    border-radius: 100px;
    font-weight: 700;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}
.plan-cta-btn.btn-free {
    background: transparent;
    border: 2px solid #1a56db;
    color: #1a56db;
}
.plan-cta-btn.btn-free:hover {
    background: #f0f5ff;
    color: #0d3aa9;
    border-color: #0d3aa9;
}
.plan-cta-btn.btn-start {
    background: #1a56db;
    color: #ffffff;
}
.plan-cta-btn.btn-start:hover {
    background: #1447be;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(26,86,219,0.35);
}
.plan-cta-btn.btn-pro {
    background: #081d52;
    color: #ffffff;
}
.plan-cta-btn.btn-pro:hover {
    background: #041133;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(8,29,82,0.35);
}
.plan-cta-btn.btn-premium {
    background: #1a56db;
    color: #ffffff;
}
.plan-cta-btn.btn-premium:hover {
    background: #1447be;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(26,86,219,0.35);
}

.plan-trust-badge {
    font-size: 0.73rem;
    color: #64748b;
    text-align: center;
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.plan-trust-badge i {
    color: #1a56db;
    font-size: 0.78rem;
}

/* ─── Card de Garantias / Benefícios Inferior ────────────────────────────── */
.plans-bottom-banner {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
    padding: 28px 32px;
    margin-top: 48px;
    border: 1px solid #e2e8f0;
}
.guarantee-item {
    display: flex;
    align-items: center;
    gap: 16px;
}
.guarantee-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #e0ecff;
    color: #1a56db;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.guarantee-title {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 2px;
}
.guarantee-desc {
    font-size: 0.78rem;
    color: #64748b;
    margin-bottom: 0;
    line-height: 1.35;
}

@media (max-width: 991px) {
    .plans-page-wrapper {
        background: linear-gradient(180deg, #072675 0%, #0d3aa9 520px, #f4f6fb 520px, #f4f6fb 100%);
    }
}

/* ─── FAQ ─────────────────────────────────────────────────────────────────── */
.plans-faq-section {
    margin-top: 60px;
    max-width: 780px;
    margin-left: auto;
    margin-right: auto;
}
.plans-faq-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    text-align: center;
    margin-bottom: 8px;
}
.plans-faq-subtitle {
    font-size: 0.95rem;
    color: #64748b;
    text-align: center;
    margin-bottom: 32px;
}
.faq-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 10px;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.faq-item:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,0.06);
}
.faq-btn {
    width: 100%;
    background: none;
    border: none;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    text-align: left;
    font-size: 0.97rem;
    font-weight: 700;
    color: #0f172a;
    cursor: pointer;
    transition: color 0.2s ease;
}
.faq-btn:hover {
    color: #1a56db;
}
.faq-btn[aria-expanded="true"] {
    color: #1a56db;
}
.faq-btn .faq-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e0ecff;
    color: #1a56db;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    transition: transform 0.25s ease, background 0.2s ease;
}
.faq-btn[aria-expanded="true"] .faq-icon {
    transform: rotate(45deg);
    background: #1a56db;
    color: #ffffff;
}
.faq-body {
    padding: 0 22px 18px;
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.65;
    border-top: 1px solid #f1f5f9;
    padding-top: 14px;
}

/* ─── Sobre o Conectado ────────────────────────────────────────────────────── */
.plans-about-section {
    margin-top: 60px;
    padding: 48px 40px;
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.05);
}
.plans-about-tag {
    display: inline-block;
    background: #e0ecff;
    color: #1a56db;
    font-size: 0.73rem;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 50px;
    margin-bottom: 14px;
}
.plans-about-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
    letter-spacing: -0.3px;
}
.plans-about-lead {
    font-size: 0.95rem;
    color: #475569;
    line-height: 1.7;
    max-width: 560px;
    margin-bottom: 28px;
}
.plans-about-stats {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
    margin-top: 8px;
}
.plans-about-stat-number {
    font-size: 2rem;
    font-weight: 900;
    color: #1a56db;
    letter-spacing: -1px;
    line-height: 1;
    margin-bottom: 4px;
}
.plans-about-stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 500;
}
.plans-about-image-col {
    display: flex;
    align-items: center;
    justify-content: center;
}
.plans-about-map-box {
    background: linear-gradient(135deg, #e0ecff 0%, #c7d9ff 100%);
    border-radius: 20px;
    padding: 40px;
    text-align: center;
    color: #1a56db;
    font-size: 5rem;
    width: 100%;
    aspect-ratio: 1;
    max-width: 260px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.plans-about-map-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #1a56db;
    letter-spacing: 0.5px;
}

@media (max-width: 768px) {
    .plans-about-section { padding: 30px 22px; }
    .plans-about-stats { gap: 24px; }
    .plans-about-map-box { max-width: 180px; }
}

/* ─── MODOS DE TEMA ESCURO (DARK MODE) ──────────────────────────────────── */
html[data-theme="dark"] .plans-page-wrapper {
    background: linear-gradient(180deg, #051647 0%, #092066 450px, #0b1120 450px, #0b1120 100%);
}

@media (max-width: 991px) {
    html[data-theme="dark"] .plans-page-wrapper {
        background: linear-gradient(180deg, #051647 0%, #092066 520px, #0b1120 520px, #0b1120 100%);
    }
}

html[data-theme="dark"] .plan-card {
    background: #111827;
    border-color: #283548;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
}

html[data-theme="dark"] .plan-card:hover {
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.6);
}

html[data-theme="dark"] .plan-card.is-pro {
    border-color: #3b82f6;
    box-shadow: 0 10px 36px rgba(59, 130, 246, 0.3);
}

html[data-theme="dark"] .plan-card-headline {
    color: #f8fafc;
}

html[data-theme="dark"] .plan-card-desc {
    color: #94a3b8;
}

html[data-theme="dark"] .plan-card-price-block {
    border-bottom-color: #1f2937;
}

html[data-theme="dark"] .plan-card-price-currency,
html[data-theme="dark"] .plan-card-price-amount {
    color: #60a5fa;
}

html[data-theme="dark"] .plan-card-price-period {
    color: #94a3b8;
}

html[data-theme="dark"] .plan-card-features li {
    color: #cbd5e1;
}

html[data-theme="dark"] .plan-card-features li.blocked-feature {
    color: #475569;
}

html[data-theme="dark"] .icon-check-blue {
    color: #3b82f6;
}

html[data-theme="dark"] .icon-cross-grey {
    color: #475569;
}

html[data-theme="dark"] .plan-cta-btn.btn-free {
    border-color: #3b82f6;
    color: #60a5fa;
}

html[data-theme="dark"] .plan-cta-btn.btn-free:hover {
    background: #172554;
    color: #93c5fd;
    border-color: #60a5fa;
}

html[data-theme="dark"] .plan-trust-badge {
    color: #94a3b8;
}

html[data-theme="dark"] .plan-trust-badge i {
    color: #3b82f6;
}

/* Banner de Garantias */
html[data-theme="dark"] .plans-bottom-banner {
    background: #111827;
    border-color: #283548;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

html[data-theme="dark"] .guarantee-icon-box {
    background: #172554;
    color: #60a5fa;
}

html[data-theme="dark"] .guarantee-title {
    color: #f8fafc;
}

html[data-theme="dark"] .guarantee-desc {
    color: #94a3b8;
}

/* FAQ */
html[data-theme="dark"] .plans-faq-title {
    color: #f8fafc;
}

html[data-theme="dark"] .plans-faq-subtitle {
    color: #94a3b8;
}

html[data-theme="dark"] .faq-item {
    background: #111827;
    border-color: #283548;
}

html[data-theme="dark"] .faq-btn {
    color: #f8fafc;
}

html[data-theme="dark"] .faq-btn:hover,
html[data-theme="dark"] .faq-btn[aria-expanded="true"] {
    color: #60a5fa;
}

html[data-theme="dark"] .faq-btn .faq-icon {
    background: #172554;
    color: #60a5fa;
}

html[data-theme="dark"] .faq-btn[aria-expanded="true"] .faq-icon {
    background: #3b82f6;
    color: #ffffff;
}

html[data-theme="dark"] .faq-body {
    color: #94a3b8;
    border-top-color: #1f2937;
}

/* Sobre o Conectado */
html[data-theme="dark"] .plans-about-section {
    background: #111827;
    border-color: #283548;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

html[data-theme="dark"] .plans-about-tag {
    background: #172554;
    color: #60a5fa;
}

html[data-theme="dark"] .plans-about-title {
    color: #f8fafc;
}

html[data-theme="dark"] .plans-about-lead {
    color: #94a3b8;
}

html[data-theme="dark"] .plans-about-stat-number {
    color: #60a5fa;
}

html[data-theme="dark"] .plans-about-stat-label {
    color: #94a3b8;
}

html[data-theme="dark"] .plans-about-map-box {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 100%);
    color: #60a5fa;
}

html[data-theme="dark"] .plans-about-map-label {
    color: #93c5fd;
}</style>
@endpush

@section('content')
<div class="plans-page-wrapper">
    {{-- ─── Cabeçalho Hero ────────────────────────────────────────────────── --}}
    <section class="plans-hero-section text-center">
        <div class="container">
            <div class="d-flex align-items-center mb-3">
                <a href="{{ url('/') }}" class="plans-back-btn">
                    <i class="fa-solid fa-arrow-left"></i> Voltar
                </a>
            </div>
            <span class="plans-hero-tag">Planos e Benefícios</span>
            <h1 class="plans-hero-title">Escolha o plano ideal para o seu negócio</h1>
            <p class="plans-hero-lead">
                Mais visibilidade, mais clientes e mais resultados para você crescer em Sergipe.
            </p>
        </div>
    </section>

    {{-- ─── Grid de 4 Cards de Planos ──────────────────────────────────────── --}}
    <section class="container mb-4">
        <div class="row g-3 justify-content-center align-items-stretch">
            
            {{-- 1. GRATUITO --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="plan-card">
                    <div class="plan-card-header-bar free">
                        GRATUITO
                    </div>
                    <div class="plan-card-body">
                        <h2 class="plan-card-headline">Comece a anunciar sem custo.</h2>
                        <p class="plan-card-desc">Ideal para quem está começando e quer divulgar seus serviços ou produtos gratuitamente.</p>

                        <div class="plan-card-price-block">
                            <span class="plan-card-price-currency">R$</span>
                            <span class="plan-card-price-amount">0,00</span>
                            <span class="plan-card-price-period">/mês</span>
                        </div>

                        <ul class="plan-card-features">
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 3 anúncios ativos</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Estatísticas dos últimos 7 dias</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Chat do site</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> WhatsApp</li>
                            <li class="blocked-feature"><i class="fa-solid fa-xmark icon-cross-grey"></i> Loja online</li>
                            <li class="blocked-feature"><i class="fa-solid fa-xmark icon-cross-grey"></i> Destaque na vitrine</li>
                            <li class="blocked-feature"><i class="fa-solid fa-xmark icon-cross-grey"></i> Selo de verificado</li>
                            <li class="blocked-feature"><i class="fa-solid fa-xmark icon-cross-grey"></i> Suporte prioritário</li>
                        </ul>

                        <div>
                            @auth
                                <a href="{{ route('page.contact') }}" class="plan-cta-btn btn-free">
                                    Anunciar Grátis <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="plan-cta-btn btn-free">
                                    Anunciar Grátis <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endauth
                            <div class="plan-trust-badge">
                                <i class="fa-solid fa-shield-halved"></i> Cadastro rápido, seguro e gratuito
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. PLANO START --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="plan-card">
                    <div class="plan-card-header-bar start">
                        <span>PLANO START</span>
                        <span class="plan-card-badge">POPULAR</span>
                    </div>
                    <div class="plan-card-body">
                        <h2 class="plan-card-headline">Dê o primeiro passo com sua loja online.</h2>
                        <p class="plan-card-desc">Ideal para pequenos vendedores e prestadores que querem mais profissionalismo.</p>

                        <div class="plan-card-price-block">
                            <span class="plan-card-price-currency">R$</span>
                            <span class="plan-card-price-amount">25,00</span>
                            <span class="plan-card-price-period">/mês</span>
                        </div>

                        <ul class="plan-card-features">
                            <li><i class="fa-solid fa-check icon-check-blue"></i> 1 loja online</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 30 produtos por loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 20 anúncios ativos</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 5 fotos por produto</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> 1 banner da loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Estatísticas dos últimos 30 dias</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Link personalizado da loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Compartilhamento nas redes sociais</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Suporte padrão</li>
                            <li class="blocked-feature"><i class="fa-solid fa-xmark icon-cross-grey"></i> Selo de verificado</li>
                            <li class="blocked-feature"><i class="fa-solid fa-xmark icon-cross-grey"></i> Destaque na vitrine</li>
                        </ul>

                        <div>
                            @auth
                                <a href="{{ route('page.contact') }}" class="plan-cta-btn btn-start">
                                    Quero o Plano Start <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="plan-cta-btn btn-start">
                                    Quero o Plano Start <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endauth
                            <div class="plan-trust-badge">
                                <i class="fa-solid fa-shield-halved"></i> Cadastro rápido, seguro e gratuito
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PLANO PRO (Destaque) --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="plan-card is-pro">
                    <div class="plan-card-header-bar pro">
                        <span>PLANO PRO</span>
                        <span class="plan-card-badge pro-badge">MAIS ESCOLHIDO</span>
                    </div>
                    <div class="plan-card-body">
                        <h2 class="plan-card-headline">Venda todos os dias e fique à frente.</h2>
                        <p class="plan-card-desc">Para lojas que vendem constantemente e querem mais visibilidade, anúncios ilimitados e destaque.</p>

                        <div class="plan-card-price-block">
                            <span class="plan-card-price-currency">R$</span>
                            <span class="plan-card-price-amount">49,90</span>
                            <span class="plan-card-price-period">/mês</span>
                        </div>

                        <ul class="plan-card-features">
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Tudo do Plano Start</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 150 produtos por loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Anúncios ilimitados</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 12 fotos por produto</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 3 banner(s) por loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Estatísticas dos últimos 60 dias</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Loja em destaque na vitrine</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Selo de verificado no perfil</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Loja aparece primeiro nas buscas</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Maior prioridade nos resultados</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Suporte prioritário</li>
                        </ul>

                        <div>
                            @auth
                                <a href="{{ route('page.contact') }}" class="plan-cta-btn btn-pro">
                                    Quero o Plano PRO <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="plan-cta-btn btn-pro">
                                    Quero o Plano PRO <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endauth
                            <div class="plan-trust-badge">
                                <i class="fa-solid fa-shield-halved"></i> Seguro · Rápido · Sem burocracia
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. PLANO PREMIUM --}}
            <div class="col-12 col-md-6 col-xl-3">
                <div class="plan-card">
                    <div class="plan-card-header-bar premium">
                        <span>PLANO PREMIUM</span>
                        <span class="plan-card-badge">MÁXIMA VISIBILIDADE</span>
                    </div>
                    <div class="plan-card-body">
                        <h2 class="plan-card-headline">Domine o mercado em Sergipe!</h2>
                        <p class="plan-card-desc">Para empresas que querem dominar as buscas, ter até 3 lojas e suporte exclusivo.</p>

                        <div class="plan-card-price-block">
                            <span class="plan-card-price-currency">R$</span>
                            <span class="plan-card-price-amount">99,90</span>
                            <span class="plan-card-price-period">/mês</span>
                        </div>

                        <ul class="plan-card-features">
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Tudo do Plano PRO</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 3 lojas online</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Produtos ilimitados por loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 20 fotos por produto</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Até 6 banner(s) por loja</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Destaque permanente na vitrine</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Destaque na página inicial</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Prioridade máxima nas pesquisas</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Estatísticas avançadas</li>
                            <li><i class="fa-solid fa-check icon-check-blue"></i> Suporte VIP exclusivo</li>
                        </ul>

                        <div>
                            @auth
                                <a href="{{ route('page.contact') }}" class="plan-cta-btn btn-premium">
                                    Quero o Plano Premium <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="plan-cta-btn btn-premium">
                                    Quero o Plano Premium <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endauth
                            <div class="plan-trust-badge">
                                <i class="fa-solid fa-shield-halved"></i> Seguro · Rápido · Sem burocracia
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ─── Card de Garantias / Benefícios Adicionais (Bottom Banner) ──────── --}}
        <div class="plans-bottom-banner">
            <div class="row g-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="guarantee-item">
                        <div class="guarantee-icon-box">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <div class="guarantee-title">Ambiente 100% Seguro</div>
                            <p class="guarantee-desc">Seus dados e anúncios sempre protegidos.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="guarantee-item">
                        <div class="guarantee-icon-box">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div>
                            <div class="guarantee-title">Mais Visibilidade</div>
                            <p class="guarantee-desc">Apareça para milhares de pessoas todos os dias.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="guarantee-item">
                        <div class="guarantee-icon-box">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <div class="guarantee-title">Mais Resultados</div>
                            <p class="guarantee-desc">Conecte-se com clientes que realmente procuram você.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="guarantee-item">
                        <div class="guarantee-icon-box">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <div>
                            <div class="guarantee-title">Suporte Humano</div>
                            <p class="guarantee-desc">Atendimento de verdade quando você precisar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── FAQ ─────────────────────────────────────────────────────────────── --}}
        <div class="plans-faq-section">
            <h2 class="plans-faq-title">Perguntas Frequentes</h2>
            <p class="plans-faq-subtitle">Tudo que você precisa saber antes de escolher seu plano.</p>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false">
                    <span>Posso começar gratuitamente e fazer upgrade depois?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq1" class="collapse">
                    <div class="faq-body">
                        Sim! O Plano Gratuito não tem prazo de validade. Você pode publicar seus primeiros anúncios sem pagar nada e, quando quiser abrir sua loja online ou ter mais visibilidade, é só escolher um plano pago. A migração é simples e seus dados são mantidos.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                    <span>Como funciona o pagamento dos planos pagos?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq2" class="collapse">
                    <div class="faq-body">
                        Os planos Start, PRO e Premium são cobrados mensalmente. Você pode entrar em contato pela nossa equipe de suporte para receber as instruções de pagamento via Pix, cartão ou boleto bancário. O acesso é liberado assim que a confirmação for recebida.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                    <span>O que é a loja online e para que serve?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq3" class="collapse">
                    <div class="faq-body">
                        A loja online é uma página dedicada exclusivamente ao seu negócio dentro do Conectado em Sergipe. Ela reúne todos os seus produtos, fotos, WhatsApp, redes sociais e banners em um só lugar — funcionando como uma vitrine digital completa para atrair clientes diretamente.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                    <span>Qual é a diferença entre o Plano Start e o Plano PRO?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq4" class="collapse">
                    <div class="faq-body">
                        O Plano Start é ideal para quem está começando: você abre sua primeira loja com até 30 produtos e 20 anúncios ativos. Já o Plano PRO é para quem vende todos os dias: anúncios ilimitados, até 150 produtos, destaque nas buscas, loja em destaque na vitrine, selo de verificado e suporte prioritário. O PRO coloca você à frente da concorrência.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false">
                    <span>Posso cancelar meu plano a qualquer momento?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq5" class="collapse">
                    <div class="faq-body">
                        Sim, sem burocracia. Basta entrar em contato com nossa equipe pelo WhatsApp ou e-mail. Após o cancelamento, sua conta continua ativa no Plano Gratuito e todos os seus dados são preservados.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq6" aria-expanded="false">
                    <span>Meu negócio aparece para pessoas de outras cidades de Sergipe?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq6" class="collapse">
                    <div class="faq-body">
                        Sim! O Conectado em Sergipe cobre todos os 75 municípios do estado. Seu anúncio pode ser encontrado por qualquer pessoa que esteja buscando na sua cidade ou em Sergipe inteiro. Nos planos pagos, você tem ainda mais visibilidade nos resultados de busca.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" data-bs-toggle="collapse" data-bs-target="#faq7" aria-expanded="false">
                    <span>Preciso de conhecimento técnico para usar a plataforma?</span>
                    <span class="faq-icon"><i class="fa-solid fa-plus"></i></span>
                </button>
                <div id="faq7" class="collapse">
                    <div class="faq-body">
                        Não! A plataforma foi criada para ser simples e intuitiva. Em poucos minutos você cria sua conta, publica seu primeiro anúncio e já está disponível para ser encontrado por clientes. Se tiver qualquer dúvida, nossa equipe de suporte humano está pronta para ajudar.
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Sobre o Conectado em Sergipe ────────────────────────────────────── --}}
        <div class="plans-about-section mt-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="plans-about-tag"><i class="fa-solid fa-map-location-dot me-1"></i> Nossa Missão</span>
                    <h2 class="plans-about-title">O maior ecossistema digital<br>de negócios de Sergipe</h2>
                    <p class="plans-about-lead">
                        O <strong>Conectado em Sergipe</strong> nasceu com uma missão clara: fazer com que empreendedores, comerciantes, prestadores de serviço e profissionais autônomos de todo o estado possam crescer digitalmente — com facilidade, segurança e custo acessível.
                    </p>
                    <p class="plans-about-lead" style="margin-bottom: 0;">
                        Cobrimos todos os <strong>75 municípios do estado</strong>, dos grandes centros como Aracaju às cidades do interior. Aqui, pequenos negócios ganham a mesma vitrine que as grandes empresas.
                    </p>
                    <div class="plans-about-stats">
                        <div>
                            <div class="plans-about-stat-number">75</div>
                            <div class="plans-about-stat-label">municípios atendidos</div>
                        </div>
                        <div>
                            <div class="plans-about-stat-number">100%</div>
                            <div class="plans-about-stat-label">feito para Sergipe</div>
                        </div>
                        <div>
                            <div class="plans-about-stat-number">6</div>
                            <div class="plans-about-stat-label">categorias de negócios</div>
                        </div>
                        <div>
                            <div class="plans-about-stat-number">R$0</div>
                            <div class="plans-about-stat-label">para começar</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="plans-about-image-col">
                        <div class="plans-about-map-box">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <div class="plans-about-map-label">Sergipe, Brasil</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>
@endsection
