document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('marketplaceHeader');
    const toggle = document.getElementById('marketplaceMobileToggle');
    const menu = document.getElementById('marketplaceMobileMenu');

    if (!header || !toggle || !menu) {
        return;
    }

    const disableLocationButtons = header.querySelectorAll('[data-global-location-disable]');
    disableLocationButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            button.disabled = true;

            try {
                const response = await fetch(button.dataset.endpoint, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (!response.ok) {
                    throw new Error('Não foi possível desativar a localização.');
                }

                const destination = new URL(window.location.href);
                destination.searchParams.delete('city');
                window.location.assign(destination.toString());
            } catch (error) {
                button.disabled = false;
                window.alert(error.message);
            }
        });
    });

    const setMenuState = open => {
        header.classList.toggle('mobile-menu-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');

        const icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-bars', !open);
            icon.classList.toggle('fa-xmark', open);
        }
    };

    toggle.addEventListener('click', () => {
        setMenuState(!header.classList.contains('mobile-menu-open'));
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && header.classList.contains('mobile-menu-open')) {
            setMenuState(false);
            toggle.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            setMenuState(false);
        }
    });

    const desktopHover = window.matchMedia('(min-width: 992px) and (hover: hover) and (pointer: fine)');
    const accountDropdown = header.querySelector('.marketplace-account-dropdown');
    const accountButton = accountDropdown?.querySelector('[data-bs-toggle="dropdown"]');

    if (desktopHover.matches && accountDropdown && accountButton && window.bootstrap?.Dropdown) {
        const dropdown = window.bootstrap.Dropdown.getOrCreateInstance(accountButton);
        let closeTimer;

        accountDropdown.addEventListener('mouseenter', () => {
            window.clearTimeout(closeTimer);
            dropdown.show();
        });

        accountDropdown.addEventListener('mouseleave', () => {
            closeTimer = window.setTimeout(() => dropdown.hide(), 180);
        });
    }

});
