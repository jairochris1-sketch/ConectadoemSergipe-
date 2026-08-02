<button type="button" id="backToTopBtn" class="back-to-top-btn" aria-label="Voltar ao topo" title="Voltar ao topo">
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
</button>

<div class="theme-toggle-container">
    <button
        type="button"
        id="themeToggleBtn"
        class="theme-toggle-btn"
        aria-label="Alterar aparência"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <svg
            class="icon-sun"
            xmlns="http://www.w3.org/2000/svg"
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2"></path>
            <path d="M12 20v2"></path>
            <path d="m4.93 4.93 1.41 1.41"></path>
            <path d="m17.66 17.66 1.41 1.41"></path>
            <path d="M2 12h2"></path>
            <path d="M20 12h2"></path>
            <path d="m6.34 17.66-1.41 1.41"></path>
            <path d="m19.07 4.93-1.41 1.41"></path>
        </svg>

        <svg
            class="icon-moon"
            xmlns="http://www.w3.org/2000/svg"
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            style="display:none"
        >
            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9"></path>
        </svg>

        <svg
            class="icon-system"
            xmlns="http://www.w3.org/2000/svg"
            width="22"
            height="22"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            style="display:none"
        >
            <rect width="20" height="14" x="2" y="3" rx="2"></rect>
            <line x1="8" x2="16" y1="21" y2="21"></line>
            <line x1="12" x2="12" y1="17" y2="21"></line>
        </svg>
    </button>

    <div class="theme-dropdown" role="menu" aria-label="Escolha a aparência">
        <button type="button" class="theme-option" data-theme-value="light" aria-pressed="false">
            <span class="theme-option-icon">☀️</span>
            <span class="theme-option-content">
                <strong>Claro</strong>
                <small>Aparência clara</small>
            </span>
        </button>

        <button type="button" class="theme-option" data-theme-value="dark" aria-pressed="false">
            <span class="theme-option-icon">🌙</span>
            <span class="theme-option-content">
                <strong>Escuro</strong>
                <small>Aparência escura</small>
            </span>
        </button>

        <button type="button" class="theme-option" data-theme-value="system" aria-pressed="false">
            <span class="theme-option-icon">💻</span>
            <span class="theme-option-content">
                <strong>Sistema</strong>
                <small>Seguir dispositivo</small>
            </span>
        </button>
    </div>
</div>
