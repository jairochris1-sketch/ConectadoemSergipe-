@if(request()->is('/'))
<div id="splash-screen" class="splash-screen">
</div>

<style>
.splash-screen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #0d3880; /* Cor azul base para preencher os cantos se a tela for muito larga */
    background-image: url('{{ asset("images/mapa-sergipe-conectado-azul.png") }}');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    z-index: 999999;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.8s ease, visibility 0.8s ease;
}

.splash-screen.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

/* Animação mais suave para a própria div se desejar, mas como é só a imagem de fundo, 
   o próprio fade out no final já cumpre o papel. */
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
