@extends('layouts.app')

@section('title', ($ad->module === 'services' ? 'Editar Perfil Profissional' : 'Editar Anúncio') . ' - Conectado em Sergipe')

@section('content')
@php
    $isService = $ad->module === 'services';
    $categoryLists = [
        'services' => ['Eletricista', 'Encanador', 'Pintor', 'Mecânico', 'Advogado', 'Faxineira / Diarista', 'Marcenaria', 'TI / Informática', 'Frete e Mudanças', 'Restaurante / Pizzaria', 'Pedreiro', 'Jardineiro'],
        'products' => ['Celulares & Telefonia', 'Informática', 'Roupas & Calçados', 'Móveis & Decoração', 'Eletrodomésticos', 'Esporte & Lazer'],
        'real_estate' => ['Casas Aluguel/Venda', 'Apartamentos', 'Terrenos & Lotes', 'Salas Comerciais', 'Sítios & Chácaras'],
        'vehicles' => ['Carros Usados/Seminovos', 'Motos', 'Caminhões', 'Peças & Acessórios'],
        'jobs' => ['Vagas de Emprego', 'Currículos / Procurando', 'Estágios'],
        'agro' => ['Animais & Pecuária', 'Tratores & Máquinas', 'Insumos & Sementes'],
    ];
    $availableCategories = $categoryLists[$ad->module] ?? [];
    $galleryImages = $ad->images->reject(fn ($image) => $image->image_path === $ad->logo);
    $liberalDetails = (array) data_get($ad->technical_specs, 'liberal_profile', []);
    $isCrmProfile = $isService
        && $ad->profile_kind === 'liberal_professional'
        && \App\Support\ServiceBookingCatalog::usesCrmCategory($ad->display_category);
@endphp

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-2">
                            {{ $isService ? 'Atualizar Perfil' : 'Atualizar Anúncio' }}
                        </span>
                        <h2 class="fw-bold text-dark">{{ $isService ? 'Editar perfil profissional' : 'Editar anúncio' }}</h2>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('ad.update', $ad->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">{{ $isService ? 'Nome do perfil profissional' : 'Título do anúncio' }} *</label>
                            <input type="text" class="form-control form-control-lg rounded-3 shadow-sm" id="title" name="title" value="{{ old('title', $ad->title) }}" required>
                        </div>

                        @if($isService)
                            <div class="mb-3">
                                <label for="public_address" class="form-label fw-semibold">Endereço público do local de atendimento (opcional)</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="public_address" name="public_address" value="{{ old('public_address', $ad->public_address) }}" maxlength="255" placeholder="Ex: Rua das Flores, 120, Centro">
                                <small class="text-muted">Preencha somente se clientes puderem ir ao local. O endereço aparecerá no perfil com o botão “Como chegar”.</small>
                            </div>
                        @endif

                        @if($isService)
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <span class="text-muted small d-block">Segmento Profissional</span>
                                        <strong class="text-dark fs-6">{{ $ad->display_category }}</strong>
                                        @php
                                            $kindLabel = \App\Models\Ad::PROFILE_KINDS[$ad->profile_kind]['label'] ?? ($ad->profile_kind ?: 'Prestador de serviços');
                                        @endphp
                                        <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $kindLabel }}</span>
                                    </div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5"><i class="fa-solid fa-lock me-1"></i> Não alterável</span>
                                </div>
                                <small class="text-muted d-block mt-2" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> O tipo de serviço e categoria são fixos para preservar o histórico de avaliações, filtros de busca e reputação.
                                </small>
                            </div>
                            <input type="hidden" name="profile_kind" value="{{ old('profile_kind', $ad->profile_kind ?: 'professional') }}">
                            <input type="hidden" name="category_name" value="{{ old('category_name', $ad->display_category) }}">
                        @else
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <span class="text-muted small d-block">Categoria do Anúncio</span>
                                        <strong class="text-dark fs-6">{{ $ad->display_category }}</strong>
                                    </div>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5"><i class="fa-solid fa-lock me-1"></i> Fixa</span>
                                </div>
                                <small class="text-muted d-block mt-2" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> A categoria do anúncio é fixa para garantir a indexação correta nas buscas da plataforma.
                                </small>
                            </div>
                            <input type="hidden" name="category_name" value="{{ old('category_name', $ad->display_category) }}">
                        @endif

                        <div class="row g-3 mb-3">
                            @unless($isService)
                                <div class="col-12 col-md-6">
                                    <label for="price" class="form-label fw-semibold">Preço (R$) *</label>
                                    <input type="text" inputmode="decimal" class="form-control form-control-lg rounded-3" id="price" name="price" value="{{ old('price', number_format($ad->price, 2, ',', '.')) }}" placeholder="Ex: 80.000,00" required>
                                </div>
                            @endunless
                            <div class="col-12 {{ $isService ? '' : 'col-md-6' }}">
                                <label for="city" class="form-label fw-semibold">Cidade em SE *</label>
                                <select class="form-select form-select-lg rounded-3" id="city" name="city" required>
                                    @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                        <option value="{{ $cityName }}" {{ old('city', $ad->city) === $cityName ? 'selected' : '' }}>{{ $cityName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if($ad->module === 'products')
                            <div class="border rounded-4 p-3 mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="text-primary fs-4"><i class="fa-solid fa-store"></i></span>
                                    <div class="flex-grow-1">
                                        <h3 class="h6 fw-bold mb-1">Exibir este produto na minha loja</h3>
                                        @if($availableStores->isNotEmpty())
                                            <p class="text-muted small mb-3">
                                                Você pode incluir, trocar ou remover este produto da sua vitrine.
                                                Limite do plano {{ auth()->user()->subscriptionPlanLabel() }}:
                                                {{ $storeProductLimit === null ? 'ilimitado' : "{$storeProductLimit} por loja" }}.
                                            </p>
                                            <input type="hidden" name="include_in_store" value="0">
                                            <div class="form-check form-switch mb-2">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    role="switch"
                                                    id="include_in_store"
                                                    name="include_in_store"
                                                    value="1"
                                                    {{ old('include_in_store', $ad->store_id ? '1' : '0') === '1' ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label fw-semibold" for="include_in_store">Incluir na loja</label>
                                            </div>
                                            <select class="form-select rounded-3" id="store_id" name="store_id">
                                                @foreach($availableStores as $store)
                                                    @php
                                                        $isCurrentStore = $ad->store_id === $store->id;
                                                        $storeUnavailable = !$isCurrentStore && (
                                                            !$store->active
                                                            || !$store->isModerationApproved()
                                                            || ($storeProductLimit !== null && $store->products_count >= $storeProductLimit)
                                                        );
                                                    @endphp
                                                    <option
                                                        value="{{ $store->id }}"
                                                        {{ (string) old('store_id', $ad->store_id) === (string) $store->id ? 'selected' : '' }}
                                                        {{ $storeUnavailable ? 'disabled' : '' }}
                                                    >
                                                        {{ $store->name }}
                                                        · {{ $store->products_count }}/{{ $storeProductLimit === null ? '∞' : $storeProductLimit }} produtos
                                                        {{ !$store->active ? ' (desativada)' : '' }}
                                                        {{ !$store->isModerationApproved() ? ' (suspensa)' : '' }}
                                                        {{ !$isCurrentStore && $storeProductLimit !== null && $store->products_count >= $storeProductLimit ? ' (limite atingido)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('store_id')
                                                <div class="text-danger small mt-2">{{ $message }}</div>
                                            @enderror
                                        @else
                                            <p class="text-muted small mb-2">Crie sua loja para poder incluir este produto em uma vitrine.</p>
                                            <a href="{{ route('store.create') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fa-solid fa-plus me-1"></i> Criar minha loja
                                            </a>
                                        @endif
                                        <div class="mt-3">
                                            <label for="display_mode" class="form-label fw-semibold mb-1">Forma de exibição</label>
                                            <select class="form-select rounded-3" id="display_mode" name="display_mode">
                                                <option value="default" @selected(old('display_mode', $ad->display_mode ?: 'default') === 'default')>Usar padrão da loja</option>
                                                <option value="catalog" @selected(old('display_mode', $ad->display_mode) === 'catalog')>Compra rápida no catálogo</option>
                                                <option value="individual" @selected(old('display_mode', $ad->display_mode) === 'individual')>Página individual completa</option>
                                            </select>
                                        </div>
                                        @include('ads._commerce-fields', ['product' => $ad])
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($isService)
                            <div class="mb-3">
                                <label for="region" class="form-label fw-semibold">Atende em quais regiões?</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="region" name="region" value="{{ old('region', $ad->region) }}" placeholder="Ex: Centro, Atalaia, Farolândia, Jardins">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="instagram" class="form-label fw-semibold">Instagram</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text"><i class="fa-brands fa-instagram"></i></span>
                                        <input type="text" class="form-control" id="instagram" name="instagram" value="{{ old('instagram', $ad->instagram) }}" placeholder="@seuinstagram ou link completo">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="facebook" class="form-label fw-semibold">Facebook</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text"><i class="fa-brands fa-facebook-f"></i></span>
                                        <input type="text" class="form-control" id="facebook" name="facebook" value="{{ old('facebook', $ad->facebook) }}" placeholder="Usuário ou link completo">
                                    </div>
                                </div>
                            </div>

                            <!-- HORÁRIOS DE ATENDIMENTO -->
                            <div class="border rounded-4 p-3 p-md-4 mb-4 bg-light">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="fa-regular fa-clock text-primary me-2"></i> Horários de Atendimento
                                        </h6>
                                        <small class="text-muted">Defina seus horários de trabalho para os clientes. (Padrão: 08:00 às 18:00)</small>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-check me-1"></i> Padrão: 08:00 às 18:00
                                    </span>
                                </div>

                                <div class="row g-2">
                                    @php
                                        $savedHours = is_array($ad->business_hours) ? $ad->business_hours : [];
                                        $weekDays = [
                                            'segunda' => ['label' => 'Segunda-feira', 'default_open' => '08:00', 'default_close' => '18:00', 'closed' => false],
                                            'terca'   => ['label' => 'Terça-feira',   'default_open' => '08:00', 'default_close' => '18:00', 'closed' => false],
                                            'quarta'  => ['label' => 'Quarta-feira',  'default_open' => '08:00', 'default_close' => '18:00', 'closed' => false],
                                            'quinta'  => ['label' => 'Quinta-feira',  'default_open' => '08:00', 'default_close' => '18:00', 'closed' => false],
                                            'sexta'   => ['label' => 'Sexta-feira',   'default_open' => '08:00', 'default_close' => '18:00', 'closed' => false],
                                            'sabado'  => ['label' => 'Sábado',        'default_open' => '08:00', 'default_close' => '12:00', 'closed' => false],
                                            'domingo' => ['label' => 'Domingo',       'default_open' => '08:00', 'default_close' => '18:00', 'closed' => true],
                                        ];
                                    @endphp

                                    @foreach($weekDays as $dayKey => $dayInfo)
                                        @php
                                            $daySaved = $savedHours[$dayKey] ?? null;
                                            $isClosed = isset($daySaved['closed']) ? (bool)$daySaved['closed'] : (!isset($daySaved) ? $dayInfo['closed'] : false);
                                            $openVal = $daySaved['open'] ?? $dayInfo['default_open'];
                                            $closeVal = $daySaved['close'] ?? $dayInfo['default_close'];
                                        @endphp
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="p-2.5 bg-white rounded-3 border">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <strong class="text-dark small">{{ $dayInfo['label'] }}</strong>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="closed_{{ $dayKey }}" name="business_hours[{{ $dayKey }}][closed]" value="1" {{ $isClosed ? 'checked' : '' }} onchange="toggleDayHours('{{ $dayKey }}')">
                                                        <label class="form-check-label text-muted" for="closed_{{ $dayKey }}" style="font-size: 0.75rem;">Fechado</label>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5" id="hours_wrap_{{ $dayKey }}" style="{{ $isClosed ? 'opacity: 0.35;' : '' }}">
                                                    <input type="time" class="form-control form-control-sm rounded-2 text-center" name="business_hours[{{ $dayKey }}][open]" value="{{ $openVal }}" {{ $isClosed ? 'disabled' : '' }}>
                                                    <span class="text-muted small">às</span>
                                                    <input type="time" class="form-control form-control-sm rounded-2 text-center" name="business_hours[{{ $dayKey }}][close]" value="{{ $closeVal }}" {{ $isClosed ? 'disabled' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">{{ $isService ? 'Sobre o profissional e seus serviços' : 'Descrição completa' }} *</label>
                            <textarea class="form-control rounded-3" id="description" name="description" rows="5" required>{{ old('description', $ad->description) }}</textarea>
                        </div>

                        @if($isService && $ad->profile_kind === 'liberal_professional')
                            <div class="border rounded-4 p-3 p-md-4 mb-4 bg-light">
                                <h3 class="h6 fw-bold text-dark mb-3"><i class="fa-solid fa-user-shield text-primary me-2"></i>Documentação e registros</h3>
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <label for="liberal_credential" class="form-label fw-semibold">Registro profissional *</label>
                                        <input type="text" class="form-control" id="liberal_credential" name="liberal_credential" value="{{ old('liberal_credential', $liberalDetails['credential'] ?? '') }}" maxlength="150" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="liberal_credential_issuer" class="form-label fw-semibold">Conselho ou órgão emissor *</label>
                                        <input type="text" class="form-control" id="liberal_credential_issuer" name="liberal_credential_issuer" value="{{ old('liberal_credential_issuer', $liberalDetails['credential_issuer'] ?? '') }}" maxlength="255" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="liberal_credential_url" class="form-label fw-semibold">Link oficial para consulta (opcional)</label>
                                        <input type="url" class="form-control" id="liberal_credential_url" name="liberal_credential_url" value="{{ old('liberal_credential_url', $liberalDetails['credential_url'] ?? '') }}" maxlength="500">
                                    </div>
                                </div>

                                @if($isCrmProfile)
                                    <div class="border rounded-3 p-3 mb-4 bg-white" id="crm-verification-panel">
                                        <strong class="d-block mb-1"><i class="fa-solid fa-stethoscope text-primary me-2"></i>Consulta do registro médico</strong>
                                        <small class="text-muted d-block mb-3">A consulta confirma que o CRM está ativo, mas não comprova a identidade do titular da conta.</small>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12">
                                                <label for="liberal_credential_name" class="form-label fw-semibold">Nome completo como consta no CRM *</label>
                                                <input type="text" class="form-control" id="liberal_credential_name" name="liberal_credential_name" value="{{ old('liberal_credential_name', $liberalDetails['credential_registry_name'] ?? '') }}" maxlength="255" required>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <label for="liberal_credential_state" class="form-label fw-semibold">UF do CRM *</label>
                                                <select class="form-select" id="liberal_credential_state" name="liberal_credential_state" required>
                                                    @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $state)
                                                        <option value="{{ $state }}" @selected(old('liberal_credential_state', $liberalDetails['credential_state'] ?? 'SE') === $state)>{{ $state }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-8"><button type="button" class="btn btn-outline-primary w-100" id="verify-crm-button"><i class="fa-solid fa-magnifying-glass me-1"></i> Consultar CRM</button></div>
                                        </div>
                                        <div class="small mt-3 d-none" id="crm-verification-result" role="status" aria-live="polite"></div>
                                    </div>
                                @endif

                                <h3 class="h6 fw-bold text-dark mb-3"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>Formação</h3>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="liberal_education" class="form-label fw-semibold">Curso ou formação</label>
                                        <input type="text" class="form-control" id="liberal_education" name="liberal_education" value="{{ old('liberal_education', $liberalDetails['education'] ?? '') }}" maxlength="255">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="liberal_education_institution" class="form-label fw-semibold">Instituição de ensino (opcional)</label>
                                        <input type="text" class="form-control" id="liberal_education_institution" name="liberal_education_institution" value="{{ old('liberal_education_institution', $liberalDetails['education_institution'] ?? '') }}" maxlength="255">
                                    </div>
                                </div>
                                <fieldset class="mt-4">
                                    <legend class="h6 fw-bold text-dark mb-2"><i class="fa-solid fa-laptop-medical text-primary me-2"></i>Formas de atendimento</legend>
                                    @php $savedServiceModes = old('service_modes', $ad->service_modes ?: ['presencial']); @endphp
                                    <div class="d-flex flex-wrap gap-3">
                                        <label class="form-check border rounded-3 px-3 py-2 mb-0"><input class="form-check-input" type="checkbox" name="service_modes[]" value="presencial" @checked(in_array('presencial', $savedServiceModes, true))><span class="form-check-label ms-1">Atendimento presencial</span></label>
                                        <label class="form-check border rounded-3 px-3 py-2 mb-0"><input class="form-check-input" type="checkbox" name="service_modes[]" value="online" @checked(in_array('online', $savedServiceModes, true))><span class="form-check-label ms-1">Atendimento online / teleconsulta</span></label>
                                    </div>
                                </fieldset>
                            </div>
                        @endif

                        <div class="border rounded-4 p-3 p-md-4 mb-4">
                            <h3 class="h5 fw-bold mb-3">Fotos do {{ $isService ? 'perfil' : 'anúncio' }}</h3>

                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label for="logo" class="form-label fw-semibold">Imagem principal</label>
                                    @if($ad->logo)
                                        <div class="position-relative d-inline-block mb-2">
                                            <img src="{{ asset($ad->logo) }}" class="rounded-3 border object-fit-cover d-block" style="width: 150px; height: 120px;" alt="Imagem principal atual">
                                            <label class="form-check mt-2 text-danger fw-semibold">
                                                <input class="form-check-input" type="checkbox" name="remove_logo" value="1">
                                                Excluir imagem atual
                                            </label>
                                        </div>
                                    @endif
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" style="border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem;">
                                        <button type="button" class="btn btn-outline-secondary" onclick="importImageByUrl('logo')" style="border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem;"><i class="fa-solid fa-link"></i> Importar por Link</button>
                                    </div>
                                    <small class="text-muted d-block mt-1">Escolha outra imagem ou importe via link para substituir a atual.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="banner" class="form-label fw-semibold">Capa</label>
                                    @if($ad->banner)
                                        <div class="mb-2">
                                            <img src="{{ asset($ad->banner) }}" class="rounded-3 border object-fit-cover d-block" style="width: 100%; max-width: 260px; height: 120px;" alt="Capa atual">
                                            <label class="form-check mt-2 text-danger fw-semibold">
                                                <input class="form-check-input" type="checkbox" name="remove_banner" value="1">
                                                Excluir capa atual
                                            </label>
                                        </div>
                                    @endif
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="banner" name="banner" accept="image/*" style="border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem;">
                                        <button type="button" class="btn btn-outline-secondary" onclick="importImageByUrl('banner')" style="border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem;"><i class="fa-solid fa-link"></i> Importar por Link</button>
                                    </div>
                                    <small class="text-muted d-block mt-1">Escolha outra capa para substituir a atual.</small>
                                </div>
                            </div>

                            @if($galleryImages->isNotEmpty())
                                <hr class="my-4">
                                <label class="form-label fw-semibold">Galeria atual</label>
                                <div class="row g-3 mb-3">
                                    @foreach($galleryImages as $image)
                                        <div class="col-6 col-md-4">
                                            <div class="border rounded-3 p-2 h-100">
                                                <img src="{{ asset($image->image_path) }}" class="rounded-2 object-fit-cover w-100" style="height: 110px;" alt="Foto da galeria">
                                                <label class="form-check mt-2 text-danger small fw-semibold">
                                                    <input class="form-check-input" type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}">
                                                    Excluir foto
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <label for="images" class="form-label fw-semibold">Adicionar novas fotos à galeria</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple style="border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem;">
                                <button type="button" class="btn btn-outline-secondary" onclick="importImageByUrl('images')" style="border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem;"><i class="fa-solid fa-link"></i> Importar por Link</button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Salvar alterações
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function importImageByUrl(inputId) {
        let url = prompt("Cole o link (URL) da imagem que você copiou do Google ou de outro site:");
        if (!url || !url.trim()) return;
        url = url.trim();

        try {
            document.body.style.cursor = 'wait';
            
            let response = await fetch(`https://api.allorigins.win/raw?url=${encodeURIComponent(url)}`);
            if (!response.ok) throw new Error("Não foi possível carregar a imagem. Verifique se o link é público.");
            
            let blob = await response.blob();
            let ext = blob.type.split('/')[1] || 'jpg';
            if (!blob.type.startsWith('image/')) {
                throw new Error("O link fornecido não parece ser uma imagem válida.");
            }

            let file = new File([blob], `imported_image_${Date.now()}.${ext}`, { type: blob.type });

            let dataTransfer = new DataTransfer();
            let fileInput = document.getElementById(inputId);
            
            if (fileInput.multiple) {
                for (let i = 0; i < fileInput.files.length; i++) {
                    dataTransfer.items.add(fileInput.files[i]);
                }
                dataTransfer.items.add(file);
            } else {
                dataTransfer.items.add(file);
            }
            
            fileInput.files = dataTransfer.files;
            
            let event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
            
            alert("Imagem importada com sucesso! Lembre-se de salvar as alterações.");
            
        } catch(e) {
            alert("Erro ao importar a imagem: " + e.message);
        } finally {
            document.body.style.cursor = 'default';
        }
    }

    function toggleDayHours(dayKey) {
        const checkbox = document.getElementById('closed_' + dayKey);
        const hoursWrap = document.getElementById('hours_wrap_' + dayKey);
        if (checkbox && hoursWrap) {
            hoursWrap.style.opacity = checkbox.checked ? '0.35' : '1';
            hoursWrap.querySelectorAll('input').forEach(input => {
                input.disabled = checkbox.checked;
            });
        }
    }
</script>
@endpush

@if($isCrmProfile)
    @push('scripts')
    <script>
        document.getElementById('verify-crm-button')?.addEventListener('click', async function () {
            const result = document.getElementById('crm-verification-result');
            this.disabled = true;
            result.className = 'small mt-3 alert alert-info';
            result.textContent = 'Consultando o registro...';
            try {
                const response = await fetch(@json(route('professionals.crm.verify')), {
                    method: 'POST',
                    headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || ''},
                    body: JSON.stringify({
                        credential: document.getElementById('liberal_credential')?.value || '',
                        state: document.getElementById('liberal_credential_state')?.value || '',
                        category: @json($ad->display_category),
                        professional_name: document.getElementById('liberal_credential_name')?.value || '',
                    }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Não foi possível consultar o CRM.');
                const professional = data.professional || {};
                result.className = 'small mt-3 alert alert-success';
                result.textContent = `${professional.name} · CRM/${professional.state} ${professional.number} · ${professional.situation}`;
            } catch (error) {
                result.className = 'small mt-3 alert alert-warning';
                result.textContent = error.message;
            } finally {
                result.classList.remove('d-none');
                this.disabled = false;
            }
        });
    </script>
    @endpush
@endif

@if($ad->module === 'products' && $availableStores->isNotEmpty())
    @push('scripts')
        <script>


            document.addEventListener('DOMContentLoaded', () => {
                const includeInStore = document.getElementById('include_in_store');
                const storeSelect = document.getElementById('store_id');

                const syncStoreSelection = () => {
                    storeSelect.disabled = !includeInStore.checked;
                    storeSelect.required = includeInStore.checked && storeSelect.options.length > 1;
                };

                includeInStore.addEventListener('change', syncStoreSelection);
                syncStoreSelection();
            });
        </script>
    @endpush
@endif

@push('scripts')
<script>
    document.getElementById('price')?.addEventListener('blur', function() {
        let value = this.value.trim().replace(/[^\d,.]/g, '');
        if (!value) return;

        if (value.includes(',')) {
            value = value.replace(/\./g, '').replace(',', '.');
        } else if ((value.match(/\./g) || []).length > 1 || /^\d+\.\d{3}$/.test(value)) {
            value = value.replace(/\./g, '');
        }

        const amount = Number(value);
        if (Number.isFinite(amount)) {
            this.value = amount.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
</script>
@endpush
