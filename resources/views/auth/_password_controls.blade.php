@once
<style>
.auth-password-control,
.auth-access-control {
    position: relative;
    min-width: 0;
}

.input-group > .auth-password-control {
    width: 1%;
    flex: 1 1 auto;
}

.auth-password-control > input {
    min-width: 0;
    padding-right: 82px !important;
}

.auth-access-control > input {
    padding-right: 52px !important;
}

.auth-password-actions {
    position: absolute;
    top: 50%;
    right: 12px;
    z-index: 5;
    display: flex;
    align-items: center;
    gap: 2px;
    transform: translateY(-50%);
}

.auth-password-actions[hidden] {
    display: none !important;
}

.auth-password-control.auth-actions-expired > input,
.auth-access-control.auth-actions-expired > input {
    padding-right: 18px !important;
}

.auth-password-action {
    display: inline-flex;
    width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    padding: 0;
    color: #6b7280;
    background: transparent;
    border: 0;
    border-radius: 8px;
}

.auth-password-action:hover,
.auth-password-action:focus-visible {
    color: #0d6efd;
    background: rgba(13, 110, 253, .1);
    outline: none;
}

html[data-theme="dark"] .auth-password-action {
    color: #aeb8c8;
}

html[data-theme="dark"] .auth-password-action:hover,
html[data-theme="dark"] .auth-password-action:focus-visible {
    color: #75a7ff;
    background: rgba(117, 167, 255, .12);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const actionVisibilityDuration = 5 * 60 * 1000;

    window.setTimeout(() => {
        document.querySelectorAll('[data-password-control], [data-access-copy-control]').forEach((control) => {
            const actions = control.querySelector('.auth-password-actions');

            if (!actions) return;

            actions.hidden = true;
            actions.setAttribute('aria-hidden', 'true');
            control.classList.add('auth-actions-expired');
        });
    }, actionVisibilityDuration);

    const copyValue = async (input, button, emptyMessage, copiedMessage, defaultLabel) => {
        if (!input.value) {
            button.setAttribute('title', emptyMessage);
            input.focus();
            return;
        }

        try {
            await navigator.clipboard.writeText(input.value);
        } catch (error) {
            const temporary = document.createElement('textarea');
            temporary.value = input.value;
            temporary.setAttribute('readonly', '');
            temporary.style.position = 'fixed';
            temporary.style.opacity = '0';
            document.body.appendChild(temporary);
            temporary.select();
            document.execCommand('copy');
            temporary.remove();
        }

        const icon = button.querySelector('i');
        icon?.classList.replace('fa-copy', 'fa-check');
        button.setAttribute('aria-label', copiedMessage);
        button.setAttribute('title', copiedMessage);

        window.setTimeout(() => {
            icon?.classList.replace('fa-check', 'fa-copy');
            button.setAttribute('aria-label', defaultLabel);
            button.setAttribute('title', defaultLabel);
        }, 1600);
    };

    document.querySelectorAll('[data-access-copy-control]').forEach((control) => {
        const input = control.querySelector('[data-access-copy-field]');
        const copy = control.querySelector('[data-access-copy]');

        if (!input || !copy) return;

        copy.addEventListener('click', () => copyValue(
            input,
            copy,
            'Digite o acesso antes de copiar',
            'Acesso copiado',
            'Copiar acesso'
        ));
    });

    document.querySelectorAll('[data-password-control]').forEach((control) => {
        const input = control.querySelector('[data-password-field]');
        const toggle = control.querySelector('[data-password-toggle]');
        const copy = control.querySelector('[data-password-copy]');

        if (!input || !toggle || !copy) return;

        toggle.addEventListener('click', () => {
            const willShow = input.type === 'password';
            input.type = willShow ? 'text' : 'password';
            toggle.setAttribute('aria-label', willShow ? 'Ocultar senha' : 'Mostrar senha');
            toggle.setAttribute('title', willShow ? 'Ocultar senha' : 'Mostrar senha');

            const icon = toggle.querySelector('i');
            icon?.classList.toggle('fa-eye', !willShow);
            icon?.classList.toggle('fa-eye-slash', willShow);
        });

        copy.addEventListener('click', () => copyValue(
            input,
            copy,
            'Digite a senha antes de copiar',
            'Senha copiada',
            'Copiar senha'
        ));
    });
});
</script>
@endonce
