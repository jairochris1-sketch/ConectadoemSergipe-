@extends('layouts.app')

@section('title', 'Termos de Uso - Conectado em Sergipe')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3">Regras da Plataforma</span>
                <h1 class="fw-bold text-dark mb-4">Termos de Uso</h1>
                
                <p class="text-muted mb-4">Última atualização: {{ date('d/m/Y') }}</p>

                <h5 class="fw-bold text-dark mt-4">1. Aceitação dos Termos</h5>
                <p class="text-secondary lh-lg">Ao acessar ou utilizar a plataforma Conectado em Sergipe, você concorda expressamente em cumprir estes Termos de Uso e todas as leis e regulamentos aplicáveis.</p>

                <h5 class="fw-bold text-dark mt-4">2. Responsabilidade pelos Anúncios</h5>
                <p class="text-secondary lh-lg">O anunciante é o único e exclusivo responsável pela veracidade, legalidade, qualidade e garantia dos produtos, veículos, imóveis ou serviços anunciados. É proibida a publicação de itens ilícitos ou falsificados.</p>

                <h5 class="fw-bold text-dark mt-4">3. Conduta do Usuário</h5>
                <p class="text-secondary lh-lg">Os usuários se comprometem a manter uma comunicação respeitosa e ética nas mensagens e negociações dentro da plataforma.</p>

                <h5 class="fw-bold text-dark mt-4">4. Modificações dos Termos</h5>
                <p class="text-secondary lh-lg">Reservamo-nos o direito de atualizar estes termos a qualquer momento para refletir melhorias na plataforma ou alterações regulatórias.</p>
            </div>
        </div>
    </div>
</div>
@endsection
