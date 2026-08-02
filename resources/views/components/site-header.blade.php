@php
    $siteName = \App\Models\Setting::get('site_name', 'Conectado em Sergipe');
    $headerUnreadNotifications = auth()->check()
        ? auth()->user()->reportNotifications()->whereNull('read_at')->count()
        : 0;
    $headerCartCount = collect(session('store_cart.items', []))
        ->sum(fn ($item) => is_array($item) ? (int) ($item['quantity'] ?? 0) : (int) $item);
    $headerItems = [
        ['label' => 'Início', 'route' => 'home', 'icon' => 'fa-house', 'class' => 'home', 'active' => request()->is('/')],
        ['label' => 'Imóveis', 'route' => 'module.real_estate', 'icon' => 'fa-building', 'class' => 'real-estate', 'active' => request()->is('imoveis*')],
        ['label' => 'Veículos', 'route' => 'module.vehicles', 'icon' => 'fa-car', 'class' => 'vehicles', 'active' => request()->is('veiculos*')],
        ['label' => 'Produtos', 'route' => 'module.products', 'icon' => 'fa-bag-shopping', 'class' => 'products', 'active' => request()->is('produtos*')],
        ['label' => 'Serviços', 'route' => 'module.services', 'icon' => 'fa-screwdriver-wrench', 'class' => 'services', 'active' => request()->is('servicos*') || request()->is('prestadores*')],
        ['label' => 'Empregos', 'route' => 'module.jobs', 'icon' => 'fa-briefcase', 'class' => 'jobs', 'active' => request()->is('empregos*')],
        ['label' => 'Agro', 'route' => 'module.agro', 'icon' => 'fa-tractor', 'class' => 'agro', 'active' => request()->is('agro*')],
    ];
@endphp

<header class="marketplace-header marketplace-header-layout-{{ $headerLayout ?? 'horizontal' }} {{ auth()->guest() ? 'marketplace-header-guest' : '' }}" id="marketplaceHeader">
    <div class="marketplace-header-shell">
        <div class="marketplace-header-row">
            <button
                class="marketplace-mobile-toggle"
                id="marketplaceMobileToggle"
                type="button"
                aria-label="Abrir menu"
                aria-controls="marketplaceMobileMenu"
                aria-expanded="false"
            >
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <a class="marketplace-brand" href="{{ route('home') }}" aria-label="{{ $siteName }}">
                <img src="{{ asset('images/logo-hero.png') }}?v=4.3" alt="" class="marketplace-brand-logo">
                <span class="marketplace-brand-name">
                    <span>Conectado</span>
                    <span>em Sergipe</span>
                </span>
                <span class="navbar-mobile-brand-title" hidden aria-hidden="true">{{ $siteName }}</span>
            </a>

            <span class="marketplace-brand-divider" aria-hidden="true"></span>

            <nav class="marketplace-desktop-nav" aria-label="Categorias principais">
                @foreach($headerItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="marketplace-nav-item marketplace-nav-{{ $item['class'] }} {{ $item['active'] ? 'active' : '' }}"
                        @if($item['active']) aria-current="page" @endif
                    >
                        <span class="marketplace-nav-icon"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                        <span class="marketplace-nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="marketplace-header-actions">
                @if(session('location_filter.enabled', false))
                    <button
                        class="marketplace-location-active"
                        type="button"
                        data-global-location-disable
                        data-endpoint="{{ route('location.destroy') }}"
                        title="Localização ativa em {{ session('location_filter.city') }}. Clique para desativar."
                        aria-label="Desativar localização em {{ session('location_filter.city') }}"
                    >
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span>{{ session('location_filter.city') }}</span>
                        <i class="fa-solid fa-xmark marketplace-location-close" aria-hidden="true"></i>
                    </button>
                @endif
                <a class="marketplace-search-button marketplace-top-search" href="{{ route('home') }}#busca-rapida" aria-label="Buscar anúncios e serviços" title="Buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </a>
                <a class="marketplace-search-button position-relative" href="{{ route('cart.index') }}" aria-label="Carrinho com {{ $headerCartCount }} itens" title="Carrinho">
                    <i class="fa-solid fa-cart-shopping"></i>
                    @if($headerCartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $headerCartCount > 99 ? '99+' : $headerCartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    <div class="dropdown marketplace-account-dropdown">
                        <button class="marketplace-account-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth()->user()->avatar)
                                <img
                                    src="{{ asset(auth()->user()->avatar) }}"
                                    alt=""
                                    class="marketplace-account-avatar"
                                    onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                                >
                                <i class="fa-regular fa-user marketplace-account-fallback" hidden></i>
                            @else
                                <i class="fa-regular fa-user marketplace-account-fallback"></i>
                            @endif
                            <span>{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end marketplace-account-menu">
                            <li><a class="dropdown-item" href="{{ route('user.panel') }}"><i class="fa-solid fa-gauge-high text-primary"></i>Meu Painel</a></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('user.panel') }}#notificacoes">
                                    <i class="fa-solid fa-bell text-warning"></i>
                                    <span>Notificações</span>
                                    @if($headerUnreadNotifications > 0)
                                        <span class="badge bg-danger rounded-pill ms-auto marketplace-notification-badge">
                                            {{ $headerUnreadNotifications > 99 ? '99+' : $headerUnreadNotifications }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('page.plans') }}"><i class="fa-solid fa-gem text-warning"></i>Planos</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="fa-solid fa-box text-success"></i>Meus pedidos</a></li>
                            <li><a class="dropdown-item" href="{{ route('chat.index') }}"><i class="fa-solid fa-comments text-success"></i>Mensagens</a></li>
                            @if(auth()->user()->role === 'admin')
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-shield-halved"></i>Painel Admin</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i>Sair</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="marketplace-account-button marketplace-guest-login" href="{{ route('login') }}">
                        <i class="fa-regular fa-user"></i>
                        <span>Entrar</span>
                    </a>
                @endauth

                <a class="marketplace-announce-button marketplace-top-announce" href="{{ route('ad.create') }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Anunciar</span>
                </a>
            </div>
        </div>

        <div class="marketplace-mobile-menu" id="marketplaceMobileMenu" aria-hidden="true">
            <nav class="marketplace-mobile-categories" aria-label="Categorias">
                @foreach($headerItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="marketplace-mobile-item marketplace-nav-{{ $item['class'] }} {{ $item['active'] ? 'active' : '' }}"
                        @if($item['active']) aria-current="page" @endif
                    >
                        <span class="marketplace-nav-icon"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="marketplace-mobile-actions">
                @if(session('location_filter.enabled', false))
                    <button
                        class="marketplace-mobile-account-link marketplace-mobile-location"
                        type="button"
                        data-global-location-disable
                        data-endpoint="{{ route('location.destroy') }}"
                    >
                        <i class="fa-solid fa-location-dot"></i>
                        <span>{{ session('location_filter.city') }} · desativar</span>
                    </button>
                @endif
                <a class="marketplace-mobile-account-link" href="{{ route('cart.index') }}">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Carrinho{{ $headerCartCount ? ' (' . $headerCartCount . ')' : '' }}</span>
                </a>
                @auth
                    <a class="marketplace-mobile-account-link" href="{{ route('user.panel') }}">
                        <i class="fa-regular fa-user"></i>
                        <span>Meu painel</span>
                    </a>
                @endauth
                <a class="marketplace-mobile-announce" href="{{ route('ad.create') }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Anunciar</span>
                </a>
            </div>

            <div class="marketplace-mobile-benefits" aria-label="Vantagens do site">
                <div><i class="fa-solid fa-location-dot"></i><span>Conteúdo local<br>de Sergipe</span></div>
                <div><i class="fa-regular fa-comment-dots"></i><span>Contato direto<br>com o anunciante</span></div>
                <div><i class="fa-regular fa-star"></i><span>Navegação<br>fácil e rápida</span></div>
                <div><i class="fa-regular fa-heart"></i><span>Apoie negócios<br>da sua região</span></div>
            </div>
        </div>
    </div>
</header>
