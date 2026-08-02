@extends('layouts.app')

@section('title', 'Sobre Nós - Conectado em Sergipe')

@push('styles')
<style>
.about-page-wrapper {
    background: #ffffff;
    padding: 20px 0 90px;
}
.about-back-bar {
    padding: 15px 0;
    background: #ffffff;
}
.about-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1d4ed8;
    text-decoration: none;
    padding: 8px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    background: #f8fafc;
    transition: all 0.2s ease;
}
.about-back-btn:hover {
    background: #eff6ff;
    border-color: #1d4ed8;
}
.about-badge {
    background-color: #eff6ff;
    color: #1d4ed8;
    font-weight: 800;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 6px 14px;
    border-radius: 50px;
    display: inline-block;
    margin-bottom: 24px;
}
.about-title {
    font-size: clamp(2rem, 3.5vw, 2.75rem);
    font-weight: 900;
    color: #0f172a;
    line-height: 1.15;
    letter-spacing: -1px;
    margin-bottom: 24px;
}
.about-accent-line {
    width: 60px;
    height: 5px;
    background-color: #1d4ed8;
    border-radius: 3px;
    margin-bottom: 32px;
}
.about-lead {
    font-size: 1.05rem;
    color: #475569;
    line-height: 1.7;
    margin-bottom: 32px;
}
.about-trust-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.about-trust-icon {
    width: 44px;
    height: 44px;
    background: #1d4ed8;
    color: #ffffff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.about-trust-text {
    font-size: 0.9rem;
    color: #334155;
    margin: 0;
    line-height: 1.4;
}

/* Cards na Direita */
.about-card {
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    text-align: left; /* Garantindo alinhamento à esquerda */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.about-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}
.about-card-img-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    /* overflow não pode ser hidden para o selo não ser cortado */
    overflow: visible;
    background-color: #f1f5f9;
}
.about-card-img-wrapper::after {
    /* Máscara para cortar apenas a imagem, não o selo */
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.about-card-img-clip {
    width: 100%;
    height: 200px;
    object-fit: cover;
    overflow: hidden;
    display: block;
    border-radius: 16px 16px 0 0;
}
.about-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 16px 16px 0 0;
}
.about-card-avatar {
    position: absolute;
    bottom: -14px; /* Metade do tamanho do badge para sobrepor a borda */
    left: 20px;
    width: 28px;
    height: 28px;
    background: #1d4ed8;
    border: 3px solid #ffffff;
    border-radius: 50%;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 10;
}
.about-card-body {
    padding: 32px 24px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.about-card-subtitle {
    font-size: 0.75rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 8px;
}
.about-card-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 16px;
}
.about-card-accent {
    width: 32px;
    height: 3px;
    background: #1d4ed8;
    border-radius: 2px;
    margin-bottom: 20px;
}
.about-card-text {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.7;
    margin-bottom: 16px;
}
.about-icon-circle {
    width: 48px;
    height: 48px;
    background: #eff6ff;
    color: #1d4ed8;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 20px;
}
.about-checklist {
    list-style: none;
    padding: 0;
    margin: 0;
}
.about-checklist li {
    font-size: 0.82rem;
    color: #334155;
    margin-bottom: 10px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.4;
}
.about-checklist li i {
    color: #1d4ed8;
    margin-top: 2px;
    flex-shrink: 0;
}
.about-cards-container {
    /* Sem padding extra para não ter cascata */
}
.about-card-wrapper {
    flex: 1;
    min-width: 250px;
    max-width: 320px;
    width: 100%;
}
@media (min-width: 768px) {
    .stagger-1 { margin-top: 0; }
    .stagger-2 { margin-top: 40px; }
    .stagger-3 { margin-top: 80px; }
}
@media (max-width: 767.98px) {
    .about-cards-container { padding-bottom: 0; }
    .about-card-wrapper { max-width: 100%; }
}

/* Stats Banner */
.about-stats-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
    border-radius: 24px;
    padding: 48px;
    color: #ffffff;
    margin-top: 40px;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
    position: relative;
    overflow: hidden;
}
.about-stats-banner::before {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background-image: url('{{ asset("images/mapa-sergipe-conectado.jpg") }}');
    background-size: cover;
    background-position: center;
    opacity: 0.15;
    mix-blend-mode: overlay;
    pointer-events: none;
}
.stats-banner-title {
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1.3;
    margin-bottom: 12px;
}
.stats-banner-subtitle {
    font-size: 0.95rem;
    color: #94a3b8;
    line-height: 1.6;
    margin-bottom: 0;
}
.stat-item {
    text-align: center;
    padding: 0 16px;
    border-left: 1px solid rgba(255,255,255,0.1);
}
.stat-item:first-child {
    border-left: none;
}
.stat-icon {
    font-size: 2rem;
    color: #3b82f6;
    margin-bottom: 12px;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    margin-bottom: 4px;
}
.stat-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #e2e8f0;
    margin-bottom: 2px;
}
.stat-sublabel {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Features Bar */
.about-features-bar {
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
    padding: 36px 0;
    margin-top: 60px;
}
.feature-box {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    height: 100%;
    transition: all 0.25s ease;
}
.feature-box:hover {
    background: #ffffff;
    border-color: #93c5fd;
    box-shadow: 0 8px 24px rgba(29, 78, 216, 0.08);
    transform: translateY(-2px);
}
.feature-icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #eff6ff;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.feature-text {
    font-size: 0.86rem;
    color: #475569;
    line-height: 1.4;
    margin: 0;
}
.feature-text strong {
    color: #0f172a;
}

@media (max-width: 991.98px) {
    .about-stats-banner { padding: 32px 24px; text-align: center; }
    .stat-item { border-left: none; border-top: 1px solid rgba(255,255,255,0.1); padding: 24px 0 0; margin-top: 24px; }
    .stat-item:first-child { border-top: none; padding-top: 0; }
}

/* Oculta o cabeçalho só na página Sobre */
body.about-page-no-header .site-header {
    display: none !important;
}

/* Botão de voltar */
.about-back-bar {
    background: var(--background, #f8fafc);
    border-bottom: 1px solid var(--border, #e2e8f0);
    padding: 12px 0;
}
.about-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    color: #1d4ed8;
    text-decoration: none;
    padding: 8px 16px;
    border: 1.5px solid #1d4ed8;
    border-radius: 50px;
    transition: all 0.2s ease;
    background: transparent;
}
.about-back-btn:hover {
    background: #1d4ed8;
    color: #ffffff;
}
.about-back-btn i {
    font-size: 0.8rem;
}
</style>
@endpush

@push('scripts')
<script>
    // Adiciona classe especial no body para ocultar o cabeçalho
    document.body.classList.add('about-page-no-header');
</script>
@endpush

@section('content')

{{-- Barra de navegação com botão de voltar --}}
<div class="about-back-bar">
    <div class="container">
        <a href="{{ route('home') }}" class="about-back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Voltar para o início
        </a>
    </div>
</div>

<div class="about-page-wrapper">
    <div class="container"> <!-- Alterado para container para ficar "encolhido" -->
        <div class="row g-4 g-xl-5 align-items-start">
            
            <!-- Lado Esquerdo: Apresentação -->
            <div class="col-12 col-xl-5 text-start">
                <span class="about-badge">Sobre o Conectado em Sergipe</span>
                <h1 class="about-title">Conectado em Sergipe nasceu com um <span class="text-primary">propósito.</span></h1>
                <div class="about-accent-line"></div>
                <p class="about-lead">
                    As pessoas precisam de conexão, oportunidades e confiança. Criamos o Conectado em Sergipe para reunir compradores, vendedores e prestadores de serviços em um só lugar, impulsionando a economia local com segurança, praticidade e tecnologia.
                </p>
                <div class="about-trust-box">
                    <div class="about-trust-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <p class="about-trust-text">
                        Fazemos o melhor para o Conectado ser um <strong class="text-primary">espaço seguro</strong> para todos.
                    </p>
                </div>
            </div>

            <!-- Lado Direito: Cards -->
            <div class="col-12 col-xl-7">
                <div class="d-flex flex-column flex-md-row gap-4 justify-content-xl-end justify-content-center align-items-stretch about-cards-container">
                    
                    <!-- Card 1: Fundador & CEO -->
                    <div class="about-card-wrapper">
                        <div class="about-card">
                            <div class="about-card-img-wrapper">
                                <img src="{{ asset('images/mapa-sergipe-conectado.jpg') }}" alt="Conectado em Sergipe" class="about-card-img">
                                <div class="about-card-avatar">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </div>
                            <div class="about-card-body pt-4">
                                <span class="about-card-subtitle">Fundador & CEO</span>
                                <h3 class="about-card-title">Jairo dos Santos</h3>
                                <div class="about-card-accent"></div>
                                <p class="about-card-text">
                                    Começar rápido é fácil. Impulsionar os negócios de Sergipe é a nossa missão. Criamos o Conectado em Sergipe para conectar a comunidade sergipana, oferecendo tecnologia prática, segura e feita para durar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Nossa Missão -->
                    <div class="about-card-wrapper">
                        <div class="about-card">
                            <div class="about-card-body">
                                <div class="about-icon-circle">
                                    <i class="fa-solid fa-bullseye"></i>
                                </div>
                                <h3 class="about-card-title">Nossa Missão</h3>
                                <div class="about-card-accent"></div>
                                <p class="about-card-text">
                                    Conectar compradores, vendedores e prestadores de serviços nos 75 municípios de Sergipe com facilidade e segurança. Colocamos as pessoas e os negócios locais no centro de tudo — garantindo oportunidades reais para todos crescerem.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: O que fazemos -->
                    <div class="about-card-wrapper">
                        <div class="about-card">
                            <div class="about-card-body">
                                <div class="about-icon-circle">
                                    <i class="fa-solid fa-rocket"></i>
                                </div>
                                <h3 class="about-card-title">O que fazemos</h3>
                                <div class="about-card-accent"></div>
                                <p class="about-card-text">
                                    Oferecemos o maior ecossistema digital do estado para comércio, imóveis, veículos e serviços locais.
                                </p>
                                <ul class="about-checklist">
                                    <li><i class="fa-solid fa-circle-check"></i> Vitrines virtuais para comércio local</li>
                                    <li><i class="fa-solid fa-circle-check"></i> Conexão com prestadores de serviços</li>
                                    <li><i class="fa-solid fa-circle-check"></i> Cobertura nos 75 municípios sergipanos</li>
                                    <li><i class="fa-solid fa-circle-check"></i> Contato rápido via WhatsApp</li>
                                    <li><i class="fa-solid fa-circle-check"></i> Ambiente seguro e focado em resultados</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div> <!-- Fim da row de conteúdo principal -->

        <!-- Banner de Estatísticas -->
        <div class="about-stats-banner">
            <div class="position-relative" style="z-index: 2;">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-lg-4 text-center text-lg-start">
                        <h2 class="stats-banner-title">O maior ecossistema de anúncios e serviços locais de Sergipe.</h2>
                        <p class="stats-banner-subtitle">Tecnologia e comunidade trabalhando juntas para um estado mais conectado.</p>
                    </div>
                    <div class="col-12 col-lg-8">
                        <div class="row g-0 justify-content-center">
                            <div class="col-6 col-md-3 stat-item">
                                <div class="stat-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                                <div class="stat-value">75</div>
                                <div class="stat-label">Municípios</div>
                                <div class="stat-sublabel">Cobertura completa</div>
                            </div>
                            <div class="col-6 col-md-3 stat-item">
                                <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
                                <div class="stat-value">50K+</div>
                                <div class="stat-label">Usuários</div>
                                <div class="stat-sublabel">Conectados todos os dias</div>
                            </div>
                            <div class="col-6 col-md-3 stat-item">
                                <div class="stat-icon"><i class="fa-solid fa-store"></i></div>
                                <div class="stat-value">30+</div>
                                <div class="stat-label">Categorias</div>
                                <div class="stat-sublabel">De anúncios e serviços</div>
                            </div>
                            <div class="col-6 col-md-3 stat-item">
                                <div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                <div class="stat-value">100%</div>
                                <div class="stat-label">Compromisso</div>
                                <div class="stat-sublabel">Com a sua segurança</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- Fim do container -->

    <!-- Barra de Features Inferior -->
    <div class="about-features-bar">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-5 g-3">
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon-badge"><i class="fa-solid fa-heart-circle-check"></i></div>
                        <p class="feature-text">Somos mais que uma plataforma.<br><span class="text-primary fw-bold">Movimento por Sergipe</span></p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon-badge"><i class="fa-solid fa-chart-line"></i></div>
                        <p class="feature-text"><strong>Desenvolvimento</strong><br>da economia local</p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon-badge"><i class="fa-solid fa-seedling"></i></div>
                        <p class="feature-text"><strong>Oportunidades</strong><br>para todos</p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon-badge"><i class="fa-solid fa-microchip"></i></div>
                        <p class="feature-text"><strong>Tecnologia</strong><br>a serviço das pessoas</p>
                    </div>
                </div>
                <div class="col">
                    <div class="feature-box">
                        <div class="feature-icon-badge"><i class="fa-solid fa-handshake-angle"></i></div>
                        <p class="feature-text">Conexão que gera<br><strong>Confiança e resultados</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
