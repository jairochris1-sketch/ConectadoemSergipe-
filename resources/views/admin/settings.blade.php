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
                $bannerGroups = [
                    [
                        'prefix' => 'home_banner',
                        'title' => 'Banners da página inicial',
                        'description' => 'Exibidos somente na página inicial.',
                        'route' => route('home'),
                        'max' => 6,
                        'defaults' => [
                            1 => 'https://images.unsplash.com/photo-1449844908441-8829872d2607?q=80&w=1600&auto=format&fit=crop',
                            2 => 'https://images.unsplash.com/photo-1519003722824-194d4455a60c?q=80&w=1600&auto=format&fit=crop',
                        ],
                    ],
                    [
                        'prefix' => 'services_banner',
                        'title' => 'Banners da página de prestadores',
                        'description' => 'Conjunto separado, exibido somente no diretório de prestadores.',
                        'route' => route('module.services'),
                        'max' => 6,
                        'defaults' => [],
                    ],
                    [
                        'prefix' => 'real_estate_banner',
                        'title' => 'Banners da página de imóveis',
                        'description' => 'Exibidos na página de Imóveis. Quando não houver nenhuma imagem cadastrada aqui, exibirá os banners da página inicial.',
                        'route' => route('module.real_estate'),
                        'max' => 5,
                        'defaults' => [],
                    ],
                    [
                        'prefix' => 'vehicles_banner',
                        'title' => 'Banners da página de veículos',
                        'description' => 'Exibidos na página de Veículos. Quando não houver nenhuma imagem cadastrada aqui, exibirá os banners da página inicial.',
                        'route' => route('module.vehicles'),
                        'max' => 5,
                        'defaults' => [],
                    ],
                ];
            @endphp

            @foreach($bannerGroups as $group)
                <section class="border-top pt-4 mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-images text-primary me-2"></i>{{ $group['title'] }}</h5>
                            <p class="text-muted small mb-0">{{ $group['description'] }} Até {{ $group['max'] ?? 6 }} imagens, recomendado 1600 × 600 px e máximo de 5 MB.</p>
                        </div>
                        <a href="{{ $group['route'] }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill text-nowrap">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Ver página
                        </a>
                    </div>

                    <div class="row g-3 mb-4">
                        @foreach(range(1, $group['max'] ?? 6) as $slot)
                            @php
                                $key = "{$group['prefix']}_{$slot}";
                                $banner = \App\Models\Setting::get($key, $group['defaults'][$slot] ?? null);
                                $bannerUrl = $banner ? (str_starts_with($banner, 'http') ? $banner : asset($banner)) : null;
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="border rounded-4 p-3 h-100 bg-light">
                                    <label for="{{ $key }}" class="form-label fw-bold">Banner {{ $slot }}</label>
                                    <div id="{{ $key }}_empty" class="{{ $bannerUrl ? 'd-none' : '' }} rounded-3 border bg-white text-muted d-flex align-items-center justify-content-center mb-3" style="height: 130px;">
                                        <span><i class="fa-regular fa-image me-1"></i> Espaço disponível</span>
                                    </div>
                                    <img src="{{ $bannerUrl ?? '' }}" id="{{ $key }}_preview" class="{{ $bannerUrl ? '' : 'd-none' }} w-100 rounded-3 object-fit-cover border mb-3" style="height: 130px;" alt="Prévia do banner {{ $slot }}">
                                    <input type="file" class="form-control form-control-sm rounded-3" id="{{ $key }}" name="{{ $key }}" accept="image/jpeg,image/png,image/webp" data-preview="{{ $key }}_preview" data-empty="{{ $key }}_empty">
                                    <small class="text-muted d-block mt-2">Vazio mantém a imagem atual.</small>
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

            const emptyState = document.getElementById(input.dataset.empty);
            emptyState?.classList.add('d-none');
        });
    });
</script>
@endpush
