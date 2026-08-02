@extends('layouts.app')

@section('title', 'Central de Mensagens - Conectado em Sergipe')

@section('content')
<div class="container py-4 py-md-5">
    <div class="chat-shell bg-white border rounded-4 shadow-sm overflow-hidden">
        <aside class="chat-sidebar border-end">
            <div class="p-3 border-bottom">
                <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-comments text-primary me-2"></i>Mensagens</h1>
            </div>

            <div class="list-group list-group-flush">
                @forelse($conversations as $conversation)
                    <a
                        href="{{ route('chat.index', ['with' => $conversation['user']->id]) }}"
                        class="list-group-item list-group-item-action p-3 {{ $activePartner?->id === $conversation['user']->id ? 'active' : '' }}"
                    >
                        <div class="d-flex align-items-center gap-2">
                            <div class="chat-avatar">
                                @if($conversation['user']->avatar)
                                    <img src="{{ asset($conversation['user']->avatar) }}" alt="">
                                @else
                                    {{ strtoupper(substr($conversation['user']->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-width-0 flex-grow-1">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong class="text-truncate">{{ $conversation['user']->name }}</strong>
                                    @if($conversation['unread'] > 0)
                                        <span class="badge rounded-pill bg-danger">{{ $conversation['unread'] }}</span>
                                    @endif
                                </div>
                                <small class="d-block text-truncate opacity-75">{{ $conversation['latest']?->content }}</small>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted small">Nenhuma conversa iniciada.</div>
                @endforelse
            </div>
        </aside>

        <section class="chat-conversation">
            @if($activePartner)
                <header class="d-flex align-items-center gap-3 p-3 border-bottom">
                    <div class="chat-avatar">
                        @if($activePartner->avatar)
                            <img src="{{ asset($activePartner->avatar) }}" alt="">
                        @else
                            {{ strtoupper(substr($activePartner->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <strong class="d-block">{{ $activePartner->name }}</strong>
                        <small class="text-muted">{{ $activePartner->city ?: 'Sergipe' }}</small>
                    </div>
                </header>

                <div class="chat-messages p-3 p-md-4">
                    @foreach($messages as $message)
                        <div class="chat-message {{ $message->sender_id === auth()->id() ? 'chat-message-own' : '' }}">
                            <div class="chat-bubble">
                                <p class="mb-1">{{ $message->content }}</p>
                                <small>{{ $message->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('chat.send') }}" method="POST" class="chat-compose p-3 border-top">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $activePartner->id }}">
                    <div class="input-group">
                        <textarea name="content" class="form-control" rows="1" maxlength="2000" required placeholder="Digite sua mensagem..."></textarea>
                        <button type="submit" class="btn btn-primary px-3" aria-label="Enviar mensagem">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            @else
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-5">
                    <i class="fa-regular fa-paper-plane text-muted display-4 mb-3"></i>
                    <h2 class="h5 fw-bold">Nenhuma conversa selecionada</h2>
                    <p class="text-muted small mb-0">As mensagens enviadas e recebidas aparecerão aqui.</p>
                </div>
            @endif
        </section>
    </div>
</div>

<style>
    .chat-shell {
        min-height: 560px;
        display: grid;
        grid-template-columns: minmax(230px, 320px) minmax(0, 1fr);
    }

    .chat-sidebar,
    .chat-conversation {
        min-width: 0;
    }

    .chat-conversation {
        display: flex;
        flex-direction: column;
    }

    .chat-avatar {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 50%;
        color: #0d6efd;
        background: #eaf2ff;
        font-weight: 800;
    }

    .chat-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        background: #f8fafc;
    }

    .chat-message {
        display: flex;
        margin-bottom: .75rem;
    }

    .chat-message-own {
        justify-content: flex-end;
    }

    .chat-bubble {
        max-width: min(78%, 560px);
        padding: .65rem .85rem;
        border-radius: 14px 14px 14px 4px;
        background: var(--card);
        color: var(--foreground);
        border: 1px solid var(--border);
        overflow-wrap: anywhere;
    }

    .chat-message-own .chat-bubble {
        color: #fff;
        background: #0d6efd;
        border-color: #0d6efd;
        border-radius: 14px 14px 4px 14px;
    }

    .chat-bubble small {
        font-size: .66rem;
        opacity: .72;
    }

    .chat-compose textarea {
        resize: none;
    }

    .min-width-0 {
        min-width: 0;
    }

    @media (max-width: 767.98px) {
        .chat-shell {
            grid-template-columns: 1fr;
        }

        .chat-sidebar {
            border-right: 0 !important;
            border-bottom: 1px solid var(--border);
            max-height: 230px;
            overflow-y: auto;
        }

        .chat-conversation {
            min-height: 430px;
        }
    }
</style>
@endsection
