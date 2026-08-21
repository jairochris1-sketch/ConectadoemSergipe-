@php
    $siteName = \App\Models\Setting::get('site_name', 'Conectado em Sergipe');
    $headerUnreadNotifications = auth()->check()
        ? auth()->user()->reportNotifications()->whereNull('read_at')->count()
        : 0;
    $headerCartCount = collect(session('store_cart.items', []))
        ->sum(fn ($item) => is_array($item) ? (int) ($item['quantity'] ?? 0) : (int) $item);
    $mainHeaderItems = [
        ['label' => 'Início', 'route' => 'home', 'icon' => 'fa-house', 'class' => 'home', 'active' => request()->is('/')],
        ['label' => 'Serviços', 'route' => 'module.services', 'icon' => 'fa-screwdriver-wrench', 'class' => 'services', 'active' => request()->is('servicos*') || request()->is('prestadores*')],
        ['label' => 'Lojas & Vendas', 'route' => 'stores-sales.index', 'icon' => 'fa-store', 'class' => 'stores', 'active' => request()->is('lojas-e-vendas*') || request()->is('lojas*') || request()->is('loja*')],
        ['label' => 'Comunidade', 'route' => 'feed.index', 'icon' => 'fa-users', 'class' => 'community', 'active' => request()->is('comunidade*')],
        ['label' => 'Arte & Cultura', 'route' => 'culture.index', 'icon' => 'fa-palette', 'class' => 'culture', 'active' => request()->is('cultura-e-cordel*')],
    ];
    $moreHeaderItems = [];
    $headerItems = array_merge($mainHeaderItems, $moreHeaderItems);
    $isMoreActive = collect($moreHeaderItems)->contains('active', true);
@endphp

<header class="marketplace-header marketplace-header-layout-{{ $headerLayout ?? 'horizontal' }} {{ auth()->guest() ? 'marketplace-header-guest' : '' }}" id="marketplaceHeader">
    @if(($headerLayout ?? 'horizontal') === 'vertical')
    <!-- Botão Flutuante de Encolher/Abrir Cabeçalho Vertical -->
    <button
        class="marketplace-vertical-toggle"
        id="marketplaceVerticalToggle"
        type="button"
        aria-label="Encolher ou Expandir Menu"
        title="Encolher / Expandir Menu"
    >
        <i class="fa-solid fa-chevron-left" id="marketplaceVerticalToggleIcon"></i>
    </button>
    @endif
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

            <nav class="marketplace-desktop-nav notranslate" translate="no" aria-label="Categorias principais">
                @foreach($mainHeaderItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="marketplace-nav-item marketplace-nav-{{ $item['class'] }} {{ $item['active'] ? 'active' : '' }}"
                        @if($item['active']) aria-current="page" @endif
                    >
                        <span class="marketplace-nav-icon"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                        <span class="marketplace-nav-label notranslate" translate="no">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                @if(count($moreHeaderItems) > 0)
                <!-- Menu Dropdown "Mais" -->
                <div class="dropdown d-inline-block">
                    <button
                        class="marketplace-nav-item dropdown-toggle border-0 bg-transparent {{ $isMoreActive ? 'active' : '' }}"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <span class="marketplace-nav-icon"><i class="fa-solid fa-ellipsis"></i></span>
                        <span class="marketplace-nav-label notranslate" translate="no">Mais <i class="fa-solid fa-chevron-down ms-1" style="font-size: 0.65rem;"></i></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 190px; transform: translateX(20%);" data-bs-popper="static">
                        @foreach($moreHeaderItems as $item)
                            <li>
                                <a
                                     href="{{ route($item['route']) }}"
                                     class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2.5 fw-semibold {{ $item['active'] ? 'active bg-primary text-white' : '' }}"
                                     style="font-size: 0.85rem;"
                                >
                                     <i class="fa-solid {{ $item['icon'] }} {{ $item['active'] ? 'text-white' : 'text-primary' }}" style="width: 18px;"></i>
                                     <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif
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
                            <li><a class="dropdown-item" href="{{ route('user.profile') }}"><i class="fa-regular fa-user text-primary"></i>Editar perfil</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.settings') }}"><i class="fa-solid fa-gear text-secondary"></i>Configurações</a></li>
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
                            @if(auth()->user()->hasCulturalArtistProfile())
                                <li><a class="dropdown-item" href="{{ route('culture.my-works') }}"><i class="fa-solid fa-feather-pointed text-warning"></i>Minhas Obras &amp; Rascunhos</a></li>
                            @endif
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
                    <a class="marketplace-account-button marketplace-guest-login" href="{{ route('login') }}" title="Entrar na sua conta">
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
            <nav class="marketplace-mobile-categories notranslate" translate="no" aria-label="Categorias">
                @foreach($headerItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="marketplace-mobile-item marketplace-nav-{{ $item['class'] }} {{ $item['active'] ? 'active' : '' }}"
                        @if($item['active']) aria-current="page" @endif
                    >
                        <span class="marketplace-nav-icon"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                        <span class="notranslate" translate="no">{{ $item['label'] }}</span>
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
<script>
    (() => {
        const toggleBtn = document.getElementById('marketplaceVerticalToggle');
        const icon = document.getElementById('marketplaceVerticalToggleIcon');
        if (!toggleBtn) return;

        const isCollapsed = localStorage.getItem('header_vertical_collapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('header-vertical-collapsed');
            if (icon) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            }
        }

        toggleBtn.addEventListener('click', () => {
            const collapsedNow = document.body.classList.toggle('header-vertical-collapsed');
            localStorage.setItem('header_vertical_collapsed', collapsedNow ? 'true' : 'false');
            if (icon) {
                if (collapsedNow) {
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                } else {
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-left');
                }
            }
        });
    })();
</script>
