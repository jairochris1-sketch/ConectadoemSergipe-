<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro rápido de perfil | Conectado em Sergipe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/quick-profile.css') }}?v=1.0">
</head>
<body>
@php
    $quickProfileInitialStep = 0;
    if ($errors->hasAny(['main_city', 'whole_state', 'cities', 'cities.*', 'neighborhood'])) {
        $quickProfileInitialStep = 1;
    } elseif ($errors->hasAny(['services', 'services.*', 'description', 'photos', 'photos.*', 'terms'])) {
        $quickProfileInitialStep = 2;
    }
@endphp
<main class="quick-profile-shell" data-quick-profile data-categories='@json($categoriesByProfileKind)' data-initial-kind="{{ old('profile_kind', request('profile_kind')) }}" data-initial-step="{{ $quickProfileInitialStep }}">
    <div class="quick-profile-texture" aria-hidden="true"></div>
    <section class="quick-profile-layout">
        <aside class="quick-profile-story">
            <a class="quick-profile-brand" href="{{ route('home') }}" aria-label="Voltar ao Conectado em Sergipe">
                <span class="quick-profile-brand-mark"><i class="fa-solid fa-location-dot"></i></span>
                <span>Conectado <b>em Sergipe</b></span>
            </a>
            <div class="quick-profile-story-content">
                <div class="quick-profile-route"><span></span> Cadastro rápido <b id="quick-side-step">01</b></div>
                <h2>Seu trabalho mais perto de quem precisa.</h2>
                <p>Crie sua conta e publique um perfil profissional em poucos passos. Depois você poderá completar ou editar tudo pelo seu painel.</p>
                <div id="quick-desktop-preview"></div>
            </div>
            <footer>Feito para quem movimenta Sergipe <span></span></footer>
        </aside>

        <section class="quick-profile-form-panel" aria-labelledby="quick-step-title">
            <header class="quick-profile-mobile-brand">
                <a class="quick-profile-brand" href="{{ route('home') }}">
                    <span class="quick-profile-brand-mark"><i class="fa-solid fa-location-dot"></i></span>
                    <span>Conectado <b>em Sergipe</b></span>
                </a>
                <a href="{{ route('home') }}" class="quick-profile-close" aria-label="Fechar cadastro"><i class="fa-solid fa-xmark"></i></a>
            </header>

            <div class="quick-profile-mobile-preview">
                <span>Prévia do perfil <small>Atualização ao vivo</small></span>
                <div id="quick-mobile-preview"></div>
            </div>

            <nav class="quick-profile-steps" aria-label="Etapas do cadastro">
                <button class="is-active" type="button" data-go-step="0"><span>01</span><em>Seu perfil</em></button>
                <button type="button" data-go-step="1"><span>02</span><em>Cobertura</em></button>
                <button type="button" data-go-step="2"><span>03</span><em>Vitrine</em></button>
            </nav>

            @if($errors->any())
                <div class="quick-profile-errors" role="alert">
                    <strong>Revise os dados informados:</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="quick-profile-heading">
                <span id="quick-step-kicker">Comece por aqui</span>
                <h1 id="quick-step-title">Que perfil você quer criar?</h1>
                <p id="quick-step-description">Escolha como deseja aparecer no Conectado em Sergipe.</p>
            </div>

            <form id="quick-profile-form" action="{{ route('quick-profile.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <section class="quick-profile-section" data-step="0">
                    <fieldset class="quick-profile-field">
                        <legend>Escolha o tipo do perfil <b>*</b></legend>
                        <div class="quick-profile-kind-grid">
                            @foreach($profileKinds as $profileKind)
                                <label class="quick-profile-kind-card">
                                    <input type="radio" name="profile_kind" value="{{ $profileKind['key'] }}" @checked(old('profile_kind', request('profile_kind')) === $profileKind['key'])>
                                    <span class="quick-profile-kind-icon"><i class="{{ $profileKind['icon'] }}"></i></span>
                                    <span><strong>{{ $profileKind['label'] }}</strong><small>{{ $profileKind['subtitle'] }}</small></span>
                                    <i class="fa-solid fa-check quick-profile-kind-check"></i>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    @guest
                        <div class="quick-profile-account-box">
                            <div><i class="fa-solid fa-user-plus"></i><span><strong>Sua conta de acesso</strong><small>Estes dados serão usados para entrar no site.</small></span></div>
                            <div class="quick-profile-fields-two">
                                <div class="quick-profile-field"><label for="account_name">Seu nome completo <b>*</b></label><input id="account_name" name="account_name" value="{{ old('account_name') }}" autocomplete="name" required></div>
                                <div class="quick-profile-field"><label for="email">E-mail <b>*</b></label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></div>
                                <div class="quick-profile-field"><label for="phone">Celular / WhatsApp <b>*</b></label><input id="phone" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel" placeholder="(79) 99999-9999" required></div>
                                <div class="quick-profile-field"><label for="password">Senha <b>*</b></label><input id="password" type="password" name="password" minlength="6" autocomplete="new-password" required></div>
                                <div class="quick-profile-field quick-profile-field-wide"><label for="password_confirmation">Confirmar senha <b>*</b></label><input id="password_confirmation" type="password" name="password_confirmation" minlength="6" autocomplete="new-password" required></div>
                            </div>
                        </div>
                    @else
                        <div class="quick-profile-signed-in"><i class="fa-solid fa-circle-check"></i><span>O perfil será vinculado à conta de <strong>{{ auth()->user()->name }}</strong>.</span></div>
                    @endguest

                    <div class="quick-profile-field"><label for="name">Nome profissional ou da empresa <b>*</b></label><small>É o nome que será exibido para os visitantes.</small><input id="name" name="name" value="{{ old('name') }}" placeholder="Ex.: Studio Ana Santos" required></div>
                    <div class="quick-profile-field"><label for="category">Categoria principal <b>*</b></label><small>As opções mudam conforme o tipo de perfil.</small><select id="category" name="category" required><option value="">Escolha primeiro o tipo do perfil</option></select></div>

                    <div class="quick-profile-liberal-fields" data-liberal-fields hidden>
                        <div class="quick-profile-field"><label for="liberal_credential">Registro profissional <b>*</b></label><input id="liberal_credential" name="liberal_credential" value="{{ old('liberal_credential') }}" placeholder="Ex.: OAB/SE 12345"></div>
                        <div class="quick-profile-field"><label for="liberal_credential_issuer">Conselho ou órgão <b>*</b></label><input id="liberal_credential_issuer" name="liberal_credential_issuer" value="{{ old('liberal_credential_issuer') }}" placeholder="Ex.: OAB, CRM, CRO"></div>
                    </div>

                    <div class="quick-profile-reassurance"><i class="fa-solid fa-bolt"></i><span><strong>Cadastro rápido e gratuito</strong><small>Você poderá completar o perfil depois.</small></span></div>
                </section>

                <section class="quick-profile-section" data-step="1" hidden>
                    <div class="quick-profile-field"><label for="main_city">Cidade principal <b>*</b></label><small>É onde seu perfil aparecerá primeiro.</small><input id="main_city" name="main_city" value="{{ old('main_city') }}" list="quick-cities" placeholder="Digite sua cidade" required><datalist id="quick-cities">@foreach($cities as $city)<option value="{{ $city }}">@endforeach</datalist></div>
                    <label class="quick-profile-state-toggle"><input id="whole_state" name="whole_state" value="1" type="checkbox" @checked(old('whole_state'))><span class="quick-profile-check">✓</span><span><strong>Atendo em todo o estado de Sergipe</strong><small>Seu perfil poderá aparecer em qualquer município.</small></span></label>
                    <div class="quick-profile-field"><div class="quick-profile-label-row"><label for="city_search">Outras cidades onde atende</label><small>Até 5</small></div><input id="city_search" type="search" placeholder="Buscar cidade"><div class="quick-profile-city-grid">@foreach($cities as $city)<label><input type="checkbox" name="cities[]" value="{{ $city }}" @checked(in_array($city, old('cities', []), true))><span>{{ $city }}</span></label>@endforeach</div></div>
                    <div class="quick-profile-field"><label for="neighborhood">Bairros ou regiões <small>(opcional)</small></label><input id="neighborhood" name="neighborhood" value="{{ old('neighborhood') }}" placeholder="Ex.: Centro, Jardins, Farolândia"></div>
                </section>

                <section class="quick-profile-section" data-step="2" hidden>
                    <div class="quick-profile-category-summary"><i class="fa-solid fa-sparkles"></i><span><small>Sua categoria</small><strong id="quick-category-summary">Sua categoria</strong></span><button type="button" data-go-step="0">Editar</button></div>
                    <fieldset class="quick-profile-field"><legend>Serviços ou áreas de atuação <b>*</b></legend><small>Escolha até cinco opções.</small><div class="quick-profile-service-grid" id="quick-service-grid"></div></fieldset>
                    <div class="quick-profile-field"><div class="quick-profile-label-row"><label for="description">Fale sobre seu trabalho <b>*</b></label><small><span id="quick-description-count">0</span>/500</small></div><textarea id="description" name="description" maxlength="500" rows="4" placeholder="Conte sua experiência, seus diferenciais e como você pode ajudar." required>{{ old('description') }}</textarea></div>
                    <div class="quick-profile-field"><label for="photos">Fotos do seu trabalho <small>(opcional)</small></label><small>JPG, PNG ou WebP. Até cinco imagens.</small><label class="quick-profile-upload"><input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple><i class="fa-solid fa-cloud-arrow-up"></i><strong id="quick-photo-label">Escolher fotos</strong></label><div class="quick-profile-thumbs" id="quick-photo-thumbs"></div></div>
                    <div class="quick-profile-crop" id="quick-crop-editor" hidden><div><strong>Ajuste a primeira foto</strong><button type="button" id="quick-close-crop" aria-label="Fechar">×</button></div><canvas id="quick-crop-canvas" width="320" height="320"></canvas><label>Zoom <input id="quick-crop-zoom" type="range" min="1" max="3" step="0.01" value="1"></label><div><button type="button" id="quick-cancel-crop">Cancelar</button><button type="button" id="quick-confirm-crop">Usar esta foto</button></div></div>
                    <label class="quick-profile-terms"><input name="terms" type="checkbox" value="1" required @checked(old('terms'))><span class="quick-profile-check">✓</span><span>Li e aceito os <a href="{{ route('page.terms') }}" target="_blank">Termos de Uso</a> e a <a href="{{ route('page.privacy') }}" target="_blank">Política de Privacidade</a>.</span></label>
                </section>

                <div class="quick-profile-actions">
                    <button class="quick-profile-primary" id="quick-next-button" type="submit">Continuar para minha cobertura <i class="fa-solid fa-arrow-right"></i></button>
                    <button class="quick-profile-back" id="quick-back-button" type="button" hidden><i class="fa-solid fa-arrow-left"></i> Voltar</button>
                </div>
            </form>
            <footer class="quick-profile-panel-footer"><span>Cadastro gratuito · Sem compromisso</span><a href="{{ route('login') }}">Já tenho conta</a></footer>
        </section>
    </section>
</main>
<script>
window.quickProfileOldServices = @json(old('services', []));
window.quickProfileOldCategory = @json(old('category'));
</script>
<script src="{{ asset('js/quick-profile.js') }}?v=1.0"></script>
</body>
</html>
