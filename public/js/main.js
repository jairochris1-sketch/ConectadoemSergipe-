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
    const allowedThemes = ['light', 'dark', 'system'];

    const getSavedTheme = () => {
        const savedTheme = localStorage.getItem('theme');
        const accountTheme = html.getAttribute('data-theme-preference');

        return savedTheme || (allowedThemes.includes(accountTheme) ? accountTheme : 'system');
    };

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

    document.querySelectorAll('input[name="theme_preference"]').forEach(option => {
        option.addEventListener('change', () => applyTheme(option.value));
    });

    document.querySelectorAll('[data-avatar-upload]').forEach(input => {
        input.addEventListener('change', async () => {
            const selectedFile = input.files?.[0];
            if (!selectedFile) return;

            try {
                if (typeof DataTransfer !== 'undefined') {
                    const stableFile = new File(
                        [await selectedFile.arrayBuffer()],
                        selectedFile.name,
                        { type: selectedFile.type, lastModified: selectedFile.lastModified }
                    );
                    const transfer = new DataTransfer();
                    transfer.items.add(stableFile);
                    input.files = transfer.files;
                }
            } catch (error) {
                // O envio tradicional continua disponivel em navegadores sem essa API.
            }

            const preview = document.getElementById(input.dataset.previewTarget || '');
            const placeholder = document.getElementById(input.dataset.placeholderTarget || '');
            if (preview) {
                preview.src = URL.createObjectURL(input.files?.[0] || selectedFile);
                preview.classList.remove('d-none');
                placeholder?.classList.add('d-none');
            }

            if (input.dataset.autoSubmit === 'true') {
                input.form?.requestSubmit();
            }
        });
    });

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

    /* =========================================================
       PROTEÇÃO GLOBAL DE IMAGENS E PORTFÓLIOS DOS PRESTADORES
       Bloqueia clique direito e arrastar em mídias protegidas
       ========================================================= */
    const isProtectedMediaTarget = target => {
        return target && target.closest && target.closest(
            '.protected-media, .provider-protected-img, .provider-airbnb-main, .provider-airbnb-thumb, .provider-gallery-modal-image, .provider-gallery-modal-thumb, .sdc-avatar-img, .provider-cover-wrapper, img[data-protected], .protected-media-shield, [data-provider-gallery]'
        );
    };

    document.addEventListener('contextmenu', e => {
        if (isProtectedMediaTarget(e.target) || (e.target.tagName === 'IMG' && e.target.closest('.provider-airbnb-card, .service-detail-shell, .provider-gallery-modal, .service-card, .sdc-card'))) {
            e.preventDefault();
        }
    }, { capture: true });

    document.addEventListener('dragstart', e => {
        if (isProtectedMediaTarget(e.target) || (e.target.tagName === 'IMG' && e.target.closest('.provider-airbnb-card, .service-detail-shell, .provider-gallery-modal, .service-card, .sdc-card'))) {
            e.preventDefault();
        }
    }, { capture: true });

    /* =========================================================
       PROTEÇÃO CONTRA INSPEÇÃO E ATALHOS DE DESENVOLVEDOR (F12)
       Bloqueia F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C, Ctrl+U, Ctrl+S
       ========================================================= */
    document.addEventListener('keydown', e => {
        const isMac = navigator.platform && navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        const cmdOrCtrl = isMac ? e.metaKey : e.ctrlKey;
        const key = e.key ? e.key.toUpperCase() : '';

        // Bloqueia F12
        if (e.key === 'F12' || e.keyCode === 123) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Bloqueia Ctrl+Shift+I (Inspecionar), Ctrl+Shift+J (Console), Ctrl+Shift+C (Inspecionar Elemento)
        if (cmdOrCtrl && e.shiftKey && (key === 'I' || key === 'J' || key === 'C')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Bloqueia Ctrl+U (Exibir Código Fonte) e Ctrl+S (Salvar Página Completa)
        if (cmdOrCtrl && !e.shiftKey && !e.altKey && (key === 'U' || key === 'S')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, { capture: true });

    /* =========================================================
       ENQUADRAMENTO INTELIGENTE DE MÍDIA NOS CARDS (AUTO DETECT)
       Decide se é foto comum (cover) ou arte/vertical (contain + scale + blur)
       ========================================================= */
    const detectCardMediaOrientation = img => {
        if (!img || img.dataset.framingProcessed) return;

        const applyClassification = () => {
            if (!img.naturalWidth || !img.naturalHeight) return;

            const ratio = img.naturalWidth / img.naturalHeight;
            const wrapper = img.closest('.card-media-hybrid, .card-media-wrapper');

            // Se for imagem vertical ou arte/panfleto alto (proporção < 0.98)
            if (ratio < 0.98) {
                img.classList.add('arte');
                img.classList.remove('foto-normal');
                if (wrapper) wrapper.classList.add('has-art');
            } else {
                // Se for horizontal (casa, moto, carro, paisagem padrão)
                img.classList.add('foto-normal');
                img.classList.remove('arte');
                if (wrapper) wrapper.classList.remove('has-art');
            }

            img.dataset.framingProcessed = 'true';
        };

        if (img.complete && img.naturalWidth > 0) {
            applyClassification();
        } else {
            img.addEventListener('load', applyClassification, { once: true });
        }
    };

    const processAllCardMedia = (root = document) => {
        root.querySelectorAll('.card-media-main, .card-media-hybrid img:not(.card-media-bg)').forEach(detectCardMediaOrientation);
    };

    // Execução inicial
    processAllCardMedia();

    // Observa novos elementos adicionados dinamicamente (carrosséis, paginação, ajax)
    if (window.MutationObserver) {
        const cardObserver = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) { // ELEMENT_NODE
                        if (node.matches && (node.matches('.card-media-main') || node.matches('.card-media-hybrid'))) {
                            processAllCardMedia(node.parentElement || node);
                        } else if (node.querySelectorAll) {
                            processAllCardMedia(node);
                        }
                    }
                });
            });
        });

        cardObserver.observe(document.body, { childList: true, subtree: true });
    }
});

