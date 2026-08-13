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
        transition: opacity 0.45s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.45s ease;
        opacity: 1;
        visibility: visible;
        overflow: hidden;
        user-select: none;
        text-align: center;
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
        width: 100%;
        max-width: 440px;
        padding: 2rem 1.5rem;
        margin: 0 auto;
        animation: splashContentEntrance 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .app-splash-logo-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        margin: 0 auto 1.25rem auto;
    }

    .app-splash-logo-glow {
        position: absolute;
        width: 115px;
        height: 115px;
        background: radial-gradient(circle, rgba(7, 91, 232, 0.25) 0%, rgba(7, 91, 232, 0) 70%);
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: splashGlowPulse 2s ease-in-out infinite alternate;
        pointer-events: none;
    }

    .app-splash-logo-img {
        width: 75px;
        height: 75px;
        object-fit: contain;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(7, 91, 232, 0.18);
        animation: splashLogoFloat 2.8s ease-in-out infinite alternate;
        position: relative;
        z-index: 2;
    }

    .app-splash-brand-name {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-weight: 800;
        font-size: 1.6rem;
        line-height: 1.15;
        letter-spacing: -0.02em;
        display: flex;
        flex-direction: row;
        gap: 0.4rem;
        align-items: center;
        justify-content: center;
        text-align: center;
        width: 100%;
        margin-bottom: 0.85rem;
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
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.5;
        color: var(--marketplace-header-muted, #5d6980);
        margin: 0 auto 1.5rem auto;
        text-align: center !important;
        text-align-last: center;
        max-width: 360px;
        width: 100%;
    }

    [data-bs-theme="dark"] .app-splash-tagline,
    [data-theme="dark"] .app-splash-tagline {
        color: #94a3b8;
    }

    /* Barra de Progresso Elegante */
    .app-splash-progress-track {
        width: 150px;
        height: 4px;
        background: rgba(7, 91, 232, 0.12);
        border-radius: 99px;
        overflow: hidden;
        position: relative;
        margin: 0 auto;
    }

    .app-splash-progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #075be8 0%, #3b82f6 50%, #60a5fa 100%);
        border-radius: 99px;
        animation: splashProgressFill 0.9s cubic-bezier(0.65, 0, 0.35, 1) forwards;
        box-shadow: 0 0 10px rgba(7, 91, 232, 0.45);
    }

    /* Animações Keyframes */
    @keyframes splashContentEntrance {
        from {
            opacity: 0;
            transform: scale(0.94) translateY(8px);
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
            transform: translateY(-5px);
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
        </div>

        <div class="app-splash-brand-name">
            <span>Conectado</span>
            <span>em Sergipe</span>
        </div>

        <p class="app-splash-tagline">
            <span class="text-primary fw-bold">conectadoemsergipe.com</span>
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

        // Verificar se é Recarregamento de Página (F5) ou Primeira Entrada no Site
        const navEntries = performance.getEntriesByType && performance.getEntriesByType('navigation');
        const isReload = navEntries && navEntries.length > 0 ? navEntries[0].type === 'reload' : (performance.navigation && performance.navigation.type === 1);
        const hasVisitedHome = sessionStorage.getItem('cis_home_visited') === 'true';

        // Se NÃO for F5 e a Home já tiver sido visitada nesta sessão (voltando de outra página), remove a splash IMEDIATAMENTE
        if (!isReload && hasVisitedHome) {
            splash.style.display = 'none';
            if (splash.parentNode) {
                splash.parentNode.removeChild(splash);
            }
            return;
        }

        // Marca que a home já foi visitada nesta sessão
        sessionStorage.setItem('cis_home_visited', 'true');

        const startTime = Date.now();
        const minDisplayTime = 800;
        let swiperReady = false;
        let pageLoaded = false;

        function tryHideSplash() {
            // Só esconde quando AMBOS estiverem prontos: página carregada E Swiper inicializado
            if (!swiperReady || !pageLoaded) return;

            const elapsedTime = Date.now() - startTime;
            const remainingTime = Math.max(0, minDisplayTime - elapsedTime);

            setTimeout(() => {
                splash.classList.add('splash-hidden');
                setTimeout(() => {
                    if (splash && splash.parentNode) {
                        splash.parentNode.removeChild(splash);
                    }
                }, 400);
            }, remainingTime);
        }

        // Aguardar o sinal do Swiper Hero estar inicializado
        window.addEventListener('swiper-hero-ready', function onSwiperReady() {
            swiperReady = true;
            window.removeEventListener('swiper-hero-ready', onSwiperReady);
            tryHideSplash();
        });

        // Página carregada
        function onPageLoad() {
            pageLoaded = true;
            tryHideSplash();
        }

        if (document.readyState === 'complete') {
            pageLoaded = true;
            tryHideSplash();
        } else {
            window.addEventListener('load', onPageLoad, { once: true });
        }

        // Fallback de segurança: após 3s esconde de qualquer jeito para não travar o site
        setTimeout(() => {
            swiperReady = true;
            pageLoaded = true;
            tryHideSplash();
        }, 3000);
    })();
</script>
