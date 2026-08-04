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
    background-color: #0d3880; /* Cor azul base caso a imagem não cubra tudo */
    background-image: url('{{ asset("images/mapa-sergipe-conectado.jpg") }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 999999;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity 0.8s ease, visibility 0.8s ease;
}

/* Criamos um pseudo-elemento para dar o tom azul escuro por cima da cidade e a logo centralizada, 
   caso a imagem seja só a cidade. Se a imagem já tiver a logo e o tom azul, podemos usar .splash-screen direto. 
   Mas vamos usar a imagem "mapa-sergipe-conectado.png" ou "mapa-sergipe-conectado-azul.png" no centro caso necessário.
   Como o usuário forneceu a imagem pronta, vamos focar em exibir a splash perfeita. */
.splash-screen::after {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    /* Se a imagem enviada (mapa-sergipe-conectado.jpg) for apenas a cidade, 
       isso aplica a sobreposição azul com a logo, senão comentamos essa parte.
       Vou colocar a imagem que já tem a logo no centro: */
    background-image: url('{{ asset("images/mapa-sergipe-conectado-azul.png") }}');
    background-size: contain; /* ou 60% se for logo */
    background-position: center;
    background-repeat: no-repeat;
    z-index: 2;
    animation: splashFadeInUp 0.8s ease-out forwards;
}

.splash-screen::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(4, 38, 99, 0.75); /* Filtro azul sobre as cidades */
    z-index: 1;
}

.splash-screen.hidden {
    opacity: 0;
    visibility: hidden;
}

@keyframes splashFadeInUp {
    0% {
        opacity: 0;
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
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
