@extends('layouts.admin')

@section('title', 'Central de Multiatendimento & Suporte ao Vivo')

@push('styles')
<style>
    .support-layout {
        display: grid;
        grid-template-columns: 340px 1fr 300px;
        gap: 1.25rem;
        height: calc(100vh - 170px);
        min-height: 580px;
    }

    /* Coluna 1: Filas e Conversas */
    .support-sidebar-panel {
        background: var(--card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    .support-tabs-nav {
        display: flex;
        border-bottom: 1px solid var(--border, #e2e8f0);
        background: var(--bg-hover, #f8fafc);
        padding: 6px;
        gap: 4px;
    }

    .support-tab-btn {
        flex: 1;
        padding: 8px 6px;
        font-size: 0.76rem;
        font-weight: 700;
        border: none;
        background: transparent;
        color: var(--text-muted, #64748b);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .support-tab-btn.active {
        background: #ffffff;
        color: #075be8;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    [data-bs-theme="dark"] .support-tab-btn.active {
        background: #1e293b;
        color: #60a5fa;
    }

    .support-ticket-list {
        flex: 1;
        overflow-y: auto;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .support-ticket-item {
        padding: 12px;
        border-radius: 14px;
        border: 1px solid var(--border, #f1f5f9);
        background: var(--card, #ffffff);
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        text-align: left;
    }

    .support-ticket-item:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .support-ticket-item.active {
        border-color: #075be8;
        background: rgba(7, 91, 232, 0.04);
        box-shadow: 0 4px 12px rgba(7, 91, 232, 0.10);
    }

    .support-ticket-item.is-waiting {
        border-left: 4px solid #f59e0b;
    }

    .support-ticket-item.is-active-chat {
        border-left: 4px solid #10b981;
    }

    /* Coluna 2: Chat */
    .support-chat-panel {
        background: var(--card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 18px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    .support-chat-header {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border, #e2e8f0);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-hover, #f8fafc);
    }

    .support-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8fafc;
    }

    [data-bs-theme="dark"] .support-chat-messages {
        background: #0b132b;
    }

    .support-msg {
        max-width: 78%;
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 0.88rem;
        line-height: 1.45;
        position: relative;
        word-wrap: break-word;
    }

    .support-msg.msg-client {
        align-self: flex-start;
        background: #ffffff;
        color: #1e293b;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
    }

    [data-bs-theme="dark"] .support-msg.msg-client {
        background: #1e293b;
        color: #f8fafc;
        border-color: #334155;
    }

    .support-msg.msg-agent {
        align-self: flex-end;
        background: linear-gradient(135deg, #075be8, #0548b8);
        color: #ffffff;
        border-bottom-right-radius: 4px;
        box-shadow: 0 4px 10px rgba(7, 91, 232, 0.25);
    }

    .support-msg.msg-internal {
        align-self: center;
        max-width: 90%;
        background: #fef9c3;
        color: #854d0e;
        border: 1px dashed #facc15;
        border-radius: 12px;
        font-size: 0.82rem;
    }

    [data-bs-theme="dark"] .support-msg.msg-internal {
        background: #422006;
        color: #fef08a;
        border-color: #ca8a04;
    }

    .support-msg.msg-system {
        align-self: center;
        max-width: 90%;
        background: rgba(0, 0, 0, 0.05);
        color: #64748b;
        border-radius: 99px;
        padding: 4px 14px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .support-chat-input-area {
        padding: 12px 18px;
        border-top: 1px solid var(--border, #e2e8f0);
        background: var(--card, #ffffff);
    }

    .support-input-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    /* Coluna 3: Informações do Cliente */
    .support-info-panel {
        background: var(--card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 18px;
        padding: 18px;
        overflow-y: auto;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    @media (max-width: 1199.98px) {
        .support-layout {
            grid-template-columns: 300px 1fr;
        }
        .support-info-panel {
            display: none;
        }
    }

    @media (max-width: 767.98px) {
        .support-layout {
            grid-template-columns: 1fr;
            height: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-headset text-primary"></i> Central de Multiatendimento
        </h2>
        <p class="text-muted small mb-0">Atendimento ao vivo, gestão de filas e suporte em tempo real.</p>
    </div>

    <!-- Status do Atendente Atual -->
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success bg-opacity-10 text-success p-2 px-3 rounded-pill fw-bold border border-success border-opacity-25" id="agentStatusBadge">
            <i class="fa-solid fa-circle me-1" style="font-size: 0.6rem;"></i> Atendente Online: {{ $currentAgent->name }}
        </span>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#cannedModal">
            <i class="fa-solid fa-bolt me-1"></i> Respostas Rápidas
        </button>
    </div>
</div>

<!-- Cards de Resumo -->
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block">Na Fila de Espera</small>
                    <h3 class="fw-bold mb-0 text-warning" id="countWaiting">{{ $stats['waiting'] }}</h3>
                </div>
                <div class="bg-warning text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fa-solid fa-hourglass-half fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block">Meus Atendimentos</small>
                    <h3 class="fw-bold mb-0 text-primary" id="countMyActive">{{ $stats['my_active'] }}</h3>
                </div>
                <div class="bg-primary text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fa-solid fa-comments fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-success bg-opacity-10 border border-success border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block">Encerrados Hoje</small>
                    <h3 class="fw-bold mb-0 text-success">{{ $stats['closed_today'] }}</h3>
                </div>
                <div class="bg-success text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 rounded-4 shadow-sm p-3 bg-info bg-opacity-10 border border-info border-opacity-25">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-bold d-block">Satisfação Média</small>
                    <h3 class="fw-bold mb-0 text-info">⭐ {{ $stats['avg_rating'] }}</h3>
                </div>
                <div class="bg-info text-white rounded-3 p-2.5 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fa-solid fa-star fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Layout de 3 Colunas -->
<div class="support-layout">

    <!-- COLUNA 1: FILAS E ATENDIMENTOS -->
    <div class="support-sidebar-panel">
        <div class="support-tabs-nav">
            <button type="button" class="support-tab-btn active" id="tabWaitingBtn" onclick="switchTab('waiting')">
                <i class="fa-solid fa-hourglass-half"></i> Fila (<span id="badgeTabWaiting">{{ $waitingTickets->count() }}</span>)
            </button>
            <button type="button" class="support-tab-btn" id="tabMyChatsBtn" onclick="switchTab('my_chats')">
                <i class="fa-solid fa-comment-dots"></i> Meus (<span id="badgeTabMyChats">{{ $myActiveTickets->count() }}</span>)
            </button>
            <button type="button" class="support-tab-btn" id="tabOtherChatsBtn" onclick="switchTab('other_chats')">
                <i class="fa-solid fa-users"></i> Outros
            </button>
        </div>

        <div class="support-ticket-list" id="ticketListContainer">
            <!-- Renderizado dinamicamente via JS / Inicial pelo Blade -->
            @forelse($waitingTickets as $t)
                <div class="support-ticket-item is-waiting" data-tab="waiting" data-id="{{ $t->id }}" onclick="selectTicket({{ $t->id }})">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <strong class="text-truncate" style="font-size: 0.88rem;">{{ $t->guest_name }}</strong>
                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Fila</span>
                    </div>
                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.75rem;">
                        <i class="fa-solid {{ $t->department?->icon ?? 'fa-headset' }} text-primary me-1"></i> {{ $t->department?->name ?? 'Geral' }}
                    </small>
                    <div class="d-flex align-items-center justify-content-between">
                        <small class="text-muted" style="font-size: 0.70rem;">
                            <i class="fa-regular fa-clock me-1"></i> {{ $t->created_at->diffForHumans(null, true) }}
                        </small>
                        <button type="button" class="btn btn-primary btn-sm py-0 px-2 rounded-pill fw-bold" style="font-size: 0.72rem;" onclick="event.stopPropagation(); claimTicket({{ $t->id }})">
                            Atender
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small" id="noTicketsWaiting">
                    <i class="fa-solid fa-circle-check fs-2 text-success opacity-50 mb-2 d-block"></i>
                    Nenhum cliente na fila de espera no momento.
                </div>
            @endforelse
        </div>
    </div>

    <!-- COLUNA 2: JANELA DE CHAT -->
    <div class="support-chat-panel" id="chatMainPanel">
        <!-- Estado Vazio (Nenhum chat selecionado) -->
        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-center p-4" id="emptyChatState">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-4 mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-comments fs-1"></i>
            </div>
            <h5 class="fw-bold mb-1">Selecione um atendimento</h5>
            <p class="text-muted small" style="max-width: 320px;">
                Clique em um cliente da fila para assumir a conversa ou escolha um de seus atendimentos em andamento.
            </p>
        </div>

        <!-- Painel Ativo de Chat (Oculto inicialmente) -->
        <div class="d-none flex-column h-100" id="activeChatState">
            <!-- Header do Chat -->
            <div class="support-chat-header">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;" id="headerClientAvatar">
                        C
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0" id="headerClientName">Cliente</h6>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            Protocolo: <strong id="headerProtocol">-</strong> · Setor: <span id="headerDept">-</span>
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#transferModal" id="btnOpenTransfer">
                        <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Transferir
                    </button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" onclick="closeActiveTicket()" id="btnCloseTicket">
                        <i class="fa-solid fa-xmark me-1"></i> Encerrar
                    </button>
                </div>
            </div>

            <!-- Lista de Mensagens -->
            <div class="support-chat-messages" id="chatMessagesList">
                <!-- Mensagens inseridas via JS -->
            </div>

            <!-- Input e Ações de Envio -->
            <div class="support-chat-input-area">
                <div class="support-input-toolbar">
                    <div class="d-flex align-items-center gap-3">
                        <label class="form-check-label small fw-semibold text-muted d-flex align-items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="checkInternalNote" class="form-check-input">
                            <span><i class="fa-solid fa-lock text-warning me-1"></i> Nota Interna (Privada)</span>
                        </label>
                    </div>
                    <!-- Menu Rápido de Atalhos -->
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill text-muted px-2.5 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-bolt text-warning me-1"></i> Atalhos
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1">
                            @foreach($cannedResponses as $c)
                                <li>
                                    <button class="dropdown-item py-1.5 px-3 rounded-2 small d-flex justify-content-between gap-3" type="button" onclick="insertCannedText(`{{ addslashes($c->content) }}`)">
                                        <strong>{{ $c->shortcut }}</strong>
                                        <span class="text-muted">{{ $c->title }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <textarea id="chatInputMessage" class="form-control rounded-3" rows="2" placeholder="Digite sua mensagem ou use /atalho... (Pressione Enter para enviar)" style="font-size: 0.88rem; resize: none;"></textarea>
                    <button type="button" class="btn btn-primary rounded-3 px-3 d-flex align-items-center justify-content-center" onclick="sendActiveChatMessage()" id="btnSendMessage">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUNA 3: FICHA E DETALHES DO CLIENTE -->
    <div class="support-info-panel" id="clientInfoPanel">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="fa-solid fa-address-card text-primary me-1.5"></i> Ficha do Atendimento
        </h6>

        <div id="infoEmptyState" class="text-muted small text-center py-4">
            Selecione uma conversa para ver os dados de contato e histórico do cliente.
        </div>

        <div id="infoDataState" class="d-none">
            <div class="mb-3">
                <small class="text-muted d-block" style="font-size: 0.72rem;">Nome Completo</small>
                <strong class="d-block" id="infoName">-</strong>
            </div>

            <div class="mb-3">
                <small class="text-muted d-block" style="font-size: 0.72rem;">E-mail</small>
                <a href="#" id="infoEmail" class="text-decoration-none text-primary fw-semibold small">-</a>
            </div>

            <div class="mb-3">
                <small class="text-muted d-block" style="font-size: 0.72rem;">WhatsApp / Telefone</small>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span id="infoPhone" class="fw-semibold small">-</span>
                    <a href="#" id="infoWhatsappBtn" target="_blank" class="btn btn-success btn-sm p-1 px-2 rounded-pill" style="font-size: 0.70rem;" title="Abrir WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <div class="mb-3">
                <small class="text-muted d-block" style="font-size: 0.72rem;">Página Atual do Cliente</small>
                <a href="#" id="infoPageUrl" target="_blank" class="text-truncate d-block small text-muted text-decoration-none">-</a>
            </div>

            <div class="mb-3 border-top pt-3">
                <small class="text-muted d-block mb-1" style="font-size: 0.72rem;">Atendente Responsável</small>
                <span class="badge bg-primary bg-opacity-10 text-primary" id="infoAgentName">-</span>
            </div>
        </div>
    </div>

</div>

<!-- Modal de Transferência -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-right-arrow-left text-primary me-2"></i>Transferir Atendimento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Transferir para Atendente:</label>
                    <select id="transferAgentSelect" class="form-select rounded-3">
                        <option value="">-- Selecionar colega --</option>
                        @foreach($agents as $a)
                            @if($a->id !== $currentAgent->id)
                                <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->email }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Ou alterar Setor/Departamento:</label>
                    <select id="transferDeptSelect" class="form-select rounded-3">
                        <option value="">-- Manter setor atual --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Nota interna de contexto (opcional):</label>
                    <textarea id="transferNoteInput" class="form-control rounded-3" rows="2" placeholder="Explique brevemente para o colega o que o cliente precisa..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="executeTransfer()">Transferir Agora</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Gerenciar Respostas Rápidas -->
<div class="modal fade" id="cannedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-bolt text-warning me-2"></i>Respostas Rápidas (Canned Responses)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-5 border-end">
                        <h6 class="fw-bold mb-2">Cadastrar Novo Atalho</h6>
                        <form id="formNewCanned" onsubmit="saveCanned(event)">
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Atalho (ex: /promo):</label>
                                <input type="text" id="newCannedShortcut" class="form-control rounded-3" placeholder="/atalho" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Título:</label>
                                <input type="text" id="newCannedTitle" class="form-control rounded-3" placeholder="Ex: Tabela de Preços" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold mb-1">Texto da Resposta:</label>
                                <textarea id="newCannedContent" class="form-control rounded-3" rows="4" placeholder="Texto que será enviado ao cliente..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Salvar Resposta Rápida</button>
                        </form>
                    </div>
                    <div class="col-12 col-md-7">
                        <h6 class="fw-bold mb-2">Respostas Cadastradas</h6>
                        <div class="list-group list-group-flush overflow-y-auto" style="max-height: 320px;" id="cannedListGroup">
                            @foreach($cannedResponses as $c)
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary rounded-pill">{{ $c->shortcut }}</span>
                                        <strong class="small">{{ $c->title }}</strong>
                                    </div>
                                    <p class="text-muted small mb-0">{{ Str::limit($c->content, 90) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let activeTicketId = null;
    let currentTab = 'waiting';
    let queueData = { waiting: [], my_tickets: [], other_tickets: [] };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const currentAgentName = "{{ $currentAgent->name }}";

    // Som suave de notificação usando Web Audio API
    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15); // A5
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.35);
        } catch(e) {}
    }

    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.support-tab-btn').forEach(btn => btn.classList.remove('active'));
        if (tab === 'waiting') document.getElementById('tabWaitingBtn').classList.add('active');
        if (tab === 'my_chats') document.getElementById('tabMyChatsBtn').classList.add('active');
        if (tab === 'other_chats') document.getElementById('tabOtherChatsBtn').classList.add('active');
        renderTicketList();
    }

    function renderTicketList() {
        const container = document.getElementById('ticketListContainer');
        if (!container) return;

        let list = [];
        if (currentTab === 'waiting') list = queueData.waiting;
        else if (currentTab === 'my_chats') list = queueData.my_tickets;
        else if (currentTab === 'other_chats') list = queueData.other_tickets;

        if (list.length === 0) {
            container.innerHTML = `<div class="text-center py-4 text-muted small"><i class="fa-solid fa-inbox fs-2 opacity-50 mb-2 d-block"></i>Nenhum chamado nesta seção.</div>`;
            return;
        }

        container.innerHTML = list.map(t => {
            const isActive = activeTicketId === t.id ? 'active' : '';
            if (currentTab === 'waiting') {
                return `
                    <div class="support-ticket-item is-waiting ${isActive}" onclick="selectTicket(${t.id})">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="text-truncate" style="font-size: 0.88rem;">${t.client_name}</strong>
                            <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Fila</span>
                        </div>
                        <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-headset text-primary me-1"></i> ${t.department_name}
                        </small>
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-muted" style="font-size: 0.70rem;">
                                <i class="fa-regular fa-clock me-1"></i> ${t.wait_time || 'Agora'}
                            </small>
                            <button type="button" class="btn btn-primary btn-sm py-0 px-2 rounded-pill fw-bold" style="font-size: 0.72rem;" onclick="event.stopPropagation(); claimTicket(${t.id})">
                                Atender
                            </button>
                        </div>
                    </div>
                `;
            } else if (currentTab === 'my_chats') {
                return `
                    <div class="support-ticket-item is-active-chat ${isActive}" onclick="selectTicket(${t.id})">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="text-truncate" style="font-size: 0.88rem;">${t.client_name}</strong>
                            ${t.unread_count > 0 ? `<span class="badge bg-danger rounded-pill">${t.unread_count}</span>` : `<span class="badge bg-success" style="font-size: 0.65rem;">Ativo</span>`}
                        </div>
                        <small class="text-muted d-block text-truncate mb-1" style="font-size: 0.75rem;">
                            ${t.last_message || t.department_name}
                        </small>
                        <small class="text-muted text-end d-block" style="font-size: 0.68rem;">${t.last_activity || ''}</small>
                    </div>
                `;
            } else {
                return `
                    <div class="support-ticket-item ${isActive}" onclick="selectTicket(${t.id})">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="text-truncate" style="font-size: 0.88rem;">${t.client_name}</strong>
                            <span class="badge bg-secondary" style="font-size: 0.65rem;">Em andamento</span>
                        </div>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">
                            Atendente: <strong>${t.agent_name}</strong>
                        </small>
                    </div>
                `;
            }
        }).join('');
    }

    async function pollQueueData() {
        try {
            const res = await fetch("{{ route('admin.support.queue_data') }}");
            if (!res.ok) return;
            const data = await res.json();

            // Tocar som se a fila aumentou
            if (data.waiting.length > queueData.waiting.length) {
                playNotificationSound();
            }

            queueData = data;
            document.getElementById('countWaiting').textContent = data.counts.waiting;
            document.getElementById('countMyActive').textContent = data.counts.my_active;
            document.getElementById('badgeTabWaiting').textContent = data.counts.waiting;
            document.getElementById('badgeTabMyChats').textContent = data.counts.my_active;

            renderTicketList();

            // Se tem um ticket ativo selecionado, atualiza as mensagens dele
            if (activeTicketId) {
                refreshActiveTicketMessages();
            }
        } catch(e) {}
    }

    async function selectTicket(ticketId) {
        activeTicketId = ticketId;
        renderTicketList();

        document.getElementById('emptyChatState').classList.add('d-none');
        document.getElementById('activeChatState').classList.remove('d-none');
        document.getElementById('activeChatState').classList.add('d-flex');

        document.getElementById('infoEmptyState').classList.add('d-none');
        document.getElementById('infoDataState').classList.remove('d-none');

        await refreshActiveTicketMessages();
    }

    async function refreshActiveTicketMessages() {
        if (!activeTicketId) return;

        try {
            const res = await fetch(`/admin/suporte/ticket/${activeTicketId}`);
            if (!res.ok) return;
            const data = await res.json();
            const t = data.ticket;

            document.getElementById('headerClientName').textContent = t.client_name;
            document.getElementById('headerClientAvatar').textContent = t.client_name.charAt(0).toUpperCase();
            document.getElementById('headerProtocol').textContent = t.protocol;
            document.getElementById('headerDept').textContent = t.department_name || 'Geral';

            document.getElementById('infoName').textContent = t.client_name;
            document.getElementById('infoEmail').textContent = t.client_email;
            document.getElementById('infoEmail').href = `mailto:${t.client_email}`;
            document.getElementById('infoPhone').textContent = t.client_phone || 'Não informado';
            if (t.client_phone) {
                const rawPhone = t.client_phone.replace(/\D/g, '');
                document.getElementById('infoWhatsappBtn').href = `https://wa.me/55${rawPhone}`;
                document.getElementById('infoWhatsappBtn').classList.remove('d-none');
            } else {
                document.getElementById('infoWhatsappBtn').classList.add('d-none');
            }
            document.getElementById('infoPageUrl').textContent = t.current_page_url || '-';
            document.getElementById('infoPageUrl').href = t.current_page_url || '#';
            document.getElementById('infoAgentName').textContent = t.agent_name || 'Aguardando Atendente';

            const msgContainer = document.getElementById('chatMessagesList');
            const atBottom = msgContainer.scrollHeight - msgContainer.scrollTop <= msgContainer.clientHeight + 100;

            msgContainer.innerHTML = data.messages.map(m => {
                if (m.sender_type === 'system') {
                    return `<div class="support-msg msg-system">${m.message}</div>`;
                } else if (m.is_internal_note) {
                    return `
                        <div class="support-msg msg-internal">
                            <strong><i class="fa-solid fa-lock me-1"></i> ${m.sender_name} (Nota Interna):</strong>
                            <div>${m.message}</div>
                            <small class="text-muted d-block text-end mt-1" style="font-size: 0.65rem;">${m.created_at}</small>
                        </div>
                    `;
                } else if (m.sender_type === 'agent') {
                    return `
                        <div class="support-msg msg-agent">
                            <div>${m.message}</div>
                            <small class="text-white-50 d-block text-end mt-1" style="font-size: 0.65rem;">${m.created_at}</small>
                        </div>
                    `;
                } else {
                    return `
                        <div class="support-msg msg-client">
                            <strong>${m.sender_name}:</strong>
                            <div>${m.message}</div>
                            <small class="text-muted d-block text-end mt-1" style="font-size: 0.65rem;">${m.created_at}</small>
                        </div>
                    `;
                }
            }).join('');

            if (atBottom) {
                msgContainer.scrollTop = msgContainer.scrollHeight;
            }
        } catch(e) {}
    }

    async function claimTicket(ticketId) {
        try {
            const res = await fetch(`/admin/suporte/atender/${ticketId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            if (res.ok) {
                switchTab('my_chats');
                await pollQueueData();
                selectTicket(ticketId);
            }
        } catch(e) {}
    }

    async function sendActiveChatMessage() {
        if (!activeTicketId) return;

        const input = document.getElementById('chatInputMessage');
        const text = input.value.trim();
        if (!text) return;

        const isInternal = document.getElementById('checkInternalNote').checked;

        input.value = '';
        input.focus();

        try {
            const res = await fetch(`/admin/suporte/mensagem/${activeTicketId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text, is_internal_note: isInternal })
            });

            if (res.ok) {
                document.getElementById('checkInternalNote').checked = false;
                await refreshActiveTicketMessages();
                await pollQueueData();
            }
        } catch(e) {}
    }

    async function closeActiveTicket() {
        if (!activeTicketId || !confirm('Deseja realmente encerrar este atendimento?')) return;

        try {
            const res = await fetch(`/admin/suporte/encerrar/${activeTicketId}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });

            if (res.ok) {
                activeTicketId = null;
                document.getElementById('emptyChatState').classList.remove('d-none');
                document.getElementById('activeChatState').classList.add('d-none');
                document.getElementById('activeChatState').classList.remove('d-flex');
                document.getElementById('infoEmptyState').classList.remove('d-none');
                document.getElementById('infoDataState').classList.add('d-none');
                await pollQueueData();
            }
        } catch(e) {}
    }

    async function executeTransfer() {
        if (!activeTicketId) return;

        const agentId = document.getElementById('transferAgentSelect').value;
        const deptId = document.getElementById('transferDeptSelect').value;
        const note = document.getElementById('transferNoteInput').value;

        try {
            const res = await fetch(`/admin/suporte/transferir/${activeTicketId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ target_agent_id: agentId, department_id: deptId, note: note })
            });

            if (res.ok) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('transferModal'));
                modal?.hide();
                document.getElementById('transferNoteInput').value = '';
                await pollQueueData();
                await refreshActiveTicketMessages();
            }
        } catch(e) {}
    }

    function insertCannedText(content) {
        const input = document.getElementById('chatInputMessage');
        const formatted = content.replace('@{{agent_name}}', currentAgentName);
        input.value = formatted;
        input.focus();
    }

    async function saveCanned(e) {
        e.preventDefault();
        const shortcut = document.getElementById('newCannedShortcut').value;
        const title = document.getElementById('newCannedTitle').value;
        const content = document.getElementById('newCannedContent').value;

        try {
            const res = await fetch("{{ route('admin.support.store_canned') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ shortcut, title, content })
            });

            if (res.ok) {
                const data = await res.json();
                document.getElementById('formNewCanned').reset();
                const list = document.getElementById('cannedListGroup');
                list.insertAdjacentHTML('afterbegin', `
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-primary rounded-pill">${data.canned.shortcut}</span>
                            <strong class="small">${data.canned.title}</strong>
                        </div>
                        <p class="text-muted small mb-0">${data.canned.content}</p>
                    </div>
                `);
            }
        } catch(e) {}
    }

    // Tecla Enter no chat
    document.getElementById('chatInputMessage')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendActiveChatMessage();
        }
    });

    // Iniciar polling
    setInterval(pollQueueData, 3000);
</script>
@endpush
@endsection
