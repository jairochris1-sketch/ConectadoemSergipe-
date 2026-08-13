<div class="text-center position-relative my-3">
    <hr class="text-muted opacity-25 my-0">
    <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted auth-google-separator">ou continue com</span>
</div>

<div class="d-grid mb-3">
    <a href="{{ route('auth.google') }}" class="auth-google-button">
        <svg class="auth-google-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.91h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.4Z"/>
            <path fill="#34A853" d="M12 22c2.7 0 4.98-.9 6.63-2.43l-3.24-2.54c-.9.6-2.05.96-3.39.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"/>
            <path fill="#FBBC05" d="M6.39 13.86A6.02 6.02 0 0 1 6.08 12c0-.65.11-1.27.31-1.86V7.52H3.04A10 10 0 0 0 2 12c0 1.61.38 3.14 1.04 4.48l3.35-2.62Z"/>
            <path fill="#EA4335" d="M12 6.01c1.47 0 2.79.51 3.82 1.5l2.88-2.88A9.67 9.67 0 0 0 12 2a10 10 0 0 0-8.96 5.52l3.35 2.62C7.18 7.77 9.39 6.01 12 6.01Z"/>
        </svg>
        <span>Continuar com Google</span>
    </a>
</div>

@once
<style>
.auth-google-separator {
    font-size: .72rem;
}

.auth-google-button {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    gap: 11px;
    padding: 10px 20px;
    color: var(--foreground);
    background: rgba(255, 255, 255, .65);
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    font-size: .88rem;
    font-weight: 700;
    text-decoration: none;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease, background .2s ease;
}

.auth-google-icon {
    width: 19px;
    height: 19px;
    flex: 0 0 19px;
}

.auth-google-button:hover {
    color: var(--foreground);
    background: #fff;
    border-color: #0d6efd;
    box-shadow: 0 6px 18px rgba(13, 110, 253, .13);
    transform: translateY(-1px);
}

.auth-google-button:focus-visible {
    color: var(--foreground);
    outline: 3px solid rgba(13, 110, 253, .22);
    outline-offset: 2px;
}

html[data-theme="dark"] .auth-google-separator {
    background: var(--background) !important;
}

html[data-theme="dark"] .auth-google-button {
    color: #f8fafc;
    background: #111827;
    border-color: #34435c;
    box-shadow: none;
}

html[data-theme="dark"] .auth-google-button:hover {
    color: #fff;
    background: #172033;
    border-color: #4f8cff;
    box-shadow: 0 7px 20px rgba(0, 0, 0, .24);
}
</style>
@endonce
