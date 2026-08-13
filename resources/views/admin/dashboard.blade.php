@extends('layouts.admin')

@section('title', 'Dashboard - Painel Administrativo')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0">Visão Geral do Sistema</h2>
        <p class="text-muted small mb-0">Métricas e atividades recentes da plataforma.</p>
    </div>
</div>

<!-- Cards de Métricas -->
@if($openReportsCount > 0)
    <a href="{{ route('admin.reports') }}" class="alert {{ $criticalReportsCount > 0 ? 'alert-danger' : 'alert-warning' }} d-flex align-items-center justify-content-between text-decoration-none rounded-4 shadow-sm mb-4">
        <span><i class="fa-solid fa-flag me-2"></i><strong>{{ $openReportsCount }} denúncia(s) aguardando análise</strong></span>
        @if($criticalReportsCount > 0)<span class="badge bg-danger">{{ $criticalReportsCount }} urgente(s)</span>@endif
    </a>
@endif

@if($pendingReviewReportsCount > 0)
    <a href="{{ route('admin.reviews') }}" class="alert alert-warning d-flex align-items-center justify-content-between text-decoration-none rounded-4 shadow-sm mb-4">
        <span><i class="fa-solid fa-star me-2"></i><strong>{{ $pendingReviewReportsCount }} denúncia(s) de avaliação aguardando análise</strong></span>
        <i class="fa-solid fa-arrow-right"></i>
    </a>
@endif

@if($pendingOrdersCount > 0)
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="alert alert-primary d-flex align-items-center justify-content-between text-decoration-none rounded-4 shadow-sm mb-4">
        <span><i class="fa-solid fa-receipt me-2"></i><strong>{{ $pendingOrdersCount }} pedido(s) aguardando confirmação</strong></span>
        <i class="fa-solid fa-arrow-right"></i>
    </a>
@endif

@if($pendingHelpRequestsCount > 0 || $reportedHelpRequestsCount > 0)
    <a href="{{ route('admin.community-help.index', $reportedHelpRequestsCount > 0 ? ['reported' => 1] : ['status' => 'pending']) }}" class="alert {{ $reportedHelpRequestsCount > 0 ? 'alert-danger' : 'alert-info' }} d-flex align-items-center justify-content-between text-decoration-none rounded-4 shadow-sm mb-4">
        <span><i class="fa-solid fa-hand-holding-heart me-2"></i><strong>{{ $pendingHelpRequestsCount }} pedido(s) para analisar</strong>@if($reportedHelpRequestsCount > 0) · {{ $reportedHelpRequestsCount }} com denúncia(s)@endif</span>
        <i class="fa-solid fa-arrow-right"></i>
    </a>
@endif

@php $operationalAttentionCount = $pendingAdsCount + $pendingClaimsCount + $pendingFeedPostsCount + $reportedFeedPostsCount + $suspendedUsersCount; @endphp
@if($operationalAttentionCount > 0)
    <section class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div><h3 class="h6 fw-bold text-dark mb-1"><i class="fa-solid fa-list-check text-primary me-2"></i>Central de pendências</h3><p class="text-muted small mb-0">Acesso direto aos pontos que exigem atenção administrativa.</p></div>
            <span class="badge bg-danger rounded-pill">{{ $operationalAttentionCount }}</span>
        </div>
        <div class="row g-2">
            @if($pendingAdsCount)<div class="col-12 col-sm-6 col-lg-4 col-xxl"><a class="btn btn-outline-warning w-100 text-start" href="{{ route('admin.ads',['status'=>'pending']) }}"><i class="fa-solid fa-rectangle-ad me-2"></i>{{ $pendingAdsCount }} anúncio(s) pendente(s)</a></div>@endif
            @if($pendingClaimsCount)<div class="col-12 col-sm-6 col-lg-4 col-xxl"><a class="btn btn-outline-primary w-100 text-start" href="{{ route('admin.provider_claims.index',['status'=>'pending']) }}"><i class="fa-solid fa-user-check me-2"></i>{{ $pendingClaimsCount }} reivindicação(ões)</a></div>@endif
            @if($pendingFeedPostsCount)<div class="col-12 col-sm-6 col-lg-4 col-xxl"><a class="btn btn-outline-info w-100 text-start" href="{{ route('admin.feed.index',['status'=>'pending']) }}"><i class="fa-solid fa-users me-2"></i>{{ $pendingFeedPostsCount }} publicação(ões)</a></div>@endif
            @if($reportedFeedPostsCount)<div class="col-12 col-sm-6 col-lg-4 col-xxl"><a class="btn btn-outline-danger w-100 text-start" href="{{ route('admin.feed.index',['status'=>'reported']) }}"><i class="fa-solid fa-flag me-2"></i>{{ $reportedFeedPostsCount }} publicação(ões) denunciada(s)</a></div>@endif
            @if($suspendedUsersCount)<div class="col-12 col-sm-6 col-lg-4 col-xxl"><a class="btn btn-outline-secondary w-100 text-start" href="{{ route('admin.users',['account_status'=>'suspended']) }}"><i class="fa-solid fa-user-slash me-2"></i>{{ $suspendedUsersCount }} conta(s) suspensa(s)</a></div>@endif
        </div>
    </section>
@endif

<div class="row g-3 mb-5">
    <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
        <a href="{{ route('admin.users') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white h-100">
                <h6 class="text-white-50">Usuários Cadastrados</h6>
                <h2 class="fw-bold mb-0">{{ $usersCount }}</h2>
                <small class="text-white opacity-75 mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> Gerenciar Usuários</small>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
        <a href="{{ route('admin.ads') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-success text-white h-100">
                <h6 class="text-white-50">Anúncios Ativos</h6>
                <h2 class="fw-bold mb-0">{{ $activeAdsCount }}</h2>
                <small class="text-white opacity-75 mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> Gerenciar Anúncios</small>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
        <a href="{{ route('admin.ads') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-warning text-dark h-100">
                <h6 class="text-dark opacity-50">Total de Anúncios</h6>
                <h2 class="fw-bold mb-0">{{ $adsCount }}</h2>
                <small class="text-dark opacity-75 mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> Ver Todos</small>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
        <a href="{{ route('admin.categories') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-info text-white h-100">
                <h6 class="text-white-50">Categorias</h6>
                <h2 class="fw-bold mb-0">{{ $categoriesCount }}</h2>
                <small class="text-white opacity-75 mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> Gerenciar Categorias</small>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
        <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-dark text-white h-100">
                <h6 class="text-white-50">Pedidos</h6>
                <h2 class="fw-bold mb-0">{{ $ordersCount }}</h2>
                <small class="text-white opacity-75 mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> {{ $pendingOrdersCount }} aguardando confirmação</small>
            </div>
        </a>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-xxl-2">
        <a href="{{ route('admin.culture.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-secondary text-white h-100">
                <h6 class="text-white-50">Obras culturais</h6>
                <h2 class="fw-bold mb-0">{{ $cultureWorksCount }}</h2>
                <small class="text-white opacity-75 mt-2 d-block"><i class="fa-solid fa-arrow-right me-1"></i> Gerenciar Cultura</small>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 g-lg-4">
    <!-- Últimos Anúncios -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-body mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Últimos Anúncios</h5>
                <a href="{{ route('admin.ads') }}" class="btn btn-sm btn-outline-primary rounded-pill">Ver Todos</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Anúncio</th>
                            <th>Valor</th>
                            <th>Cidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAds as $ad)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $ad->title }}</td>
                            <td class="text-success fw-bold">R$ {{ number_format($ad->price, 2, ',', '.') }}</td>
                            <td>{{ $ad->city }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">Nenhum anúncio cadastrado ainda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Usuários Recentes -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header border-0 py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-body mb-0"><i class="fa-solid fa-user-plus text-success me-2"></i> Usuários Recentes</h5>
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary rounded-pill">Ver Todos</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($recentUsers as $u)
                <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">{{ $u->name }}</h6>
                        <small class="text-muted">{{ $u->email }}</small>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $u->city ?? 'Aracaju' }}</span>
                </div>
                @empty
                <div class="p-3 text-center text-muted">Nenhum usuário cadastrado ainda.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
