@extends('layouts.admin')

@section('title', 'Moderação de Lojas - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <span class="admin-store-eyebrow">VITRINE COMERCIAL</span>
        <h1 class="h2 fw-bold text-dark mb-1"><i class="fa-solid fa-store text-primary me-2"></i> Moderação de lojas</h1>
        <p class="text-muted small mb-0">Analise cadastros, produtos, avaliações e a visibilidade das lojas.</p>
    </div>
    <a href="{{ route('stores.index') }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Abrir vitrine
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
@endif

<section class="admin-store-metrics mb-4" aria-label="Resumo das lojas">
    <article>
        <span class="is-blue"><i class="fa-solid fa-store"></i></span>
        <div><strong>{{ $metrics['total'] }}</strong><small>Total de lojas</small></div>
    </article>
    <article>
        <span class="is-green"><i class="fa-solid fa-eye"></i></span>
        <div><strong>{{ $metrics['public'] }}</strong><small>Visíveis ao público</small></div>
    </article>
    <article>
        <span class="is-gray"><i class="fa-solid fa-eye-slash"></i></span>
        <div><strong>{{ $metrics['inactive'] }}</strong><small>Desativadas</small></div>
    </article>
    <article>
        <span class="is-red"><i class="fa-solid fa-shield-halved"></i></span>
        <div><strong>{{ $metrics['suspended'] }}</strong><small>Suspensas</small></div>
    </article>
    <article>
        <span class="is-yellow"><i class="fa-solid fa-star"></i></span>
        <div><strong>{{ $metrics['featured'] }}</strong><small>Em destaque</small></div>
    </article>
</section>

<section class="admin-store-filter-card mb-4">
    <form method="GET" action="{{ route('admin.stores') }}" class="admin-store-filters">
        <label class="admin-store-search">
            <span>Pesquisar</span>
            <div><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Loja, proprietário, e-mail ou slug"></div>
        </label>
        <label>
            <span>Exibição</span>
            <select name="status">
                <option value="">Todas</option>
                <option value="active" @selected($status === 'active')>Ativas</option>
                <option value="inactive" @selected($status === 'inactive')>Desativadas</option>
            </select>
        </label>
        <label>
            <span>Moderação</span>
            <select name="moderation">
                <option value="">Todas</option>
                <option value="approved" @selected($moderation === 'approved')>Aprovadas</option>
                <option value="suspended" @selected($moderation === 'suspended')>Suspensas</option>
            </select>
        </label>
        <label>
            <span>Destaque</span>
            <select name="featured">
                <option value="">Todas</option>
                <option value="yes" @selected($featured === 'yes')>Em destaque</option>
                <option value="no" @selected($featured === 'no')>Sem destaque</option>
            </select>
        </label>
        <label>
            <span>Categoria</span>
            <select name="category">
                <option value="">Todas</option>
                @foreach($categories as $storeCategory)
                    <option value="{{ $storeCategory }}" @selected($category === $storeCategory)>{{ $storeCategory }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Cidade</span>
            <select name="city">
                <option value="">Todas</option>
                @foreach($cities as $storeCity)
                    <option value="{{ $storeCity }}" @selected($city === $storeCity)>{{ $storeCity }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit"><i class="fa-solid fa-filter"></i> Filtrar</button>
        @if($q || $status || $moderation || $featured || $category || $city)
            <a href="{{ route('admin.stores') }}">Limpar filtros</a>
        @endif
    </form>
</section>

<section class="admin-store-table-card">
    <div class="admin-store-table-heading">
        <div>
            <h2>Lojas cadastradas</h2>
            <p>{{ $stores->total() }} {{ $stores->total() === 1 ? 'resultado encontrado' : 'resultados encontrados' }}</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 admin-store-table">
            <thead>
                <tr>
                    <th>Loja</th>
                    <th>Proprietário</th>
                    <th>Indicadores</th>
                    <th>Situação</th>
                    <th>Última análise</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stores as $store)
                    <tr>
                        <td>
                            <div class="admin-store-identity">
                                <div>
                                    @if($store->logo)
                                        <img src="{{ asset($store->logo) }}" alt="">
                                    @else
                                        <i class="fa-solid fa-store"></i>
                                    @endif
                                </div>
                                <span>
                                    <strong>{{ $store->name }}</strong>
                                    <small>{{ $store->category ?: 'Sem categoria' }} · {{ $store->city ?: 'Sergipe' }}</small>
                                    <small>#{{ $store->id }} · /{{ $store->slug }}</small>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="admin-store-owner">
                                <strong>{{ $store->user?->name ?: 'Conta removida' }}</strong>
                                <small>{{ $store->user?->email ?: 'Sem e-mail' }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="admin-store-indicators">
                                <span><i class="fa-solid fa-box"></i> {{ $store->products_count }} produtos</span>
                                <span>
                                    <i class="fa-solid fa-star"></i>
                                    {{ $store->approved_reviews_count ? number_format($store->approved_reviews_average, 1, ',', '.') : 'Sem nota' }}
                                    @if($store->approved_reviews_count) ({{ $store->approved_reviews_count }}) @endif
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="admin-store-statuses">
                                <span class="{{ $store->active ? 'is-active' : 'is-inactive' }}">
                                    {{ $store->active ? 'Ativa' : 'Desativada' }}
                                </span>
                                <span class="{{ $store->moderation_status === 'approved' ? 'is-approved' : 'is-suspended' }}">
                                    {{ $store->moderation_status === 'approved' ? 'Aprovada' : 'Suspensa' }}
                                </span>
                                @if($store->isCurrentlyFeatured())
                                    <span class="is-featured"><i class="fa-solid fa-star"></i> Destaque</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="admin-store-moderation-date">
                                @if($store->moderated_at)
                                    <strong>{{ $store->moderated_at->format('d/m/Y H:i') }}</strong>
                                    <small>{{ $store->moderator?->name ?: 'Administrador removido' }}</small>
                                @else
                                    <span>Sem ação registrada</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="admin-store-actions">
                                <a href="{{ route('admin.stores.show', $store) }}" class="is-primary">
                                    <i class="fa-solid fa-magnifying-glass"></i> Analisar
                                </a>
                                <a href="{{ route('store.show', $store->slug) }}" target="_blank" title="Abrir loja">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="admin-store-empty">
                                <i class="fa-solid fa-store-slash"></i>
                                <strong>Nenhuma loja encontrada</strong>
                                <span>Altere os filtros para ampliar a busca.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($stores->hasPages())
        <div class="admin-store-pagination">{{ $stores->onEachSide(1)->links() }}</div>
    @endif
</section>

@push('styles')
<style>
    .admin-store-eyebrow { color: #2563eb; font-size: .7rem; font-weight: 800; letter-spacing: .08em; }
    .admin-store-metrics {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
    }
    .admin-store-metrics article {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
        padding: 18px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 7px 22px rgba(15, 23, 42, .045);
    }
    .admin-store-metrics article > span {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        flex: 0 0 44px;
        border-radius: 13px;
    }
    .admin-store-metrics .is-blue { color: #2563eb; background: rgba(37, 99, 235, .11); }
    .admin-store-metrics .is-green { color: #159455; background: rgba(21, 148, 85, .11); }
    .admin-store-metrics .is-gray { color: #64748b; background: rgba(100, 116, 139, .12); }
    .admin-store-metrics .is-red { color: #dc3545; background: rgba(220, 53, 69, .11); }
    .admin-store-metrics .is-yellow { color: #b77900; background: rgba(245, 184, 0, .14); }
    .admin-store-metrics article div { display: grid; min-width: 0; }
    .admin-store-metrics strong { color: var(--foreground); font-size: 1.35rem; }
    .admin-store-metrics small { color: var(--muted-foreground); font-size: .72rem; }
    .admin-store-filter-card,
    .admin-store-table-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .045);
    }
    .admin-store-filter-card { padding: 18px; }
    .admin-store-filters {
        display: grid;
        grid-template-columns: minmax(220px, 1.45fr) repeat(5, minmax(115px, .7fr)) auto;
        align-items: end;
        gap: 10px;
    }
    .admin-store-filters label { min-width: 0; }
    .admin-store-filters label > span {
        display: block;
        margin: 0 0 6px 2px;
        color: var(--muted-foreground);
        font-size: .68rem;
        font-weight: 700;
    }
    .admin-store-filters input,
    .admin-store-filters select {
        width: 100%;
        height: 42px;
        padding: 0 11px;
        color: var(--foreground);
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 11px;
        font-size: .76rem;
    }
    .admin-store-search > div { position: relative; }
    .admin-store-search i { position: absolute; top: 50%; left: 13px; color: var(--muted-foreground); transform: translateY(-50%); }
    .admin-store-search input { padding-left: 36px; }
    .admin-store-filters > button {
        height: 42px;
        padding: 0 16px;
        color: #fff;
        background: #2563eb;
        border: 0;
        border-radius: 11px;
        font-size: .76rem;
        font-weight: 750;
    }
    .admin-store-filters > a {
        grid-column: 1 / -1;
        justify-self: end;
        color: #2563eb;
        font-size: .7rem;
        font-weight: 700;
    }
    .admin-store-table-card { overflow: hidden; }
    .admin-store-table-heading { padding: 18px 20px; border-bottom: 1px solid var(--border); }
    .admin-store-table-heading h2 { margin: 0; color: var(--foreground); font-size: 1rem; font-weight: 800; }
    .admin-store-table-heading p { margin: 3px 0 0; color: var(--muted-foreground); font-size: .7rem; }
    .admin-store-table thead th {
        padding: 12px 15px;
        color: var(--muted-foreground);
        background: var(--muted-bg);
        border-color: var(--border);
        font-size: .67rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .admin-store-table tbody td { padding: 14px 15px; color: var(--foreground); background: var(--card); border-color: var(--border); font-size: .76rem; }
    .admin-store-identity { display: flex; align-items: center; gap: 10px; min-width: 230px; }
    .admin-store-identity > div {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        flex: 0 0 48px;
        overflow: hidden;
        color: #2563eb;
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
    }
    .admin-store-identity img { width: 100%; height: 100%; object-fit: contain; }
    .admin-store-identity > span,
    .admin-store-owner,
    .admin-store-indicators,
    .admin-store-moderation-date { display: grid; gap: 3px; min-width: 0; }
    .admin-store-identity strong,
    .admin-store-owner strong,
    .admin-store-moderation-date strong { color: var(--foreground); }
    .admin-store-identity small,
    .admin-store-owner small,
    .admin-store-moderation-date small,
    .admin-store-moderation-date > span { color: var(--muted-foreground); font-size: .66rem; }
    .admin-store-indicators span { color: var(--muted-foreground); font-size: .69rem; white-space: nowrap; }
    .admin-store-indicators i { width: 14px; color: #2563eb; }
    .admin-store-indicators span + span i { color: #f5b800; }
    .admin-store-statuses { display: flex; flex-wrap: wrap; gap: 5px; min-width: 135px; }
    .admin-store-statuses span {
        padding: 5px 8px;
        border-radius: 999px;
        font-size: .61rem;
        font-weight: 800;
    }
    .admin-store-statuses .is-active,
    .admin-store-statuses .is-approved { color: #117748; background: rgba(17, 119, 72, .1); }
    .admin-store-statuses .is-inactive { color: #64748b; background: rgba(100, 116, 139, .12); }
    .admin-store-statuses .is-suspended { color: #c52d3e; background: rgba(197, 45, 62, .1); }
    .admin-store-statuses .is-featured { color: #8a5b00; background: rgba(245, 184, 0, .16); }
    .admin-store-actions { display: flex; justify-content: flex-end; gap: 6px; }
    .admin-store-actions a {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 0 10px;
        color: var(--foreground);
        border: 1px solid var(--border);
        border-radius: 9px;
        font-size: .68rem;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
    }
    .admin-store-actions a.is-primary { color: #fff; background: #2563eb; border-color: #2563eb; }
    .admin-store-empty { display: grid; place-items: center; gap: 5px; padding: 40px; color: var(--muted-foreground); }
    .admin-store-empty i { margin-bottom: 5px; color: #2563eb; font-size: 2rem; }
    .admin-store-empty strong { color: var(--foreground); }
    .admin-store-pagination { padding: 17px 20px; border-top: 1px solid var(--border); }
    @media (max-width: 1399.98px) {
        .admin-store-metrics { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .admin-store-filters { grid-template-columns: repeat(3, minmax(150px, 1fr)); }
    }
    @media (max-width: 991.98px) {
        .admin-store-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-store-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .admin-store-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-store-search { grid-column: 1 / -1; }
        .admin-store-filters > button { grid-column: 1 / -1; }
    }
    @media (max-width: 479.98px) {
        .admin-store-metrics { grid-template-columns: 1fr; gap: 8px; }
        .admin-store-metrics article { padding: 12px; }
        .admin-store-metrics article > span { width: 36px; height: 36px; flex-basis: 36px; }
        .admin-store-metrics strong { font-size: 1.05rem; }
        .admin-store-filters { grid-template-columns: 1fr; }
        .admin-store-search,
        .admin-store-filters > button { grid-column: auto; }
    }
</style>
@endpush
@endsection
