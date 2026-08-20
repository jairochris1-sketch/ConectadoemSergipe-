<link rel="stylesheet" href="{{ asset('css/pwa-install.css') }}?v=1.0">

<aside class="pwa-install-prompt" data-pwa-install-prompt hidden aria-labelledby="pwa-install-title">
    <button type="button" class="pwa-install-close" data-pwa-install-dismiss aria-label="Fechar convite de instalação">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
    </button>
    <div class="pwa-install-icon" aria-hidden="true">
        <img src="{{ asset('pwa/icon-192.png') }}" alt="">
    </div>
    <div class="pwa-install-copy">
        <span class="pwa-install-eyebrow">Acesso rápido</span>
        <strong id="pwa-install-title">Instale o Conectado em Sergipe</strong>
        <p data-pwa-install-description>Tenha serviços, lojas e comunidades locais direto na tela inicial.</p>
        <p class="pwa-install-ios-help" data-pwa-ios-help hidden>
            No Safari, toque em <i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i> e depois em <strong>Adicionar à Tela de Início</strong>.
        </p>
    </div>
    <div class="pwa-install-actions">
        <button type="button" class="pwa-install-later" data-pwa-install-dismiss>Agora não</button>
        <button type="button" class="pwa-install-confirm" data-pwa-install-confirm>
            <i class="fa-solid fa-download" aria-hidden="true"></i> Instalar
        </button>
    </div>
</aside>
