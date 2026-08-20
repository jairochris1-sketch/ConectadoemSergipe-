@extends('layouts.app')

@section('title', 'Central de Mensagens - Conectado em Sergipe')

@section('content')
<div class="container py-2 py-md-4 chat-page-container">
    <div class="chat-shell border rounded-4 shadow-sm overflow-hidden {{ $activePartner ? 'has-active-conversation' : '' }}">
        <aside class="chat-sidebar border-end">
            <div class="chat-sidebar-header p-3 border-bottom d-flex align-items-center justify-content-between">
                <h1 class="h5 fw-bold mb-0"><i class="fa-solid fa-comments text-primary me-2"></i>Mensagens</h1>
                @if($conversations->count() > 0)
                    <span class="badge chat-count-badge border rounded-pill">{{ $conversations->count() }}</span>
                @endif
            </div>

            <div class="list-group list-group-flush chat-conversations-list">
                @forelse($conversations as $conversation)
                    <a
                        href="{{ route('chat.index', ['with' => $conversation['user']->id]) }}"
                        class="list-group-item list-group-item-action p-3 {{ $activePartner?->id === $conversation['user']->id ? 'active' : '' }}"
                    >
                        <div class="d-flex align-items-center gap-2">
                            <div class="chat-avatar flex-shrink-0">
                                @if($conversation['user']->avatar)
                                    <img src="{{ asset($conversation['user']->avatar) }}" alt="">
                                @else
                                    {{ strtoupper(substr($conversation['user']->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-width-0 flex-grow-1">
                                <div class="d-flex justify-content-between gap-2 align-items-center">
                                    <strong class="text-truncate chat-partner-name">{{ $conversation['user']->name }}</strong>
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
                <header class="chat-header d-flex align-items-center justify-content-between p-3 border-bottom flex-shrink-0">
                    <div class="d-flex align-items-center gap-2 min-width-0">
                        <a href="{{ route('chat.index') }}" class="btn btn-sm btn-light border rounded-pill d-md-none me-1 chat-back-btn" title="Voltar para a lista de conversas" aria-label="Voltar para a lista de conversas">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <div class="chat-avatar flex-shrink-0">
                            @if($activePartner->avatar)
                                <img src="{{ asset($activePartner->avatar) }}" alt="">
                            @else
                                {{ strtoupper(substr($activePartner->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-width-0">
                            <strong class="d-block text-truncate chat-active-name">{{ $activePartner->name }}</strong>
                            <small class="text-muted text-truncate d-block">{{ $activePartner->city ?: 'Sergipe' }}</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-light border rounded-circle chat-options-btn d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mais opções" title="Mais opções">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3 p-1">
                                <li>
                                    <button type="button" class="dropdown-item py-2 rounded-2 text-danger small fw-semibold d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#reportChatModal">
                                        <i class="fa-regular fa-flag text-danger"></i>
                                        <span>Denunciar usuário</span>
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    @if($isBlocking)
                                        <form action="{{ route('chat.unblock', $activePartner) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 rounded-2 text-primary small fw-semibold d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-lock-open text-primary"></i>
                                                <span>Desbloquear usuário</span>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('chat.block', $activePartner) }}" method="POST" onsubmit="return confirm('Deseja realmente bloquear {{ $activePartner->name }}? Você não receberá mais mensagens deste usuário.');">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 rounded-2 text-danger small fw-semibold d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-ban text-danger"></i>
                                                <span>Bloquear usuário</span>
                                            </button>
                                        </form>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <div class="chat-messages p-3 p-md-4" id="chat-messages">
                    @foreach($messages as $message)
                        <div class="chat-message {{ $message->sender_id === auth()->id() ? 'chat-message-own' : '' }}">
                            <div class="chat-bubble">
                                <p class="mb-1">{{ $message->content }}</p>
                                <small>{{ $message->created_at?->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($isBlocking)
                    <div class="chat-blocked-banner p-3 border-top text-center flex-shrink-0">
                        <div class="text-danger small fw-semibold mb-2">
                            <i class="fa-solid fa-ban me-1"></i> Você bloqueou este usuário.
                        </div>
                        <form action="{{ route('chat.unblock', $activePartner) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fa-solid fa-lock-open me-1"></i> Desbloquear para conversar
                            </button>
                        </form>
                    </div>
                @elseif($isBlockedBy)
                    <div class="chat-blocked-banner p-3 border-top text-center flex-shrink-0 text-muted small">
                        <i class="fa-solid fa-lock me-1"></i> Este usuário não está recebendo mensagens no momento.
                    </div>
                @else
                    <form action="{{ route('chat.send') }}" method="POST" class="chat-compose p-3 border-top flex-shrink-0" id="chat-form">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $activePartner->id }}">

                        @if($errors->has('content'))
                            <div class="alert alert-danger py-2 px-3 mb-2 rounded-3 small d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
                                <div>{{ $errors->first('content') }}</div>
                            </div>
                        @endif

                        <div class="chat-input-row d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary chat-send-btn d-flex align-items-center justify-content-center" aria-label="Enviar mensagem" title="Enviar mensagem">
                                <i class="fa-solid fa-arrow-up"></i>
                            </button>
                            <textarea name="content" id="chat-textarea" class="form-control chat-textarea" rows="1" maxlength="2000" required placeholder="Digite sua mensagem...">{{ old('content') }}</textarea>
                        </div>

                        <div class="chat-security-notice mt-2 text-muted small d-flex align-items-center gap-1">
                            <i class="fa-solid fa-shield-halved text-primary"></i>
                            <span>Por segurança, não é permitido o envio de links ou números de telefone no chat.</span>
                        </div>
                    </form>
                @endif
            @else
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-5">
                    <i class="fa-regular fa-comments text-muted display-4 mb-3"></i>
                    <h2 class="h5 fw-bold">Nenhuma conversa selecionada</h2>
                    <p class="text-muted small mb-0">Selecione uma conversa para começar a interagir.</p>
                </div>
            @endif
        </section>
    </div>
</div>

@if($activePartner)
    <!-- Modal Denunciar Usuário no Chat -->
    <div class="modal fade" id="reportChatModal" tabindex="-1" aria-labelledby="reportChatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="{{ route('chat.report', $activePartner) }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="reportChatModalLabel">
                            <i class="fa-regular fa-flag text-danger me-2"></i>Denunciar Usuário
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Sua denúncia é anônima e será analisada por nossa equipe para manter a comunidade segura.
                        </p>
                        <div class="mb-3">
                            <label for="report_reason" class="form-label fw-semibold small">Motivo da denúncia *</label>
                            <select name="reason" id="report_reason" class="form-select rounded-3" required>
                                <option value="" disabled selected>Selecione um motivo...</option>
                                @foreach(\App\Models\Report::CHAT_REASONS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="report_details" class="form-label fw-semibold small">Detalhes adicionais (opcional)</label>
                            <textarea name="details" id="report_details" rows="3" maxlength="1000" class="form-control rounded-3" placeholder="Descreva brevemente o ocorrido..."></textarea>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="block_too" value="1" id="report_block_too" checked>
                            <label class="form-check-label small" for="report_block_too">
                                Bloquear este usuário também (não receber mais mensagens dele)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Enviar denúncia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<style>
    .chat-page-container {
        max-width: 1140px;
    }

    .chat-shell {
        height: min(720px, calc(100dvh - 170px));
        min-height: 520px;
        display: grid;
        grid-template-columns: minmax(240px, 320px) minmax(0, 1fr);
        background: var(--card);
        border-color: var(--border) !important;
        color: var(--foreground);
    }

    .chat-sidebar {
        min-width: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-color: var(--border) !important;
        background: var(--card);
    }

    .chat-sidebar-header {
        background: var(--card);
        border-color: var(--border) !important;
    }

    .chat-count-badge {
        background: var(--muted-bg);
        color: var(--muted);
        border-color: var(--border) !important;
    }

    .chat-conversations-list {
        flex: 1;
        overflow-y: auto;
        background: var(--card);
    }

    .chat-conversations-list .list-group-item {
        background: var(--card);
        color: var(--foreground);
        border-color: var(--border);
        transition: background-color 0.15s ease;
    }

    .chat-conversations-list .list-group-item:hover {
        background: var(--muted-bg);
    }

    .chat-conversations-list .list-group-item.active {
        background: var(--accent) !important;
        color: var(--accent-foreground) !important;
        border-color: var(--border) !important;
    }

    .chat-conversations-list .list-group-item.active .chat-partner-name {
        color: var(--accent-foreground) !important;
    }

    .chat-conversation {
        min-width: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: var(--card);
    }

    .chat-header {
        background: var(--card);
        border-color: var(--border) !important;
    }

    .chat-back-btn {
        background: var(--muted-bg);
        color: var(--foreground);
        border-color: var(--border) !important;
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
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        background: var(--background);
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
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

    .chat-compose {
        background: var(--card);
        border-color: var(--border) !important;
    }

    .chat-send-btn {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 12px;
        font-size: 1.05rem;
        flex-shrink: 0;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }

    .chat-send-btn:hover {
        transform: scale(1.05);
    }

    .chat-textarea {
        border-radius: 12px;
        min-height: 44px;
        resize: none;
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
        background: var(--card);
        color: var(--foreground);
        border-color: var(--border);
    }

    .chat-security-notice {
        font-size: 0.76rem;
        opacity: 0.85;
    }

    .min-width-0 {
        min-width: 0;
    }

    .chat-options-btn {
        width: 36px;
        height: 36px;
        color: var(--foreground);
        background: var(--card);
        border-color: var(--border) !important;
        font-size: 0.95rem;
    }

    .chat-options-btn:hover {
        background: var(--muted-bg);
    }

    .chat-blocked-banner {
        background: var(--muted-bg);
        border-color: var(--border) !important;
    }

    /* Suporte Específico para Dark Mode */
    html[data-theme="dark"] .chat-shell {
        background: #111827;
        border-color: #283548 !important;
        color: #f8fafc;
    }

    html[data-theme="dark"] .chat-sidebar,
    html[data-theme="dark"] .chat-sidebar-header,
    html[data-theme="dark"] .chat-header,
    html[data-theme="dark"] .chat-conversation,
    html[data-theme="dark"] .chat-compose {
        background: #111827 !important;
        border-color: #283548 !important;
        color: #f8fafc;
    }

    html[data-theme="dark"] .chat-options-btn,
    html[data-theme="dark"] .chat-blocked-banner {
        background: #172033 !important;
        color: #f8fafc !important;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .dropdown-menu {
        background-color: #111827 !important;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .dropdown-item {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dropdown-item:hover {
        background-color: #172033;
        color: #f8fafc;
    }

    html[data-theme="dark"] .dropdown-divider {
        border-color: #283548;
    }

    html[data-theme="dark"] .modal-content {
        background-color: #111827 !important;
        color: #f8fafc;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .modal-content .form-select,
    html[data-theme="dark"] .modal-content textarea {
        background-color: #172033 !important;
        color: #f8fafc !important;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .modal-content .form-select option {
        background-color: #111827 !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .modal-content .btn-light {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .modal-content .btn-close {
        filter: invert(1) grayscale(100%) brightness(180%);
    }

    html[data-theme="dark"] .chat-conversations-list {
        background: #111827;
    }

    html[data-theme="dark"] .chat-conversations-list .list-group-item {
        background: #111827;
        color: #f8fafc;
        border-color: #283548;
    }

    html[data-theme="dark"] .chat-conversations-list .list-group-item:hover {
        background: #172033;
    }

    html[data-theme="dark"] .chat-conversations-list .list-group-item.active {
        background: #172554 !important;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .chat-conversations-list .list-group-item.active .chat-partner-name {
        color: #93c5fd !important;
    }

    html[data-theme="dark"] .chat-messages {
        background: #0b1120 !important;
    }

    html[data-theme="dark"] .chat-bubble {
        background: #172033;
        color: #f8fafc;
        border-color: #283548;
    }

    html[data-theme="dark"] .chat-message-own .chat-bubble {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    html[data-theme="dark"] .chat-textarea {
        background: #172033 !important;
        color: #f8fafc !important;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .chat-textarea::placeholder {
        color: #94a3b8 !important;
    }

    html[data-theme="dark"] .chat-avatar {
        background: #1e293b;
        color: #60a5fa;
    }

    html[data-theme="dark"] .chat-count-badge {
        background: #172033;
        color: #94a3b8;
        border-color: #283548 !important;
    }

    html[data-theme="dark"] .chat-back-btn {
        background: #172033;
        color: #f8fafc;
        border-color: #283548 !important;
    }

    @media (max-width: 767.98px) {
        body:has(.chat-shell.has-active-conversation),
        body.chat-active-body {
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
        }

        body:has(.chat-shell.has-active-conversation) .site-header,
        body:has(.chat-shell.has-active-conversation) .site-footer,
        body.chat-active-body .site-header,
        body.chat-active-body .site-footer {
            display: none !important;
        }

        .chat-page-container {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        .chat-shell.has-active-conversation {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            width: 100vw !important;
            height: 100% !important;
            height: 100dvh !important;
            min-height: 0 !important;
            max-height: 100% !important;
            z-index: 1060 !important;
            border-radius: 0 !important;
            border: none !important;
            display: flex !important;
            flex-direction: column !important;
            background: var(--card) !important;
            overflow: hidden !important;
            margin: 0 !important;
        }

        html[data-theme="dark"] .chat-shell.has-active-conversation {
            background: #111827 !important;
        }

        .chat-shell.has-active-conversation .chat-sidebar {
            display: none !important;
        }

        .chat-shell.has-active-conversation .chat-conversation {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
            min-height: 0 !important;
            max-height: 100% !important;
            flex: 1 1 auto !important;
            overflow: hidden !important;
        }

        .chat-shell:not(.has-active-conversation) {
            grid-template-columns: 1fr;
            height: calc(100dvh - 120px);
            min-height: 440px;
            margin: 0.5rem;
            border-radius: 16px;
        }

        .chat-shell:not(.has-active-conversation) .chat-conversation {
            display: none !important;
        }

        .chat-sidebar {
            border-right: 0 !important;
        }

        .chat-messages {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 0.75rem !important;
        }

        .chat-compose {
            flex-shrink: 0 !important;
            padding: 0.5rem 0.75rem !important;
            border-top: 1px solid var(--border) !important;
        }

        .chat-security-notice {
            display: none !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const textarea = document.getElementById('chat-textarea');
    const messagesContainer = document.getElementById('chat-messages');
    const activeShell = document.querySelector('.chat-shell.has-active-conversation');

    if (activeShell) {
        document.body.classList.add('chat-active-body');
    }

    function scrollChatToBottom(smooth = false) {
        if (messagesContainer) {
            if (smooth) {
                messagesContainer.scrollTo({ top: messagesContainer.scrollHeight, behavior: 'smooth' });
            } else {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
    }

    function syncViewport() {
        if (!activeShell || window.innerWidth > 767.98) {
            if (activeShell) {
                activeShell.style.height = '';
                activeShell.style.top = '';
            }
            return;
        }
        if (window.visualViewport) {
            const vv = window.visualViewport;
            activeShell.style.setProperty('height', `${vv.height}px`, 'important');
            activeShell.style.setProperty('top', `${vv.offsetTop}px`, 'important');
        }
        scrollChatToBottom(false);
    }

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', syncViewport);
        window.visualViewport.addEventListener('scroll', syncViewport);
    }

    window.addEventListener('resize', syncViewport);

    if (textarea) {
        textarea.addEventListener('focus', function() {
            setTimeout(syncViewport, 50);
            setTimeout(syncViewport, 200);
            setTimeout(syncViewport, 400);
        });

        textarea.addEventListener('blur', function() {
            setTimeout(syncViewport, 50);
            setTimeout(syncViewport, 200);
        });
    }

    // Inicialização
    syncViewport();

    if (!chatForm || !textarea) return;

    const linkRegex = /(?:https?:\/\/|ftp:\/\/|www\.)\S+|[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.(?:com|br|net|org|io|app|site|online|me|link|top|xyz|gov|edu|info|biz|co|us|uk|tech|store|shop|tv|cc|to|gg|page|club|live|dev)(?:\/[^\s]*)?/i;
    const phoneRegex = /(?:\+?55\s*)?(?:\(?0?[1-9]{2}\)?\s*)?9\s*\d{4}[-\s\.]?\d{4}|(?:\(?0?[1-9]{2}\)?\s*)?[2-8]\d{3}[-\s\.]?\d{4}|(?:\d[\s\.\-_,\(\)\/]*){8,}/;

    chatForm.addEventListener('submit', function(e) {
        const val = textarea.value.trim();
        if (linkRegex.test(val)) {
            e.preventDefault();
            alert('Não é permitido o envio de links ou sites externos pelo chat.');
            textarea.focus();
            return false;
        }
        if (phoneRegex.test(val)) {
            e.preventDefault();
            alert('Não é permitido o envio de números de telefone ou contatos pelo chat.');
            textarea.focus();
            return false;
        }
    });

    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            if (!e.defaultPrevented) {
                chatForm.submit();
            }
        }
    });
});
</script>
@endsection
