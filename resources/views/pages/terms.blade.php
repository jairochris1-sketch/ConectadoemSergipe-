@extends('layouts.app')

@section('title', 'Termos de Uso - Conectado em Sergipe')

@push('styles')
<style>
    /* Estilos de Contraste e Legibilidade dos Termos */
    .terms-card {
        background-color: var(--card, #ffffff);
        border-color: var(--border, #e2e8f0) !important;
    }
    .terms-title {
        color: var(--foreground, #0f172a);
    }
    .terms-text {
        color: var(--muted-foreground, #334155);
        font-size: 0.95rem;
        line-height: 1.7;
    }
    .terms-section-title {
        color: var(--foreground, #0f172a);
    }

    /* Suporte a Modo Escuro */
    html[data-theme="dark"] .terms-card {
        background-color: var(--card, #1e293b) !important;
        border-color: var(--border, #334155) !important;
    }
    html[data-theme="dark"] .terms-title,
    html[data-theme="dark"] .terms-section-title {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .terms-text,
    html[data-theme="dark"] .terms-text li,
    html[data-theme="dark"] .terms-text p {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .terms-box-light {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Card Principal -->
            <div class="terms-card rounded-4 shadow-sm border overflow-hidden">
                
                <!-- Cabeçalho de Destaque com Vidro Fosco -->
                <div class="p-4 p-md-5 text-white position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background-color: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(4px);">
                            <i class="fa-solid fa-file-contract fs-4 text-white"></i>
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-pill small fw-bold mb-1 d-inline-block text-white" style="background-color: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(4px); font-size: 0.75rem;">Regras da Plataforma</span>
                            <h1 class="fw-bold mb-0 text-white" style="font-size: clamp(1.5rem, 4vw, 2.2rem);">Termos de Uso</h1>
                        </div>
                    </div>
                    <p class="text-white opacity-90 small mb-0 mt-3"><i class="fa-regular fa-clock me-1"></i>Última atualização: {{ date('d/m/Y') }}</p>
                </div>

                <!-- Conteúdo dos Termos -->
                <div class="p-4 p-md-5 terms-text">
                    
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-primary rounded-3 p-3 mb-4 d-flex align-items-center gap-3">
                        <i class="fa-solid fa-circle-info fs-4 flex-shrink-0"></i>
                        <div class="small">
                            Seja bem-vindo ao <strong>Conectado em Sergipe</strong>. Agradecemos por utilizar nossa plataforma. Ao acessar ou utilizar nosso site, aplicativo e canais de comunicação, você concorda com as regras estabelecidas abaixo.
                        </div>
                    </div>

                    <!-- Seção 1 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">1</span>
                            Quem Somos e Nossa Missão
                        </h4>
                        <p class="mb-0">
                            O <strong>Conectado em Sergipe</strong> é uma plataforma digital independente, <strong>sem qualquer vínculo governamental</strong>, voltada para a exibição e divulgação de anúncios de imóveis, veículos, produtos, prestação de serviços, empregos, agronegócio e lojas no estado de Sergipe. Nossa missão é conectar anunciantes, empresas e prestadores de serviços a potenciais interessados por meio do nosso website, aplicativo e redes sociais (Instagram e WhatsApp), facilitando a visibilidade e o desenvolvimento econômico local.
                        </p>
                    </div>

                    <!-- Seção 2 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">2</span>
                            Funcionamento e Planos de Anúncio
                        </h4>
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><strong>Plataforma de Divulgação:</strong> Disponibilizamos espaço para publicação de anúncios categorizados e páginas de lojas virtuais/locais.</li>
                            <li class="mb-2"><strong>Assinaturas e Permanência:</strong> O cadastro, o nível de destaque e o tempo de permanência do anúncio exibido no site seguem rigorosamente as regras e o período de vigência do plano contratado pelo usuário (ex.: Planos Prata, Ouro e Diamante).</li>
                            <li class="mb-2"><strong>Contato Direto:</strong> A comunicação entre interessados e anunciantes ocorre diretamente via WhatsApp, telefone ou pelos canais informados pelo próprio anunciante.</li>
                        </ul>
                    </div>

                    <!-- Seção 3 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">3</span>
                            Limitação de Responsabilidade
                        </h4>
                        <p class="mb-2">
                            <strong>Intermediação Estritamente Publicitária:</strong> O Conectado em Sergipe atua exclusivamente como um canal de publicidade e conexão. Não intermediamos pagamentos, entregas, fretes nem participamos das negociações e transações financeiras entre as partes.
                        </p>
                        <p class="mb-0">
                            <strong>Isenção de Vínculo e Garantia:</strong> O contato e a contratação são feitos diretamente entre o interessado e o anunciante. Portanto, não nos responsabilizamos pela qualidade, entrega, procedência, garantia ou conduta ética e comercial dos prestadores de serviço e vendedores listados. Buscamos sempre promover os melhores prestadores com base em avaliações e reputação informada pelos próprios usuários na internet para proporcionar melhor segurança e experiência.
                        </p>
                    </div>

                    <!-- Seção 4 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">4</span>
                            Condições de Acesso e Cadastro
                        </h4>
                        <p class="mb-0">
                            <strong>Idade Mínima:</strong> Para utilizar o site, cadastrar anúncios ou entrar em contato com os anunciantes e prestadores de serviços, o usuário deve ter, no mínimo, <strong>16 anos de idade</strong> (ou capacidade legal bastante). O anunciante responde única e exclusivamente pela veracidade e exatidão de todos os dados e imagens publicados.
                        </p>
                    </div>

                    <!-- Seção 5 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">5</span>
                            Moderação, Denúncias e Remoção de Anúncios
                        </h4>
                        <p class="mb-2">
                            Reservamo-nos o direito de remover anúncios e suspender ou cancelar o acesso de anunciantes e prestadores de serviço nas seguintes hipóteses:
                        </p>
                        <ul class="ps-3 mb-2">
                            <li>Recebimento de denúncias fundamentadas de fraude, engano ou abusos;</li>
                            <li>Confirmação de inadimplência ou má-fé por parte do anunciante;</li>
                            <li>Identificação de produtos, serviços ou conteúdos irregulares, ofensivos ou ilícitos.</li>
                        </ul>
                        <p class="mb-0">
                            Em tais casos, o anúncio ou serviço publicado só poderá ser reativado após a devida resolução do conflito e análise da administração.
                        </p>
                    </div>

                    <!-- Seção 6 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">6</span>
                            Suspensão e Bloqueio da Conta
                        </h4>
                        <p class="mb-3">
                            O <strong>Conectado em Sergipe</strong> poderá suspender temporariamente ou bloquear definitivamente uma conta sempre que identificar indícios de irregularidades, violações destes Termos de Uso ou situações que possam comprometer a segurança da plataforma, de seus usuários ou de terceiros.
                        </p>
                        <p class="mb-3">
                            A conta poderá ser <strong>suspensa para verificação de informações cadastrais</strong>. Nesses casos, o usuário poderá entrar em contato com o suporte para regularizar a situação e solicitar a reativação do acesso, quando cabível.
                        </p>
                        <p class="fw-semibold mb-2">
                            O bloqueio definitivo poderá ocorrer, entre outras hipóteses, quando o usuário:
                        </p>
                        <ul class="ps-3 mb-3">
                            <li class="mb-1">Anunciar ou comercializar produtos, serviços ou conteúdos proibidos por lei;</li>
                            <li class="mb-1">Publicar anúncios enganosos, fraudulentos ou com informações falsas;</li>
                            <li class="mb-1">Utilizar dados falsos ou inconsistentes no cadastro;</li>
                            <li class="mb-1">Anunciar mais de um produto ou serviço em um único anúncio, quando isso contrariar as regras da plataforma;</li>
                            <li class="mb-1">Utilizar mecanismos para burlar limitações, sistemas de segurança ou qualquer funcionalidade do site;</li>
                            <li class="mb-1">Praticar fraudes, golpes ou qualquer atividade ilícita;</li>
                            <li class="mb-1">Cometer violações reiteradas destes Termos de Uso;</li>
                            <li class="mb-1">Utilizar a plataforma de forma abusiva, prejudicando outros usuários ou o funcionamento do serviço.</li>
                        </ul>
                        <p class="mb-2">
                            O Conectado em Sergipe poderá adotar as medidas cabíveis de acordo com a gravidade da infração, incluindo advertência, suspensão temporária, remoção de anúncios ou bloqueio definitivo da conta.
                        </p>
                        <p class="mb-0">
                            Nos casos de bloqueio definitivo motivados por infrações graves ou reincidência, a decisão poderá ser irreversível, sem prejuízo das medidas legais cabíveis.
                        </p>
                    </div>

                    <!-- Seção 7 -->
                    <div class="mb-4 pb-3 border-bottom">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">7</span>
                            Propriedade Intelectual e Direitos Autorais
                        </h4>
                        <p class="mb-0">
                            Respeitamos os direitos de propriedade intelectual. Se você identificar que qualquer conteúdo, imagem ou marca veiculada na plataforma viola seus direitos autorais ou marca registrada, por favor, envie-nos uma mensagem imediatamente para que possamos analisar e tomar as devidas providências cabíveis.
                        </p>
                    </div>

                    <!-- Seção 8 -->
                    <div class="mb-4">
                        <h4 class="fw-bold terms-section-title d-flex align-items-center gap-2 mb-3" style="font-size: 1.2rem;">
                            <span class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center small flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.85rem;">8</span>
                            Fale Conosco e Encarregado de Dados (DPO)
                        </h4>
                        <p class="mb-3">
                            Caso tenha qualquer dúvida sobre os serviços ofertados ou sobre estes Termos de Uso, entre em contato conosco. Para assuntos relacionados à Lei Geral de Proteção de Dados (LGPD), tratamento de Dados Pessoais ou solicitações de privacidade, consulte nosso Encarregado de Proteção de Dados:
                        </p>

                        <div class="p-3 terms-box-light bg-light rounded-3 border d-flex flex-column gap-2 small">
                            <div><i class="fa-solid fa-user-shield text-primary me-2"></i><strong>Encarregado (DPO):</strong> Anderson Pereira</div>
                            <div><i class="fa-solid fa-envelope text-primary me-2"></i><strong>E-mail Oficial:</strong> <a href="mailto:conectadoemsergipe@gmail.com" class="text-primary text-decoration-none fw-bold">conectadoemsergipe@gmail.com</a></div>
                            <div><i class="fa-solid fa-globe text-primary me-2"></i><strong>Plataforma:</strong> Conectado em Sergipe</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
