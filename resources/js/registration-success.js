import { DotLottie } from '@lottiefiles/dotlottie-web';
import dotLottieWasmUrl from '@lottiefiles/dotlottie-web/dotlottie-player.wasm?url';

const successPanel = document.getElementById('registration-success');

if (successPanel) {
    const canvas = successPanel.querySelector('[data-registration-animation]');
    const loginUrl = successPanel.dataset.loginUrl;
    const redirectDelay = Number(successPanel.dataset.redirectDelay || 4200);
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (canvas && !reduceMotion) {
        DotLottie.setWasmUrl(dotLottieWasmUrl);

        try {
            const animation = new DotLottie({
                autoplay: true,
                loop: false,
                canvas,
                src: successPanel.dataset.animationSrc,
            });
            const showFallback = () => {
                canvas.closest('.registration-success-animation')?.classList.add('is-fallback');
            };

            animation.addEventListener('loadError', showFallback);
            animation.addEventListener('renderError', showFallback);
        } catch (error) {
            canvas.closest('.registration-success-animation')?.classList.add('is-fallback');
        }
    }

    window.setTimeout(() => {
        window.location.assign(loginUrl);
    }, reduceMotion ? 1200 : redirectDelay);
}
