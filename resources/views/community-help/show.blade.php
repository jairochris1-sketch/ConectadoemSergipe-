@extends('layouts.app')
@section('title', $helpRequest->title.' - Pedidos locais')
@push('styles')<link rel="stylesheet" href="{{ asset('css/community-help.css') }}?v=1">@endpush
@section('content')
@php
    $statusLabels = ['pending'=>'Em análise','open'=>'Aberto','in_progress'=>'Em atendimento','resolved'=>'Resolvido','rejected'=>'Precisa de ajustes'];
    $myResponse = auth()->check() ? $helpRequest->responses->firstWhere('user_id', auth()->id()) : null;
@endphp
<main class="help-page">
    <div class="help-shell">
        <nav class="help-topnav"><a class="help-back" href="{{ route('community-help.index') }}"><i class="fa-solid fa-arrow-left"></i> Pedidos locais</a><div class="help-nav-links">@auth<a class="help-nav-link" href="{{ route('community-help.index', ['scope'=>'mine']) }}">Meus pedidos</a>@endauth</div></nav>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($helpRequest->status === 'pending')<div class="help-alert is-warning"><strong>Este pedido está em análise.</strong> Somente você e a administração conseguem vê-lo até a aprovação. @auth @if($helpRequest->user_id === auth()->id())<a href="{{ route('community-help.edit', $helpRequest) }}">Revisar informações</a>@endif @endauth</div>@endif
        @if($helpRequest->status === 'rejected')<div class="help-alert is-danger"><strong>O pedido precisa de ajustes.</strong> {{ $helpRequest->moderation_reason ?: 'Revise as informações antes de reenviar.' }} @auth @if($helpRequest->user_id === auth()->id())<a href="{{ route('community-help.edit', $helpRequest) }}">Corrigir pedido</a>@endif @endauth</div>@endif

        <div class="help-detail-layout">
            <div>
                <article class="help-panel help-detail">
                    <header class="help-detail-head">
                        <div class="help-detail-meta"><span class="help-chip {{ $helpRequest->urgency === 'urgent' ? 'is-urgent' : ($helpRequest->urgency === 'today' ? 'is-today' : '') }}"><i class="fa-solid fa-clock"></i> {{ $urgencies[$helpRequest->urgency] }}</span><span class="help-chip">{{ $categories[$helpRequest->category] }}</span><span class="help-status help-status-{{ $helpRequest->status }}">{{ $statusLabels[$helpRequest->status] }}</span></div>
                        <h1>{{ $helpRequest->title }}</h1>
                        <div class="help-card-meta"><span><i class="fa-solid fa-location-dot"></i> {{ $helpRequest->neighborhood }}, {{ $helpRequest->city }}</span><span>·</span><span>{{ $helpRequest->published_at?->locale('pt_BR')->diffForHumans() ?? $helpRequest->created_at->locale('pt_BR')->diffForHumans() }}</span></div>
                    </header>
                    <div class="help-detail-body">
                        <div class="help-description">{{ $helpRequest->description }}</div>
                        <div class="help-owner">
                            @if($helpRequest->user->avatar)<img class="help-avatar" src="{{ asset($helpRequest->user->avatar) }}" alt="">@else<div class="help-avatar help-avatar-placeholder">{{ mb_strtoupper(mb_substr($helpRequest->user->name, 0, 1)) }}</div>@endif
                            <div><strong>{{ $helpRequest->user->name }}</strong><span>Membro da comunidade</span></div>
                        </div>
                    </div>
                </article>

                @auth @if($helpRequest->user_id === auth()->id() || auth()->user()->role === 'admin')
                    @if(in_array($helpRequest->status, ['open','in_progress','resolved'], true))
                    <section class="help-panel mt-3"><div class="help-panel-body"><h2 class="h5 fw-bold">Atualizar andamento</h2><p class="text-muted small">Mostre à comunidade se você ainda precisa de ajuda.</p><form class="help-actions" action="{{ route('community-help.status', $helpRequest) }}" method="POST">@csrf @method('PATCH')<button class="help-action" name="status" value="open">Reabrir</button><button class="help-action is-primary" name="status" value="in_progress">Em atendimento</button><button class="help-action is-success" name="status" value="resolved"><i class="fa-solid fa-check"></i> Resolvido</button></form></div></section>
                    @endif
                @endif @endauth

                <section class="help-panel mt-3" id="respostas"><div class="help-panel-body"><h2 class="h5 fw-bold mb-1">Respostas da comunidade</h2><p class="text-muted small mb-3">{{ $helpRequest->responses->count() }} pessoa(s) responderam.</p>
                    @forelse($helpRequest->responses as $response)
                        <article class="help-response {{ $response->status === 'hidden' ? 'is-hidden' : '' }}">
                            <div class="help-response-head">
                                <strong>{{ $response->user->name }}
                                    @if($response->is_selected)<span class="badge bg-success ms-1"><i class="fa-solid fa-check"></i> Ajudou a resolver</span>@endif
                                    @if($response->status === 'hidden')<span class="badge bg-secondary ms-1"><i class="fa-solid fa-eye-slash"></i> Oculta</span>@endif
                                    @if(auth()->user()?->role === 'admin' && $response->reports_count > 0)<span class="badge bg-danger ms-1"><i class="fa-solid fa-flag"></i> {{ $response->reports_count }} denúncia(s)</span>@endif
                                </strong>
                                <small>{{ $response->created_at->locale('pt_BR')->diffForHumans() }}</small>
                            </div>
                            <p>{{ $response->message }}</p>

                            @auth
                                @if($helpRequest->user_id === auth()->id() && $response->status === 'published')
                                    <div class="help-actions mt-3">
                                        <a class="help-action is-primary text-decoration-none" href="{{ route('chat.index', ['with'=>$response->user_id]) }}"><i class="fa-regular fa-comments"></i> Conversar</a>
                                        @if(!$response->is_selected)<form action="{{ route('community-help.responses.select', [$helpRequest, $response]) }}" method="POST">@csrf @method('PATCH')<button class="help-action is-success" type="submit"><i class="fa-solid fa-check"></i> Esta ajuda resolveu</button></form>@endif
                                    </div>
                                @endif

                                @if(auth()->user()->role === 'admin')
                                    <form class="help-response-moderation mt-3" action="{{ route('community-help.responses.moderate', [$helpRequest, $response]) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input class="form-control" name="moderation_reason" maxlength="500" value="{{ $response->moderation_reason }}" placeholder="Motivo da moderação">
                                        <div class="help-actions">
                                            @if($response->status === 'hidden')
                                                <button class="help-action is-primary" name="action" value="restore"><i class="fa-solid fa-rotate-left"></i> Restaurar</button>
                                            @else
                                                <button class="help-action" name="action" value="hide"><i class="fa-solid fa-eye-slash"></i> Ocultar</button>
                                                @if($response->reports_count > 0)<button class="help-action is-primary" name="action" value="dismiss_reports"><i class="fa-solid fa-check"></i> Manter resposta</button>@endif
                                            @endif
                                        </div>
                                    </form>
                                @elseif(auth()->id() !== $response->user_id && $response->status === 'published')
                                    <details class="help-report mt-3">
                                        <summary><i class="fa-regular fa-flag"></i> Denunciar resposta</summary>
                                        <form action="{{ route('community-help.responses.report', [$helpRequest, $response]) }}" method="POST">
                                            @csrf
                                            <select class="form-select" name="reason" required>
                                                <option value="spam">Spam</option><option value="scam">Possível golpe</option><option value="inappropriate">Conteúdo impróprio</option><option value="harassment">Assédio</option><option value="personal_data">Expõe dados pessoais</option><option value="other">Outro motivo</option>
                                            </select>
                                            <textarea class="form-control" name="details" maxlength="700" placeholder="Explique o problema (opcional)"></textarea>
                                            <button class="help-action" type="submit">Enviar denúncia</button>
                                        </form>
                                    </details>
                                @endif
                            @endauth
                        </article>
                    @empty
                        <p class="text-muted">Ainda não há respostas. Compartilhe o pedido com pessoas da região.</p>
                    @endforelse

                    @auth @if($helpRequest->isPubliclyVisible() && in_array($helpRequest->status, ['open','in_progress'], true) && $helpRequest->user_id !== auth()->id())
                    <form class="help-form mt-4" action="{{ route('community-help.respond', $helpRequest) }}" method="POST">@csrf<label for="message">{{ $myResponse ? 'Atualize sua resposta' : 'Você consegue ajudar?' }}</label><textarea class="form-control" id="message" name="message" minlength="10" maxlength="700" required placeholder="Explique como pode ajudar, sem publicar telefone ou dados pessoais.">{{ old('message', $myResponse?->message) }}</textarea><div class="help-submit-row"><button class="help-submit" type="submit"><i class="fa-solid fa-paper-plane"></i> {{ $myResponse ? 'Atualizar resposta' : 'Enviar resposta' }}</button></div></form>
                    @endif @else<p class="small mt-3"><a href="{{ route('login') }}">Entre na sua conta</a> para oferecer ajuda.</p>@endauth
                </div></section>
            </div>

            <aside>
                @auth @if(auth()->user()->role === 'admin' && $helpRequest->status === 'pending')<section class="help-panel help-side-panel mb-3"><h2>Análise administrativa</h2><p class="small text-muted">Confira se o pedido é claro, local e não expõe dados sensíveis.</p><form class="help-moderation" action="{{ route('community-help.moderate', $helpRequest) }}" method="POST">@csrf @method('PATCH')<textarea class="form-control" name="moderation_reason" maxlength="500" placeholder="Orientação ao autor, se necessário"></textarea><div class="help-actions"><button class="help-action is-success" name="action" value="approve"><i class="fa-solid fa-check"></i> Aprovar</button><button class="help-action" name="action" value="reject"><i class="fa-solid fa-rotate-left"></i> Pedir ajustes</button></div></form></section>@endif @endauth
                <section class="help-panel help-side-panel"><h2>Andamento transparente</h2><div class="help-timeline"><div class="help-timeline-item"><span class="help-timeline-icon"><i class="fa-solid fa-paper-plane"></i></span><span>Enviado {{ $helpRequest->created_at->locale('pt_BR')->diffForHumans() }}</span></div>
                    @if($helpRequest->published_at)<div class="help-timeline-item"><span class="help-timeline-icon"><i class="fa-solid fa-shield-check"></i></span><span>Analisado e publicado pela equipe</span></div>@endif
                    @if($helpRequest->responses->isNotEmpty())<div class="help-timeline-item"><span class="help-timeline-icon"><i class="fa-solid fa-people-group"></i></span><span>{{ $helpRequest->responses->count() }} resposta(s) recebida(s)</span></div>@endif
                    @if($helpRequest->resolved_at)<div class="help-timeline-item"><span class="help-timeline-icon"><i class="fa-solid fa-circle-check"></i></span><span>Resolvido {{ $helpRequest->resolved_at->locale('pt_BR')->diffForHumans() }}</span></div>@endif
                </div></section>
            </aside>
        </div>
    </div>
</main>
@endsection
