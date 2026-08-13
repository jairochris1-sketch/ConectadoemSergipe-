@extends('layouts.app')

@section('title', 'Painel do Anunciante - Conectado em Sergipe')

@section('content')
<div class="container py-5 user-panel-page">
    @if(session('success'))
        <div class="alert alert-success rounded-3 mb-4">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar do Perfil -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <div class="card-body">
                    <form action="{{ route('user.avatar.update') }}" method="POST" enctype="multipart/form-data" id="user-panel-avatar-form">
                        @csrf
                        <div class="user-panel-avatar-wrapper position-relative mx-auto mb-3">
                            <div class="user-panel-avatar rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-2">
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
                    <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> {{ $user->city ?? 'Aracaju' }}, SE</p>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold mb-4">Conta Ativa</span>

                    <div class="d-flex flex-column align-items-center gap-3">
                        <a href="{{ route('user.profile') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2">
                            <i class="fa-regular fa-user me-2"></i>Editar Perfil
                        </a>
                        <a href="{{ route('user.settings') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2">
                            <i class="fa-solid fa-gear me-2"></i>Configurações
                        </a>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="{{ route('chat.index') }}" class="panel-quick-icon" aria-label="Abrir mensagens" title="Mensagens">
                                <i class="fa-solid fa-comments"></i>
                                @if($unreadMessagesCount > 0)
                                    <span class="panel-quick-badge">{{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}</span>
                                @endif
                            </a>
                            <a href="#notificacoes" class="panel-quick-icon" aria-label="Ver notificações" title="Notificações">
                                <i class="fa-solid fa-bell"></i>
                                @if($unreadNotificationsCount > 0)
                                    <span class="panel-quick-badge">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo do Painel -->
        <div class="col-12 col-md-8">
                <div class="bg-white rounded-4 shadow-sm border p-3 p-md-4 mb-4" id="notificacoes">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="h5 fw-bold mb-0"><i class="fa-solid fa-bell text-warning me-2"></i>Notificações</h4>
                        <form action="{{ route('user.notifications.preference') }}" method="POST">
                            @csrf
                            <input type="hidden" name="notifications_enabled" value="{{ $user->notifications_enabled ? 0 : 1 }}">
                            <button type="submit" class="btn btn-sm {{ $user->notifications_enabled ? 'btn-outline-secondary' : 'btn-outline-success' }} rounded-pill px-3">
                                <i class="fa-solid {{ $user->notifications_enabled ? 'fa-bell-slash' : 'fa-bell' }} me-1"></i>
                                {{ $user->notifications_enabled ? 'Desativar notificações' : 'Ativar notificações' }}
                            </button>
                        </form>
                    </div>

                    @if(session('notification_preference_success'))
                        <div class="alert alert-success py-2 px-3 small rounded-3">
                            {{ session('notification_preference_success') }}
                        </div>
                    @endif

                    @unless($user->notifications_enabled)
                        <div class="alert alert-secondary py-2 px-3 small rounded-3">
                            As novas notificações estão desativadas. As mensagens e avaliações continuam funcionando normalmente.
                        </div>
                    @endunless

                    @forelse($reportNotifications as $notification)
                        <a href="{{ route('user.notifications.open', $notification) }}" class="panel-notification-item border-top py-3 d-flex align-items-start gap-3 {{ $notification->read_at ? '' : 'panel-notification-unread' }}">
                            <i class="fa-solid {{ $notification->kind === 'message_received' ? 'fa-comment-dots text-primary' : ($notification->kind === 'review_received' ? 'fa-star text-warning' : ($notification->kind === 'review_replied' ? 'fa-reply text-success' : 'fa-circle-info text-secondary')) }} mt-1"></i>
                            <div class="flex-grow-1 min-width-0">
                                <p class="mb-1 small">{{ $notification->message }}</p>
                                <small class="text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted small mt-2"></i>
                        </a>
                    @empty
                        <p class="text-muted small mb-0">Nenhuma notificação no momento.</p>
                    @endforelse
                </div>
            @if(session('store_success'))
                <div class="alert alert-success rounded-4">{{ session('store_success') }}</div>
            @endif
            @if(session('store_limit'))
                <div class="alert alert-warning rounded-4 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm border-0 p-3 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-crown text-warning fs-4"></i>
                        <span class="fw-semibold">{{ session('store_limit') }}</span>
                    </div>
                    <a href="{{ route('page.plans') }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Fazer Upgrade
                    </a>
                </div>
            @endif

            <section class="user-store-panel mb-4">
                <div class="user-store-panel-heading">
                    <div>
                        <span>
                            <i class="fa-solid fa-store"></i>
                            Minhas lojas · Plano {{ $user->subscriptionPlanLabel() }}
                        </span>
                        <h4>{{ $stores->isNotEmpty() ? 'Gerencie suas vitrines comerciais' : 'Crie sua vitrine comercial' }}</h4>
                        <p>
                            Uso: {{ $stores->count() }} de {{ $storeLimit === null ? 'ilimitadas' : ($storeLimit === 0 ? '0 (Necessário Plano Start ou superior)' : $storeLimit) }}
                            {{ $storeLimit === 1 ? 'loja' : 'lojas' }}
                        </p>
                    </div>
                    @if($canCreateStore)
                        <a href="{{ route('store.create') }}" class="user-store-create-button">
                            <i class="fa-solid fa-plus"></i>
                            {{ $stores->isEmpty() ? 'Criar minha loja' : 'Criar outra loja' }}
                        </a>
                    @else
                        <a href="{{ route('page.plans') }}" class="user-store-create-button">
                            <i class="fa-solid fa-crown"></i>
                            Ver planos
                        </a>
                    @endif
                </div>

                @forelse($stores as $storeItem)
                    <div class="user-store-summary {{ !$loop->last ? 'mb-3' : '' }}">
                            <div class="user-store-cover">
                                @if($storeItem->banner)
                                    <img src="{{ asset($storeItem->banner) }}" alt="">
                                @else
                                    <i class="fa-solid fa-panorama"></i>
                                @endif
                            </div>
                            <div class="user-store-logo">
                                @if($storeItem->logo)
                                    <img src="{{ asset($storeItem->logo) }}" alt="Logo da {{ $storeItem->name }}">
                                @else
                                    <i class="fa-solid fa-store"></i>
                                @endif
                            </div>
                            <div class="user-store-data">
                                <div class="user-store-title">
                                    <strong>{{ $storeItem->name }}</strong>
                                    <span class="{{ !$storeItem->isModerationApproved() ? 'is-suspended' : ($storeItem->active ? 'is-active' : 'is-inactive') }}">
                                        {{ !$storeItem->isModerationApproved() ? 'Suspensa' : ($storeItem->active ? 'Ativa' : 'Desativada') }}
                                    </span>
                                    @if($storeItem->isCurrentlyFeatured())
                                        <span class="is-featured"><i class="fa-solid fa-star"></i> Destaque</span>
                                    @endif
                                </div>
                                <p><i class="fa-solid fa-location-dot"></i> {{ $storeItem->city ?: $user->city ?: 'Sergipe' }}</p>
                                <div class="user-store-metrics">
                                    <span>
                                        <i class="fa-solid fa-cube"></i>
                                        {{ $storeItem->products_count }} de {{ $storeProductLimit === null ? 'ilimitados' : $storeProductLimit }} produtos
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-star"></i>
                                        {{ $storeItem->approved_reviews_count ? number_format($storeItem->approved_reviews_average, 1, ',', '.') : 'Sem avaliações' }}
                                    </span>
                                    <span>
                                        <i class="fa-solid fa-box"></i>
                                        {{ $storeItem->orders_count }} pedidos
                                        @if($storeItem->pending_orders_count)
                                            · {{ $storeItem->pending_orders_count }} novos
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="user-store-actions">
                                <a href="{{ route('store.show', $storeItem->slug) }}"><i class="fa-solid fa-eye"></i> Ver</a>
                                <a href="{{ $stores->count() === 1 ? route('store.edit') : route('store.manage', $storeItem) }}" class="is-primary"><i class="fa-solid fa-pen"></i> Gerenciar</a>
                                <a href="{{ route('seller.orders.index', $storeItem) }}"><i class="fa-solid fa-box"></i> Pedidos</a>
                                @if($storeItem->isModerationApproved())
                                    <form action="{{ route('store.toggle_specific', $storeItem) }}" method="POST">
                                        @csrf
                                        <button type="submit">
                                            <i class="fa-solid {{ $storeItem->active ? 'fa-pause' : 'fa-play' }}"></i>
                                            {{ $storeItem->active ? 'Desativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                    </div>
                @empty
                    <p class="mb-0 text-muted">Você ainda não cadastrou uma loja.</p>
                @endforelse
            </section>

            <section class="user-followed-stores-panel mb-4" id="lojas-seguidas">
                <div class="user-followed-stores-heading">
                    <div>
                        <span><i class="fa-solid fa-heart"></i> Lojas seguidas</span>
                        <h4>Lojas que você quer acompanhar</h4>
                        <p>{{ $followedStores->count() }} {{ $followedStores->count() === 1 ? 'loja salva' : 'lojas salvas' }}</p>
                    </div>
                    <a href="{{ route('stores.index') }}">
                        Explorar lojas <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                @if($followedStores->isEmpty())
                    <div class="user-followed-stores-empty">
                        <i class="fa-regular fa-heart"></i>
                        <div>
                            <strong>Você ainda não segue nenhuma loja</strong>
                            <p>Siga suas lojas preferidas para encontrá-las rapidamente aqui.</p>
                        </div>
                    </div>
                @else
                    <div class="user-followed-stores-grid">
                        @foreach($followedStores as $followedStore)
                            <article>
                                <a href="{{ route('store.show', $followedStore->slug) }}" class="user-followed-store-cover">
                                    @if($followedStore->banner)
                                        <img src="{{ asset($followedStore->banner) }}" alt="">
                                    @else
                                        <i class="fa-solid fa-store"></i>
                                    @endif
                                </a>
                                <div class="user-followed-store-body">
                                    <img src="{{ asset($followedStore->logo ?: 'images/logo.png') }}" alt="Logo da {{ $followedStore->name }}">
                                    <div>
                                        <a href="{{ route('store.show', $followedStore->slug) }}">{{ $followedStore->name }}</a>
                                        <span><i class="fa-solid fa-location-dot"></i> {{ $followedStore->city ?: 'Sergipe' }}/SE</span>
                                    </div>
                                </div>
                                <div class="user-followed-store-footer">
                                    <span><i class="fa-solid fa-box-open"></i> {{ $followedStore->active_ads_count }} produtos</span>
                                    <button
                                        type="button"
                                        class="is-following"
                                        data-store-follow
                                        data-store-id="{{ $followedStore->id }}"
                                        data-endpoint="{{ route('store.follow.toggle', $followedStore) }}"
                                        data-label-idle="Seguir"
                                        data-label-following="Deixar de seguir"
                                        aria-pressed="true"
                                    >
                                        <i class="fa-solid fa-heart"></i>
                                        <span data-store-follow-label>Deixar de seguir</span>
                                        <small data-store-follow-count>{{ $followedStore->followers_count }}</small>
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="bg-white rounded-4 shadow-sm border p-4 user-items-panel" data-user-ads-panel>
                <h4 class="fw-bold text-dark mb-4 user-items-title"><i class="fa-solid fa-rectangle-ad text-primary me-2"></i> Meus anúncios e perfis (<span data-user-ads-count>{{ count($ads) }}</span>)</h4>

                <div class="alert mb-3" data-user-ads-feedback role="status" aria-live="polite" hidden></div>

                <div class="text-center py-5 {{ count($ads) === 0 ? '' : 'd-none' }}" data-user-ads-empty>
                    <i class="fa-solid fa-folder-open text-muted fs-1 mb-3"></i>
                    <h6 class="fw-bold text-dark">Nenhum anúncio publicado ainda</h6>
                    <p class="text-muted small mb-4">Comece a vender ou oferecer seus serviços em Sergipe hoje!</p>
                    <a href="{{ route('ad.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Anunciar Agora</a>
                </div>

                <div class="row g-3 {{ count($ads) === 0 ? 'd-none' : '' }}" data-user-ads-list>
                    @foreach($ads as $item)
                        <div class="col-12 user-ad-row" data-user-ad-row="{{ $item->id }}">
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light user-item-card">
                                <div class="d-flex align-items-center gap-3 user-item-summary">
                                    @if($item->card_image)
                                        <img src="{{ asset($item->card_image) }}" class="rounded-3 object-fit-cover user-item-image" alt="Imagem de {{ $item->title }}">
                                    @else
                                        <div class="rounded-3 bg-white d-flex align-items-center justify-content-center border user-item-image">
                                            <i class="fa-solid fa-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="user-item-details">
                                        <h6 class="fw-bold text-dark mb-1 user-item-name">{{ $item->title }}</h6>
                                        @if($item->module === 'services')
                                            <span class="fw-bold text-primary"><i class="fa-solid fa-user-tie me-1"></i>Perfil profissional</span>
                                        @else
                                            <span class="fw-bold text-primary">{{ $item->formatted_price }}</span>
                                        @endif
                                        @if($item->module === 'products' && $item->store)
                                            <span class="badge rounded-pill text-bg-light border ms-2 user-item-store">
                                                <i class="fa-solid fa-store me-1"></i>{{ $item->store->name }}
                                            </span>
                                        @endif
                                        <span class="badge bg-info bg-opacity-10 text-info ms-2 user-item-status">{{ strtoupper($item->status) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap justify-content-end gap-2 user-item-actions">
                                    <a href="{{ $item->module === 'services' ? route('provider.show', $item->slug) : route('ad.show', $item->slug) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                        <i class="fa-solid fa-eye me-1"></i> Ver
                                    </a>
                                    @if(\App\Support\ServiceBookingCatalog::eligible($item))
                                        <a href="{{ route('service-booking.manage', $item) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fa-regular fa-calendar-check me-1"></i> Agenda e financeiro
                                        </a>
                                    @endif
                                    <a href="{{ route('ad.edit', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fa-solid fa-pen me-1"></i> Editar
                                    </a>
                                    <form action="{{ route('ad.destroy', $item->id) }}" method="POST" data-user-ad-delete-form data-ad-title="{{ $item->title }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="fa-solid fa-trash me-1"></i> Excluir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #075be8;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2.5px solid #ffffff;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.22);
        cursor: pointer;
        transition: transform .18s ease, background-color .18s ease;
        z-index: 5;
    }

    .user-avatar-edit-btn:hover {
        background: #0044aa;
        transform: scale(1.12);
        color: #ffffff;
    }

    .user-avatar-edit-btn i {
        font-size: .82rem;
    }

    .panel-quick-icon {
        position: relative;
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #0d6efd;
        border-radius: 50%;
        color: #0d6efd;
        background: var(--card);
        color: var(--foreground);
        text-decoration: none;
        transition: .2s ease;
    }

    .panel-quick-icon:hover,
    .panel-quick-icon:focus {
        color: #fff;
        background: #0d6efd;
        transform: translateY(-1px);
    }

    .panel-quick-badge {
        position: absolute;
        top: -5px;
        right: -7px;
        min-width: 19px;
        height: 19px;
        padding: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--card);
        border-radius: 999px;
        color: #fff;
        background: #dc3545;
        font-size: .62rem;
        font-weight: 800;
    }

    .panel-notification-item {
        position: relative;
        color: var(--foreground);
        text-decoration: none;
        transition: background-color .18s ease, padding .18s ease;
    }

    .panel-notification-item:hover,
    .panel-notification-item:focus {
        color: var(--foreground);
        background: var(--muted-bg);
        padding-right: .65rem;
        padding-left: .65rem;
        border-radius: 10px;
    }

    .panel-notification-unread::before {
        content: "";
        position: absolute;
        top: 1.15rem;
        left: -.55rem;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #0d6efd;
    }

    .min-width-0 {
        min-width: 0;
    }

    .user-item-summary,
    .user-item-details {
        min-width: 0;
    }

    .user-item-summary {
        flex: 1 1 auto;
    }

    .user-item-image {
        width: 60px;
        height: 60px;
        flex: 0 0 60px;
    }

    .user-item-name {
        overflow-wrap: anywhere;
    }

    .user-item-actions {
        flex: 0 0 auto;
        margin-left: 1rem;
    }

    .user-ad-row {
        transition: opacity .2s ease, transform .2s ease;
    }

    .user-ad-row.is-removing {
        opacity: 0;
        transform: translateX(14px);
        pointer-events: none;
    }

    @media (max-width: 575.98px) {
        .user-panel-page {
            padding-top: 1rem !important;
            padding-right: .75rem;
            padding-left: .75rem;
        }

        .user-items-panel {
            padding: 1rem !important;
        }

        .user-items-title {
            margin-bottom: 1rem !important;
            font-size: clamp(1.25rem, 6vw, 1.55rem);
            line-height: 1.18;
        }

        .user-item-card {
            align-items: stretch !important;
            flex-direction: column;
            gap: .9rem;
            padding: .85rem !important;
        }

        .user-item-summary {
            align-items: flex-start !important;
            gap: .75rem !important;
            width: 100%;
        }

        .user-item-image {
            width: 72px;
            height: 72px;
            flex-basis: 72px;
        }

        .user-item-name {
            font-size: 1rem;
            line-height: 1.25;
        }

        .user-item-details {
            flex: 1 1 auto;
        }

        .user-item-details > .fw-bold.text-primary {
            display: block;
            font-size: .92rem;
            line-height: 1.25;
        }

        .user-item-status {
            display: inline-block;
            margin-top: .45rem;
            margin-left: 0 !important;
        }

        .user-item-actions {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem !important;
            width: 100%;
            margin-left: 0;
        }

        .user-item-actions > a,
        .user-item-actions > form,
        .user-item-actions button {
            width: 100%;
            min-width: 0;
        }

        .user-item-actions .btn {
            padding: .42rem .35rem !important;
            font-size: .78rem;
            white-space: nowrap;
        }

        .user-item-actions .btn i {
            margin-right: .2rem !important;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    (() => {
        const panel = document.querySelector('[data-user-ads-panel]');
        if (!panel) return;

        const list = panel.querySelector('[data-user-ads-list]');
        const emptyState = panel.querySelector('[data-user-ads-empty]');
        const count = panel.querySelector('[data-user-ads-count]');
        const feedback = panel.querySelector('[data-user-ads-feedback]');

        const showFeedback = (message, type) => {
            feedback.textContent = message;
            feedback.className = `alert alert-${type} mb-3`;
            feedback.hidden = false;
        };

        panel.addEventListener('submit', async (event) => {
            const form = event.target.closest('[data-user-ad-delete-form]');
            if (!form) return;

            event.preventDefault();

            const adTitle = form.dataset.adTitle || 'este anúncio';
            if (!window.confirm(`Deseja realmente excluir “${adTitle}”?`)) return;

            const button = form.querySelector('button[type="submit"]');
            const originalButtonContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Excluindo';
            feedback.hidden = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let payload = {};
                try {
                    payload = await response.json();
                } catch (error) {
                    // A mensagem padrão abaixo cobre respostas sem JSON.
                }

                if (!response.ok) {
                    throw new Error(payload.message || 'Não foi possível excluir o anúncio. Tente novamente.');
                }

                const row = form.closest('[data-user-ad-row]');
                row.classList.add('is-removing');
                await new Promise((resolve) => window.setTimeout(resolve, 200));
                row.remove();

                const remainingAds = list.querySelectorAll('[data-user-ad-row]').length;
                count.textContent = remainingAds;

                if (remainingAds === 0) {
                    list.classList.add('d-none');
                    emptyState.classList.remove('d-none');
                }

                showFeedback(payload.message || 'Anúncio removido com sucesso!', 'success');
            } catch (error) {
                button.disabled = false;
                button.innerHTML = originalButtonContent;
                showFeedback(error.message || 'Não foi possível excluir o anúncio. Tente novamente.', 'danger');
            }
        });
    })();
</script>
@endpush
