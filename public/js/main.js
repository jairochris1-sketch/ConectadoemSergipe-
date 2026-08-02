document.addEventListener('DOMContentLoaded', () => {
    /* =========================================================
       TEMA GLOBAL
       CLARO / ESCURO / SISTEMA
       ========================================================= */

    const themeContainer = document.querySelector('.theme-toggle-container');
    const themeBtn = document.getElementById('themeToggleBtn');
    const themeOptions = document.querySelectorAll('.theme-option');
    const html = document.documentElement;
    const systemThemeMedia = window.matchMedia('(prefers-color-scheme: dark)');

    const getSavedTheme = () => localStorage.getItem('theme') || 'system';

    const getResolvedTheme = theme => {
        if (theme === 'system') {
            return systemThemeMedia.matches ? 'dark' : 'light';
        }

        return theme;
    };

    const updateThemeIcon = theme => {
        if (!themeBtn) return;

        const iconSun = themeBtn.querySelector('.icon-sun');
        const iconMoon = themeBtn.querySelector('.icon-moon');
        const iconSystem = themeBtn.querySelector('.icon-system');

        if (!iconSun || !iconMoon || !iconSystem) return;

        iconSun.style.display = theme === 'light' ? 'block' : 'none';
        iconMoon.style.display = theme === 'dark' ? 'block' : 'none';
        iconSystem.style.display = theme === 'system' ? 'block' : 'none';
    };

    const updateThemeOptions = theme => {
        themeOptions.forEach(option => {
            const isActive = option.dataset.themeValue === theme;
            option.classList.toggle('active', isActive);
            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    };

    const applyTheme = (theme, save = false) => {
        const allowedThemes = ['light', 'dark', 'system'];

        if (!allowedThemes.includes(theme)) {
            theme = 'system';
        }

        const resolvedTheme = getResolvedTheme(theme);
        html.setAttribute('data-theme', resolvedTheme);
        html.setAttribute('data-bs-theme', resolvedTheme);
        html.setAttribute('data-theme-preference', theme);

        if (save) {
            localStorage.setItem('theme', theme);
        }

        updateThemeOptions(theme);
        updateThemeIcon(theme);
    };

    applyTheme(getSavedTheme());

    if (themeContainer && themeBtn) {
        const desktopHover = window.matchMedia('(min-width: 992px) and (hover: hover) and (pointer: fine)');
        let hoverCloseTimer;

        if (desktopHover.matches) {
            themeContainer.addEventListener('mouseenter', () => {
                window.clearTimeout(hoverCloseTimer);
                themeContainer.classList.add('active');
                themeBtn.setAttribute('aria-expanded', 'true');
            });

            themeContainer.addEventListener('mouseleave', () => {
                hoverCloseTimer = window.setTimeout(() => {
                    themeContainer.classList.remove('active');
                    themeBtn.setAttribute('aria-expanded', 'false');
                }, 180);
            });
        }

        themeBtn.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();

            const isActive = themeContainer.classList.toggle('active');
            themeBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });

        themeOptions.forEach(option => {
            option.addEventListener('click', event => {
                event.preventDefault();
                applyTheme(option.dataset.themeValue, true);
                themeContainer.classList.remove('active');
                themeBtn.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', event => {
            if (!themeContainer.contains(event.target)) {
                themeContainer.classList.remove('active');
                themeBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                themeContainer.classList.remove('active');
                themeBtn.setAttribute('aria-expanded', 'false');
                themeBtn.focus();
            }
        });
    }

    const handleSystemThemeChange = () => {
        if (getSavedTheme() === 'system') {
            applyTheme('system');
        }
    };

    if (systemThemeMedia.addEventListener) {
        systemThemeMedia.addEventListener('change', handleSystemThemeChange);
    } else if (systemThemeMedia.addListener) {
        systemThemeMedia.addListener(handleSystemThemeChange);
    }

    const backToTopBtn = document.getElementById('backToTopBtn');

    if (backToTopBtn) {
        const updateBackToTopVisibility = () => {
            backToTopBtn.classList.toggle('is-visible', window.scrollY > 320);
        };

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
            });
        });

        window.addEventListener('scroll', updateBackToTopVisibility, { passive: true });
        updateBackToTopVisibility();
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('[data-store-follow]').forEach(button => {
        button.addEventListener('click', async () => {
            if (!csrfToken || button.dataset.busy === 'true') return;

            button.dataset.busy = 'true';
            button.disabled = true;

            try {
                const response = await fetch(button.dataset.endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Não foi possível atualizar esta loja.');
                }

                document.querySelectorAll(`[data-store-follow][data-store-id="${button.dataset.storeId}"]`).forEach(item => {
                    const label = item.querySelector('[data-store-follow-label]');
                    const count = item.querySelector('[data-store-follow-count]');
                    const icon = item.querySelector('.fa-heart');
                    const followingLabel = item.dataset.labelFollowing || 'Seguindo';
                    const idleLabel = item.dataset.labelIdle || 'Seguir';

                    item.classList.toggle('is-following', result.following);
                    item.setAttribute('aria-pressed', result.following ? 'true' : 'false');
                    item.title = result.following ? 'Deixar de seguir' : 'Seguir loja';
                    if (label) label.textContent = result.following ? followingLabel : idleLabel;
                    if (count) count.textContent = result.followers_count;
                    if (icon) {
                        icon.classList.toggle('fa-solid', result.following);
                        icon.classList.toggle('fa-regular', !result.following);
                    }
                });
            } catch (error) {
                const label = button.querySelector('[data-store-follow-label]');
                if (label) {
                    const original = label.textContent;
                    label.textContent = error.message || 'Tente novamente';
                    window.setTimeout(() => {
                        label.textContent = original;
                    }, 2200);
                }
            } finally {
                button.disabled = false;
                delete button.dataset.busy;
            }
        });
    });
});
