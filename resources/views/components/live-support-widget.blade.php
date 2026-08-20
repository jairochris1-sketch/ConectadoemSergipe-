<!-- Widget de Suporte ao Vivo & Multiatendimento com Filas -->
<style>
    .live-support-launcher {
        position: fixed;
        bottom: 80px;
        right: 22px;
        z-index: 1040;
        background: linear-gradient(135deg, #075be8 0%, #0548b8 100%);
        color: #ffffff;
        border: none;
        border-radius: 999px;
        padding: 10px 18px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(7, 91, 232, 0.35);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
    }

    .live-support-launcher:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 28px rgba(7, 91, 232, 0.45);
        color: #ffffff;
    }

    .live-support-dot {
        width: 10px;
        height: 10px;
        background: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.4);
        animation: pulseLiveDot 2s infinite alternate;
    }

    @keyframes pulseLiveDot {
        from { transform: scale(0.9); opacity: 0.8; }
        to { transform: scale(1.2); opacity: 1; }
    }

    .live-support-box {
        position: fixed;
        bottom: 80px;
        right: 22px;
        width: 360px;
        height: 520px;
        max-height: calc(100vh - 100px);
        max-width: calc(100vw - 30px);
        z-index: 1050;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.22);
        border: 1px solid rgba(0, 0, 0, 0.08);
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: supportBoxOpen 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    [data-bs-theme="dark"] .live-support-box,
    [data-theme="dark"] .live-support-box {
        background: #0f172a;
        border-color: #1e293b;
        color: #f8fafc;
    }

    @keyframes supportBoxOpen {
        from { opacity: 0; transform: scale(0.92) translateY(15px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .live-support-header {
        background: linear-gradient(135deg, #075be8 0%, #0548b8 100%);
        color: #ffffff;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .live-support-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
    }

    /* Tela de Fila */
    .support-queue-badge {
        font-size: 2.2rem;
        font-weight: 800;
        color: #f59e0b;
        margin: 12px 0 4px;
    }

    /* Mensagens no widget */
    .widget-msg-list {
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-bottom: 8px;
    }

    .widget-msg {
        max-width: 82%;
        padding: 8px 12px;
        border-radius: 14px;
        font-size: 0.84rem;
        line-height: 1.4;
    }

    .widget-msg.client {
        align-self: flex-end;
        background: #075be8;
        color: #ffffff;
        border-bottom-right-radius: 3px;
    }

    .widget-msg.agent {
        align-self: flex-start;
        background: #f1f5f9;
        color: #1e293b;
        border-bottom-left-radius: 3px;
    }

    [data-bs-theme="dark"] .widget-msg.agent {
        background: #1e293b;
        color: #f8fafc;
    }

    .widget-msg.system {
        align-self: center;
        background: rgba(0, 0, 0, 0.05);
        color: #64748b;
        font-size: 0.72rem;
        border-radius: 99px;
        padding: 3px 10px;
    }

    .widget-input-area {
        padding: 10px 14px;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        background: #ffffff;
    }

    [data-bs-theme="dark"] .widget-input-area {
        background: #0f172a;
        border-top-color: #1e293b;
    }

    /* Estrelas de Avaliação */
    .support-star-rating {
        display: flex;
        gap: 8px;
        justify-content: center;
        font-size: 1.8rem;
        color: #cbd5e1;
        cursor: pointer;
    }
    .support-star-rating i.active {
        color: #f59e0b;
    }
</style>

<!-- Botão Flutuante -->
<button type="button" class="live-support-launcher" id="liveSupportLauncher" aria-label="Abrir Suporte Online">
    <span class="live-support-dot"></span>
    <i class="fa-solid fa-headset"></i>
    <span>Atendimento</span>
</button>

<!-- Janela do Chat -->
<div class="live-support-box" id="liveSupportBox">
    <!-- Header -->
    <div class="live-support-header">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-headset fs-5"></i>
            <div>
                <strong class="d-block lh-1" style="font-size: 0.9rem;" id="widgetHeaderTitle">Suporte Online</strong>
                <small class="text-white-50" style="font-size: 0.70rem;" id="widgetHeaderSubtitle">Conectado em Sergipe</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-link text-white p-0 border-0 text-decoration-none" onclick="toggleLiveSupportBox()" aria-label="Minimizar">
                <i class="fa-solid fa-minus"></i>
            </button>
        </div>
    </div>

    <!-- 1. TELA INICIAL (FORMULÁRIO DE ENTRADA) -->
    <div class="live-support-body" id="supportStateStart">
        <div class="text-center mb-3">
            <h6 class="fw-bold mb-1">Como podemos te ajudar?</h6>
            <p class="text-muted small mb-0">Preencha seus dados para iniciar o atendimento:</p>
        </div>

        <form id="formStartSupport" onsubmit="submitStartSupport(event)">
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">Seu Nome:</label>
                <input type="text" id="widgetInputName" class="form-control form-control-sm rounded-3" placeholder="Nome completo" required>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">Seu E-mail:</label>
                <input type="email" id="widgetInputEmail" class="form-control form-control-sm rounded-3" placeholder="email@exemplo.com" required>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">WhatsApp / Telefone:</label>
                <input type="text" id="widgetInputPhone" class="form-control form-control-sm rounded-3" placeholder="(79) 99999-9999">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold mb-1">Qual é o assunto?</label>
                <select id="widgetSelectDept" class="form-select form-select-sm rounded-3" required>
                    <option value="">-- Selecione o setor --</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold mb-1">Mensagem Inicial:</label>
                <textarea id="widgetInputInitialMsg" class="form-control form-control-sm rounded-3" rows="2" placeholder="Explique brevemente sua dúvida..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm" style="font-size: 0.88rem;" id="btnWidgetStart">
                <i class="fa-solid fa-paper-plane me-1"></i> Iniciar Atendimento
            </button>
        </form>
    </div>

    <!-- 2. TELA DE FILA DE ESPERA -->
    <div class="live-support-body d-none flex-column align-items-center justify-content-center text-center" id="supportStateQueue">
        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
            <i class="fa-solid fa-hourglass-half fs-2"></i>
        </div>
        <h6 class="fw-bold mb-0">Você está na fila de espera</h6>
        <div class="support-queue-badge" id="widgetQueuePosition">#1</div>
        <p class="text-muted small mb-3" style="max-width: 260px;">
            Aguarde um instante. Um de nossos atendentes está sendo chamado para falar com você.
        </p>
        <div class="spinner-border spinner-border-sm text-primary mb-3" role="status"></div>
        <small class="text-muted d-block" style="font-size: 0.72rem;">Protocolo: <strong id="widgetQueueProtocol">-</strong></small>
    </div>

    <!-- 3. TELA DE CHAT AO VIVO -->
    <div class="d-none flex-column h-100" id="supportStateChat">
        <div class="live-support-body" id="widgetMessagesBody">
            <div class="widget-msg-list" id="widgetMessagesList"></div>
        </div>

        <div class="widget-input-area">
            <div class="d-flex gap-2">
                <input type="text" id="widgetChatMessageInput" class="form-control form-control-sm rounded-pill" placeholder="Digite sua mensagem...">
                <button type="button" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="sendWidgetChatMessage()">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1.5 px-1">
                <small class="text-muted" style="font-size: 0.68rem;" id="widgetChatAgentName">Atendente conectado</small>
                <button type="button" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size: 0.70rem;" onclick="closeWidgetTicket()">
                    Encerrar Chat
                </button>
            </div>
        </div>
    </div>

    <!-- 4. TELA DE AVALIAÇÃO -->
    <div class="live-support-body d-none flex-column align-items-center justify-content-center text-center" id="supportStateRating">
        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
            <i class="fa-solid fa-circle-check fs-2"></i>
        </div>
        <h6 class="fw-bold mb-1">Atendimento Finalizado!</h6>
        <p class="text-muted small mb-3">Como foi sua experiência com nosso suporte?</p>

        <div class="support-star-rating mb-3" id="starRatingContainer">
            <i class="fa-solid fa-star active" data-rating="1" onclick="setRating(1)"></i>
            <i class="fa-solid fa-star active" data-rating="2" onclick="setRating(2)"></i>
            <i class="fa-solid fa-star active" data-rating="3" onclick="setRating(3)"></i>
            <i class="fa-solid fa-star active" data-rating="4" onclick="setRating(4)"></i>
            <i class="fa-solid fa-star active" data-rating="5" onclick="setRating(5)"></i>
        </div>

        <textarea id="widgetFeedbackInput" class="form-control form-control-sm rounded-3 mb-3" rows="2" placeholder="Deixe um elogio ou sugestão (opcional)..."></textarea>
        <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold py-2 mb-2" style="font-size: 0.88rem;" onclick="submitRating()">
            Enviar Avaliação
        </button>
        <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" onclick="resetSupportWidget()">
            Iniciar novo atendimento
        </button>
    </div>
</div>

<script>
    (() => {
        const launcher = document.getElementById('liveSupportLauncher');
        const box = document.getElementById('liveSupportBox');
        let currentTicketId = localStorage.getItem('cis_active_support_ticket_id');
        let pollTimer = null;
        let selectedRating = 5;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        window.toggleLiveSupportBox = function() {
            if (box.style.display === 'flex') {
                box.style.display = 'none';
            } else {
                box.style.display = 'flex';
                if (!currentTicketId) {
                    loadDepartments();
                } else {
                    pollWidgetStatus();
                }
            }
        };

        launcher?.addEventListener('click', toggleLiveSupportBox);

        async function loadDepartments() {
            try {
                const res = await fetch("{{ route('support.departments') }}");
                if (!res.ok) return;
                const data = await res.json();

                const select = document.getElementById('widgetSelectDept');
                select.innerHTML = '<option value="">-- Selecione o setor --</option>' +
                    data.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');

                if (data.user) {
                    document.getElementById('widgetInputName').value = data.user.name || '';
                    document.getElementById('widgetInputEmail').value = data.user.email || '';
                    document.getElementById('widgetInputPhone').value = data.user.phone || '';
                }
            } catch(e) {}
        }

        window.submitStartSupport = async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnWidgetStart');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Entrando na fila...';

            const payload = {
                name: document.getElementById('widgetInputName').value.trim(),
                email: document.getElementById('widgetInputEmail').value.trim(),
                phone: document.getElementById('widgetInputPhone').value.trim(),
                department_id: document.getElementById('widgetSelectDept').value,
                initial_message: document.getElementById('widgetInputInitialMsg').value.trim(),
                current_page_url: window.location.href,
            };

            try {
                const res = await fetch("{{ route('support.start') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success) {
                    currentTicketId = data.ticket.id;
                    localStorage.setItem('cis_active_support_ticket_id', currentTicketId);
                    showState('queue');
                    document.getElementById('widgetQueuePosition').textContent = '#' + data.ticket.queue_position;
                    document.getElementById('widgetQueueProtocol').textContent = data.ticket.protocol;
                    startPolling();
                } else {
                    alert('Não foi possível iniciar o atendimento. Tente novamente.');
                }
            } catch(e) {
                alert('Erro ao conectar ao suporte.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Iniciar Atendimento';
            }
        };

        function showState(state) {
            document.getElementById('supportStateStart').classList.add('d-none');
            document.getElementById('supportStateQueue').classList.add('d-none');
            document.getElementById('supportStateQueue').classList.remove('d-flex');
            document.getElementById('supportStateChat').classList.add('d-none');
            document.getElementById('supportStateChat').classList.remove('d-flex');
            document.getElementById('supportStateRating').classList.add('d-none');
            document.getElementById('supportStateRating').classList.remove('d-flex');

            if (state === 'start') document.getElementById('supportStateStart').classList.remove('d-none');
            if (state === 'queue') {
                document.getElementById('supportStateQueue').classList.remove('d-none');
                document.getElementById('supportStateQueue').classList.add('d-flex');
            }
            if (state === 'chat') {
                document.getElementById('supportStateChat').classList.remove('d-none');
                document.getElementById('supportStateChat').classList.add('d-flex');
            }
            if (state === 'rating') {
                document.getElementById('supportStateRating').classList.remove('d-none');
                document.getElementById('supportStateRating').classList.add('d-flex');
            }
        }

        async function pollWidgetStatus() {
            if (!currentTicketId) return;

            try {
                const res = await fetch(`/suporte/${currentTicketId}/status`);
                if (!res.ok) {
                    resetSupportWidget();
                    return;
                }
                const data = await res.json();
                const t = data.ticket;

                if (t.status === 'waiting') {
                    showState('queue');
                    document.getElementById('widgetQueuePosition').textContent = '#' + (t.queue_position || 1);
                    document.getElementById('widgetQueueProtocol').textContent = t.protocol;
                } else if (t.status === 'in_progress') {
                    showState('chat');
                    document.getElementById('widgetHeaderTitle').textContent = t.agent?.name || 'Atendente Online';
                    document.getElementById('widgetChatAgentName').textContent = 'Atendente: ' + (t.agent?.name || 'Suporte');

                    renderMessages(data.messages);
                } else if (t.status === 'closed') {
                    if (!t.rating) {
                        showState('rating');
                    } else {
                        resetSupportWidget();
                    }
                }
            } catch(e) {}
        }

        function renderMessages(messages) {
            const list = document.getElementById('widgetMessagesList');
            if (!list) return;

            const isBottom = list.parentElement.scrollHeight - list.parentElement.scrollTop <= list.parentElement.clientHeight + 80;

            list.innerHTML = messages.map(m => {
                if (m.sender_type === 'system') return `<div class="widget-msg system">${m.message}</div>`;
                return `
                    <div class="widget-msg ${m.sender_type}">
                        <div>${m.message}</div>
                        <small class="d-block text-end opacity-75 mt-0.5" style="font-size: 0.62rem;">${m.created_at}</small>
                    </div>
                `;
            }).join('');

            if (isBottom) {
                list.parentElement.scrollTop = list.parentElement.scrollHeight;
            }
        }

        window.sendWidgetChatMessage = async function() {
            if (!currentTicketId) return;
            const input = document.getElementById('widgetChatMessageInput');
            const msg = input.value.trim();
            if (!msg) return;

            input.value = '';

            try {
                await fetch(`/suporte/${currentTicketId}/mensagem`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ message: msg })
                });
                pollWidgetStatus();
            } catch(e) {}
        };

        window.closeWidgetTicket = async function() {
            if (!currentTicketId || !confirm('Deseja realmente finalizar o atendimento?')) return;
            try {
                await fetch(`/suporte/${currentTicketId}/encerrar`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                showState('rating');
            } catch(e) {}
        };

        window.setRating = function(stars) {
            selectedRating = stars;
            document.querySelectorAll('#starRatingContainer i').forEach(icon => {
                const r = parseInt(icon.getAttribute('data-rating'));
                icon.classList.toggle('active', r <= stars);
            });
        };

        window.submitRating = async function() {
            if (!currentTicketId) return;
            const feedback = document.getElementById('widgetFeedbackInput').value.trim();

            try {
                await fetch(`/suporte/${currentTicketId}/avaliar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ rating: selectedRating, feedback: feedback })
                });
            } catch(e) {}

            alert('Obrigado pelo seu feedback!');
            resetSupportWidget();
        };

        window.resetSupportWidget = function() {
            localStorage.removeItem('cis_active_support_ticket_id');
            currentTicketId = null;
            clearInterval(pollTimer);
            showState('start');
            loadDepartments();
        };

        function startPolling() {
            clearInterval(pollTimer);
            pollTimer = setInterval(pollWidgetStatus, 2500);
        }

        document.getElementById('widgetChatMessageInput')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendWidgetChatMessage();
            }
        });

        // Se já houver ticket ativo no localStorage, inicia o monitoramento
        if (currentTicketId) {
            startPolling();
        }
    })();
</script>
