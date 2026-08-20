<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Administrativo - Conectado em Sergipe')</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @include('components.theme-head')

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--background);
            color: var(--foreground);
        }

        .admin-sidebar {
            width: 260px;
            background-color: #0f172a;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: margin-left 0.3s ease, transform 0.3s ease;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #334155 #0f172a;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .admin-sidebar::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .admin-sidebar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        .admin-sidebar .nav-link {
            color: #94a3b8;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: 12px;
            margin: 4px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
            color: #ffffff;
            background-color: rgba(99, 102, 241, 0.15);
            font-weight: 700;
        }

        .admin-sidebar .nav-link.active {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }

        .admin-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
            transition: margin-left 0.3s ease, padding 0.3s ease;
        }

        .admin-menu-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            transition: all 0.2s ease;
        }

        .admin-sidebar-overlay,
        .admin-sidebar-close {
            display: none;
        }

        /* Suporte para recolher sidebar no Desktop/Notebook */
        @media (min-width: 992px) {
            body.admin-sidebar-collapsed .admin-sidebar {
                margin-left: -260px;
            }
            body.admin-sidebar-collapsed .admin-content {
                margin-left: 0;
            }
        }

        /* Ajustes otimizados para notebooks (1366x768 e similares até 1399px) */
        @media (max-width: 1399.98px) {
            .admin-content {
                padding: 20px 20px 60px;
            }
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                margin-left: -260px;
                box-shadow: 12px 0 35px rgba(15, 23, 42, 0.2);
            }
            .admin-sidebar.show { margin-left: 0; }
            .admin-content {
                margin-left: 0;
                padding: 16px 16px 96px;
            }
            .admin-sidebar-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                color: #cbd5e1;
                background: transparent;
                border: 1px solid rgba(148, 163, 184, 0.28);
                border-radius: 12px;
            }
            .admin-sidebar-close:hover,
            .admin-sidebar-close:focus-visible {
                color: #ffffff;
                background: rgba(148, 163, 184, 0.12);
                border-color: rgba(148, 163, 184, 0.5);
            }
            .admin-sidebar .nav-link {
                min-height: 48px;
            }
            .admin-sidebar-overlay {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 999;
                background: rgba(15, 23, 42, 0.46);
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.2s ease, visibility 0.2s ease;
            }
            .admin-sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }
            body.admin-menu-open { overflow: hidden; }
            .theme-toggle-container {
                right: 14px;
                bottom: 14px;
            }
            .theme-toggle-btn,
            .back-to-top-btn {
                width: 44px;
                height: 44px;
            }
            .back-to-top-btn {
                right: 14px;
                bottom: 70px;
            }
            .admin-page-heading {
                flex-direction: column;
                align-items: stretch !important;
                gap: 14px;
            }
            .admin-page-heading > button,
            .admin-page-heading > a { width: 100%; text-align: center; }
        }

        @media (max-width: 575px) {
            .admin-content { padding: 10px 10px 96px; }
            .admin-header-environment { display: none; }
            .admin-current-user {
                max-width: 190px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }

        /* Estilização suave para scrollbars de tabelas em todas as telas */
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/theme-toggle.css') }}?v=2.2">
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside class="admin-sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary border-opacity-25 d-flex align-items-center gap-2">
            <div class="rounded-3 bg-primary bg-gradient p-2 text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="fa-solid fa-shield-halved fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold text-white mb-0">Painel Admin</h6>
                <small class="text-white-50" style="font-size: 0.75rem;">Conectado em Sergipe</small>
            </div>
            <button type="button" class="admin-sidebar-close ms-auto" aria-label="Fechar menu administrativo">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="nav flex-column py-3 flex-grow-1">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line text-info"></i> Dashboard
            </a>
            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->is('admin/usuarios*') ? 'active' : '' }}">
                <i class="fa-solid fa-users text-primary"></i> Usuários
            </a>
            <a href="{{ route('admin.ads') }}" class="nav-link {{ request()->is('admin/anuncios*') ? 'active' : '' }}">
                <i class="fa-solid fa-rectangle-ad text-warning"></i> Anúncios
            </a>
            <a href="{{ route('admin.provider_claims.index') }}" class="nav-link {{ request()->is('admin/reivindicacoes*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-check text-success"></i> Reivindicações
            </a>
            <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->is('admin/denuncias*') ? 'active' : '' }}">
                <i class="fa-solid fa-flag text-danger"></i> Denúncias
            </a>
            <a href="{{ route('admin.reviews') }}" class="nav-link {{ request()->is('admin/avaliacoes*') ? 'active' : '' }}">
                <i class="fa-solid fa-star text-warning"></i> Avaliações
            </a>
            <a href="{{ route('admin.feed.index') }}" class="nav-link {{ request()->is('admin/comunidade*') ? 'active' : '' }}">
                <i class="fa-solid fa-users text-info"></i> Comunidade
            </a>
            <a href="{{ route('admin.community-help.index') }}" class="nav-link {{ request()->is('admin/ajuda-comunitaria*') ? 'active' : '' }}">
                <i class="fa-solid fa-hand-holding-heart text-success"></i> Ajuda Comunitária
            </a>
            <a href="{{ route('admin.culture.index') }}" class="nav-link {{ request()->is('admin/cultura*') ? 'active' : '' }}">
                <i class="fa-solid fa-masks-theater text-warning"></i> Cultura
            </a>
            <a href="{{ route('admin.support.index') }}" class="nav-link {{ request()->is('admin/suporte*') ? 'active' : '' }}" style="position:relative;">
                <i class="fa-solid fa-headset text-success"></i> Suporte ao Vivo
            </a>
            <a href="{{ route('admin.categories') }}" class="nav-link {{ request()->is('admin/categorias*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check text-success"></i> Categorias
            </a>
            <a href="{{ route('admin.plans.index') }}" class="nav-link {{ request()->is('admin/planos*') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group text-info"></i> Planos
            </a>
            <a href="{{ route('admin.stores') }}" class="nav-link {{ request()->is('admin/lojas*') ? 'active' : '' }}">
                <i class="fa-solid fa-store text-danger"></i> Lojas & Empresas
            </a>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->is('admin/pedidos*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt text-primary"></i> Pedidos
            </a>
            <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->is('admin/configuracoes*') ? 'active' : '' }}">
                <i class="fa-solid fa-gears text-secondary"></i> Configurações
            </a>
            <a href="{{ route('admin.system.update') }}" class="nav-link {{ request()->is('admin/atualizacao*') ? 'active' : '' }}">
                <i class="fa-solid fa-cloud-arrow-down text-warning"></i> Atualização
            </a>
        </nav>

        <div class="p-3 border-top border-secondary border-opacity-25">
            <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-light btn-sm w-100 rounded-3 mb-2">
                <i class="fa-solid fa-globe me-1"></i> Ver Marketplace
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100 rounded-3">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Sair do Admin
                </button>
            </form>
        </div>
    </aside>
    <button type="button" class="admin-sidebar-overlay border-0 p-0" aria-label="Fechar menu administrativo"></button>

    <!-- Main Content -->
    <main class="admin-content">
        <!-- Header -->
        <header class="admin-topbar rounded-4 shadow-sm p-3 mb-4" style="background: var(--bs-body-bg, #fff); border: 1px solid var(--bs-border-color, rgba(0,0,0,.09));">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="admin-menu-toggle btn btn-outline-primary rounded-3" aria-label="Abrir menu administrativo" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <span class="admin-header-environment badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">Ambiente de Controle</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="admin-current-user fw-bold text-body me-2"><i class="fa-solid fa-user-gear text-primary me-1"></i> {{ auth()->user()->name ?? 'Administrador' }}</span>
                </div>
            </div>

        </header>

        @if(session('error'))
            <div class="alert alert-danger rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Não foi possível salvar:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @include('components.theme-toggle')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.querySelector('.admin-sidebar-overlay');
            const toggle = document.querySelector('.admin-menu-toggle');
            const closeButton = document.querySelector('.admin-sidebar-close');
            const mobileSidebar = window.matchMedia('(max-width: 991px)');

            if (!sidebar || !overlay || !toggle) return;

            // Carregar estado salvo do desktop no localStorage
            const isDesktopCollapsed = localStorage.getItem('admin_sidebar_collapsed') === 'true';
            if (!mobileSidebar.matches && isDesktopCollapsed) {
                document.body.classList.add('admin-sidebar-collapsed');
            }

            const setMobileOpen = (open) => {
                sidebar.classList.toggle('show', open);
                overlay.classList.toggle('show', open);
                document.body.classList.toggle('admin-menu-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                sidebar.toggleAttribute('inert', !open);
                sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
            };

            const toggleDesktop = () => {
                const isCollapsed = document.body.classList.toggle('admin-sidebar-collapsed');
                localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
                toggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            };

            toggle.addEventListener('click', () => {
                if (mobileSidebar.matches) {
                    setMobileOpen(!sidebar.classList.contains('show'));
                } else {
                    toggleDesktop();
                }
            });

            closeButton?.addEventListener('click', () => {
                if (mobileSidebar.matches) {
                    setMobileOpen(false);
                    toggle.focus();
                } else {
                    toggleDesktop();
                }
            });

            overlay.addEventListener('click', () => {
                if (mobileSidebar.matches) setMobileOpen(false);
            });

            sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
                if (mobileSidebar.matches) setMobileOpen(false);
            }));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && sidebar.classList.contains('show') && mobileSidebar.matches) {
                    setMobileOpen(false);
                    toggle.focus();
                }
            });

            mobileSidebar.addEventListener('change', (e) => {
                if (e.matches) {
                    document.body.classList.remove('admin-sidebar-collapsed');
                    setMobileOpen(false);
                } else {
                    sidebar.removeAttribute('inert');
                    sidebar.removeAttribute('aria-hidden');
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                    document.body.classList.remove('admin-menu-open');
                    if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                        document.body.classList.add('admin-sidebar-collapsed');
                    }
                }
            });
        })();
    </script>
    <script src="{{ asset('js/main.js') }}?v=1.3"></script>
    @stack('scripts')
</body>
</html>
