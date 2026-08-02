@extends('layouts.app')

@section('title', 'Configurações - Conectado em Sergipe')

@section('content')
<div class="container py-4 py-md-5 user-settings-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary mb-2">Sua conta</span>
            <h1 class="h2 fw-bold text-dark mb-1"><i class="fa-solid fa-gear text-primary me-2"></i>Configurações</h1>
            <p class="text-muted mb-0">Personalize a exibição, suas integrações e as notificações recebidas.</p>
        </div>
        <a href="{{ route('user.panel') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Voltar ao painel
        </a>
    </div>

    @if(session('settings_success'))
        <div class="alert alert-success rounded-4">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('settings_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('user.settings.update') }}" method="POST">
        @csrf

        <section class="settings-section">
            <div class="settings-section-heading">
                <span class="settings-section-icon"><i class="fa-solid fa-display"></i></span>
                <div>
                    <h2>Exibição</h2>
                    <p>Escolha como o menu será mostrado no computador.</p>
                </div>
            </div>

            <div class="settings-choice-grid settings-layout-grid">
                <label class="settings-choice">
                    <input type="radio" name="header_layout" value="horizontal" @checked(old('header_layout', $user->header_layout) === 'horizontal')>
                    <span class="settings-choice-preview preview-horizontal" aria-hidden="true">
                        <i></i><i></i><i></i><i></i>
                    </span>
                    <strong>Horizontal atual</strong>
                    <small>Menu fino e contínuo no topo.</small>
                </label>

                <label class="settings-choice">
                    <input type="radio" name="header_layout" value="vertical" @checked(old('header_layout', $user->header_layout) === 'vertical')>
                    <span class="settings-choice-preview preview-vertical" aria-hidden="true">
                        <i></i><i></i><i></i><i></i>
                    </span>
                    <strong>Vertical à esquerda</strong>
                    <small>Menu lateral somente no desktop.</small>
                </label>
            </div>

            <hr>

            <h3 class="settings-subtitle">Aparência</h3>
            <div class="settings-choice-grid settings-theme-grid">
                @foreach([
                    'light' => ['Claro', 'fa-sun'],
                    'dark' => ['Escuro', 'fa-moon'],
                    'system' => ['Sistema', 'fa-desktop'],
                ] as $themeValue => [$themeLabel, $themeIcon])
                    <label class="settings-choice settings-theme-choice">
                        <input type="radio" name="theme_preference" value="{{ $themeValue }}" @checked(old('theme_preference', $user->theme_preference) === $themeValue)>
                        <span class="settings-theme-icon"><i class="fa-solid {{ $themeIcon }}"></i></span>
                        <strong>{{ $themeLabel }}</strong>
                        <small>{{ $themeValue === 'system' ? 'Segue seu dispositivo.' : 'Usar sempre o modo '.mb_strtolower($themeLabel).'.' }}</small>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="settings-section">
            <div class="settings-section-heading">
                <span class="settings-section-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                <div>
                    <h2>Busca inteligente</h2>
                    <p>Controle sugestões automáticas, voz e busca após selecionar os filtros.</p>
                </div>
            </div>

            <div class="settings-switch-row">
                <div>
                    <strong>Ativar busca inteligente</strong>
                    <small>Mostra sugestões enquanto você digita e pesquisa automaticamente 2 segundos após escolher cidade ou categoria.</small>
                </div>
                <input type="hidden" name="smart_search_enabled" value="0">
                <div class="form-check form-switch m-0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="smart_search_enabled"
                        value="1"
                        id="smart_search_enabled"
                        @checked((bool) old('smart_search_enabled', $user->smart_search_enabled))
                    >
                    <label class="visually-hidden" for="smart_search_enabled">Ativar busca inteligente</label>
                </div>
            </div>
        </section>

        <section class="settings-section">
            <div class="settings-section-heading">
                <span class="settings-section-icon"><i class="fa-solid fa-circle-dot text-success"></i></span>
                <div>
                    <h2>Disponibilidade de Atendimento</h2>
                    <p>Defina se você aparece como "Disponível agora" na lista de serviços.</p>
                </div>
            </div>

            <div class="settings-switch-row">
                <div>
                    <strong>Estou disponível para novos atendimentos</strong>
                    <small>Quando ativado (e você estiver ativo no site), seu perfil exibirá a bolinha verde e a tag "Disponível agora".</small>
                </div>
                <input type="hidden" name="is_available" value="0">
                <div class="form-check form-switch m-0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        name="is_available"
                        value="1"
                        id="is_available_switch"
                        @checked((bool) old('is_available', $user->is_available))
                    >
                    <label class="visually-hidden" for="is_available_switch">Estou disponível para novos atendimentos</label>
                </div>
            </div>
        </section>

        <section class="settings-section">
            <div class="settings-section-heading">
                <span class="settings-section-icon"><i class="fa-solid fa-link"></i></span>
                <div>
                    <h2>Integrações</h2>
                    <p>Contatos usados em seus perfis profissionais quando o anúncio não tiver um link próprio.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="settings_whatsapp" class="form-label fw-semibold">WhatsApp</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-brands fa-whatsapp text-success"></i></span>
                        <input type="text" class="form-control" id="settings_whatsapp" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="(79) 99999-9999">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="settings_website" class="form-label fw-semibold">Site</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-globe text-primary"></i></span>
                        <input type="url" class="form-control" id="settings_website" name="website" value="{{ old('website', $user->website) }}" placeholder="https://seusite.com.br">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="settings_instagram" class="form-label fw-semibold">Instagram</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-brands fa-instagram text-danger"></i></span>
                        <input type="text" class="form-control" id="settings_instagram" name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="@seuusuario ou link completo">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="settings_facebook" class="form-label fw-semibold">Facebook</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-brands fa-facebook text-primary"></i></span>
                        <input type="text" class="form-control" id="settings_facebook" name="facebook" value="{{ old('facebook', $user->facebook) }}" placeholder="@suaPagina ou link completo">
                    </div>
                </div>
            </div>
        </section>

        <section class="settings-section">
            <div class="settings-section-heading">
                <span class="settings-section-icon"><i class="fa-solid fa-bell"></i></span>
                <div>
                    <h2>Notificações</h2>
                    <p>Escolha os avisos que deseja receber dentro do site.</p>
                </div>
            </div>

            @foreach([
                'notifications_enabled' => ['Todas as notificações', 'Controle principal para interromper ou liberar todos os avisos.', $user->notifications_enabled],
                'notification_messages_enabled' => ['Novas mensagens', 'Avisos quando outro usuário enviar uma mensagem pelo chat.', $user->notification_messages_enabled],
                'notification_reviews_enabled' => ['Avaliações e respostas', 'Avisos sobre novas avaliações ou respostas em perfis profissionais.', $user->notification_reviews_enabled],
                'notification_reports_enabled' => ['Denúncias e análises', 'Resultados de denúncias e solicitações da equipe de moderação.', $user->notification_reports_enabled],
            ] as $field => [$label, $description, $currentValue])
                <div class="settings-switch-row">
                    <div>
                        <strong>{{ $label }}</strong>
                        <small>{{ $description }}</small>
                    </div>
                    <input type="hidden" name="{{ $field }}" value="0">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" name="{{ $field }}" value="1" id="{{ $field }}" @checked((bool) old($field, $currentValue))>
                        <label class="visually-hidden" for="{{ $field }}">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </section>

        <div class="settings-save-bar">
            <a href="{{ route('user.panel') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="fa-solid fa-floppy-disk me-2"></i>Salvar configurações
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .user-settings-page {
        max-width: 1050px;
    }

    .settings-section {
        margin-bottom: 20px;
        padding: 24px;
        color: var(--foreground);
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, .06);
    }

    .settings-section-heading {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        margin-bottom: 20px;
    }

    .settings-section-heading h2,
    .settings-subtitle {
        margin: 0;
        color: var(--foreground);
        font-size: 1.08rem;
        font-weight: 800;
    }

    .settings-section-heading p {
        margin: 3px 0 0;
        color: var(--muted);
        font-size: .86rem;
    }

    .settings-section-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: grid;
        place-items: center;
        color: #075be8;
        background: color-mix(in srgb, #075be8 10%, transparent);
        border-radius: 11px;
    }

    .settings-choice-grid {
        display: grid;
        gap: 12px;
    }

    .settings-layout-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .settings-theme-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 12px;
    }

    .settings-choice {
        position: relative;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 14px;
        cursor: pointer;
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-radius: 14px;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .settings-choice:has(input:checked) {
        border-color: #075be8;
        box-shadow: 0 0 0 2px rgba(7, 91, 232, .12);
    }

    .settings-choice input {
        position: absolute;
        top: 12px;
        right: 12px;
        accent-color: #075be8;
    }

    .settings-choice strong {
        color: var(--foreground);
        font-size: .9rem;
    }

    .settings-choice small,
    .settings-switch-row small {
        display: block;
        color: var(--muted);
        font-size: .77rem;
    }

    .settings-choice-preview {
        height: 58px;
        display: flex;
        gap: 4px;
        padding: 7px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 9px;
    }

    .settings-choice-preview i {
        display: block;
        background: #adaeb0;
        border-radius: 4px;
    }

    .preview-horizontal {
        align-items: flex-start;
    }

    .preview-horizontal i {
        width: 17%;
        height: 10px;
    }

    .preview-horizontal i:first-child {
        width: 28%;
        background: #075be8;
    }

    .preview-vertical {
        width: 100%;
        flex-direction: column;
        padding-right: 70%;
    }

    .preview-vertical i {
        width: 100%;
        height: 8px;
    }

    .preview-vertical i:first-child {
        background: #075be8;
    }

    .settings-theme-choice {
        align-items: center;
        text-align: center;
    }

    .settings-theme-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        color: #075be8;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 50%;
    }

    .settings-switch-row {
        min-height: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 12px 0;
        border-top: 1px solid var(--border);
    }

    .settings-switch-row:first-of-type {
        border-top: 0;
    }

    .settings-switch-row strong {
        display: block;
        color: var(--foreground);
        font-size: .9rem;
    }

    .settings-switch-row .form-check-input {
        width: 2.6rem;
        height: 1.35rem;
        cursor: pointer;
    }

    .settings-save-bar {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px;
        position: sticky;
        bottom: 12px;
        z-index: 5;
        background: color-mix(in srgb, var(--card) 94%, transparent);
        border: 1px solid var(--border);
        border-radius: 16px;
        backdrop-filter: blur(12px);
    }

    @media (max-width: 575.98px) {
        .settings-section {
            padding: 17px;
            border-radius: 15px;
        }

        .settings-layout-grid,
        .settings-theme-grid {
            grid-template-columns: 1fr;
        }

        .settings-theme-choice {
            align-items: flex-start;
            padding-left: 64px;
            text-align: left;
        }

        .settings-theme-icon {
            position: absolute;
            top: 12px;
            left: 12px;
        }

        .settings-save-bar {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
        }

        .settings-save-bar .btn {
            min-width: 0;
            padding-right: .65rem !important;
            padding-left: .65rem !important;
            font-size: .78rem;
        }
    }
</style>
@endpush
