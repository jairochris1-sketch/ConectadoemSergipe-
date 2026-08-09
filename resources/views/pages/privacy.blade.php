@extends('layouts.app')

@section('title', 'Política de Privacidade - Conectado em Sergipe')

@push('styles')
<style>
    /* Estilos de Contraste e Legibilidade de Privacidade */
    .privacy-card {
        background-color: var(--card, #ffffff);
        border-color: var(--border, #e2e8f0) !important;
    }
    .privacy-title {
        color: var(--foreground, #0f172a);
    }
    .privacy-text {
        color: var(--muted-foreground, #334155);
        font-size: 0.95rem;
        line-height: 1.7;
    }
    .privacy-section-title {
        color: var(--foreground, #0f172a);
    }

    /* Suporte a Modo Escuro */
    html[data-theme="dark"] .privacy-card {
        background-color: var(--card, #1e293b) !important;
        border-color: var(--border, #334155) !important;
    }
    html[data-theme="dark"] .privacy-title,
    html[data-theme="dark"] .privacy-section-title {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .privacy-text,
    html[data-theme="dark"] .privacy-text p {
        color: #cbd5e1 !important;
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Card Principal -->
            <div class="privacy-card rounded-4 shadow-sm border overflow-hidden">
                
                <!-- Cabeçalho de Destaque com Vidro Fosco -->
                <div class="p-4 p-md-5 text-white position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="p-2 rounded-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background-color: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(4px);">
                            <i class="fa-solid fa-shield-halved fs-4 text-white"></i>
                        </div>
                        <div>
                            <span class="px-3 py-1 rounded-pill small fw-bold mb-1 d-inline-block text-white" style="background-color: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(4px); font-size: 0.75rem;">LGPD & Segurança</span>
                            <h1 class="fw-bold mb-0 text-white" style="font-size: clamp(1.5rem, 4vw, 2.2rem);">Política de Privacidade</h1>
                        </div>
                    </div>
                    <p class="text-white opacity-90 small mb-0 mt-3"><i class="fa-regular fa-clock me-1"></i>Última atualização: {{ date('d/m/Y') }}</p>
                </div>

                <!-- Conteúdo de Privacidade -->
                <div class="p-4 p-md-5 privacy-text">
                    <h5 class="fw-bold privacy-section-title mt-2 mb-3">1. Coleta de Informações</h5>
                    <p class="mb-4">Coletamos informações pessoais que você nos fornece voluntariamente ao se cadastrar, criar um anúncio ou entrar em contato conosco. Isso inclui nome, e-mail, telefone/WhatsApp, cidade e dados dos anúncios publicados.</p>

                    <h5 class="fw-bold privacy-section-title mt-4 mb-3">2. Uso das Informações</h5>
                    <p class="mb-4">As informações coletadas são utilizadas exclusivamente para exibir seus anúncios aos compradores interessados, permitir o contato entre as partes, aprimorar a experiência do usuário e garantir a segurança do marketplace.</p>

                    <h5 class="fw-bold privacy-section-title mt-4 mb-3">3. Recomendações e personalização</h5>
                    <p class="mb-4">Para organizar recomendações na Comunidade, podemos considerar cidade, anúncios visualizados, favoritos e cliques realizados dentro do próprio Conectado em Sergipe. Visitantes não autenticados são reconhecidos somente por uma chave pseudonimizada da sessão; este recurso não armazena endereço IP nem agente do navegador. Impressões e preferências de visitantes são mantidas por até 30 dias, e as vinculadas a uma conta por até 90 dias. O usuário pode escolher os modos “Recentes” ou “Perto de você”, ocultar recomendações específicas e utilizar o sinal de privacidade do navegador.</p>

                    <h5 class="fw-bold privacy-section-title mt-4 mb-3">4. Proteção de Dados</h5>
                    <p class="mb-4">Adotamos medidas técnicas e organizacionais rigorosas para proteger seus dados contra acessos não autorizados, perdas ou alterações ilícitas, em total conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>

                    <h5 class="fw-bold privacy-section-title mt-4 mb-3">5. Compartilhamento de Dados</h5>
                    <p class="mb-0">Não vendemos nem alugamos suas informações pessoais a terceiros. As informações de contato fornecidas em anúncios públicos serão exibidas apenas para conectar compradores e vendedores.</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
