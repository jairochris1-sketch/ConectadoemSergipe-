<style>
    /* Intent Modal Styling */
    #userIntentModalOverlay {
        position: fixed;
        inset: 0;
        z-index: 1055;
        background: rgba(10, 18, 38, 0.72);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    #userIntentModalOverlay.show {
        display: flex;
        opacity: 1;
    }

    .intent-modal-card {
        background: var(--card, #ffffff);
        color: var(--text-color, #1e293b);
        border: 1px solid var(--border, rgba(0, 0, 0, 0.08));
        border-radius: 24px;
        max-width: 640px;
        width: 100%;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        overflow: hidden;
        transform: scale(0.95) translateY(10px);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    #userIntentModalOverlay.show .intent-modal-card {
        transform: scale(1) translateY(0);
    }

    .intent-modal-header {
        padding: 1.75rem 1.75rem 1rem 1.75rem;
        text-align: center;
        position: relative;
    }

    .intent-modal-close-btn {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid var(--border, #e2e8f0);
        background: var(--bg-hover, #f8fafc);
        color: var(--text-muted, #64748b);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .intent-modal-close-btn:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fca5a5;
        transform: rotate(90deg);
    }

    .intent-modal-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 700;
        background: rgba(7, 91, 232, 0.10);
        color: #075be8;
        margin-bottom: 0.75rem;
    }

    .intent-modal-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        margin-bottom: 0.35rem;
        color: var(--text-color, #0f172a);
    }

    .intent-modal-subtitle {
        font-size: 0.88rem;
        color: var(--text-muted, #64748b);
        margin-bottom: 0;
        line-height: 1.4;
    }

    .intent-modal-body {
        padding: 0.5rem 1.75rem 1.5rem 1.75rem;
    }

    .intent-options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .intent-option-card {
        border-radius: 18px;
        border: 2px solid var(--border, #e2e8f0);
        background: var(--card, #ffffff);
        padding: 1.25rem;
        text-align: left;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .intent-option-card:hover {
        border-color: #075be8;
        transform: translateY(-4px);
        box-shadow: 0 12px 28px -6px rgba(7, 91, 232, 0.20);
        color: inherit;
    }

    .intent-option-card.card-services:hover {
        border-color: #075be8;
        background: linear-gradient(180deg, rgba(7, 91, 232, 0.03) 0%, rgba(7, 91, 232, 0.08) 100%);
    }

    .intent-option-card.card-stores:hover {
        border-color: #10b981;
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.03) 0%, rgba(16, 185, 129, 0.08) 100%);
    }

    .intent-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 0.85rem;
        transition: transform 0.25s ease;
    }

    .intent-option-card:hover .intent-icon-wrapper {
        transform: scale(1.1);
    }

    .icon-services {
        background: rgba(7, 91, 232, 0.12);
        color: #075be8;
    }

    .icon-stores {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }

    .intent-option-title {
        font-size: 1.05rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
        color: var(--text-color, #0f172a);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .intent-option-desc {
        font-size: 0.78rem;
        color: var(--text-muted, #64748b);
        line-height: 1.35;
        margin-bottom: 0.85rem;
    }

    .intent-option-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: auto;
    }

    .intent-tag {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 6px;
        background: var(--bg-hover, #f1f5f9);
        color: var(--text-muted, #475569);
    }

    .intent-btn-action {
        width: 100%;
        padding: 0.55rem 0.85rem;
        border-radius: 12px;
        font-size: 0.82rem;
        font-weight: 700;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 0.85rem;
        transition: all 0.2s ease;
    }

    .btn-action-services {
        background: #075be8;
        color: #ffffff;
    }
    .btn-action-services:hover {
        background: #0548b8;
        color: #ffffff;
    }

    .btn-action-stores {
        background: #10b981;
        color: #ffffff;
    }
    .btn-action-stores:hover {
        background: #059669;
        color: #ffffff;
    }

    .intent-modal-footer {
        padding: 0.85rem 1.75rem 1.5rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid var(--border, #e2e8f0);
        font-size: 0.82rem;
        color: var(--text-muted, #64748b);
    }

    .intent-remember-wrap {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        user-select: none;
    }

    .intent-explore-all {
        color: var(--text-muted, #64748b);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease;
    }
    .intent-explore-all:hover {
        color: #075be8;
        text-decoration: underline;
    }

    /* Dark Mode */
    [data-bs-theme="dark"] .intent-modal-card,
    [data-theme="dark"] .intent-modal-card,
    html[data-theme="dark"] .intent-modal-card {
        background: #0f172a;
        border-color: #1e293b;
        color: #f8fafc;
    }

    [data-bs-theme="dark"] .intent-modal-close-btn,
    [data-theme="dark"] .intent-modal-close-btn,
    html[data-theme="dark"] .intent-modal-close-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }

    [data-bs-theme="dark"] .intent-option-card,
    [data-theme="dark"] .intent-option-card,
    html[data-theme="dark"] .intent-option-card {
        background: #1e293b;
        border-color: #334155;
    }

    [data-bs-theme="dark"] .intent-tag,
    [data-theme="dark"] .intent-tag,
    html[data-theme="dark"] .intent-tag {
        background: #334155;
        color: #cbd5e1;
    }

    [data-bs-theme="dark"] .intent-modal-footer,
    [data-theme="dark"] .intent-modal-footer,
    html[data-theme="dark"] .intent-modal-footer {
        border-top-color: #1e293b;
    }

    /* Mobile Responsiveness */
    @media (max-width: 575.98px) {
        .intent-options-grid {
            grid-template-columns: 1fr;
            gap: 0.85rem;
        }
        .intent-modal-card {
            border-radius: 20px;
        }
        .intent-modal-header {
            padding: 1.25rem 1.25rem 0.75rem 1.25rem;
        }
        .intent-modal-body {
            padding: 0.5rem 1.25rem 1rem 1.25rem;
        }
        .intent-modal-footer {
            padding: 0.75rem 1.25rem 1.25rem 1.25rem;
            flex-direction: column;
            gap: 0.65rem;
            text-align: center;
        }
    }
</style>

<div id="userIntentModalOverlay" role="dialog" aria-modal="true" aria-labelledby="intentModalTitle">
    <div class="intent-modal-card">
        <button type="button" class="intent-modal-close-btn" id="closeIntentModalBtn" aria-label="Fechar janela">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="intent-modal-header">
            <span class="intent-modal-badge">
                <i class="fa-solid fa-compass"></i> Escolha seu objetivo
            </span>
            <h2 class="intent-modal-title" id="intentModalTitle">O que você procura hoje em Sergipe?</h2>
            <p class="intent-modal-subtitle">Selecione para onde você gostaria de ir primeiro:</p>
        </div>

        <div class="intent-modal-body">
            <div class="intent-options-grid">
                <!-- Opção 1: Prestadores de Serviços -->
                <div class="intent-option-card card-services" id="intentSelectServices" role="button" tabindex="0">
                    <div class="intent-icon-wrapper icon-services">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div class="intent-option-title">
                        <span>Prestadores de Serviços</span>
                        <i class="fa-solid fa-arrow-right text-primary fs-6"></i>
                    </div>
                    <p class="intent-option-desc">
                        Encontre profissionais qualificados para reparos, reformas, saúde, beleza e suporte local.
                    </p>
                    <div class="intent-option-tags">
                        <span class="intent-tag"><i class="fa-solid fa-bolt me-1 text-warning"></i>Eletricistas</span>
                        <span class="intent-tag"><i class="fa-solid fa-broom me-1 text-info"></i>Diaristas</span>
                        <span class="intent-tag"><i class="fa-solid fa-faucet-drip me-1 text-primary"></i>Encanadores</span>
                        <span class="intent-tag">+50 tipos</span>
                    </div>
                    <button type="button" class="intent-btn-action btn-action-services">
                        <span>Ver Prestadores de Serviços</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Opção 2: Lojas & Vendas -->
                <div class="intent-option-card card-stores" id="intentSelectStores" role="button" tabindex="0">
                    <div class="intent-icon-wrapper icon-stores">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="intent-option-title">
                        <span>Lojas &amp; Vendas</span>
                        <i class="fa-solid fa-arrow-right text-success fs-6"></i>
                    </div>
                    <p class="intent-option-desc">
                        Descubra lojas virtuais locais, produtos físicos, imóveis, veículos, empregos e agro em Sergipe.
                    </p>
                    <div class="intent-option-tags">
                        <span class="intent-tag"><i class="fa-solid fa-store me-1 text-success"></i>Lojas</span>
                        <span class="intent-tag"><i class="fa-solid fa-mobile-screen me-1 text-purple"></i>Produtos</span>
                        <span class="intent-tag"><i class="fa-solid fa-building me-1 text-info"></i>Imóveis</span>
                        <span class="intent-tag"><i class="fa-solid fa-car me-1 text-danger"></i>Veículos</span>
                    </div>
                    <button type="button" class="intent-btn-action btn-action-stores">
                        <span>Ver Lojas e Produtos</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="intent-modal-footer">
            <label class="intent-remember-wrap">
                <input type="checkbox" id="intentRememberChoice" class="form-check-input" checked>
                <span>Lembrar minha preferência</span>
            </label>
            <a href="javascript:void(0)" class="intent-explore-all" id="intentExploreAll">
                Explorar tudo <i class="fa-solid fa-arrow-right-long ms-1"></i>
            </a>
        </div>
    </div>
</div>

<script>
    (() => {
        // 1. Verificação instantânea de redirecionamento para preferência salva
        const urlParams = new URLSearchParams(window.location.search);
        const forceHome = urlParams.has('home') || urlParams.has('explorar');
        const resetPref = urlParams.has('trocar_preferencia') || urlParams.has('reset_pref');

        if (resetPref) {
            localStorage.removeItem('cis_user_intent');
            sessionStorage.removeItem('cis_intent_modal_shown');
        }

        const storedChoice = localStorage.getItem('cis_user_intent');
        const isHomePage = window.location.pathname === '/' || window.location.pathname === '';

        // Se a preferência salva for Lojas & Vendas, redireciona automaticamente
        if (isHomePage && storedChoice === 'stores' && !forceHome && !resetPref) {
            window.location.replace("{{ route('stores-sales.index') }}");
            return;
        }

        // 2. Elementos do Modal
        const modalOverlay = document.getElementById('userIntentModalOverlay');
        const closeBtn = document.getElementById('closeIntentModalBtn');
        const btnServices = document.getElementById('intentSelectServices');
        const btnStores = document.getElementById('intentSelectStores');
        const btnExploreAll = document.getElementById('intentExploreAll');
        const rememberCheckbox = document.getElementById('intentRememberChoice');

        if (!modalOverlay) return;

        function hideModal(rememberDecision, choice) {
            modalOverlay.classList.remove('show');
            setTimeout(() => {
                modalOverlay.style.display = 'none';
            }, 350);

            if (rememberDecision && choice) {
                localStorage.setItem('cis_user_intent', choice);
            } else if (!rememberDecision) {
                localStorage.removeItem('cis_user_intent');
            }
            sessionStorage.setItem('cis_intent_modal_shown', 'true');
        }

        function showModal() {
            modalOverlay.style.display = 'flex';
            void modalOverlay.offsetWidth;
            modalOverlay.classList.add('show');
        }

        // Expor função globalmente para reabrir caso o usuário clique em algum gatilho
        window.openIntentSelectorModal = function() {
            showModal();
        };

        // Lógica de auto-exibição do modal:
        // Exibe se estiver na Home ('/'), sem preferência definida e na primeira vez da sessão
        const hasSeenSession = sessionStorage.getItem('cis_intent_modal_shown');

        if ((!hasSeenSession && !storedChoice && isHomePage) || resetPref) {
            setTimeout(() => {
                showModal();
            }, 1000);
        }

        // Handlers de interação
        closeBtn?.addEventListener('click', () => {
            hideModal(false, null);
        });

        modalOverlay?.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                hideModal(false, null);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalOverlay.classList.contains('show')) {
                hideModal(false, null);
            }
        });

        btnServices?.addEventListener('click', () => {
            const remember = rememberCheckbox?.checked ?? true;
            hideModal(remember, 'services');
            if (window.location.pathname === '/' || window.location.pathname === '') {
                const providersSection = document.querySelector('.home-provider-heading-row') || document.querySelector('.home-highlights-layout');
                if (providersSection) {
                    providersSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                window.location.href = "{{ route('home') }}";
            }
        });

        btnStores?.addEventListener('click', () => {
            const remember = rememberCheckbox?.checked ?? true;
            hideModal(remember, 'stores');
            window.location.href = "{{ route('stores-sales.index') }}";
        });

        btnExploreAll?.addEventListener('click', () => {
            const remember = rememberCheckbox?.checked ?? true;
            hideModal(remember, 'explore');
        });
    })();
</script>
