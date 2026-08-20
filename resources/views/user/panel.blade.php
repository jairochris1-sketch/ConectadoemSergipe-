@extends('layouts.app')

@section('title', 'Painel do Usuário - Conectado em Sergipe')

@section('content')
<div class="container py-4 py-md-5 user-panel-page">
    @if(session('success'))
        <div class="alert alert-success rounded-4 mb-4 shadow-sm border-0 d-flex align-items-center">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-4 shadow-sm border-0">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- ============================================================
             SIDEBAR DO PERFIL (Esquerda)
        ============================================================ --}}
        <div class="col-12 col-lg-3 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center user-sidebar-card">
                <div class="card-body p-0">
                    <form action="{{ route('user.avatar.update') }}" method="POST" enctype="multipart/form-data" id="user-panel-avatar-form">
                        @csrf
                        <div class="user-panel-avatar-wrapper position-relative mx-auto mb-3">
                            <div class="user-panel-avatar rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-1">
                                @if($user->avatar)
                                    <img src="{{ asset($user->avatar) }}" alt="Foto de {{ $user->name }}">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </div>
                            <label for="user-avatar-file-input" class="user-avatar-edit-btn" title="Alterar foto de perfil">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input type="file" id="user-avatar-file-input" name="avatar" class="visually-hidden" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" onchange="document.getElementById('user-panel-avatar-form').submit()">
                        </div>
                    </form>

                    <h5 class="fw-bold mb-1 user-sidebar-name">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">
                        <i class="fa-solid fa-location-dot me-1 text-muted"></i>
                        {{ $user->city ?? 'Nossa Senhora da Glória' }}, SE
                    </p>
                    
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-semibold mb-4" style="font-size: .75rem;">
                        Conta ativa
                    </span>

                    <div class="d-flex flex-column gap-2 mb-4">
                        <a href="{{ route('user.profile') }}" class="btn btn-outline-primary rounded-pill py-2 px-3 fw-semibold small d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-regular fa-user"></i> Editar perfil
                        </a>
                        <a href="{{ route('user.settings') }}" class="btn btn-outline-secondary rounded-pill py-2 px-3 fw-semibold small d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-gear"></i> Configurações
                        </a>
                    </div>

                    <div class="user-sidebar-shortcuts text-start border-top pt-3">
                        <span class="text-muted small fw-bold text-uppercase d-block mb-2" style="font-size: .72rem; letter-spacing: .5px;">Atalhos</span>
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ route('chat.index') }}" class="user-sidebar-shortcut-link d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none">
                                <span class="d-flex align-items-center gap-2 text-secondary">
                                    <i class="fa-regular fa-comment-dots text-primary fs-6"></i> Mensagens
                                </span>
                                <div class="d-flex align-items-center gap-1">
                                    @if($unreadMessagesCount > 0)
                                        <span class="badge bg-primary rounded-pill px-2 py-0.5" style="font-size: .65rem;">{{ $unreadMessagesCount }}</span>
                                    @endif
                                    <i class="fa-solid fa-chevron-right text-muted small" style="font-size: .68rem;"></i>
                                </div>
                            </a>

                            <a href="#notificacoes" class="user-sidebar-shortcut-link d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none">
                                <span class="d-flex align-items-center gap-2 text-secondary">
                                    <i class="fa-regular fa-bell text-warning fs-6"></i> Notificações
                                </span>
                                <div class="d-flex align-items-center gap-1">
                                    @if($unreadNotificationsCount > 0)
                                        <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: .65rem;">{{ $unreadNotificationsCount }}</span>
                                    @endif
                                    <i class="fa-solid fa-chevron-right text-muted small" style="font-size: .68rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             CONTEÚDO PRINCIPAL (Direita)
        ============================================================ --}}
        <div class="col-12 col-lg-9 col-md-8">
            {{-- Título da Página --}}
            <div class="mb-4">
                <h3 class="fw-bold mb-1 user-page-title">Painel do usuário</h3>
                <p class="text-muted small mb-0">Acompanhe suas notificações, anúncios e lojas em um só lugar.</p>
            </div>

            {{-- 1. Métricas do Topo (4 cards analíticos) --}}
            <div class="row g-3 mb-4 user-stats-row">
                {{-- Card 1: Visualizações Totais --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 user-stat-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="user-stat-icon-wrap bg-primary bg-opacity-10 text-primary">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: .75rem;">Visualizações totais</small>
                                <span class="fs-4 fw-bold user-stat-num text-primary">{{ number_format($totalViews, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <span class="small text-muted d-inline-flex align-items-center gap-1 mt-auto pt-1" style="font-size: .72rem;">
                            <i class="fa-solid fa-chart-line text-success"></i> Alcance de público
                        </span>
                    </div>
                </div>

                {{-- Card 2: Meus anúncios --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 user-stat-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="user-stat-icon-wrap bg-info bg-opacity-10 text-info">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: .75rem;">Meus anúncios</small>
                                <span class="fs-4 fw-bold user-stat-num">{{ $ads->count() }}</span>
                            </div>
                        </div>
                        <a href="#meus-anuncios" class="small fw-semibold text-primary text-decoration-none mt-auto pt-1 d-inline-flex align-items-center gap-1" style="font-size: .75rem;">
                            {{ $activeAdsCount }} ativos <i class="fa-solid fa-angle-right" style="font-size: .65rem;"></i>
                        </a>
                    </div>
                </div>

                {{-- Card 3: Minhas lojas --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 user-stat-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="user-stat-icon-wrap bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: .75rem;">Minhas lojas</small>
                                <span class="fs-4 fw-bold user-stat-num">{{ $stores->count() }}</span>
                            </div>
                        </div>
                        <a href="#minhas-lojas" class="small fw-semibold text-primary text-decoration-none mt-auto pt-1 d-inline-flex align-items-center gap-1" style="font-size: .75rem;">
                            Gerenciar <i class="fa-solid fa-angle-right" style="font-size: .65rem;"></i>
                        </a>
                    </div>
                </div>

                {{-- Card 4: Notificações --}}
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 h-100 user-stat-card">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="user-stat-icon-wrap bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block" style="font-size: .75rem;">Notificações</small>
                                <span class="fs-4 fw-bold user-stat-num">{{ $unreadNotificationsCount > 0 ? $unreadNotificationsCount : $reportNotifications->count() }}</span>
                            </div>
                        </div>
                        <a href="#notificacoes" class="small fw-semibold text-primary text-decoration-none mt-auto pt-1 d-inline-flex align-items-center gap-1" style="font-size: .75rem;">
                            Ver todas <i class="fa-solid fa-angle-right" style="font-size: .65rem;"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 2. Ações Rápidas --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-3 user-section-title">Ações rápidas</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <a href="{{ route('ad.create') }}" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none user-quick-action-card h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-quick-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fw-bold user-quick-title" style="font-size: .88rem;">Criar anúncio</strong>
                                    <small class="text-muted" style="font-size: .75rem;">Publique um novo anúncio</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <a href="{{ route('store.create') }}" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none user-quick-action-card h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-quick-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-store"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fw-bold user-quick-title" style="font-size: .88rem;">Criar minha loja</strong>
                                    <small class="text-muted" style="font-size: .75rem;">Tenha sua vitrine comercial</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-4">
                        <a href="{{ route('chat.index') }}" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none user-quick-action-card h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="user-quick-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-comments"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fw-bold user-quick-title" style="font-size: .88rem;">Ver mensagens</strong>
                                    <small class="text-muted" style="font-size: .75rem;">Acesse suas conversas</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. Notificações Recentes --}}
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 user-notifications-card" id="notificacoes">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h6 class="fw-bold mb-0 user-section-title">Notificações recentes</h6>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#notificacoes" class="small fw-semibold text-primary text-decoration-none me-2">Ver todas</a>
                        <form action="{{ route('user.notifications.preference') }}" method="POST">
                            @csrf
                            <input type="hidden" name="notifications_enabled" value="{{ $user->notifications_enabled ? 0 : 1 }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" style="font-size: .75rem;">
                                <i class="fa-solid {{ $user->notifications_enabled ? 'fa-bell-slash' : 'fa-bell' }} me-1"></i>
                                {{ $user->notifications_enabled ? 'Desativar notificações' : 'Ativar notificações' }}
                            </button>
                        </form>
                    </div>
                </div>

                @if(session('notification_preference_success'))
                    <div class="alert alert-success py-2 px-3 small rounded-3 mb-2">
                        {{ session('notification_preference_success') }}
                    </div>
                @endif

                @unless($user->notifications_enabled)
                    <div class="alert alert-secondary py-2 px-3 small rounded-3 mb-2">
                        As novas notificações estão desativadas. As mensagens e avaliações continuam funcionando normalmente.
                    </div>
                @endunless

                <div class="user-notification-list">
                    @forelse($reportNotifications->take(4) as $notification)
                        <a href="{{ route('user.notifications.open', $notification) }}" class="user-notification-row d-flex align-items-center justify-content-between py-2.5 px-2 border-top text-decoration-none">
                            <div class="d-flex align-items-center gap-2.5 min-width-0">
                                <span class="user-notification-dot {{ $notification->read_at ? 'is-read' : 'is-unread' }}"></span>
                                <div class="user-notification-icon bg-light rounded-circle text-muted d-flex align-items-center justify-content-center">
                                    <i class="fa-solid {{ $notification->kind === 'message_received' ? 'fa-comment-dots text-primary' : ($notification->kind === 'review_received' ? 'fa-star text-warning' : ($notification->kind === 'review_replied' ? 'fa-reply text-success' : 'fa-calendar text-secondary')) }}"></i>
                                </div>
                                <p class="mb-0 small text-dark text-truncate user-notification-msg" style="font-size: .82rem;">
                                    {{ $notification->message }}
                                </p>
                            </div>
                            <small class="text-muted ms-3 flex-shrink-0" style="font-size: .72rem;">
                                {{ $notification->created_at->format('d/m/Y H:i') }}
                            </small>
                        </a>
                    @empty
                        <p class="text-muted small py-3 mb-0 text-center">Nenhuma notificação no momento.</p>
                    @endforelse
                </div>

                @if($reportNotifications->count() > 4)
                    <div class="text-center pt-3 border-top mt-2">
                        <a href="#notificacoes" class="small fw-semibold text-primary text-decoration-none">
                            Ver todas as notificações <i class="fa-solid fa-angle-right ms-1"></i>
                        </a>
                    </div>
                @endif
            </div>

            {{-- 4. Cartões Lado a Lado: Minha Loja & Lojas Seguidas --}}
            <div class="row g-3 mb-4">
                {{-- Minha Loja --}}
                <div class="col-12 col-md-6" id="minhas-lojas">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 user-feature-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-store text-primary"></i>
                                <strong class="fw-bold user-feature-title" style="font-size: .88rem;">Minha loja</strong>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 small fw-semibold" style="font-size: .72rem;">
                                Plano {{ $user->subscriptionPlanLabel() }} · {{ $stores->count() }} de {{ $storeLimit === null ? 'ilimitadas' : $storeLimit }}
                            </span>
                        </div>

                        @if($stores->isEmpty())
                            <div class="d-flex align-items-center gap-3 my-auto py-2">
                                <div class="user-empty-icon bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="fa-solid fa-shop fs-3"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fw-bold" style="font-size: .85rem;">Você ainda não cadastrou uma loja.</strong>
                                    <p class="text-muted small mb-2" style="font-size: .75rem;">Crie sua vitrine e comece a receber clientes.</p>
                                    @if($canCreateStore)
                                        <a href="{{ route('store.create') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: .75rem;">
                                            Criar minha loja
                                        </a>
                                    @else
                                        <a href="{{ route('page.plans') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: .75rem;">
                                            Ver planos
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            @php $store = $stores->first(); @endphp
                            <div class="d-flex align-items-center justify-content-between gap-2 p-2 rounded-3 bg-light border mb-3">
                                <div class="d-flex align-items-center gap-2.5 min-width-0">
                                    @if($store->logo)
                                        <img src="{{ asset($store->logo) }}" class="rounded-3" style="width: 44px; height: 44px; object-fit: cover;" alt="">
                                    @else
                                        <div class="rounded-3 bg-white d-flex align-items-center justify-content-center border" style="width: 44px; height: 44px;">
                                            <i class="fa-solid fa-store text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="min-width-0">
                                        <strong class="d-block text-truncate text-dark" style="font-size: .85rem;">{{ $store->name }}</strong>
                                        <small class="text-muted d-block" style="font-size: .72rem;">{{ $store->products_count }} produtos · {{ $store->city ?: 'Sergipe' }}</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    <a href="{{ route('store.show', $store->slug) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" style="font-size: .72rem;">Ver</a>
                                    <a href="{{ route('store.edit') }}" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1" style="font-size: .72rem;">Gerenciar</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-2 mt-auto">
                                @if($canCreateStore)
                                    <a href="{{ route('store.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: .75rem;">
                                        <i class="fa-solid fa-plus me-1"></i> Criar outra loja
                                    </a>
                                @else
                                    <a href="{{ route('page.plans') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" style="font-size: .75rem;">
                                        <i class="fa-solid fa-crown me-1 text-warning"></i> Ver planos
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Lojas Seguidas --}}
                <div class="col-12 col-md-6" id="lojas-seguidas">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 user-feature-card">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="fa-solid fa-heart text-primary"></i>
                            <strong class="fw-bold user-feature-title" style="font-size: .88rem;">Lojas seguidas</strong>
                        </div>

                        @if($followedStores->isEmpty())
                            <div class="d-flex align-items-center gap-3 my-auto py-2">
                                <div class="user-empty-icon bg-primary bg-opacity-10 text-primary rounded-4 d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="fa-solid fa-store user-followed-stores-empty-icon fs-3"></i>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fw-bold" style="font-size: .85rem;">Você ainda não segue nenhuma loja.</strong>
                                    <p class="text-muted small mb-2" style="font-size: .75rem;">Siga lojas preferidas e encontre novidades.</p>
                                    <a href="{{ route('stores.index') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: .75rem;">
                                        Explorar lojas
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-2">
                                @foreach($followedStores->take(2) as $followedStore)
                                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                                        <div class="d-flex align-items-center gap-2 min-width-0">
                                            <img src="{{ asset($followedStore->logo ?: 'images/logo.png') }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" alt="">
                                            <div class="min-width-0">
                                                <a href="{{ route('store.show', $followedStore->slug) }}" class="fw-bold text-dark text-truncate text-decoration-none d-block" style="font-size: .8rem;">{{ $followedStore->name }}</a>
                                                <small class="text-muted" style="font-size: .7rem;">{{ $followedStore->city ?: 'Sergipe' }}</small>
                                            </div>
                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                                            <a href="{{ route('store.show', $followedStore->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: .7rem;">Ver</a>
                                            <form action="{{ route('store.follow.toggle', $followedStore) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5" style="font-size: .7rem;" title="Deixar de seguir">Deixar de seguir</button>
                                            </form>
                                        </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 5. Anúncios Salvos / Favoritos Organizados por Pasta --}}
            @if($favoriteFolders->isNotEmpty() || $unfiledFavorites->isNotEmpty() || $favorites->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 user-favorites-card" id="favoritos">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-folder-open text-primary fs-5"></i>
                            <h6 class="fw-bold mb-0 user-section-title">Anúncios organizados por pasta</h6>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @foreach($favoriteFolders as $folder)
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <strong class="text-dark fw-bold" style="font-size: .88rem;"><i class="fa-regular fa-folder me-1.5 text-primary"></i> {{ $folder->name }}</strong>
                                    <span class="badge bg-secondary rounded-pill px-2 py-0.5" style="font-size: .68rem;">{{ $folder->ads->count() }} anúncios</span>
                                </div>
                                <div class="row g-2">
                                    @foreach($folder->ads as $favAd)
                                        <div class="col-12 col-md-6">
                                            <a href="{{ $favAd->module === 'services' ? route('provider.show', $favAd->slug) : route('ad.show', $favAd->slug) }}" class="d-flex align-items-center gap-2 p-2 bg-white rounded-3 border text-decoration-none text-dark">
                                                @if($favAd->card_image)
                                                    <img src="{{ asset($favAd->card_image) }}" class="rounded-2" style="width: 40px; height: 40px; object-fit: cover;" alt="">
                                                @else
                                                    <div class="rounded-2 bg-light d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                                                        <i class="fa-solid fa-tag text-muted small"></i>
                                                    </div>
                                                @endif
                                                <div class="min-width-0">
                                                    <span class="fw-bold d-block text-truncate small">{{ $favAd->title }}</span>
                                                    <small class="text-muted">{{ $favAd->formatted_price }}</small>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @if($unfiledFavorites->isNotEmpty())
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <strong class="text-dark fw-bold" style="font-size: .88rem;"><i class="fa-regular fa-bookmark me-1.5 text-secondary"></i> Salvos sem pasta</strong>
                                    <span class="badge bg-secondary rounded-pill px-2 py-0.5" style="font-size: .68rem;">{{ $unfiledFavorites->count() }} anúncios</span>
                                </div>
                                <div class="row g-2">
                                    @foreach($unfiledFavorites as $favAd)
                                        <div class="col-12 col-md-6">
                                            <a href="{{ $favAd->module === 'services' ? route('provider.show', $favAd->slug) : route('ad.show', $favAd->slug) }}" class="d-flex align-items-center gap-2 p-2 bg-white rounded-3 border text-decoration-none text-dark">
                                                <div class="min-width-0">
                                                    <span class="fw-bold d-block text-truncate small">{{ $favAd->title }}</span>
                                                    <small class="text-muted">{{ $favAd->formatted_price }}</small>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 5. Meus Anúncios e Perfis (Tabela Moderna) --}}
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 user-items-panel" id="meus-anuncios" data-user-ads-panel>
                {{-- Cabeçalho da Seção com Lupa --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bullhorn text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0 user-section-title">Meus anúncios e perfis (<span data-user-ads-count>{{ count($ads) }}</span>)</h6>
                    </div>

                    <div class="user-ads-search-wrapper {{ count($ads) === 0 ? 'd-none' : '' }}" data-user-ads-search-wrapper>
                        <i class="fa-solid fa-magnifying-glass user-ads-search-icon"></i>
                        <input
                            type="search"
                            id="user-ads-search"
                            class="user-ads-search-input"
                            placeholder="Buscar anúncio..."
                            autocomplete="off"
                            data-user-ads-search
                        >
                    </div>
                </div>

                {{-- Feedback de Mensagem AJAX --}}
                <div class="alert mb-3" data-user-ads-feedback role="status" aria-live="polite" hidden></div>

                {{-- Estado Vazio --}}
                <div class="text-center py-5 {{ count($ads) === 0 ? '' : 'd-none' }}" data-user-ads-empty>
                    <i class="fa-solid fa-folder-open text-muted fs-1 mb-3"></i>
                    <h6 class="fw-bold text-dark">Nenhum anúncio publicado ainda</h6>
                    <p class="text-muted small mb-4">Comece a vender ou oferecer seus serviços em Sergipe hoje!</p>
                    <a href="{{ route('ad.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Anunciar Agora</a>
                </div>

                {{-- Sem resultados de busca --}}
                <div class="text-center py-4 d-none" data-user-ads-no-results>
                    <i class="fa-solid fa-search text-muted fs-2 mb-2"></i>
                    <p class="text-muted small mb-0">Nenhum anúncio encontrado para esta busca.</p>
                </div>

                {{-- Tabela / Grade de Anúncios no Estilo Mockup --}}
                <div class="user-ads-table-wrapper {{ count($ads) === 0 ? 'd-none' : '' }}" data-user-ads-list>
                    {{-- Cabeçalho da Tabela (Visível em telas médias e grandes) --}}
                    <div class="user-ads-table-header d-none d-md-flex align-items-center py-2 px-3 text-muted small fw-semibold border-bottom">
                        <div style="flex: 2.1;">Anúncio</div>
                        <div style="flex: 1.1;">Categoria</div>
                        <div style="flex: 0.8;">Status</div>
                        <div style="flex: 0.9;">Visualizações</div>
                        <div style="flex: 0.9;">Publicado em</div>
                        <div style="flex: 1.8;" class="text-end">Ações</div>
                    </div>

                    {{-- Linhas de Anúncios --}}
                    @foreach($ads as $item)
                        <div class="user-ad-row py-3 px-2 px-md-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2" data-user-ad-row="{{ $item->id }}" data-ad-title-search="{{ strtolower($item->title) }}">
                            {{-- Coluna 1: Imagem + Título --}}
                            <div class="d-flex align-items-center gap-3" style="flex: 2.1; min-width: 0;">
                                @if($item->card_image)
                                    <img src="{{ asset($item->card_image) }}" class="rounded-3 user-table-img flex-shrink-0" alt="{{ $item->title }}">
                                @else
                                    <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center user-table-img flex-shrink-0">
                                        <i class="fa-solid fa-image text-muted"></i>
                                    </div>
                                @endif
                                <div class="min-width-0">
                                    <strong class="d-block text-truncate text-dark user-table-title" style="font-size: .85rem;">{{ $item->title }}</strong>
                                    <div class="d-flex align-items-center gap-2 mt-0.5 d-md-none">
                                        <small class="text-primary fw-semibold" style="font-size: .75rem;">
                                            {{ $item->module === 'services' ? 'Serviços' : $item->formatted_price }}
                                        </small>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: .65rem;">
                                            <i class="fa-regular fa-eye me-0.5"></i> {{ number_format($item->views ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Coluna 2: Categoria --}}
                            <div class="d-none d-md-block text-muted small" style="flex: 1.1; font-size: .8rem;">
                                {{ $item->category?->name ?? ($item->module === 'services' ? 'Serviços' : 'Geral') }}
                            </div>

                            {{-- Coluna 3: Status --}}
                            <div style="flex: 0.8;">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: .7rem;">
                                    {{ $item->status === 'active' ? 'Ativo' : ucfirst($item->status) }}
                                </span>
                            </div>

                            {{-- Coluna 4: Visualizações --}}
                            <div class="d-none d-md-flex align-items-center" style="flex: 0.9;">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" style="font-size: .72rem;" title="{{ number_format($item->views ?? 0, 0, ',', '.') }} visualizações">
                                    <i class="fa-regular fa-eye"></i> {{ number_format($item->views ?? 0, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Coluna 5: Publicado em --}}
                            <div class="d-none d-md-block text-muted small" style="flex: 0.9; font-size: .78rem;">
                                {{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}
                            </div>

                            {{-- Coluna 5: Ações --}}
                            <div class="d-flex align-items-center justify-content-end gap-1.5 flex-wrap user-table-actions" style="flex: 1.8;">
                                <a href="{{ $item->module === 'services' ? route('provider.show', $item->slug) : route('ad.show', $item->slug) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" style="font-size: .75rem;" title="Ver">
                                    <i class="fa-regular fa-eye me-1"></i> Ver
                                </a>
                                @if(\App\Support\ServiceBookingCatalog::eligible($item))
                                    <a href="{{ route('service-booking.manage', $item) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: .75rem;" title="Agenda e financeiro">
                                        <i class="fa-regular fa-calendar-check me-1"></i> Agenda e financeiro
                                    </a>
                                @endif
                                <a href="{{ route('ad.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: .75rem;" title="Editar">
                                    <i class="fa-solid fa-pen me-1"></i> Editar
                                </a>
                                <form action="{{ route('ad.destroy', $item->id) }}" method="POST" data-user-ad-delete-form data-ad-title="{{ $item->title }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" style="font-size: .75rem;" title="Excluir">
                                        <i class="fa-solid fa-trash me-1"></i> Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Paginação --}}
                <nav class="user-ads-pagination mt-4 {{ count($ads) <= 5 ? 'd-none' : '' }}" data-user-ads-pagination aria-label="Paginação dos anúncios">
                    <button class="user-ads-page-btn" data-ads-prev aria-label="Página anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="user-ads-page-numbers" data-ads-page-numbers></div>
                    <button class="user-ads-page-btn" data-ads-next aria-label="Próxima página">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos Gerais do Painel */
    .user-panel-avatar-wrapper {
        position: relative;
        width: 88px;
        height: 88px;
    }

    .user-panel-avatar {
        width: 88px;
        height: 88px;
        overflow: hidden;
    }

    .user-panel-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-avatar-edit-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #0d6efd;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2.5px solid #ffffff;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.18);
        cursor: pointer;
        transition: transform .18s ease, background-color .18s ease;
        z-index: 5;
    }

    .user-avatar-edit-btn:hover {
        background: #0b5ed7;
        transform: scale(1.1);
        color: #ffffff;
    }

    .user-avatar-edit-btn i {
        font-size: .75rem;
    }

    .user-sidebar-shortcut-link {
        transition: background .18s ease;
    }

    .user-sidebar-shortcut-link:hover {
        background: var(--muted-bg, #f3f4f6);
    }

    /* Cards de Estatísticas */
    .user-stat-card {
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .user-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06) !important;
    }

    .user-stat-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    /* Ações Rápidas */
    .user-quick-action-card {
        transition: transform .2s ease, box-shadow .2s ease;
        border: 1px solid transparent !important;
    }

    .user-quick-action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.08) !important;
        border-color: rgba(13, 110, 253, 0.2) !important;
    }

    .user-quick-icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        font-size: 1rem;
    }

    /* Notificações */
    .user-notification-row {
        transition: background .18s ease;
        border-radius: 8px;
    }

    .user-notification-row:hover {
        background: var(--muted-bg, #f9fafb);
    }

    .user-notification-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .user-notification-dot.is-unread {
        background: #0d6efd;
    }

    .user-notification-dot.is-read {
        background: transparent;
    }

    .user-notification-icon {
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        font-size: .75rem;
    }

    /* Cards de Recursos (Minha Loja & Seguidas) */
    .user-empty-icon {
        width: 58px;
        height: 58px;
    }

    /* Tabela de Anúncios */
    .user-table-img {
        width: 48px;
        height: 48px;
        object-fit: cover;
    }

    .user-ad-row {
        transition: opacity .2s ease, transform .2s ease, background-color .18s ease;
    }

    .user-ad-row:hover {
        background: var(--muted-bg, #fcfcfc);
    }

    .user-ad-row.is-removing {
        opacity: 0;
        transform: translateX(14px);
        pointer-events: none;
    }

    /* Campo de Busca */
    .user-ads-search-wrapper {
        position: relative;
        flex: 0 1 240px;
        min-width: 140px;
    }

    .user-ads-search-icon {
        position: absolute;
        left: .85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: .82rem;
        pointer-events: none;
    }

    .user-ads-search-input {
        width: 100%;
        padding: .4rem .75rem .4rem 2.2rem;
        border: 1.5px solid var(--border, #e5e7eb);
        border-radius: 999px;
        font-size: .82rem;
        background: var(--muted-bg, #f9fafb);
        color: var(--foreground, #111);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .user-ads-search-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, .12);
        background: #fff;
    }

    /* Paginação */
    .user-ads-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        flex-wrap: wrap;
    }

    .user-ads-page-btn, .user-ads-page-num {
        width: 32px;
        height: 32px;
        border: 1.5px solid var(--border, #e5e7eb);
        border-radius: 50%;
        background: var(--card, #fff);
        color: var(--foreground, #333);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .18s;
        font-size: .78rem;
        font-weight: 600;
    }

    .user-ads-page-btn:hover:not(:disabled), .user-ads-page-num:hover {
        background: #eff6ff;
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .user-ads-page-num.is-active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    .user-ads-page-btn:disabled {
        opacity: .35;
        cursor: not-allowed;
    }

    .min-width-0 {
        min-width: 0;
    }

    /* Dark Mode Adaptations */
    [data-bs-theme="dark"] .user-sidebar-card,
    [data-bs-theme="dark"] .user-stat-card,
    [data-bs-theme="dark"] .user-quick-action-card,
    [data-bs-theme="dark"] .user-notifications-card,
    [data-bs-theme="dark"] .user-feature-card,
    [data-bs-theme="dark"] .user-items-panel,
    html[data-theme="dark"] .user-sidebar-card,
    html[data-theme="dark"] .user-stat-card,
    html[data-theme="dark"] .user-quick-action-card,
    html[data-theme="dark"] .user-notifications-card,
    html[data-theme="dark"] .user-feature-card,
    html[data-theme="dark"] .user-items-panel {
        background: #1e293b !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    [data-bs-theme="dark"] .user-sidebar-name,
    [data-bs-theme="dark"] .user-page-title,
    [data-bs-theme="dark"] .user-stat-num,
    [data-bs-theme="dark"] .user-section-title,
    [data-bs-theme="dark"] .user-quick-title,
    [data-bs-theme="dark"] .user-feature-title,
    [data-bs-theme="dark"] .user-table-title,
    [data-bs-theme="dark"] .user-notification-msg,
    html[data-theme="dark"] .user-sidebar-name,
    html[data-theme="dark"] .user-page-title,
    html[data-theme="dark"] .user-stat-num,
    html[data-theme="dark"] .user-section-title,
    html[data-theme="dark"] .user-quick-title,
    html[data-theme="dark"] .user-feature-title,
    html[data-theme="dark"] .user-table-title,
    html[data-theme="dark"] .user-notification-msg {
        color: #f8fafc !important;
    }

    [data-bs-theme="dark"] .user-sidebar-shortcut-link span,
    html[data-theme="dark"] .user-sidebar-shortcut-link span {
        color: #cbd5e1 !important;
    }

    [data-bs-theme="dark"] .user-ads-search-input,
    html[data-theme="dark"] .user-ads-search-input {
        background: #0f172a !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }

    /* Responsividade Mobile */
    @media (max-width: 575.98px) {
        .user-panel-page {
            padding-top: 1rem !important;
        }

        .user-ads-search-wrapper {
            flex: 1 1 100%;
        }

        .user-table-actions {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .35rem !important;
        }

        .user-table-actions .btn,
        .user-table-actions form {
            width: 100%;
        }

        .user-table-actions .btn {
            padding: .35rem .25rem !important;
            font-size: .72rem;
            white-space: nowrap;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    (() => {
        const PER_PAGE = 5;
        const panel = document.querySelector('[data-user-ads-panel]');
        if (!panel) return;

        const list        = panel.querySelector('[data-user-ads-list]');
        const emptyState  = panel.querySelector('[data-user-ads-empty]');
        const noResults   = panel.querySelector('[data-user-ads-no-results]');
        const countEl     = panel.querySelector('[data-user-ads-count]');
        const feedback    = panel.querySelector('[data-user-ads-feedback]');
        const searchInput = panel.querySelector('[data-user-ads-search]');
        const searchWrap  = panel.querySelector('[data-user-ads-search-wrapper]');
        const pagination  = panel.querySelector('[data-user-ads-pagination]');
        const pageNums    = panel.querySelector('[data-ads-page-numbers]');
        const btnPrev     = panel.querySelector('[data-ads-prev]');
        const btnNext     = panel.querySelector('[data-ads-next]');

        const allRows = () => [...list.querySelectorAll('[data-user-ad-row]')];

        let currentPage    = 1;
        let filteredRows   = allRows();
        let totalRows      = allRows().length;

        const showFeedback = (message, type) => {
            feedback.textContent = message;
            feedback.className = `alert alert-${type} mb-3`;
            feedback.hidden = false;
        };

        const totalPages = () => Math.max(1, Math.ceil(filteredRows.length / PER_PAGE));

        const renderPagination = () => {
            if (!pagination) return;
            const pages = totalPages();

            if (pages <= 1) {
                pagination.classList.add('d-none');
                return;
            }
            pagination.classList.remove('d-none');

            btnPrev.disabled = currentPage === 1;
            btnNext.disabled = currentPage === pages;

            pageNums.innerHTML = '';
            const start = Math.max(1, currentPage - 2);
            const end   = Math.min(pages, currentPage + 2);
            for (let i = start; i <= end; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'user-ads-page-num' + (i === currentPage ? ' is-active' : '');
                btn.textContent = i;
                btn.setAttribute('aria-label', `Página ${i}`);
                btn.addEventListener('click', () => goToPage(i));
                pageNums.appendChild(btn);
            }
        };

        const showPage = (page) => {
            const start = (page - 1) * PER_PAGE;
            const end   = start + PER_PAGE;
            allRows().forEach(row => row.style.display = 'none');
            filteredRows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? '' : 'none';
            });
        };

        const goToPage = (page) => {
            currentPage = Math.max(1, Math.min(page, totalPages()));
            showPage(currentPage);
            renderPagination();
        };

        const applySearch = (term) => {
            const q = term.trim().toLowerCase();
            const rows = allRows();

            if (q === '') {
                filteredRows = rows;
            } else {
                filteredRows = rows.filter(row => {
                    const title = row.dataset.adTitleSearch || '';
                    return title.includes(q);
                });
            }

            if (filteredRows.length === 0 && rows.length > 0) {
                list.classList.add('d-none');
                if (noResults) noResults.classList.remove('d-none');
                if (pagination) pagination.classList.add('d-none');
            } else {
                list.classList.remove('d-none');
                if (noResults) noResults.classList.add('d-none');
                currentPage = 1;
                goToPage(1);
            }
        };

        if (searchInput) {
            searchInput.addEventListener('input', (e) => applySearch(e.target.value));
        }

        if (btnPrev) btnPrev.addEventListener('click', () => goToPage(currentPage - 1));
        if (btnNext) btnNext.addEventListener('click', () => goToPage(currentPage + 1));

        const init = () => {
            filteredRows = allRows();
            totalRows    = filteredRows.length;
            if (totalRows > 0) {
                goToPage(1);
            }
        };
        init();

        panel.addEventListener('submit', async (event) => {
            const form = event.target.closest('[data-user-ad-delete-form]');
            if (!form) return;

            event.preventDefault();
            const adTitle = form.dataset.adTitle || 'este anúncio';
            if (!window.confirm(`Deseja realmente excluir "${adTitle}"?`)) return;

            const button = form.querySelector('button[type="submit"]');
            const originalButtonContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Excluindo';
            feedback.hidden = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                let payload = {};
                try { payload = await response.json(); } catch (_) {}

                if (!response.ok) throw new Error(payload.message || 'Erro ao excluir.');

                const row = form.closest('[data-user-ad-row]');
                row.classList.add('is-removing');
                await new Promise((resolve) => window.setTimeout(resolve, 200));
                row.remove();

                const remaining = allRows().length;
                if (countEl) countEl.textContent = remaining;

                if (remaining === 0) {
                    list.classList.add('d-none');
                    emptyState.classList.remove('d-none');
                    if (searchWrap) searchWrap.classList.add('d-none');
                    if (pagination) pagination.classList.add('d-none');
                } else {
                    applySearch(searchInput ? searchInput.value : '');
                }

                showFeedback(payload.message || 'Anúncio removido com sucesso!', 'success');
            } catch (error) {
                button.disabled = false;
                button.innerHTML = originalButtonContent;
                showFeedback(error.message || 'Erro ao excluir.', 'danger');
            }
        });
    })();
</script>
@endpush
