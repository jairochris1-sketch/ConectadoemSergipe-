@php
    $authBalloonEnabled = \App\Models\Setting::get('auth_balloon_enabled', '1') === '1';
    $authBalloonMessages = array_values(array_filter([
        \App\Models\Setting::get('auth_balloon_msg1', 'Conecte-se a serviços, produtos, imóveis, veículos e oportunidades em um único lugar.'),
        \App\Models\Setting::get('auth_balloon_msg2'),
        \App\Models\Setting::get('auth_balloon_msg3'),
    ], fn ($message) => filled($message)));
@endphp

@if($authBalloonEnabled && $authBalloonMessages !== [])
    <aside id="auth-floating-balloon" class="auth-message-balloon position-absolute top-50 start-50 translate-middle text-white" aria-label="Mensagem do Conectado em Sergipe">
        <div class="auth-message-balloon-glow" aria-hidden="true"></div>

        <button type="button" class="auth-message-balloon-close" data-auth-balloon-close aria-label="Fechar balão" title="Fechar">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>

        <div class="auth-message-balloon-header">
            <span class="auth-message-balloon-icon" aria-hidden="true">
                <i class="fa-solid fa-location-dot"></i>
            </span>
            <div>
                <strong>Conectado em Sergipe</strong>
                <small>Seu estado em um só lugar</small>
            </div>
        </div>

        <div class="auth-message-balloon-body">
            <span class="auth-message-balloon-label">Descubra novas possibilidades</span>
            <h3 id="auth-balloon-text" aria-live="polite">
                {{ $authBalloonMessages[0] }}
            </h3>
        </div>

        <div class="auth-message-balloon-footer">
            <div class="auth-message-balloon-dots" aria-hidden="true">
                @foreach($authBalloonMessages as $messageIndex => $message)
                    <span class="auth-message-balloon-dot {{ $messageIndex === 0 ? 'is-active' : '' }}" data-balloon-dot="{{ $messageIndex }}"></span>
                @endforeach
            </div>
            <div class="auth-message-balloon-navigation" aria-label="Controles das mensagens">
                <button type="button" data-balloon-previous aria-label="Mensagem anterior" title="Mensagem anterior">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" data-balloon-pause aria-label="Pausar mensagens" title="Pausar mensagens">
                    <i class="fa-solid fa-pause" aria-hidden="true"></i>
                </button>
                <button type="button" data-balloon-next aria-label="Próxima mensagem" title="Próxima mensagem">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
            <small><span data-balloon-current>1</span> / {{ count($authBalloonMessages) }}</small>
        </div>
    </aside>

    @once
        <style>
            .auth-message-balloon {
                z-index: 10;
                width: min(82%, 480px);
                height: 315px;
                padding: 22px 24px 18px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                background: linear-gradient(145deg, rgba(15, 23, 42, .9), rgba(22, 37, 63, .82));
                border: 1px solid rgba(255, 255, 255, .2);
                border-radius: 28px;
                box-shadow: 0 28px 70px rgba(2, 8, 23, .42), inset 0 1px 0 rgba(255, 255, 255, .1);
                backdrop-filter: blur(20px) saturate(135%);
                -webkit-backdrop-filter: blur(20px) saturate(135%);
                animation: authBalloonEntrance .5s ease both;
            }

            .auth-message-balloon-glow {
                position: absolute;
                top: -90px;
                right: -70px;
                width: 210px;
                height: 210px;
                background: radial-gradient(circle, rgba(13, 110, 253, .34), transparent 68%);
                pointer-events: none;
            }

            .auth-message-balloon-close {
                position: absolute;
                top: 16px;
                right: 16px;
                z-index: 2;
                display: inline-flex;
                width: 34px;
                height: 34px;
                align-items: center;
                justify-content: center;
                color: #dbeafe;
                background: rgba(255, 255, 255, .08);
                border: 1px solid rgba(255, 255, 255, .14);
                border-radius: 50%;
                transition: background .2s ease, transform .2s ease;
            }

            .auth-message-balloon-close:hover,
            .auth-message-balloon-close:focus-visible {
                color: #fff;
                background: rgba(255, 255, 255, .18);
                outline: none;
                transform: rotate(8deg) scale(1.05);
            }

            .auth-message-balloon-header {
                position: relative;
                display: flex;
                align-items: center;
                gap: 11px;
                padding-right: 42px;
            }

            .auth-message-balloon-icon {
                display: inline-flex;
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                align-items: center;
                justify-content: center;
                color: #bfdbfe;
                background: linear-gradient(135deg, #0d6efd, #2563eb);
                border-radius: 12px;
                box-shadow: 0 8px 22px rgba(13, 110, 253, .3);
            }

            .auth-message-balloon-header strong,
            .auth-message-balloon-header small {
                display: block;
            }

            .auth-message-balloon-header strong {
                color: #fff;
                font-size: .82rem;
            }

            .auth-message-balloon-header small {
                margin-top: 1px;
                color: #93c5fd;
                font-size: .67rem;
            }

            .auth-message-balloon-body {
                position: relative;
                display: flex;
                min-height: 0;
                flex: 1 1 auto;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                padding: 14px 0 12px;
                text-align: center;
            }

            .auth-message-balloon-label {
                display: inline-flex;
                margin-bottom: 9px;
                padding: 4px 10px;
                color: #bfdbfe;
                background: rgba(59, 130, 246, .13);
                border: 1px solid rgba(147, 197, 253, .16);
                border-radius: 999px;
                font-size: .65rem;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
            }

            #auth-balloon-text {
                width: 100%;
                max-width: 390px;
                max-height: 145px;
                margin: 0 auto;
                padding: 0 5px;
                overflow-y: auto;
                color: #fff;
                font-size: clamp(.98rem, 1.7vw, 1.12rem);
                font-weight: 700;
                line-height: 1.45;
                text-wrap: balance;
                transition: opacity .35s ease, transform .35s ease;
            }

            .auth-message-balloon-footer {
                position: relative;
                display: grid;
                grid-template-columns: 1fr auto 1fr;
                align-items: center;
                gap: 12px;
                padding-top: 12px;
                border-top: 1px solid rgba(255, 255, 255, .1);
            }

            .auth-message-balloon-dots {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .auth-message-balloon-dot {
                width: 6px;
                height: 6px;
                background: rgba(255, 255, 255, .28);
                border-radius: 999px;
                transition: width .3s ease, background .3s ease;
            }

            .auth-message-balloon-dot.is-active {
                width: 22px;
                background: #60a5fa;
            }

            .auth-message-balloon-footer > small {
                justify-self: end;
                color: #94a3b8;
                font-size: .66rem;
                font-weight: 700;
            }

            .auth-message-balloon-navigation {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
            }

            .auth-message-balloon-navigation button {
                display: inline-flex;
                width: 32px;
                height: 32px;
                align-items: center;
                justify-content: center;
                padding: 0;
                color: #dbeafe;
                background: rgba(255, 255, 255, .07);
                border: 1px solid rgba(255, 255, 255, .13);
                border-radius: 50%;
                font-size: .72rem;
                transition: color .2s ease, background .2s ease, transform .2s ease;
            }

            .auth-message-balloon-navigation button:hover,
            .auth-message-balloon-navigation button:focus-visible {
                color: #fff;
                background: rgba(59, 130, 246, .3);
                outline: none;
                transform: translateY(-1px);
            }

            .auth-message-balloon-navigation button:disabled {
                cursor: not-allowed;
                opacity: .35;
            }

            @keyframes authBalloonEntrance {
                from { opacity: 0; margin-top: 12px; }
                to { opacity: 1; margin-top: 0; }
            }

            @media (max-width: 767.98px) {
                .auth-message-balloon {
                    width: calc(100% - 32px);
                    height: 310px;
                    padding: 20px 18px 16px;
                    border-radius: 22px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .auth-message-balloon {
                    animation: none;
                }
            }
        </style>
    @endonce

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const messages = @json($authBalloonMessages);
            const balloon = document.getElementById('auth-floating-balloon');
            const balloonText = document.getElementById('auth-balloon-text');
            const closeButton = balloon?.querySelector('[data-auth-balloon-close]');
            const currentMessage = balloon?.querySelector('[data-balloon-current]');
            const dots = balloon?.querySelectorAll('[data-balloon-dot]') ?? [];
            const previousButton = balloon?.querySelector('[data-balloon-previous]');
            const nextButton = balloon?.querySelector('[data-balloon-next]');
            const pauseButton = balloon?.querySelector('[data-balloon-pause]');
            let messageIndex = 0;
            let rotationTimer;
            let isPaused = false;

            const renderMessage = (nextIndex) => {
                if (!balloonText || messages.length === 0) return;

                messageIndex = (nextIndex + messages.length) % messages.length;
                balloonText.style.opacity = '0';
                balloonText.style.transform = 'translateY(5px)';

                window.setTimeout(() => {
                    balloonText.textContent = messages[messageIndex];
                    balloonText.scrollTop = 0;
                    balloonText.style.opacity = '1';
                    balloonText.style.transform = 'translateY(0)';
                    if (currentMessage) currentMessage.textContent = messageIndex + 1;
                    dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === messageIndex));
                }, 350);
            };

            const startRotation = () => {
                window.clearInterval(rotationTimer);

                if (isPaused || messages.length < 2) return;

                rotationTimer = window.setInterval(() => {
                    renderMessage(messageIndex + 1);
                }, 7000);
            };

            const updatePauseButton = () => {
                if (!pauseButton) return;

                const icon = pauseButton.querySelector('i');
                pauseButton.setAttribute('aria-label', isPaused ? 'Continuar mensagens' : 'Pausar mensagens');
                pauseButton.setAttribute('title', isPaused ? 'Continuar mensagens' : 'Pausar mensagens');
                icon?.classList.toggle('fa-pause', !isPaused);
                icon?.classList.toggle('fa-play', isPaused);
            };

            closeButton?.addEventListener('click', () => {
                window.clearInterval(rotationTimer);
                balloon.hidden = true;
            });

            if (!balloonText) return;

            const hasMultipleMessages = messages.length > 1;
            previousButton?.toggleAttribute('disabled', !hasMultipleMessages);
            nextButton?.toggleAttribute('disabled', !hasMultipleMessages);
            pauseButton?.toggleAttribute('disabled', !hasMultipleMessages);

            previousButton?.addEventListener('click', () => {
                renderMessage(messageIndex - 1);
                startRotation();
            });

            nextButton?.addEventListener('click', () => {
                renderMessage(messageIndex + 1);
                startRotation();
            });

            pauseButton?.addEventListener('click', () => {
                isPaused = !isPaused;
                updatePauseButton();
                startRotation();
            });

            updatePauseButton();
            startRotation();
        });
    </script>
@endif
