@extends('layouts.app')
@section('title', 'Pedidos locais - Comunidade')
@push('styles')<link rel="stylesheet" href="{{ asset('css/community-help.css') }}?v=1">@endpush
@section('content')
<main class="help-page">
    <div class="help-shell">
        <nav class="help-topnav" aria-label="Navegação da comunidade">
            <a class="help-back" href="{{ route('feed.index') }}"><i class="fa-solid fa-arrow-left"></i> Comunidade</a>
            <div class="help-nav-links">
                <a class="help-nav-link is-active" href="{{ route('community-help.index') }}">Pedidos locais</a>
                @auth<a class="help-nav-link" href="{{ route('community-help.index', ['scope' => 'mine']) }}">Meus pedidos</a>@endauth
            </div>
        </nav>

        <section class="help-hero">
            <div class="help-hero-content">
                <span class="help-eyebrow"><i class="fa-solid fa-location-dot"></i> Sergipe se ajudando</span>
                <h1>O que você precisa resolver perto de você?</h1>
                <p>Descreva uma necessidade local e encontre pessoas que possam orientar, indicar ou ajudar. Os pedidos passam por análise antes de aparecer.</p>
                @auth
                    <a class="help-primary-action" href="{{ route('community-help.create') }}"><i class="fa-solid fa-plus"></i> Preciso agora</a>
                @else
                    <a class="help-primary-action" href="{{ route('login') }}"><i class="fa-solid fa-right-to-bracket"></i> Entrar para pedir ajuda</a>
                @endauth
            </div>
            <div class="help-stats" aria-label="Resultados da comunidade">
                <div class="help-stat"><strong>{{ $stats['active'] }}</strong><span>pedidos ativos</span></div>
                <div class="help-stat"><strong>{{ $stats['cities'] }}</strong><span>cidades conectadas</span></div>
                <div class="help-stat"><strong>{{ $stats['resolved'] }}</strong><span>resolvidos em 30 dias</span></div>
            </div>
        </section>

        @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif

        <section class="help-toolbar" aria-label="Filtros dos pedidos">
            <nav class="help-tabs">
                <a class="help-tab {{ $scope === 'all' && request('status') !== 'resolved' ? 'is-active' : '' }}" href="{{ route('community-help.index') }}">Precisam de ajuda</a>
                <a class="help-tab {{ request('status') === 'resolved' ? 'is-active' : '' }}" href="{{ route('community-help.index', ['status' => 'resolved']) }}">Resolvidos</a>
                @auth<a class="help-tab {{ $scope === 'mine' ? 'is-active' : '' }}" href="{{ route('community-help.index', ['scope' => 'mine']) }}">Meus pedidos</a>@endauth
                @auth @if(auth()->user()->role === 'admin')<a class="help-tab {{ $scope === 'moderation' ? 'is-active' : '' }}" href="{{ route('community-help.index', ['scope' => 'moderation']) }}">Aguardando análise</a><a class="help-tab {{ $scope === 'reported' ? 'is-active' : '' }}" href="{{ route('community-help.index', ['scope' => 'reported']) }}">Respostas denunciadas</a>@endif @endauth
            </nav>
            <form class="help-filter" action="{{ route('community-help.index') }}" method="GET">
                @if($scope !== 'all')<input type="hidden" name="scope" value="{{ $scope }}">@endif
                @if(request('status') === 'resolved')<input type="hidden" name="status" value="resolved">@endif
                <input class="form-control help-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Buscar necessidade ou bairro" aria-label="Buscar pedidos">
                <select class="form-select" name="category" aria-label="Filtrar por categoria"><option value="">Todas as categorias</option>@foreach($categories as $value => $label)<option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>@endforeach</select>
                <select class="form-select" name="city" aria-label="Filtrar por cidade"><option value="">Todas as cidades</option>@foreach($cities as $cityName)<option value="{{ $cityName }}" @selected($city === $cityName)>{{ $cityName }}</option>@endforeach</select>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
            </form>
        </section>

        <section class="help-grid" aria-label="Pedidos locais">
            @forelse($helpRequests as $item)
                @php
                    $statusLabels = ['pending'=>'Em análise','open'=>'Aberto','in_progress'=>'Em atendimento','resolved'=>'Resolvido','rejected'=>'Precisa de ajustes'];
                    $urgencyClass = $item->urgency === 'urgent' ? 'is-urgent' : ($item->urgency === 'today' ? 'is-today' : '');
                @endphp
                <a class="help-card" href="{{ route('community-help.show', $item) }}">
                    <div class="help-card-top">
                        <span class="help-chip {{ $urgencyClass }}"><i class="fa-solid {{ $item->urgency === 'urgent' ? 'fa-bolt' : 'fa-clock' }}"></i> {{ $urgencies[$item->urgency] }}</span>
                        <span class="help-status help-status-{{ $item->status }}">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                    </div>
                    <h2>{{ $item->title }}</h2>
                    <p class="help-card-description">{{ str($item->description)->limit(145) }}</p>
                    <div class="help-card-meta"><span class="city-badge" style="font-size: 0.72rem; padding: 1px 8px;"><i class="fa-solid fa-location-dot"></i> {{ $item->neighborhood }}, {{ $item->city }}</span><span>·</span><span>{{ $categories[$item->category] }}</span></div>
                    <div class="help-card-footer"><span>{{ $item->published_at?->locale('pt_BR')->diffForHumans() ?? $item->created_at->locale('pt_BR')->diffForHumans() }}</span><span class="help-card-responses"><i class="fa-regular fa-comments"></i> {{ $item->responses_count }} resposta(s) @if(($item->pending_response_reports_count ?? 0) > 0)<strong class="help-report-count"><i class="fa-solid fa-triangle-exclamation"></i> {{ $item->pending_response_reports_count }} denunciada(s)</strong>@endif</span></div>
                </a>
            @empty
                <div class="help-empty"><i class="fa-regular fa-compass"></i><h2>Nenhum pedido encontrado</h2><p class="text-muted">Ajuste os filtros ou seja a primeira pessoa a pedir ajuda nesta região.</p></div>
            @endforelse
        </section>
        <div class="help-pagination">{{ $helpRequests->links() }}</div>
    </div>
</main>
@endsection
