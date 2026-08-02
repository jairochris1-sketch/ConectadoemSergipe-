<dialog class="social-share-modal" id="social-share-modal" aria-labelledby="social-share-title">
    <div class="social-share-panel">
        <button type="button" class="social-share-close" data-social-share-close aria-label="Fechar compartilhamento">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <header>
            <span class="social-share-heading-icon"><i class="fa-solid fa-share-nodes"></i></span>
            <h2 id="social-share-title">Compartilhar</h2>
            <p>Escolha como deseja compartilhar este conteúdo</p>
        </header>

        <div class="social-share-networks" aria-label="Redes sociais">
            <button type="button" data-share-network="facebook">
                <span class="is-facebook"><i class="fa-brands fa-facebook-f"></i></span>
                <small>Facebook</small>
            </button>
            <button type="button" data-share-network="whatsapp">
                <span class="is-whatsapp"><i class="fa-brands fa-whatsapp"></i></span>
                <small>WhatsApp</small>
            </button>
            <button type="button" data-share-network="instagram">
                <span class="is-instagram"><i class="fa-brands fa-instagram"></i></span>
                <small>Instagram</small>
            </button>
            <button type="button" data-share-network="twitter">
                <span class="is-twitter"><i class="fa-brands fa-x-twitter"></i></span>
                <small>X (Twitter)</small>
            </button>
            <button type="button" data-share-network="native">
                <span class="is-more"><i class="fa-solid fa-ellipsis"></i></span>
                <small>Mais opções</small>
            </button>
        </div>

        <div class="social-share-separator"><span>ou copie o link</span></div>

        <div class="social-share-copy">
            <i class="fa-solid fa-link"></i>
            <input type="text" readonly data-social-share-url aria-label="Link para compartilhar">
            <button type="button" data-social-share-copy>Copiar</button>
        </div>
        <p class="social-share-feedback" data-social-share-feedback role="status" aria-live="polite"></p>
    </div>
</dialog>

<style>
    .social-share-modal {
        --share-panel: #fff;
        --share-surface: #f8fafc;
        --share-border: #e2e8f0;
        --share-text: #172033;
        --share-muted: #64748b;
        --share-subtle: #94a3b8;
        width: min(520px, calc(100% - 32px));
        max-width: none;
        padding: 0;
        border: 0;
        border-radius: 18px;
        background: transparent;
        color: var(--share-text);
        overflow: visible;
    }
    html[data-theme="dark"] .social-share-modal {
        --share-panel: #0f172a;
        --share-surface: #172033;
        --share-border: #334155;
        --share-text: #f8fafc;
        --share-muted: #cbd5e1;
        --share-subtle: #94a3b8;
    }
    .social-share-modal::backdrop {
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(4px);
    }
    .social-share-panel {
        position: relative;
        padding: 23px;
        border: 1px solid var(--share-border);
        border-radius: 18px;
        background: var(--share-panel);
        color: var(--share-text);
        box-shadow: 0 28px 80px rgba(15, 23, 42, .28);
    }
    .social-share-close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 50%;
        background: var(--share-surface);
        color: var(--share-muted);
        font-size: 1rem;
    }
    .social-share-panel > header {
        text-align: center;
    }
    .social-share-heading-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        margin: 0 auto 8px;
        border-radius: 50%;
        color: #6d28d9;
        background: #f3e8ff;
    }
    .social-share-panel h2 {
        margin: 0;
        color: var(--share-text);
        font-size: 1.2rem;
        font-weight: 800;
    }
    .social-share-panel header p {
        margin: 5px 0 0;
        color: var(--share-muted);
        font-size: .78rem;
    }
    .social-share-networks {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        margin: 21px 0 18px;
    }
    .social-share-networks button {
        min-width: 0;
        display: grid;
        justify-items: center;
        gap: 8px;
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--share-text);
    }
    .social-share-networks button > span {
        width: 50px;
        height: 50px;
        display: grid;
        place-items: center;
        border: 1px solid var(--share-border);
        border-radius: 50%;
        background: var(--share-surface);
        font-size: 1.5rem;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .social-share-networks button:hover > span,
    .social-share-networks button:focus-visible > span {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
    }
    .social-share-networks small {
        font-size: .65rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .social-share-networks .is-facebook { color: #fff; border-color: #1877f2; background: #1877f2; }
    .social-share-networks .is-whatsapp { color: #fff; border-color: #20c763; background: #20c763; }
    .social-share-networks .is-instagram { color: #fff; border: 0; background: radial-gradient(circle at 30% 105%, #fdf497 0 5%, #fd5949 42%, #d6249f 67%, #285aeb 100%); }
    .social-share-networks .is-twitter { color: #fff; border-color: #111827; background: #111827; }
    .social-share-networks .is-more { color: var(--share-muted); background: var(--share-surface); }
    .social-share-separator {
        display: flex;
        align-items: center;
        gap: 14px;
        color: var(--share-subtle);
        font-size: .68rem;
    }
    .social-share-separator::before,
    .social-share-separator::after {
        height: 1px;
        flex: 1;
        content: "";
        background: var(--share-border);
    }
    .social-share-copy {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        padding: 5px 5px 5px 12px;
        border: 1px solid var(--share-border);
        border-radius: 11px;
        background: var(--share-panel);
        box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
    }
    .social-share-copy > i { color: #7c3aed; }
    .social-share-copy input {
        min-width: 0;
        padding: 7px 0;
        border: 0;
        outline: 0;
        color: var(--share-muted);
        background: transparent;
        font-size: .72rem;
    }
    html[data-theme="dark"] .social-share-copy input {
        background: transparent !important;
    }
    .social-share-copy button {
        padding: 9px 18px;
        border: 0;
        border-radius: 9px;
        color: #fff;
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        font-size: .74rem;
        font-weight: 800;
    }
    .social-share-feedback {
        min-height: 18px;
        margin: 8px 0 -10px;
        color: #16834b;
        text-align: center;
        font-size: .72rem;
        font-weight: 700;
    }
    @media (max-width: 575.98px) {
        .social-share-modal {
            width: calc(100% - 16px);
            margin: auto;
        }
        .social-share-panel {
            padding: 20px 12px 14px;
            border-radius: 16px;
        }
        .social-share-heading-icon { display: none; }
        .social-share-networks {
            grid-template-columns: repeat(5, minmax(58px, 1fr));
            gap: 3px;
            margin: 18px -8px 15px;
            padding: 0 8px 5px;
            overflow-x: auto;
        }
        .social-share-networks button > span {
            width: 46px;
            height: 46px;
            font-size: 1.35rem;
        }
        .social-share-networks small {
            font-size: .6rem;
        }
        .social-share-copy button { padding-inline: 13px; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('social-share-modal');
        if (!modal) return;

        const urlInput = modal.querySelector('[data-social-share-url]');
        const feedback = modal.querySelector('[data-social-share-feedback]');
        let shareData = { title: document.title, text: '', url: window.location.href };
        let opener = null;

        const setFeedback = (message) => {
            feedback.textContent = message;
            window.clearTimeout(modal.feedbackTimer);
            modal.feedbackTimer = window.setTimeout(() => feedback.textContent = '', 2600);
        };

        const copyLink = async () => {
            try {
                await navigator.clipboard.writeText(shareData.url);
            } catch (error) {
                urlInput.focus();
                urlInput.select();
                document.execCommand('copy');
            }
            setFeedback('Link copiado para a área de transferência.');
            opener?.dispatchEvent(new CustomEvent('social-share:completed', { bubbles: true, detail: { network: 'copy' } }));
        };

        const openShareWindow = (url, network) => {
            window.open(url, '_blank', 'noopener,noreferrer,width=720,height=640');
            opener?.dispatchEvent(new CustomEvent('social-share:completed', { bubbles: true, detail: { network } }));
        };

        document.querySelectorAll('[data-social-share]').forEach((button) => {
            button.addEventListener('click', () => {
                opener = button;
                shareData = {
                    title: button.dataset.shareTitle || document.title,
                    text: button.dataset.shareText || `Confira ${button.dataset.shareTitle || 'este conteúdo'} no Conectado em Sergipe.`,
                    url: button.dataset.shareUrl || window.location.href,
                };
                urlInput.value = shareData.url;
                feedback.textContent = '';
                modal.showModal();
            });
        });

        modal.querySelector('[data-social-share-close]').addEventListener('click', () => modal.close());
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });
        modal.addEventListener('close', () => opener?.focus());
        modal.querySelector('[data-social-share-copy]').addEventListener('click', copyLink);
        urlInput.addEventListener('click', () => urlInput.select());

        modal.querySelectorAll('[data-share-network]').forEach((button) => {
            button.addEventListener('click', async () => {
                const network = button.dataset.shareNetwork;
                const encodedUrl = encodeURIComponent(shareData.url);
                const encodedText = encodeURIComponent(shareData.text);

                if (network === 'facebook') {
                    openShareWindow(`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`, network);
                } else if (network === 'whatsapp') {
                    openShareWindow(`https://wa.me/?text=${encodeURIComponent(`${shareData.text} ${shareData.url}`)}`, network);
                } else if (network === 'twitter') {
                    openShareWindow(`https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedUrl}`, network);
                } else if (network === 'instagram') {
                    await copyLink();
                    setFeedback('Link copiado. Cole-o em uma conversa ou publicação no Instagram.');
                    window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
                } else if (navigator.share) {
                    try {
                        await navigator.share(shareData);
                        opener?.dispatchEvent(new CustomEvent('social-share:completed', { bubbles: true, detail: { network: 'native' } }));
                    } catch (error) {
                        if (error?.name !== 'AbortError') setFeedback('Não foi possível abrir as opções de compartilhamento.');
                    }
                } else {
                    await copyLink();
                }
            });
        });
    });
</script>
