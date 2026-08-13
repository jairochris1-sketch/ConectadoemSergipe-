@php
    $showPublicHeader = !request()->is('login')
        && !request()->is('cadastro')
        && !request()->is('esqueci-senha*')
        && !request()->is('admin*');
    $userHeaderLayout = auth()->user()?->header_layout ?? 'horizontal';
    $userHeaderLayout = in_array($userHeaderLayout, ['horizontal', 'vertical'], true)
        ? $userHeaderLayout
        : 'horizontal';
@endphp
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\Setting::get('site_name', 'Conectado em Sergipe') . ' - O Maior Marketplace do Estado')</title>
    @stack('meta')
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5 CSS & Swiper -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    

    @include('components.theme-head')

    <!-- Estilos Personalizados -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=9.1">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v=3.6">
    <link rel="stylesheet" href="{{ asset('css/theme-toggle.css') }}?v=3.6">
    <link rel="stylesheet" href="{{ asset('css/cookie-consent.css') }}?v=1.0">

    <!-- Proteção contra FOUC e inicialização lenta do Swiper -->
    <style>
        .swiper:not(.swiper-initialized) {
            overflow: hidden !important;
        }
        .swiper:not(.swiper-initialized) > .swiper-wrapper {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow: hidden !important;
        }
        .swiper:not(.swiper-initialized) > .swiper-wrapper > .swiper-slide:not(:first-child) {
            display: none !important;
        }
    </style>
    @stack('styles')
</head>
<body class="{{ $showPublicHeader ? 'site-header-layout-'.$userHeaderLayout : '' }} @yield('body-class')">
    <!-- Splash Screen (Exibida apenas na Página Inicial) -->
    @if(request()->routeIs('home'))
        @include('components.splash-screen')
    @endif

    @if($showPublicHeader)
    <!-- Navbar Pública -->
    @include('components.site-header', ['headerLayout' => $userHeaderLayout])
    {{--
    <nav class="navbar navbar-expand-xl navbar-custom site-header sticky-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 py-1" href="{{ route('home') }}" aria-label="Conectado em Sergipe">
                <img src="{{ asset('images/logo-hero.png') }}?v=4.3" alt="Conectado em Sergipe" class="navbar-brand-logo">
                <span class="navbar-mobile-brand-title">{{ \App\Models\Setting::get('site_name', 'Conectado em Sergipe') }}</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Menu Categorias no Header (Desktop) -->
                <div class="d-none d-xl-flex align-items-center gap-2 mx-auto header-category-menu">
                    <a href="{{ route('home') }}" class="{{ request()->is('/') ? 'active' : '' }}"><i class="fa-solid fa-house"></i> Início</a>
                    <a href="{{ route('module.real_estate') }}" class="{{ request()->is('imoveis*') ? 'active' : '' }}"><i class="fa-solid fa-building"></i> Imóveis</a>
                    <a href="{{ route('module.vehicles') }}" class="{{ request()->is('veiculos*') ? 'active' : '' }}"><i class="fa-solid fa-car"></i> Veículos</a>
                    <a href="{{ route('module.products') }}" class="{{ request()->is('produtos*') ? 'active' : '' }}"><i class="fa-solid fa-mobile-screen"></i> Produtos</a>
                    <a href="{{ route('module.services') }}" class="{{ request()->is('servicos*') ? 'active' : '' }}"><i class="fa-solid fa-screwdriver-wrench"></i> Serviços</a>
                    <a href="{{ route('module.jobs') }}" class="{{ request()->is('empregos*') ? 'active' : '' }}"><i class="fa-solid fa-briefcase"></i> Empregos</a>
                    <a href="{{ route('module.agro') }}" class="{{ request()->is('agro*') ? 'active' : '' }}"><i class="fa-solid fa-tractor"></i> Agro</a>
                </div>

                <!-- Menu Categorias no Header (Celular e Tablet) -->
                <div class="d-grid d-xl-none mobile-category-menu">
                    <a href="{{ route('module.real_estate') }}" class="{{ request()->is('imoveis*') ? 'active' : '' }}"><i class="fa-solid fa-building text-info"></i> Imóveis</a>
                    <a href="{{ route('module.vehicles') }}" class="{{ request()->is('veiculos*') ? 'active' : '' }}"><i class="fa-solid fa-car text-danger"></i> Veículos</a>
                    <a href="{{ route('module.products') }}" class="{{ request()->is('produtos*') ? 'active' : '' }}"><i class="fa-solid fa-mobile-screen text-purple"></i> Produtos</a>
                    <a href="{{ route('module.services') }}" class="{{ request()->is('servicos*') ? 'active' : '' }}"><i class="fa-solid fa-screwdriver-wrench text-success"></i> Serviços</a>
                    <a href="{{ route('module.jobs') }}" class="{{ request()->is('empregos*') ? 'active' : '' }}"><i class="fa-solid fa-briefcase text-warning"></i> Empregos</a>
                    <a href="{{ route('module.agro') }}" class="{{ request()->is('agro*') ? 'active' : '' }}"><i class="fa-solid fa-tractor text-success"></i> Agro</a>
                </div>
                
                <!-- Menu Direito -->
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 rounded-pill text-decoration-none px-3 py-1 border" style="border-color: #8b5cf6 !important; color: #6d28d9; background-color: rgba(139, 92, 246, 0.05); font-weight: 600; font-size: 0.82rem;" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; color: #6d28d9;">
                                    <i class="fa-solid fa-user" style="font-size: 0.75rem;"></i>
                                </div>
                                <span class="navbar-user-name">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2 p-2" style="font-size: 0.9rem; min-width: 180px;">
                                <li><a class="dropdown-item py-2 rounded-2 fw-semibold" href="{{ route('user.panel') }}"><i class="fa-solid fa-gauge-high text-primary me-2"></i> Meu Painel</a></li>
                                <li><a class="dropdown-item py-2 rounded-2 fw-semibold" href="{{ route('user.profile') }}"><i class="fa-solid fa-user-gear text-info me-2"></i> Meu Perfil</a></li>
                                <li><a class="dropdown-item py-2 rounded-2 fw-semibold" href="{{ route('page.plans') }}"><i class="fa-solid fa-gem text-warning me-2"></i> Planos</a></li>
                                <li><a class="dropdown-item py-2 rounded-2 fw-semibold" href="{{ route('chat.index') }}"><i class="fa-solid fa-comments text-success me-2"></i> Mensagens</a></li>
                                @if(auth()->user()->role === 'admin')
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li><a class="dropdown-item py-2 rounded-2 text-danger fw-bold" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-shield-halved me-2"></i> Painel Admin</a></li>
                                @endif
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 rounded-2 text-muted fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i> Sair</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link text-muted fw-semibold" style="font-size: 0.85rem;" href="{{ route('page.plans') }}"><i class="fa-solid fa-gem text-warning me-1"></i> Planos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" style="font-size: 0.85rem;" href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket text-primary me-1"></i> Entrar</a>
                        </li>
                    @endauth
                    
                    <li class="nav-item ms-lg-2">
                        <a class="btn header-announce-btn rounded-pill px-3 py-1.5 font-weight-bold shadow-sm d-flex align-items-center gap-1" style="font-size: 0.85rem;" href="{{ route('ad.create') }}">
                            <i class="fa-solid fa-plus-circle"></i>
                            <span>Anunciar</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    --}}
    @endif

    <!-- Conteúdo Principal -->
    <main>
        @yield('content')
    </main>

    @if($showPublicHeader)
    <!-- Footer -->
    <footer class="site-footer py-5 mt-5">
        <div class="container">
            <div class="row g-4 mb-4 text-center text-md-start">
                <div class="col-12 col-md-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-layer-group text-primary me-2"></i> Conectado em Sergipe</h5>
                    <p class="text-secondary small mb-3">O maior ecossistema digital de anúncios, produtos, veículos, imóveis e serviços cobrindo todos os 75 municípios de Sergipe.</p>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <h6 class="fw-bold mb-3">Institucional</h6>
                    <ul class="list-unstyled text-secondary small space-y-2 mb-0">
                        <li class="mb-2"><a href="{{ route('page.about') }}" class="text-secondary text-decoration-none"><i class="fa-solid fa-angle-right me-1"></i> Sobre Nós</a></li>
                        <li class="mb-2"><a href="{{ route('page.plans') }}" class="text-secondary text-decoration-none"><i class="fa-solid fa-angle-right me-1"></i> Planos e Preços</a></li>
                        <li class="mb-2"><a href="{{ route('page.contact') }}" class="text-secondary text-decoration-none"><i class="fa-solid fa-angle-right me-1"></i> Fale Conosco</a></li>
                    </ul>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <h6 class="fw-bold mb-3">Termos & Privacidade</h6>
                    <ul class="list-unstyled text-secondary small space-y-2 mb-0">
                        <li class="mb-2"><a href="{{ route('page.privacy') }}" class="text-secondary text-decoration-none"><i class="fa-solid fa-angle-right me-1"></i> Política de Privacidade</a></li>
                        <li class="mb-2"><a href="{{ route('page.terms') }}" class="text-secondary text-decoration-none"><i class="fa-solid fa-angle-right me-1"></i> Termos de Uso</a></li>
                        <li class="mb-2"><button type="button" class="site-footer-cookie-button" data-cookie-settings><i class="fa-solid fa-cookie-bite me-1" aria-hidden="true"></i> Redefinir Cookies</button></li>
                    </ul>
                </div>
                <div class="col-12 col-md-3">
                    <h6 class="fw-bold mb-3">Siga-nos nas Redes</h6>
                    <p class="text-secondary small mb-3">Acompanhe novidades, ofertas e destaques das cidades de Sergipe:</p>
                    <div class="d-flex justify-content-center justify-content-md-start gap-2">
                        <a href="{{ \App\Models\Setting::get('instagram_url', 'https://instagram.com') }}" target="_blank" rel="noopener" class="site-footer-social d-flex align-items-center justify-content-center" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://facebook.com" target="_blank" rel="noopener" class="site-footer-social d-flex align-items-center justify-content-center" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', \App\Models\Setting::get('whatsapp_number', '5579999999999')) }}" target="_blank" rel="noopener" class="site-footer-social d-flex align-items-center justify-content-center" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" class="site-footer-social d-flex align-items-center justify-content-center" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary opacity-25">
            <div class="text-center text-secondary small">
                <p class="mb-0">&copy; {{ date('Y') }} Conectado em Sergipe. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    @endif

    @stack('report-modals')
    @include('components.share-modal')

    @include('components.theme-toggle')
    @include('components.vlibras-widget')
    @include('components.cookie-consent')

    <!-- Scripts JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}?v=1.4"></script>
    <script src="{{ asset('js/site-header.js') }}?v=1.3"></script>
    <script src="{{ asset('js/cookie-consent.js') }}?v=1.0"></script>
    @stack('scripts')
</body>
</html>
