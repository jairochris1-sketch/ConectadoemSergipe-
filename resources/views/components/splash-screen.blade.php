<style>
    /* Splash Screen Critical Styles */
    #appSplashScreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: var(--marketplace-header-bg, #ffffff);
        color: var(--marketplace-header-text, #101936);
        z-index: 999999;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s ease;
        opacity: 1;
        visibility: visible;
        overflow: hidden;
        user-select: none;
    }

    /* Modos Claro e Escuro */
    [data-bs-theme="dark"] #appSplashScreen,
    [data-theme="dark"] #appSplashScreen,
    body.dark-mode #appSplashScreen {
        background-color: #0b132b !important;
        color: #ffffff !important;
    }

    .app-splash-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        max-width: 480px;
        padding: 2rem 1.5rem;
        animation: splashContentEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .app-splash-logo-wrapper {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .app-splash-logo-glow {
        position: absolute;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(7, 91, 232, 0.25) 0%, rgba(7, 91, 232, 0) 70%);
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: splashGlowPulse 2s ease-in-out infinite alternate;
        pointer-events: none;
    }

    .app-splash-logo-img {
        width: 90px;
        height: 90px;
        object-fit: contain;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(7, 91, 232, 0.2);
        animation: splashLogoFloat 3s ease-in-out infinite alternate;
        position: relative;
        z-index: 2;
    }

    .app-splash-brand-name {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.65rem;
        line-height: 1.15;
        letter-spacing: -0.02em;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .app-splash-brand-name span:first-child {
        color: var(--marketplace-header-text, #101936);
    }
    [data-bs-theme="dark"] .app-splash-brand-name span:first-child,
    [data-theme="dark"] .app-splash-brand-name span:first-child {
        color: #ffffff;
    }

    .app-splash-brand-name span:last-child {
        color: #075be8;
    }

    .app-splash-tagline {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 0.98rem;
        font-weight: 500;
        line-height: 1.5;
        color: var(--marketplace-header-muted, #5d6980);
        margin: 0 0 2rem 0;
        max-width: 380px;
    }

    [data-bs-theme="dark"] .app-splash-tagline,
    [data-theme="dark"] .app-splash-tagline {
        color: #94a3b8;
    }

    /* Barra de Progresso Elegante */
    .app-splash-progress-track {
        width: 160px;
        height: 4px;
        background: rgba(7, 91, 232, 0.12);
        border-radius: 99px;
        overflow: hidden;
        position: relative;
    }

    .app-splash-progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #075be8 0%, #3b82f6 50%, #60a5fa 100%);
        border-radius: 99px;
        animation: splashProgressFill 1.2s cubic-bezier(0.65, 0, 0.35, 1) forwards;
        box-shadow: 0 0 10px rgba(7, 91, 232, 0.5);
    }

    /* Animações Keyframes */
    @keyframes splashContentEntrance {
        from {
            opacity: 0;
            transform: scale(0.94) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes splashGlowPulse {
        from {
            transform: translate(-50%, -50%) scale(0.85);
            opacity: 0.5;
        }
        to {
            transform: translate(-50%, -50%) scale(1.25);
            opacity: 0.9;
        }
    }

    @keyframes splashLogoFloat {
        from {
            transform: translateY(0px);
        }
        to {
            transform: translateY(-6px);
        }
    }

    @keyframes splashProgressFill {
        0% {
            width: 0%;
        }
        50% {
            width: 70%;
        }
        100% {
            width: 100%;
        }
    }

    /* Ocultar splash screen quando terminado */
    #appSplashScreen.splash-hidden {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
</style>

<div id="appSplashScreen" aria-hidden="false" role="dialog" aria-label="Carregando Conectado em Sergipe">
    <div class="app-splash-container">
        <div class="app-splash-logo-wrapper">
            <div class="app-splash-logo-glow"></div>
            <img src="{{ asset('images/logo-hero.png') }}" alt="Conectado em Sergipe" class="app-splash-logo-img">
            <div class="app-splash-brand-name">
                <span>Conectado</span>
                <span>em Sergipe</span>
            </div>
        </div>

        <p class="app-splash-tagline">
            Encontre prestadores de serviços, lojas e comércio local no Conectado em Sergipe
        </p>

        <div class="app-splash-progress-track">
            <div class="app-splash-progress-bar"></div>
        </div>
    </div>
</div>

<script>
    (() => {
        const splash = document.getElementById('appSplashScreen');
        if (!splash) return;

        const startTime = Date.now();
        const minDisplayTime = 1100; // Tempo mínimo para exibição suave (1.1 segundos)

        function hideSplash() {
            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minDisplayTime - elapsedTime);

            setTimeout(() => {
                splash.classList.add('splash-hidden');
                setTimeout(() => {
                    if (splash && splash.parentNode) {
                        splash.parentNode.removeChild(splash);
                    }
                }, 550);
            }, remainingTime);
        }

        if (document.readyState === 'complete') {
            hideSplash();
        } else {
            window.addEventListener('load', hideSplash);
            // Fallback de segurança para garantir que a tela suma em no máximo 3.5 segundos se a rede estiver lenta
            setTimeout(hideSplash, 3500);
        }
    })();
</script>
