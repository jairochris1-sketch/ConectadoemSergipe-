<div
    id="cookie-consent-banner"
    class="cookie-consent-banner"
    role="region"
    aria-labelledby="cookie-consent-title"
    aria-describedby="cookie-consent-description"
    hidden
>
    <div class="cookie-consent-shell">
        <div class="cookie-consent-copy">
            <h2 id="cookie-consent-title"><i class="fa-solid fa-cookie-bite" aria-hidden="true"></i> Sua privacidade importa</h2>
            <p id="cookie-consent-description">
                Usamos cookies essenciais para o site funcionar. Com sua permissão, também podemos usar cookies opcionais para lembrar preferências, entender o uso da plataforma e personalizar comunicações.
                <a href="{{ route('page.privacy') }}">Saiba mais na Política de Privacidade.</a>
            </p>
        </div>
        <div class="cookie-consent-actions" aria-label="Opções de cookies">
            <button type="button" class="cookie-button cookie-button-secondary" data-cookie-settings>
                <i class="fa-solid fa-sliders" aria-hidden="true"></i> Gerenciar cookies
            </button>
            <button type="button" class="cookie-button cookie-button-secondary" data-cookie-reject>Rejeitar opcionais</button>
            <button type="button" class="cookie-button cookie-button-primary" data-cookie-accept>Aceitar todos</button>
        </div>
    </div>
</div>

<div id="cookie-preferences-backdrop" class="cookie-preferences-backdrop" hidden>
    <section
        id="cookie-preferences-dialog"
        class="cookie-preferences-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-preferences-title"
        aria-describedby="cookie-preferences-description"
        tabindex="-1"
    >
        <header class="cookie-preferences-header">
            <div>
                <span class="cookie-preferences-eyebrow"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Central de privacidade</span>
                <h2 id="cookie-preferences-title">Gerenciar cookies</h2>
            </div>
            <button type="button" class="cookie-dialog-close" data-cookie-close aria-label="Fechar preferências de cookies">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="cookie-preferences-body">
            <p id="cookie-preferences-description">Escolha quais categorias opcionais podem ser utilizadas. Sua decisão pode ser alterada a qualquer momento.</p>

            <div class="cookie-category">
                <div class="cookie-category-copy">
                    <strong>Cookies essenciais</strong>
                    <span>Mantêm a sessão, a segurança dos formulários e os recursos básicos do site.</span>
                </div>
                <label class="cookie-switch is-required">
                    <input type="checkbox" checked disabled aria-label="Cookies essenciais sempre ativos">
                    <span aria-hidden="true"></span>
                    <em>Sempre ativos</em>
                </label>
            </div>

            <div class="cookie-category">
                <div class="cookie-category-copy">
                    <strong>Preferências</strong>
                    <span>Permitem lembrar escolhas que tornam a navegação mais conveniente.</span>
                </div>
                <label class="cookie-switch">
                    <input type="checkbox" data-cookie-category-input="preferences">
                    <span aria-hidden="true"></span>
                    <em>Autorizar</em>
                </label>
            </div>

            <div class="cookie-category">
                <div class="cookie-category-copy">
                    <strong>Análise e desempenho</strong>
                    <span>Ajudam a entender, de forma agregada, como a plataforma é utilizada e onde pode melhorar.</span>
                </div>
                <label class="cookie-switch">
                    <input type="checkbox" data-cookie-category-input="analytics">
                    <span aria-hidden="true"></span>
                    <em>Autorizar</em>
                </label>
            </div>

            <div class="cookie-category">
                <div class="cookie-category-copy">
                    <strong>Marketing</strong>
                    <span>Permitem medir campanhas e apresentar comunicações mais relevantes.</span>
                </div>
                <label class="cookie-switch">
                    <input type="checkbox" data-cookie-category-input="marketing">
                    <span aria-hidden="true"></span>
                    <em>Autorizar</em>
                </label>
            </div>
        </div>

        <footer class="cookie-preferences-footer">
            <button type="button" class="cookie-button cookie-button-secondary" data-cookie-reject>Rejeitar opcionais</button>
            <button type="button" class="cookie-button cookie-button-secondary" data-cookie-save>Salvar escolhas</button>
            <button type="button" class="cookie-button cookie-button-primary" data-cookie-accept>Aceitar todos</button>
        </footer>
    </section>
</div>

<div id="cookie-consent-status" class="visually-hidden" role="status" aria-live="polite"></div>
