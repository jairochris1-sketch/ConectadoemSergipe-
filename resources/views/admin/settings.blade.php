@extends('layouts.admin')

@section('title', 'Configurações Globais - Painel Admin')

@section('content')
<div class="admin-page-heading d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-gears text-primary me-2"></i> Configurações do Sistema</h2>
        <p class="text-muted small mb-0">Definições institucionais, redes sociais e modo de funcionamento.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        {{ $errors->first() }}
    </div>
@endif

@php
    $activePublishDesign = \App\Models\Setting::get('publish_page_design', 'design4');
    $publishDesigns = [
        'design4' => [
            'number' => '4',
            'title' => 'Bordas arredondadas',
            'description' => 'Cards compactos, ícones destacados e efeito suave ao passar o mouse.',
            'class' => 'admin-design-preview-four',
        ],
        'design5' => [
            'number' => '5',
            'title' => 'Minimalista',
            'description' => 'Visual claro, espaçado e com identidade verde.',
            'class' => 'admin-design-preview-five',
        ],
    ];
@endphp

<section class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-gradient bg-primary text-white">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <span class="badge bg-white text-primary fw-bold mb-2"><i class="fa-solid fa-code-branch me-1"></i> Sincronização via GitHub</span>
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-cloud-arrow-down me-2"></i> Atualização da Aplicação</h5>
            <p class="mb-0 small text-white-50">Após enviar suas alterações locais para o GitHub, acesse a central para atualizar o código no servidor em 1 clique.</p>
        </div>
        <a href="{{ route('admin.system.update') }}" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm text-primary">
            <i class="fa-solid fa-rotate me-1"></i> Ir para Atualização
        </a>
    </div>
</section>

<section class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-palette text-primary me-2"></i>Modelo da página de anunciar</h5>
                <p class="text-muted small mb-0">Clique em ativar para trocar imediatamente o visual da seleção de categorias.</p>
            </div>
            <a href="{{ route('ad.create') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver página
            </a>
        </div>

        <div class="row g-3">
            @foreach($publishDesigns as $designKey => $design)
                @php
                    $isActiveDesign = $activePublishDesign === $designKey;
                @endphp
                <div class="col-12 col-lg-6">
                    <div class="admin-publish-design-card {{ $isActiveDesign ? 'active' : '' }} h-100">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div>
                                <span class="badge rounded-pill {{ $isActiveDesign ? 'bg-success' : 'bg-secondary bg-opacity-10 text-secondary' }} mb-2">
                                    {{ $isActiveDesign ? 'Ativo agora' : 'Modelo ' . $design['number'] }}
                                </span>
                                <h6 class="fw-bold text-dark mb-1">Modelo {{ $design['number'] }} — {{ $design['title'] }}</h6>
                                <p class="text-muted small mb-0">{{ $design['description'] }}</p>
                            </div>
                        </div>

                        <div class="admin-design-preview {{ $design['class'] }}" aria-hidden="true">
                            <span><i class="fa-solid fa-bag-shopping"></i></span>
                            <span><i class="fa-solid fa-house"></i></span>
                            <span><i class="fa-solid fa-car"></i></span>
                            <span><i class="fa-solid fa-briefcase"></i></span>
                            <span><i class="fa-solid fa-tractor"></i></span>
                        </div>

                        <form action="{{ route('admin.settings.publish_design') }}" method="POST" class="mt-3">
                            @csrf
                            <input type="hidden" name="publish_page_design" value="{{ $designKey }}">
                            <button type="submit" class="btn {{ $isActiveDesign ? 'btn-success' : 'btn-primary' }} rounded-pill px-4 fw-bold" {{ $isActiveDesign ? 'disabled' : '' }}>
                                <i class="fa-solid {{ $isActiveDesign ? 'fa-circle-check' : 'fa-bolt' }} me-1"></i>
                                {{ $isActiveDesign ? 'Modelo ativo' : 'Ativar este modelo' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-sliders text-primary me-2"></i> Dados Gerais</h5>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label for="site_name" class="form-label fw-semibold">Nome da Plataforma</label>
                    <input type="text" class="form-control rounded-3" id="site_name" name="site_name" value="{{ \App\Models\Setting::get('site_name', 'Conectado em Sergipe') }}">
                </div>
                <div class="col-12 col-md-6">
                    <label for="contact_email" class="form-label fw-semibold">E-mail Oficial de Atendimento</label>
                    <input type="email" class="form-control rounded-3" id="contact_email" name="contact_email" value="{{ \App\Models\Setting::get('contact_email', 'contato@conectadoemsergipe.com.br') }}">
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3"><i class="fa-brands fa-whatsapp text-success me-2"></i> Contato & Redes Sociais</h5>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <label for="whatsapp_number" class="form-label fw-semibold">WhatsApp de Suporte</label>
                    <input type="text" class="form-control rounded-3" id="whatsapp_number" name="whatsapp_number" value="{{ \App\Models\Setting::get('whatsapp_number', '5579999999999') }}">
                </div>
                <div class="col-12 col-md-6">
                    <label for="instagram_url" class="form-label fw-semibold">Link do Instagram</label>
                    <input type="url" class="form-control rounded-3" id="instagram_url" name="instagram_url" value="{{ \App\Models\Setting::get('instagram_url', 'https://instagram.com') }}">
                </div>
            </div>

            @php
                $landingImageLabels = [
                    1 => 'Prestador de serviços',
                    2 => 'Loja local',
                    3 => 'Imóveis',
                    4 => 'Veículos',
                    5 => 'Alimentação',
                    6 => 'Agro',
                    7 => 'Profissional local',
                ];
            @endphp

            <section class="border rounded-4 bg-light p-3 p-md-4 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-window-maximize text-primary me-2"></i>Landing page de entrada</h5>
                        <p class="text-muted small mb-0">A página exibida na raiz do site antes de o visitante acessar a plataforma.</p>
                    </div>
                    <a href="{{ route('landing', ['preview' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Visualizar landing page
                    </a>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="landing_enabled" name="landing_enabled" value="1" {{ \App\Models\Setting::get('landing_enabled', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="landing_enabled">Exibir a landing page antes da plataforma</label>
                    <small class="text-muted d-block">Quando desativada, o endereço principal encaminha o visitante diretamente para a plataforma.</small>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="landing_eyebrow" class="form-label fw-semibold">Texto superior</label>
                        <input type="text" class="form-control rounded-3" id="landing_eyebrow" name="landing_eyebrow" value="{{ \App\Models\Setting::get('landing_eyebrow', 'O ecossistema digital de Sergipe') }}">
                    </div>
                    <div class="col-12 col-md-7">
                        <label for="landing_title" class="form-label fw-semibold">Título principal</label>
                        <input type="text" class="form-control rounded-3" id="landing_title" name="landing_title" value="{{ \App\Models\Setting::get('landing_title', 'Conectado em') }}">
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="landing_highlight" class="form-label fw-semibold">Texto azul em destaque</label>
                        <input type="text" class="form-control rounded-3" id="landing_highlight" name="landing_highlight" value="{{ \App\Models\Setting::get('landing_highlight', 'Sergipe') }}">
                    </div>
                    <div class="col-12">
                        <label for="landing_description" class="form-label fw-semibold">Descrição</label>
                        <textarea class="form-control rounded-3" id="landing_description" name="landing_description" rows="3">{{ \App\Models\Setting::get('landing_description', 'A plataforma que conecta serviços, lojas, produtos, imóveis, veículos, empregos, agro e oportunidades dos 75 municípios de Sergipe. Tudo o que você precisa, em um só lugar.') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label for="landing_supporting_text" class="form-label fw-semibold">Texto complementar</label>
                        <input type="text" class="form-control rounded-3" id="landing_supporting_text" name="landing_supporting_text" value="{{ \App\Models\Setting::get('landing_supporting_text', 'Conectamos pessoas, profissionais e negócios locais para impulsionar o que Sergipe tem de melhor.') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="landing_primary_label" class="form-label fw-semibold">Botão de entrada</label>
                        <input type="text" class="form-control rounded-3" id="landing_primary_label" name="landing_primary_label" value="{{ \App\Models\Setting::get('landing_primary_label', 'Entrar no site') }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="landing_secondary_label" class="form-label fw-semibold">Botão institucional</label>
                        <input type="text" class="form-control rounded-3" id="landing_secondary_label" name="landing_secondary_label" value="{{ \App\Models\Setting::get('landing_secondary_label', 'Conhecer a plataforma') }}">
                    </div>
                    <div class="col-12"><hr class="my-2"><h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-layer-group text-primary me-2"></i>Apresentação abaixo da capa</h6></div>
                    <div class="col-12">
                        <label for="landing_about_eyebrow" class="form-label fw-semibold">Chamada superior da apresentação</label>
                        <input type="text" class="form-control rounded-3" id="landing_about_eyebrow" name="landing_about_eyebrow" value="{{ \App\Models\Setting::get('landing_about_eyebrow', 'Descubra Sergipe de um novo jeito') }}">
                    </div>
                    <div class="col-12">
                        <label for="landing_about_title" class="form-label fw-semibold">Título da apresentação</label>
                        <input type="text" class="form-control rounded-3" id="landing_about_title" name="landing_about_title" value="{{ \App\Models\Setting::get('landing_about_title', 'Uma plataforma feita para conectar todo o estado') }}">
                    </div>
                    <div class="col-12">
                        <label for="landing_about_description" class="form-label fw-semibold">Descrição da apresentação</label>
                        <textarea class="form-control rounded-3" id="landing_about_description" name="landing_about_description" rows="3">{{ \App\Models\Setting::get('landing_about_description', 'Do litoral ao sertão, aproximamos pessoas de profissionais, empresas, produtos e oportunidades em uma experiência simples, local e confiável.') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label for="landing_video_url" class="form-label fw-semibold">Link do vídeo no YouTube</label>
                        <input type="url" class="form-control rounded-3" id="landing_video_url" name="landing_video_url" value="{{ \App\Models\Setting::get('landing_video_url', 'https://youtu.be/LS0ObEgTwZk') }}" placeholder="https://youtu.be/LS0ObEgTwZk">
                        <small class="text-muted">O vídeo aparece antes do carrossel 3D das cidades.</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-images text-primary"></i>
                    <h6 class="fw-bold text-dark mb-0">Imagens da composição</h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill">7 imagens</span>
                </div>
                <p class="text-muted small">Os retratos e cenários semelhantes à referência já estão configurados. Envie uma imagem somente quando quiser substituir um card; as fotos da pasta Cidades passam separadamente no fundo.</p>

                <div class="row g-3">
                    @foreach($landingImageLabels as $slot => $label)
                        @php
                            $key = "landing_image_{$slot}";
                            $customImage = \App\Models\Setting::get($key);
                            $landingDefaults = [
                                1 => 'images/landing/prestador.jpg',
                                2 => 'images/landing/loja-local.jpg',
                                3 => 'images/landing/veiculo.jpg',
                                4 => 'images/landing/imovel.jpg',
                                5 => 'images/landing/alimentacao.jpg',
                                6 => 'images/landing/agro.jpg',
                                7 => 'images/landing/profissional.jpg',
                            ];
                            $fallbackImage = $landingDefaults[$slot];
                            $landingImage = $customImage ?: $fallbackImage;
                            $landingImageUrl = $landingImage ? (str_starts_with($landingImage, 'http') ? $landingImage : asset($landingImage)) : null;
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded-4 p-3 bg-white h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <label for="{{ $key }}" class="form-label fw-bold mb-0">{{ $slot }}. {{ $label }}</label>
                                    <span class="badge {{ $customImage ? 'bg-success' : 'bg-secondary bg-opacity-10 text-secondary' }} rounded-pill">
                                        {{ $customImage ? 'Personalizada' : 'Imagem padrão' }}
                                    </span>
                                </div>
                                <div id="{{ $key }}_empty" class="{{ $landingImageUrl ? 'd-none' : '' }} rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center mb-3" style="height: 130px;">
                                    <span><i class="fa-regular fa-image me-1"></i> Sem imagem</span>
                                </div>
                                <div class="mb-3 {{ $landingImageUrl ? '' : 'd-none' }}" id="{{ $key }}_wrapper">
                                    <img src="{{ $landingImageUrl ?? '' }}" id="{{ $key }}_preview" class="w-100 rounded-3 object-fit-cover border" style="height: 130px;" alt="Prévia: {{ $label }}">
                                </div>
                                <input type="file" class="form-control form-control-sm rounded-3" id="{{ $key }}" name="{{ $key }}" accept="image/jpeg,image/png,image/webp" data-preview="{{ $key }}_preview" data-empty="{{ $key }}_empty">
                                <small class="text-muted d-block mt-2">JPG, PNG ou WebP, máximo 5 MB.</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-shield-halved text-primary me-2"></i> Moderação de Imagens & Anti-Pornografia (SafeSearch)</h5>
            <div class="card border rounded-4 bg-light p-3 p-md-4 mb-4">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="image_moderation_enabled" name="image_moderation_enabled" value="1" {{ \App\Models\Setting::get('image_moderation_enabled', '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="image_moderation_enabled">
                        Ativar Moderação Automática de Conteúdo Impróprio
                    </label>
                    <small class="text-muted d-block mt-0.5">Quando ativado, todas as fotos enviadas nos anúncios, lojas, perfis e avaliações são verificadas automaticamente contra pornografia, nudez e violência.</small>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="google_vision_api_key" class="form-label fw-semibold">Chave da API Google Cloud Vision (SafeSearch)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-brands fa-google text-danger"></i></span>
                            <input type="password" class="form-control rounded-end-3" id="google_vision_api_key" name="google_vision_api_key" value="" autocomplete="new-password" placeholder="{{ \App\Models\Setting::get('google_vision_api_key') ? 'Configurada — deixe em branco para manter' : 'Ex: AIzaSyD...' }}">
                        </div>
                        <small class="text-muted d-block mt-1">
                            Insira sua chave de API com o recurso <strong>Google Cloud Vision API</strong> ativado no console do Google Cloud. Deixe em branco se preferir utilizar variáveis de ambiente (`.env`).
                        </small>
                    </div>
                </div>
            <h5 class="fw-bold text-dark mb-3"><i class="fa-brands fa-google text-danger me-2"></i> Autenticação Social com o Google</h5>
            <div class="card border rounded-4 bg-light p-3 p-md-4 mb-4">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="google_login_enabled" name="google_login_enabled" value="1" {{ \App\Models\Setting::get('google_login_enabled', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="google_login_enabled">
                        Ativar Botão "Entrar com o Google" nas telas de login e cadastro
                    </label>
                    <small class="text-muted d-block mt-0.5">Quando ativado, os usuários poderão entrar ou se cadastrar com 1 clique usando a conta do Google.</small>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="google_client_id" class="form-label fw-semibold">Google Client ID</label>
                        <input type="text" class="form-control rounded-3" id="google_client_id" name="google_client_id" value="{{ \App\Models\Setting::get('google_client_id') }}" placeholder="Ex: 123456789-abc.apps.googleusercontent.com">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="google_client_secret" class="form-label fw-semibold">Google Client Secret</label>
                        <input type="password" class="form-control rounded-3" id="google_client_secret" name="google_client_secret" value="" autocomplete="new-password" placeholder="{{ \App\Models\Setting::get('google_client_secret') ? 'Configurado — deixe em branco para manter' : 'Ex: GOCSPX-...' }}">
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info rounded-3 mb-0 py-2.5 px-3 small border-0 bg-info bg-opacity-10 text-info">
                            <i class="fa-solid fa-circle-info me-1.5"></i>
                            <strong>URI de Redirecionamento Autorizado (Callback):</strong> <code>{{ route('auth.google.callback') }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-comment-dots text-primary me-2"></i> Balão de Mensagem (Login, Cadastro e Recuperação)</h5>
            <div class="card border rounded-4 bg-light p-3 p-md-4 mb-4">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="auth_balloon_enabled" name="auth_balloon_enabled" value="1" {{ \App\Models\Setting::get('auth_balloon_enabled', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="auth_balloon_enabled">
                        Exibir Balão Flutuante nas telas de Login, Cadastro e Recuperação
                    </label>
                    <small class="text-muted d-block mt-0.5">Quando ativado, exibe um balão de vidro com mensagens institucionais sobre o site.</small>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="auth_balloon_msg1" class="form-label fw-semibold">Mensagem 1</label>
                        <input type="text" class="form-control rounded-3" id="auth_balloon_msg1" name="auth_balloon_msg1" value="{{ \App\Models\Setting::get('auth_balloon_msg1', 'Conecte-se a serviços, produtos, imóveis, veículos e oportunidades em um único lugar.') }}" placeholder="Ex: Conecte-se a serviços, produtos...">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="auth_balloon_msg2" class="form-label fw-semibold">Mensagem 2 (Opcional)</label>
                        <input type="text" class="form-control rounded-3" id="auth_balloon_msg2" name="auth_balloon_msg2" value="{{ \App\Models\Setting::get('auth_balloon_msg2') }}" placeholder="Ex: Encontre os melhores prestadores de serviço de Sergipe.">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="auth_balloon_msg3" class="form-label fw-semibold">Mensagem 3 (Opcional)</label>
                        <input type="text" class="form-control rounded-3" id="auth_balloon_msg3" name="auth_balloon_msg3" value="{{ \App\Models\Setting::get('auth_balloon_msg3') }}" placeholder="Ex: Anuncie gratuitamente e ganhe destaque no mercado.">
                    </div>
                </div>
            </div>

            <section class="border rounded-4 bg-light p-3 p-md-4 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-people-group text-primary me-2"></i>Grupos por cidade na página inicial</h5>
                        <p class="text-muted small mb-0">Altere a capa e o link do botão “Entrar no grupo”. A ordem abaixo é a mesma exibida na página inicial.</p>
                    </div>
                    <a href="{{ route('home') }}#home-city-groups-title" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver na página inicial
                    </a>
                </div>

                <div class="row g-3">
                    @foreach(\App\Support\HomeCityGroupCatalog::all() as $group)
                        @php
                            $slot = $group['slot'];
                            $coverKey = "home_city_group_cover_{$slot}";
                            $linkKey = "home_city_group_link_{$slot}";
                            $enabledKey = "home_city_group_enabled_{$slot}";
                            $customCover = \App\Models\Setting::get($coverKey);
                            $cover = $customCover ?: $group['cover'];
                            $coverUrl = str_starts_with($cover, 'http') ? $cover : asset($cover);
                            $defaultLink = route('home', ['city' => $group['city']]);
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="border rounded-4 p-3 bg-white h-100 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                    <strong class="text-dark">{{ $slot }}. {{ $group['city'] }}</strong>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $enabledKey }}" name="{{ $enabledKey }}" value="1" {{ \App\Models\Setting::get($enabledKey, $group['default_enabled'] ? '1' : '0') === '1' ? 'checked' : '' }}>
                                        <label class="visually-hidden" for="{{ $enabledKey }}">Exibir grupo de {{ $group['city'] }}</label>
                                    </div>
                                </div>

                                <div class="position-relative mb-3" id="{{ $coverKey }}_wrapper">
                                    <img src="{{ $coverUrl }}" id="{{ $coverKey }}_preview" class="w-100 rounded-3 object-fit-cover border" style="height: 130px;" alt="Capa do grupo de {{ $group['city'] }}">
                                    <span class="position-absolute bottom-0 start-0 m-2 badge {{ $customCover ? 'bg-success' : 'bg-dark bg-opacity-75' }}">{{ $customCover ? 'Capa personalizada' : 'Capa padrão' }}</span>
                                </div>
                                <div id="{{ $coverKey }}_empty" class="d-none rounded-3 border bg-light text-muted align-items-center justify-content-center mb-3" style="height:130px">Sem imagem</div>

                                <label for="{{ $coverKey }}" class="form-label fw-semibold small">Nova capa</label>
                                <input type="file" class="form-control form-control-sm rounded-3 mb-3" id="{{ $coverKey }}" name="{{ $coverKey }}" accept="image/jpeg,image/png,image/webp" data-preview="{{ $coverKey }}_preview" data-empty="{{ $coverKey }}_empty">

                                <label for="{{ $linkKey }}" class="form-label fw-semibold small">Link do botão Entrar</label>
                                <input type="url" class="form-control form-control-sm rounded-3" id="{{ $linkKey }}" name="{{ $linkKey }}" value="{{ old($linkKey, \App\Models\Setting::get($linkKey, $defaultLink)) }}" placeholder="https://...">
                                <small class="text-muted d-block mt-2">Aceita links de WhatsApp, Facebook ou outra comunidade.</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            @php
                $defaultCityBanners = array_values(array_map(function ($filePath) {
                    return 'Cidades/' . basename($filePath);
                }, glob(public_path('Cidades/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}'), GLOB_BRACE) ?: []));

                $bannerGroups = [
                    [
                        'prefix' => 'home_banner',
                        'title' => 'Banners da página inicial',
                        'description' => 'Exibidos no carrossel hero da Home.',
                        'recommended_size' => '1600 × 600 px',
                        'ratio' => '16:6',
                        'route' => route('home'),
                        'max' => 6,
                        'defaults' => $defaultCityBanners,
                    ],
                    [
                        'prefix' => 'services_banner',
                        'title' => 'Banners da página de prestadores',
                        'description' => 'Exibidos no diretório de Prestadores de Serviços.',
                        'recommended_size' => '1600 × 600 px',
                        'ratio' => '16:6',
                        'route' => route('module.services'),
                        'max' => 6,
                        'defaults' => $defaultCityBanners,
                    ],
                    [
                        'prefix' => 'real_estate_banner',
                        'title' => 'Banners da página de imóveis',
                        'description' => 'Exibidos na vitrine de Imóveis. (Fallback para a Home se vazio).',
                        'recommended_size' => '1600 × 600 px',
                        'ratio' => '16:6',
                        'route' => route('module.real_estate'),
                        'max' => 5,
                        'defaults' => $defaultCityBanners,
                    ],
                    [
                        'prefix' => 'vehicles_banner',
                        'title' => 'Banners da página de veículos',
                        'description' => 'Exibidos na vitrine de Veículos. (Fallback para a Home se vazio).',
                        'recommended_size' => '1600 × 600 px',
                        'ratio' => '16:6',
                        'route' => route('module.vehicles'),
                        'max' => 5,
                        'defaults' => $defaultCityBanners,
                    ],
                    [
                        'prefix' => 'culture_banner',
                        'title' => 'Banners da página de Arte & Cultura',
                        'description' => 'Exibidos no topo da página de Arte & Cultura / Varal de Cordel.',
                        'recommended_size' => '1600 × 600 px',
                        'ratio' => '16:6',
                        'route' => route('culture.index'),
                        'max' => 4,
                        'defaults' => [
                            'images/cordelista_hero.png',
                        ],
                    ],
                ];
            @endphp

            @foreach($bannerGroups as $group)
                <section class="border-top pt-4 mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-images text-primary me-2"></i>{{ $group['title'] }}</h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 font-monospace small">
                                    <i class="fa-solid fa-ruler-combined me-1"></i>{{ $group['recommended_size'] }} ({{ $group['ratio'] }})
                                </span>
                            </div>
                            <p class="text-muted small mb-0">
                                {{ $group['description'] }} Até {{ $group['max'] ?? 6 }} imagens.
                                <strong>Recomendado:</strong> {{ $group['recommended_size'] }} (Proporção {{ $group['ratio'] }} Widescreen), máximo 5 MB por foto. Converte automaticamente para WebP otimizado.
                            </p>
                        </div>
                        <a href="{{ $group['route'] }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill text-nowrap">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver página
                        </a>
                    </div>

                    @if($group['prefix'] === 'home_banner')
                        <div class="row g-3 mb-4 p-3 rounded-4 bg-white border">
                            <div class="col-12 col-md-6">
                                <label for="home_banner_brightness" class="form-label fw-bold text-dark d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-sun text-warning me-2"></i> Escurecimento / Claridade do Banner</span>
                                    <span id="brightness_val_badge" class="badge bg-primary rounded-pill">{{ \App\Models\Setting::get('home_banner_brightness', 62) }}%</span>
                                </label>
                                <input type="range" class="form-range" id="home_banner_brightness" name="home_banner_brightness" min="10" max="90" value="{{ \App\Models\Setting::get('home_banner_brightness', 62) }}" oninput="document.getElementById('brightness_val_badge').innerText = this.value + '%'">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>10% (Muito Claro)</span>
                                    <span>62% (Recomendado)</span>
                                    <span>90% (Escuro)</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="home_banner_blur" class="form-label fw-bold text-dark d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-wand-magic-sparkles text-info me-2"></i> Efeito de Desfoque (Blur)</span>
                                    <span id="blur_val_badge" class="badge bg-info rounded-pill">{{ \App\Models\Setting::get('home_banner_blur', 0) }}px</span>
                                </label>
                                <input type="range" class="form-range" id="home_banner_blur" name="home_banner_blur" min="0" max="15" value="{{ \App\Models\Setting::get('home_banner_blur', 0) }}" oninput="document.getElementById('blur_val_badge').innerText = this.value + 'px'">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>0px (Nenhum)</span>
                                    <span>5px (Suave)</span>
                                    <span>15px (Forte)</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row g-3 mb-4">
                        @foreach(range(1, $group['max'] ?? 6) as $slot)
                            @php
                                $key = "{$group['prefix']}_{$slot}";
                                $customSetting = \App\Models\Setting::get($key);
                                $isCustom = !empty($customSetting);

                                if ($isCustom) {
                                    $banner = $customSetting;
                                } else {
                                    $banner = $group['defaults'][$slot - 1] ?? ($group['defaults'][0] ?? null);
                                }

                                $bannerUrl = $banner ? (str_starts_with($banner, 'http') ? $banner : asset($banner)) : null;
                                $bannerName = $banner ? rawurldecode(pathinfo($banner, PATHINFO_FILENAME)) : null;
                            @endphp
                            <div class="col-12 col-md-6 col-xxl-4">
                                <div class="border rounded-4 p-3 h-100 bg-white shadow-sm position-relative">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label for="{{ $key }}" class="form-label fw-bold mb-0">Banner Slot {{ $slot }}</label>
                                        @if($isCustom)
                                            <span class="badge bg-success rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.71rem;">
                                                <i class="fa-solid fa-circle-check me-1"></i> Personalizado em Uso
                                            </span>
                                        @elseif($bannerUrl)
                                            <span class="badge bg-primary bg-gradient rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.71rem;" title="Imagem padrão da pasta Cidades em uso no site">
                                                <i class="fa-solid fa-location-dot me-1"></i> Padrão em Uso
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.71rem;">
                                                Disponível
                                            </span>
                                        @endif
                                    </div>

                                    <div id="{{ $key }}_empty" class="{{ $bannerUrl ? 'd-none' : '' }} rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center mb-3" style="height: 130px;">
                                        <span><i class="fa-regular fa-image me-1"></i> Sem imagem</span>
                                    </div>

                                    <div class="position-relative mb-2 {{ $bannerUrl ? '' : 'd-none' }}" id="{{ $key }}_wrapper">
                                        <img src="{{ $bannerUrl ?? '' }}" id="{{ $key }}_preview" class="w-100 rounded-3 object-fit-cover border shadow-sm" style="height: 130px;" alt="Prévia do banner {{ $slot }}">
                                        @if($bannerName && !$isCustom)
                                            <span class="position-absolute bottom-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-image me-1"></i> {{ $bannerName }}
                                            </span>
                                        @endif
                                    </div>

                                    <input type="file" class="form-control form-control-sm rounded-3" id="{{ $key }}" name="{{ $key }}" accept="image/jpeg,image/png,image/webp" data-preview="{{ $key }}_preview" data-empty="{{ $key }}_empty">
                                    <small class="text-muted d-block mt-1.5" style="font-size: 0.72rem;">
                                        @if($isCustom)
                                            Envie uma nova imagem se desejar substituir a imagem personalizada atual.
                                        @else
                                            Envie uma imagem para substituir o banner padrão (ex: {{ $bannerName ?? 'imagem da cidade' }}) atualmente em exibição no site.
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Salvar Configurações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .admin-publish-design-card {
        padding: 18px;
        color: var(--foreground);
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 16px;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .admin-publish-design-card.active {
        border-color: #198754;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, .12);
    }
    .admin-design-preview {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 7px;
        padding: 14px;
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 13px;
    }
    .admin-design-preview span {
        min-height: 54px;
        display: grid;
        place-items: center;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 11px;
    }
    .admin-design-preview i {
        color: #1265f5;
        font-size: 1.35rem;
    }
    .admin-design-preview i.fa-tractor {
        color: #228b3c;
    }
    .admin-design-preview-five {
        background: #f5f8f1;
    }
    .admin-design-preview-five span {
        border-color: #dce8d5;
        border-radius: 6px;
    }
    .admin-design-preview-five i {
        color: #2f7b2e;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const preview = document.getElementById(input.dataset.preview);

            if (!file || !preview) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
            preview.parentElement?.classList.remove('d-none');

            const emptyState = document.getElementById(input.dataset.empty);
            emptyState?.classList.add('d-none');
        });
    });
</script>
@endpush
