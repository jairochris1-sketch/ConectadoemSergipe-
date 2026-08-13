(() => {
    'use strict';

    const cookieName = 'conectado_cookie_consent';
    const consentVersion = 1;
    const cookieLifetime = 60 * 60 * 24 * 180;
    const categories = ['preferences', 'analytics', 'marketing'];

    const readConsent = () => {
        const prefix = `${cookieName}=`;
        const stored = document.cookie
            .split(';')
            .map((value) => value.trim())
            .find((value) => value.startsWith(prefix));

        if (!stored) {
            return null;
        }

        try {
            const consent = JSON.parse(decodeURIComponent(stored.slice(prefix.length)));
            return consent.version === consentVersion && consent.necessary === true ? consent : null;
        } catch (error) {
            return null;
        }
    };

    const writeConsent = (consent) => {
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${cookieName}=${encodeURIComponent(JSON.stringify(consent))}; Path=/; Max-Age=${cookieLifetime}; SameSite=Lax${secure}`;
    };

    let currentConsent = readConsent();
    const hasConsent = (category) => category === 'necessary' || currentConsent?.[category] === true;

    window.ConectadoCookieConsent = {
        get: () => currentConsent ? { ...currentConsent } : null,
        has: hasConsent,
        open: () => document.querySelector('[data-cookie-settings]')?.click(),
        onConsent: (category, callback) => {
            if (hasConsent(category)) {
                callback();
                return;
            }

            const handleConsentUpdate = (event) => {
                if (event.detail?.[category] === true) {
                    callback();
                    document.removeEventListener('cookie-consent:updated', handleConsentUpdate);
                }
            };

            document.addEventListener('cookie-consent:updated', handleConsentUpdate);
        },
    };

    const banner = document.getElementById('cookie-consent-banner');
    const backdrop = document.getElementById('cookie-preferences-backdrop');
    const dialog = document.getElementById('cookie-preferences-dialog');
    const status = document.getElementById('cookie-consent-status');

    if (!banner || !backdrop || !dialog) {
        return;
    }

    const inputs = Object.fromEntries(categories.map((category) => [
        category,
        dialog.querySelector(`[data-cookie-category-input="${category}"]`),
    ]));
    let lastFocusedElement = null;

    const activateDeferredResources = (consent) => {
        document.querySelectorAll('[data-cookie-category][data-cookie-src]').forEach((element) => {
            const category = element.dataset.cookieCategory;
            if (!consent[category] || element.dataset.cookieActivated === 'true') {
                return;
            }

            if (element.tagName === 'SCRIPT') {
                const script = document.createElement('script');
                script.src = element.dataset.cookieSrc;
                script.async = element.hasAttribute('data-cookie-async');
                element.replaceWith(script);
            } else {
                element.src = element.dataset.cookieSrc;
                element.dataset.cookieActivated = 'true';
            }
        });
    };

    const closePreferences = (restoreFocus = true) => {
        backdrop.hidden = true;
        document.body.classList.remove('cookie-preferences-open');

        if (!currentConsent) {
            banner.hidden = false;
        }

        if (restoreFocus && lastFocusedElement instanceof HTMLElement) {
            lastFocusedElement.focus();
        }
    };

    const openPreferences = (trigger) => {
        lastFocusedElement = trigger instanceof HTMLElement ? trigger : document.activeElement;

        categories.forEach((category) => {
            if (inputs[category]) {
                inputs[category].checked = currentConsent?.[category] === true;
            }
        });

        banner.hidden = true;
        backdrop.hidden = false;
        document.body.classList.add('cookie-preferences-open');
        window.requestAnimationFrame(() => dialog.focus());
    };

    const saveConsent = (selection, message) => {
        currentConsent = {
            version: consentVersion,
            necessary: true,
            preferences: selection.preferences === true,
            analytics: selection.analytics === true,
            marketing: selection.marketing === true,
            updatedAt: new Date().toISOString(),
        };

        writeConsent(currentConsent);
        activateDeferredResources(currentConsent);
        banner.hidden = true;
        closePreferences(false);
        document.documentElement.dataset.cookieConsent = 'configured';

        if (status) {
            status.textContent = message;
        }

        document.dispatchEvent(new CustomEvent('cookie-consent:updated', {
            detail: { ...currentConsent },
        }));
    };

    document.querySelectorAll('[data-cookie-settings]').forEach((button) => {
        button.addEventListener('click', () => openPreferences(button));
    });

    document.querySelectorAll('[data-cookie-accept]').forEach((button) => {
        button.addEventListener('click', () => saveConsent({
            preferences: true,
            analytics: true,
            marketing: true,
        }, 'Todos os cookies foram autorizados.'));
    });

    document.querySelectorAll('[data-cookie-reject]').forEach((button) => {
        button.addEventListener('click', () => saveConsent({
            preferences: false,
            analytics: false,
            marketing: false,
        }, 'Somente os cookies essenciais permanecerão ativos.'));
    });

    dialog.querySelector('[data-cookie-save]')?.addEventListener('click', () => saveConsent({
        preferences: inputs.preferences?.checked === true,
        analytics: inputs.analytics?.checked === true,
        marketing: inputs.marketing?.checked === true,
    }, 'Suas preferências de cookies foram salvas.'));

    dialog.querySelector('[data-cookie-close]')?.addEventListener('click', () => closePreferences());
    backdrop.addEventListener('click', (event) => {
        if (event.target === backdrop) {
            closePreferences();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !backdrop.hidden) {
            closePreferences();
            return;
        }

        if (event.key === 'Tab' && !backdrop.hidden) {
            const focusable = Array.from(dialog.querySelectorAll(
                'button:not([disabled]), input:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
            )).filter((element) => !element.hidden);
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        }
    });

    if (currentConsent) {
        document.documentElement.dataset.cookieConsent = 'configured';
        activateDeferredResources(currentConsent);
    } else {
        banner.hidden = false;
    }
})();
