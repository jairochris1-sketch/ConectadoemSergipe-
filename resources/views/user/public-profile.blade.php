@extends('layouts.app')

@section('title', $user->name . ' - Conectado em Sergipe')

@push('styles')
<style>
.public-profile-page{background:#f4f6f8;min-height:80vh}.public-profile-hero{background:linear-gradient(135deg,#174f91,#0c376c);color:#fff;border-radius:22px}.public-profile-avatar{width:112px;height:112px;object-fit:cover;border:4px solid rgba(255,255,255,.9)}.public-profile-card{border:0;border-radius:18px}.public-profile-official{color:#bfe0ff}.public-profile-link,.public-profile-link:visited{color:#174f91;font-weight:700;text-decoration:none}.public-profile-link:hover,.public-profile-link:focus{color:#0c376c;text-decoration:underline}
</style>
@endpush

@section('content')
<main class="public-profile-page py-4 py-lg-5">
    <div class="container" style="max-width:960px">
        <section class="public-profile-hero shadow-sm p-4 p-lg-5 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4 text-center text-md-start">
                @if($user->avatar)
                    <img src="{{ asset($user->avatar) }}" class="public-profile-avatar rounded-circle" alt="Foto de {{ $user->name }}">
                @else
                    <div class="public-profile-avatar rounded-circle bg-white text-primary d-flex align-items-center justify-content-center display-5 fw-bold">{{ mb_strtoupper(mb_substr($user->name,0,1)) }}</div>
                @endif
                <div>
                    <h1 class="h2 fw-bold mb-1">{{ $user->name }} @if($user->role==='admin')<span class="public-profile-official" title="Conta oficial" aria-label="Conta oficial"><i class="fa-solid fa-circle-check"></i></span>@endif</h1>
                    <div class="mb-2">{{ '@'.$user->username }}</div>
                    <p class="mb-0 opacity-75">
                        @if($user->role === 'admin')
                            Equipe oficial do Conectado em Sergipe
                        @elseif($user->role === 'collaborator')
                            Colaborador do Conectado em Sergipe
                        @else
                            Membro da comunidade
                        @endif
                        @if($user->city) · {{ $user->city }}@endif
                    </p>
                </div>
            </div>
        </section>

        @if($posts->isNotEmpty())
            <section class="card public-profile-card shadow-sm mb-4"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Publicações na comunidade</h2>@foreach($posts as $post)<article class="border-bottom py-3"><a href="{{ route('feed.index').'#publicacao-'.$post->id }}" class="public-profile-link">{{ $post->title ?: \Illuminate\Support\Str::limit($post->body, 90) }}</a><div class="small text-muted mt-1">{{ $post->published_at?->diffForHumans() }}</div></article>@endforeach</div></section>
        @endif

        @if($ads->isNotEmpty() || $stores->isNotEmpty() || $works->isNotEmpty())
            <section class="card public-profile-card shadow-sm"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Perfis e conteúdos públicos</h2><div class="row g-3">
                @foreach($stores as $store)<div class="col-md-6"><a href="{{ route('store.show',$store->slug) }}" class="public-profile-link"><i class="fa-solid fa-store me-2"></i>{{ $store->name }}</a></div>@endforeach
                @foreach($ads as $ad)<div class="col-md-6"><a href="{{ $ad->module==='services' ? route('provider.show',$ad->slug) : route('ad.show',$ad->slug) }}" class="public-profile-link"><i class="fa-solid fa-rectangle-ad me-2"></i>{{ $ad->title }}</a></div>@endforeach
                @foreach($works as $work)<div class="col-md-6"><a href="{{ route('culture.show',$work->slug) }}" class="public-profile-link"><i class="fa-solid fa-book-open me-2"></i>{{ $work->title }}</a></div>@endforeach
            </div></div></section>
        @endif
    </div>
</main>
@endsection
