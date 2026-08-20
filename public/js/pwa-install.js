(() => {
    'use strict';

    const prompt = document.querySelector('[data-pwa-install-prompt]');
    const installButton = prompt?.querySelector('[data-pwa-install-confirm]');
    const iosHelp = prompt?.querySelector('[data-pwa-ios-help]');
    const dismissalKey = 'ces:pwa-install-dismissed-at';
    const dismissalDuration = 7 * 24 * 60 * 60 * 1000;
    let deferredPrompt = null;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isSafari = isIos && /safari/i.test(window.navigator.userAgent) && !/crios|fxios|edgios/i.test(window.navigator.userAgent);

    const recentlyDismissed = () => {
        const dismissedAt = Number(window.localStorage.getItem(dismissalKey) || 0);
        return dismissedAt > 0 && Date.now() - dismissedAt < dismissalDuration;
    };

    const showPrompt = (ios = false) => {
        if (!prompt || isStandalone || recentlyDismissed()) {
            return;
        }

        iosHelp?.toggleAttribute('hidden', !ios);
        if (installButton) {
            installButton.innerHTML = ios
                ? '<i class="fa-solid fa-circle-info" aria-hidden="true"></i> Como instalar'
                : '<i class="fa-solid fa-download" aria-hidden="true"></i> Instalar';
        }
        prompt.hidden = false;
    };

    const hidePrompt = (remember = false) => {
        if (prompt) {
            prompt.hidden = true;
        }
        if (remember) {
            window.localStorage.setItem(dismissalKey, String(Date.now()));
        }
    };

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        window.setTimeout(() => showPrompt(false), 1200);
    });

    if (isSafari && !isStandalone) {
        window.setTimeout(() => showPrompt(true), 2200);
    }

    prompt?.querySelectorAll('[data-pwa-install-dismiss]').forEach((button) => {
        button.addEventListener('click', () => hidePrompt(true));
    });

    installButton?.addEventListener('click', async () => {
        if (isSafari && !deferredPrompt) {
            iosHelp?.removeAttribute('hidden');
            return;
        }
        if (!deferredPrompt) {
            return;
        }

        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        hidePrompt(false);
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        hidePrompt(false);
        window.localStorage.removeItem(dismissalKey);
    });
})();
