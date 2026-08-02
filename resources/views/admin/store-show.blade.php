@extends('layouts.admin')

@section('title', 'Analisar ' . $store->name . ' - Painel Admin')

@section('content')
@php
    $statusLabel = $store->moderation_status === 'approved' ? 'Aprovada' : 'Suspensa';
@endphp

<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <a href="{{ route('admin.stores') }}" class="admin-store-back"><i class="fa-solid fa-arrow-left"></i> Voltar às lojas</a>
        <h1 class="h2 fw-bold text-dark mt-2 mb-1">Análise da loja</h1>
        <p class="text-muted small mb-0">Confira cadastro, responsável, produtos e avaliações antes de aplicar uma ação.</p>
    </div>
    <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Visualizar loja
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
@endif

<section class="admin-store-review-hero">
    <div class="admin-store-review-cover">
        @if($store->banner)
            <img src="{{ asset($store->banner) }}" alt="">
        @endif
    </div>
    <div class="admin-store-review-identity">
        <div class="admin-store-review-logo">
            @if($store->logo)
                <img src="{{ asset($store->logo) }}" alt="Logo da {{ $store->name }}">
            @else
                <i class="fa-solid fa-store"></i>
            @endif
        </div>
        <div>
            <span>{{ $store->category ?: 'Sem categoria' }}</span>
            <h2>{{ $store->name }}</h2>
            <p><i class="fa-solid fa-location-dot"></i> {{ $store->city ?: 'Sergipe' }}/{{ $store->state ?: 'SE' }}</p>
        </div>
        <div class="admin-store-review-status">
            <span class="{{ $store->active ? 'is-active' : 'is-inactive' }}">{{ $store->active ? 'Ativa' : 'Desativada' }}</span>
            <span class="{{ $store->moderation_status === 'approved' ? 'is-approved' : 'is-suspended' }}">{{ $statusLabel }}</span>
            @if($store->isCurrentlyFeatured())
                <span class="is-featured"><i class="fa-solid fa-star"></i> Em destaque</span>
            @endif
        </div>
    </div>
</section>

<section class="admin-store-review-metrics">
    <article><i class="fa-solid fa-box"></i><div><strong>{{ $store->products_count }}</strong><small>Produtos vinculados</small></div></article>
    <article><i class="fa-solid fa-star"></i><div><strong>{{ $store->approved_reviews_average ? number_format($store->approved_reviews_average, 1, ',', '.') : '—' }}</strong><small>{{ $store->reviews_count }} avaliações totais</small></div></article>
    <article><i class="fa-solid fa-calendar"></i><div><strong>{{ $store->created_at->format('d/m/Y') }}</strong><small>Data do cadastro</small></div></article>
    <article><i class="fa-solid fa-clock-rotate-left"></i><div><strong>{{ $store->moderated_at?->format('d/m/Y') ?: '—' }}</strong><small>Última moderação</small></div></article>
    <article><i class="fa-solid fa-panorama"></i><div><strong>{{ ($store->banner ? 1 : 0) + $store->additional_banners_count }}</strong><small>Banners cadastrados</small></div></article>
    <article><i class="fa-solid fa-images"></i><div><strong>{{ $store->gallery_images_count }}</strong><small>Fotos na galeria</small></div></article>
</section>

<div class="admin-store-review-layout">
    <div class="admin-store-review-main">
        <section class="admin-store-review-card">
            <div class="admin-store-review-card-heading">
                <div><span>CADASTRO</span><h2>Informações da loja</h2></div>
            </div>
            <p class="admin-store-review-description">{{ $store->description ?: 'A loja não informou uma descrição.' }}</p>
            <dl class="admin-store-review-details">
                <div><dt>Slug público</dt><dd>/{{ $store->slug }}</dd></div>
                <div><dt>Categoria</dt><dd>{{ $store->category ?: 'Não informada' }}</dd></div>
                <div><dt>Cidade</dt><dd>{{ $store->city ?: 'Não informada' }}</dd></div>
                <div><dt>WhatsApp</dt><dd>{{ $store->whatsapp ?: 'Não informado' }}</dd></div>
                <div><dt>Telefone</dt><dd>{{ $store->phone ?: 'Não informado' }}</dd></div>
                <div><dt>Instagram</dt><dd>{{ $store->instagram ?: 'Não informado' }}</dd></div>
                <div><dt>Site</dt><dd>{{ $store->website ?: 'Não informado' }}</dd></div>
            </dl>
        </section>

        <section class="admin-store-review-card">
            <div class="admin-store-review-card-heading">
                <div><span>PRODUTOS</span><h2>Publicações vinculadas</h2></div>
                <strong>{{ $store->ads->count() }} exibidos</strong>
            </div>
            <div class="admin-store-review-products">
                @forelse($store->ads as $product)
                    <article>
                        <div>
                            @if($product->card_image)
                                <img src="{{ asset($product->card_image) }}" alt="">
                            @else
                                <i class="fa-solid fa-image"></i>
                            @endif
                        </div>
                        <span>
                            <strong>{{ $product->title }}</strong>
                            <small>R$ {{ number_format($product->price, 2, ',', '.') }} · {{ ucfirst($product->status) }}</small>
                        </span>
                        <a href="{{ route('ad.show', $product->slug) }}" target="_blank" title="Abrir produto"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    </article>
                @empty
                    <div class="admin-store-review-empty">Nenhum produto vinculado a esta loja.</div>
                @endforelse
            </div>
        </section>

        <section class="admin-store-review-card">
            <div class="admin-store-review-card-heading">
                <div><span>REPUTAÇÃO</span><h2>Avaliações recentes</h2></div>
                <a href="{{ route('admin.reviews', ['status' => 'approved']) }}">Ver moderação de avaliações</a>
            </div>
            <div class="admin-store-review-reviews">
                @forelse($store->reviews as $review)
                    <article>
                        <div>
                            <strong>{{ $review->user?->name ?: 'Usuário removido' }}</strong>
                            <span>
                                @foreach(range(1, 5) as $star)<i class="fa-{{ $star <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>@endforeach
                                {{ number_format($review->rating, 1, ',', '.') }}
                            </span>
                            <small>{{ $review->created_at->format('d/m/Y H:i') }} · {{ ucfirst($review->status) }}</small>
                        </div>
                        <p>{{ $review->comment }}</p>
                    </article>
                @empty
                    <div class="admin-store-review-empty">Esta loja ainda não recebeu avaliações.</div>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="admin-store-review-sidebar">
        <section class="admin-store-review-card">
            <div class="admin-store-review-card-heading">
                <div><span>RESPONSÁVEL</span><h2>Proprietário</h2></div>
            </div>
            <div class="admin-store-review-owner">
                <span>{{ strtoupper(substr($store->user?->name ?: '?', 0, 1)) }}</span>
                <div>
                    <strong>{{ $store->user?->name ?: 'Conta removida' }}</strong>
                    <small>{{ $store->user?->email ?: 'Sem e-mail' }}</small>
                    <small>{{ $store->user?->city ?: 'Cidade não informada' }}</small>
                    <small>Plano {{ $store->user?->subscriptionPlanLabel() ?: 'não identificado' }}</small>
                </div>
            </div>
        </section>

        <section class="admin-store-review-card admin-store-moderation-card">
            <div class="admin-store-review-card-heading">
                <div><span>MODERAÇÃO</span><h2>Aplicar ação</h2></div>
            </div>
            <form method="POST" action="{{ route('admin.stores.action', $store) }}">
                @csrf
                <label>
                    <span>Ação administrativa</span>
                    <select name="action" id="store-moderation-action" required>
                        <option value="">Escolha uma ação</option>
                        @if($store->moderation_status !== 'approved')
                            <option value="approve">Aprovar e publicar</option>
                        @endif
                        @if($store->moderation_status !== 'suspended')
                            <option value="suspend">Suspender pela moderação</option>
                        @endif
                        @if($store->active)
                            <option value="deactivate">Desativar temporariamente</option>
                        @elseif($store->moderation_status === 'approved')
                            <option value="activate">Ativar loja</option>
                        @endif
                        @if($store->isCurrentlyFeatured())
                            <option value="unfeature">Remover dos destaques</option>
                        @elseif($store->active && $store->isModerationApproved() && $store->user?->canHaveFeaturedStore())
                            <option value="feature">Colocar em destaque</option>
                        @endif
                    </select>
                </label>
                @if(!$store->isCurrentlyFeatured() && $store->user?->canHaveFeaturedStore())
                    <label id="store-featured-days-field" hidden>
                        <span>Duração do destaque</span>
                        <select name="featured_days">
                            <option value="7">7 dias</option>
                            <option value="15">15 dias</option>
                            <option value="30" selected>30 dias</option>
                            <option value="60">60 dias</option>
                            <option value="90">90 dias</option>
                        </select>
                        <small>O destaque expira automaticamente ao final do período.</small>
                    </label>
                @elseif(!$store->user?->canHaveFeaturedStore())
                    <div class="admin-store-featured-note">
                        <i class="fa-solid fa-lock"></i>
                        Destaque disponível no plano Ouro.
                    </div>
                @endif
                <label>
                    <span>Observação administrativa</span>
                    <textarea name="moderation_note" id="store-moderation-note" rows="5" maxlength="2000" placeholder="Explique o motivo da suspensão ou registre a análise realizada.">{{ old('moderation_note', $store->moderation_note) }}</textarea>
                    <small>Obrigatória ao suspender. O proprietário poderá visualizar esta observação.</small>
                </label>
                <button type="submit" onclick="return confirm('Aplicar esta ação à loja?');">
                    <i class="fa-solid fa-shield-halved"></i> Confirmar ação
                </button>
            </form>
            @if($store->moderated_at)
                <div class="admin-store-last-action">
                    <strong>Última ação</strong>
                    <span>{{ $store->moderated_at->format('d/m/Y H:i') }}</span>
                    <span>por {{ $store->moderator?->name ?: 'administrador removido' }}</span>
                    @if($store->moderation_note)<p>{{ $store->moderation_note }}</p>@endif
                </div>
            @endif
        </section>
    </aside>
</div>

@push('styles')
<style>
    .admin-store-back { display: inline-flex; align-items: center; gap: 6px; color: #2563eb; font-size: .75rem; font-weight: 750; text-decoration: none; }
    .admin-store-review-hero {
        overflow: hidden;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .05);
    }
    .admin-store-review-cover {
        height: 170px;
        overflow: hidden;
        background: linear-gradient(135deg, #0e3d91, #1265f5);
    }
    .admin-store-review-cover img { width: 100%; height: 100%; object-fit: cover; }
    .admin-store-review-identity {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        padding: 17px 22px;
    }
    .admin-store-review-logo {
        width: 88px;
        height: 88px;
        display: grid;
        place-items: center;
        flex: 0 0 88px;
        margin-top: -55px;
        overflow: hidden;
        color: #2563eb;
        background: #fff;
        border: 4px solid #fff;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .16);
        font-size: 1.6rem;
    }
    .admin-store-review-logo img { width: 100%; height: 100%; object-fit: contain; }
    .admin-store-review-identity > div:nth-child(2) { min-width: 0; flex: 1; }
    .admin-store-review-identity > div:nth-child(2) > span { color: #2563eb; font-size: .66rem; font-weight: 800; text-transform: uppercase; }
    .admin-store-review-identity h2 { margin: 2px 0; color: var(--foreground); font-size: 1.25rem; font-weight: 850; }
    .admin-store-review-identity p { margin: 0; color: var(--muted-foreground); font-size: .74rem; }
    .admin-store-review-status { display: flex; gap: 6px; }
    .admin-store-review-status span { padding: 6px 9px; border-radius: 999px; font-size: .62rem; font-weight: 800; }
    .admin-store-review-status .is-active,
    .admin-store-review-status .is-approved { color: #117748; background: rgba(17, 119, 72, .1); }
    .admin-store-review-status .is-inactive { color: #64748b; background: rgba(100, 116, 139, .12); }
    .admin-store-review-status .is-suspended { color: #c52d3e; background: rgba(197, 45, 62, .1); }
    .admin-store-review-status .is-featured { color: #8a5b00; background: rgba(245, 184, 0, .16); }
    .admin-store-review-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin: 16px 0;
    }
    .admin-store-review-metrics article {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
    }
    .admin-store-review-metrics i { width: 36px; height: 36px; display: grid; place-items: center; color: #2563eb; background: rgba(37,99,235,.1); border-radius: 10px; }
    .admin-store-review-metrics div { display: grid; }
    .admin-store-review-metrics strong { color: var(--foreground); font-size: .94rem; }
    .admin-store-review-metrics small { color: var(--muted-foreground); font-size: .64rem; }
    .admin-store-review-layout { display: grid; grid-template-columns: minmax(0, 1fr) 330px; align-items: start; gap: 16px; }
    .admin-store-review-main,
    .admin-store-review-sidebar { display: grid; gap: 16px; min-width: 0; }
    .admin-store-review-sidebar { position: sticky; top: 16px; }
    .admin-store-review-card {
        padding: 20px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 7px 22px rgba(15, 23, 42, .04);
    }
    .admin-store-review-card-heading { display: flex; align-items: end; justify-content: space-between; gap: 1rem; margin-bottom: 14px; }
    .admin-store-review-card-heading span { color: #2563eb; font-size: .62rem; font-weight: 850; letter-spacing: .08em; }
    .admin-store-review-card-heading h2 { margin: 2px 0 0; color: var(--foreground); font-size: 1rem; font-weight: 850; }
    .admin-store-review-card-heading > strong,
    .admin-store-review-card-heading > a { color: #2563eb; font-size: .65rem; font-weight: 750; }
    .admin-store-review-description { color: var(--muted-foreground); font-size: .78rem; line-height: 1.65; white-space: pre-line; }
    .admin-store-review-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 18px; margin: 0; }
    .admin-store-review-details > div { display: flex; justify-content: space-between; gap: 1rem; padding: 10px 0; border-top: 1px solid var(--border); font-size: .7rem; }
    .admin-store-review-details dt { color: var(--muted-foreground); font-weight: 500; }
    .admin-store-review-details dd { margin: 0; overflow-wrap: anywhere; text-align: right; font-weight: 700; }
    .admin-store-review-products { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    .admin-store-review-products article { display: grid; grid-template-columns: 48px minmax(0, 1fr) auto; align-items: center; gap: 9px; padding: 8px; background: var(--muted-bg); border: 1px solid var(--border); border-radius: 11px; }
    .admin-store-review-products article > div { width: 48px; height: 48px; display: grid; place-items: center; overflow: hidden; color: #2563eb; background: var(--card); border-radius: 8px; }
    .admin-store-review-products img { width: 100%; height: 100%; object-fit: cover; }
    .admin-store-review-products article > span { display: grid; min-width: 0; }
    .admin-store-review-products strong { overflow: hidden; color: var(--foreground); font-size: .69rem; text-overflow: ellipsis; white-space: nowrap; }
    .admin-store-review-products small { color: var(--muted-foreground); font-size: .6rem; }
    .admin-store-review-products a { color: #2563eb; }
    .admin-store-review-reviews { display: grid; gap: 8px; }
    .admin-store-review-reviews article { padding: 12px; background: var(--muted-bg); border: 1px solid var(--border); border-radius: 11px; }
    .admin-store-review-reviews article > div { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .admin-store-review-reviews strong { color: var(--foreground); font-size: .72rem; }
    .admin-store-review-reviews span { color: #f5b800; font-size: .65rem; }
    .admin-store-review-reviews small { margin-left: auto; color: var(--muted-foreground); font-size: .6rem; }
    .admin-store-review-reviews p { margin: 7px 0 0; color: var(--muted-foreground); font-size: .69rem; line-height: 1.5; }
    .admin-store-review-empty { grid-column: 1 / -1; padding: 25px; color: var(--muted-foreground); background: var(--muted-bg); border-radius: 11px; text-align: center; font-size: .72rem; }
    .admin-store-review-owner { display: flex; align-items: center; gap: 10px; }
    .admin-store-review-owner > span { width: 42px; height: 42px; display: grid; place-items: center; flex: 0 0 42px; color: #fff; background: #2563eb; border-radius: 50%; font-weight: 800; }
    .admin-store-review-owner > div { display: grid; min-width: 0; }
    .admin-store-review-owner strong { color: var(--foreground); font-size: .76rem; }
    .admin-store-review-owner small { color: var(--muted-foreground); font-size: .63rem; }
    .admin-store-moderation-card form { display: grid; gap: 12px; }
    .admin-store-moderation-card label > span { display: block; margin-bottom: 5px; color: var(--foreground); font-size: .68rem; font-weight: 750; }
    .admin-store-moderation-card select,
    .admin-store-moderation-card textarea { width: 100%; padding: 10px; color: var(--foreground); background: var(--muted-bg); border: 1px solid var(--border); border-radius: 10px; font-size: .7rem; }
    .admin-store-moderation-card label small { display: block; margin-top: 5px; color: var(--muted-foreground); font-size: .59rem; line-height: 1.4; }
    .admin-store-featured-note { padding: 10px; color: #8a5b00; background: rgba(245, 184, 0, .12); border-radius: 10px; font-size: .66rem; font-weight: 700; }
    .admin-store-moderation-card form > button { min-height: 42px; color: #fff; background: #2563eb; border: 0; border-radius: 11px; font-size: .72rem; font-weight: 800; }
    .admin-store-last-action { display: grid; gap: 3px; margin-top: 15px; padding-top: 14px; color: var(--muted-foreground); border-top: 1px solid var(--border); font-size: .62rem; }
    .admin-store-last-action strong { color: var(--foreground); }
    .admin-store-last-action p { margin: 6px 0 0; padding: 8px; background: var(--muted-bg); border-radius: 8px; white-space: pre-line; }
    @media (max-width: 991.98px) {
        .admin-store-review-layout { grid-template-columns: 1fr; }
        .admin-store-review-sidebar { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .admin-store-review-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-store-review-products,
        .admin-store-review-details { grid-template-columns: 1fr; }
    }
    @media (max-width: 575.98px) {
        .admin-store-review-cover { height: 120px; }
        .admin-store-review-identity { align-items: flex-end; padding: 12px; }
        .admin-store-review-logo { width: 68px; height: 68px; flex-basis: 68px; margin-top: -38px; }
        .admin-store-review-identity h2 { font-size: .95rem; }
        .admin-store-review-status { flex-direction: column; }
        .admin-store-review-sidebar { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const action = document.getElementById('store-moderation-action');
        const note = document.getElementById('store-moderation-note');
        const featuredDays = document.getElementById('store-featured-days-field');
        if (!action || !note) return;

        const syncRequiredNote = () => {
            note.required = action.value === 'suspend';
            if (featuredDays) {
                featuredDays.hidden = action.value !== 'feature';
            }
        };
        action.addEventListener('change', syncRequiredNote);
        syncRequiredNote();
    });
</script>
@endpush
@endsection
