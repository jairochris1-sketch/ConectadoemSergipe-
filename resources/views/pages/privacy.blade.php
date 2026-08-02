@extends('layouts.app')

@section('title', 'Política de Privacidade - Conectado em Sergipe')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">LGPD & Segurança</span>
                <h1 class="fw-bold text-dark mb-4">Política de Privacidade</h1>
                
                <p class="text-muted mb-4">Última atualização: {{ date('d/m/Y') }}</p>

                <h5 class="fw-bold text-dark mt-4">1. Coleta de Informações</h5>
                <p class="text-secondary lh-lg">Coletamos informações pessoais que você nos fornece voluntariamente ao se cadastrar, criar um anúncio ou entrar em contato conosco. Isso inclui nome, e-mail, telefone/WhatsApp, cidade e dados dos anúncios publicados.</p>

                <h5 class="fw-bold text-dark mt-4">2. Uso das Informações</h5>
                <p class="text-secondary lh-lg">As informações coletadas são utilizadas exclusivamente para exibir seus anúncios aos compradores interessados, permitir o contato entre as partes, aprimorar a experiência do usuário e garantir a segurança do marketplace.</p>

                <h5 class="fw-bold text-dark mt-4">3. Proteção de Dados</h5>
                <p class="text-secondary lh-lg">Adotamos medidas técnicas e organizacionais rigorosas para proteger seus dados contra acessos não autorizados, perdas ou alterações ilícitas, em total conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>

                <h5 class="fw-bold text-dark mt-4">4. Compartilhamento de Dados</h5>
                <p class="text-secondary lh-lg">Não vendemos nem alugamos suas informações pessoais a terceiros. As informações de contato fornecidas em anúncios públicos serão exibidas apenas para conectar compradores e vendedores.</p>
            </div>
        </div>
    </div>
</div>
@endsection
