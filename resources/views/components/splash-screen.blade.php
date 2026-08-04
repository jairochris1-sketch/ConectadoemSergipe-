@if(request()->is('/'))
<div id="splash-screen" class="splash-screen">
    <div class="splash-content">
        <img src="{{ asset('images/logo_full_white.png') }}" alt="Logo Conectado em Sergipe" class="splash-logo">
        
        <h1 class="splash-title">Conectado em <span class="splash-highlight">Sergipe</span></h1>
        <p class="splash-subtitle">Encontre prestadores de serviços, lojas e comércio local no Conectado em Sergipe.</p>
    </div>
</div>

<style>
.splash-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #17181c;
    background: radial-gradient(circle at center, #232530 0%, #13141a 100%);
    z-index: 999999;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transition: opacity 0.6s ease, visibility 0.6s ease;
}

.splash-screen.hidden {
    opacity: 0;
    visibility: hidden;
}

.splash-content {
    text-align: center;
    animation: splashFadeInUp 0.8s ease-out forwards;
    padding: 0 20px;
    opacity: 0;
}

.splash-logo {
    width: 160px;
    margin-bottom: 50px;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
}

.splash-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 15px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    letter-spacing: -0.5px;
}

.splash-highlight {
    background: linear-gradient(90deg, #c4e4ff 0%, #7dd3fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.splash-subtitle {
    color: #9ca3af;
    font-size: 1.05rem;
    max-width: 400px;
    margin: 0 auto;
    line-height: 1.6;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

@keyframes splashFadeInUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
    window.addEventListener('load', () => {
        const splash = document.getElementById('splash-screen');
        if (splash) {
            setTimeout(() => {
                splash.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 1800);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const splash = document.getElementById('splash-screen');
        if (splash) {
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => {
                if (!splash.classList.contains('hidden')) {
                    splash.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }, 4000);
        }
    });
</script>
@endif
