@extends('layouts.app')

@section('title', 'Publicar Anúncio - Conectado em Sergipe')

@section('content')
@php
    $publishDesign = \App\Models\Setting::get('publish_page_design', 'design4');
    $publishDesign = in_array($publishDesign, ['design4', 'design5'], true) ? $publishDesign : 'design4';
@endphp
<div class="container py-4 py-md-5 publish-page publish-design-{{ $publishDesign }}">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            
            <!-- Top Navbar / Header com Voltar ao Início e Salvar Rascunho -->
            <div class="d-flex align-items-center justify-content-between mb-4 publish-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Início
                    </a>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2" onclick="saveDraft()">
                        <i class="fa-regular fa-bookmark me-1"></i> Salvar rascunho
                    </button>
                    <a href="{{ route('home') }}" class="btn-close" aria-label="Close"></a>
                </div>
            </div>

            <!-- CARD UNIFICADO (STEPPER + FORMULÁRIO) -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 publish-form-card">

                <!-- CABEÇALHO DE ETAPAS -->
                <div class="publish-stepper-header border-bottom p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center position-relative px-2 px-md-5 publish-step-track">
                        
                        <!-- Etapa 1 -->
                        <div class="text-center z-index-2 step-item active" id="step-nav-1">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">1</div>
                            <small class="step-label d-none d-md-block fw-semibold">Categoria</small>
                        </div>
                        <!-- Etapa 2 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-2">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">2</div>
                            <small class="step-label d-none d-md-block fw-semibold">Informações & Contato</small>
                        </div>
                        <!-- Etapa 3 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-3">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">3</div>
                            <small class="step-label d-none d-md-block fw-semibold">Fotos</small>
                        </div>
                        <!-- Etapa 4 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-4">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">4</div>
                            <small class="step-label d-none d-md-block fw-semibold">Publicar</small>
                        </div>

                    </div>
                </div>

                <div class="card-body p-4 p-md-5">

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <div class="fw-bold mb-1">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                Confira os dados do anúncio:
                            </div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('ad.store') }}" method="POST" enctype="multipart/form-data" id="wizardForm" novalidate>
                        @csrf
                        <input type="hidden" name="profile_kind" id="service-profile-kind" value="{{ old('profile_kind', $requestedProfileKind) }}">

                        <!-- ================= ETAPA 1: CATEGORIA ================= -->
                        <div class="wizard-step" id="wizard-step-1">
                            <div class="mb-4">
                                <h2 class="fw-bold text-dark mb-1" style="font-size: 1.85rem; letter-spacing: -0.5px;">O que você deseja anunciar?</h2>
                                <p class="text-muted fs-6 mb-0">Escolha a opção que melhor representa o que você quer criar no Conectado em Sergipe.</p>
                            </div>

                            {{-- OS 2 GRANDES CARDS MESTRES (SERVIÇOS vs PRODUTOS, IMÓVEIS E MAIS) --}}
                            <div class="row g-3 mb-4">
                                <div class="col-12 col-md-6">
                                    <div class="master-choice-card is-active shadow-sm" id="card-choice-services" onclick="selectPublishMode('services')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="master-choice-icon is-services">
                                                <i class="fa-solid fa-briefcase"></i>
                                            </div>
                                            <div class="master-choice-copy flex-grow-1">
                                                <strong class="master-choice-title text-dark d-block">Serviços</strong>
                                                <span class="master-choice-text text-muted">Divulgue seus serviços profissionais e encontre mais clientes.</span>
                                            </div>
                                            <div class="master-choice-check">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="master-choice-card shadow-sm" id="card-choice-items" onclick="selectPublishMode('items')">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="master-choice-icon is-items">
                                                <i class="fa-solid fa-bag-shopping"></i>
                                            </div>
                                            <div class="master-choice-copy flex-grow-1">
                                                <strong class="master-choice-title text-dark d-block">Vendas e Anúncios</strong>
                                                <span class="master-choice-text text-muted">Anuncie produtos, imóveis, veículos, empregos, lojas e agro.</span>
                                            </div>
                                            <div class="master-choice-check">
                                                <i class="fa-solid fa-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="radio" class="d-none" name="module" id="mod_services" value="services" {{ old('module', $requestedModule) === 'services' ? 'checked' : '' }} onchange="selectModule('services')">

                            {{-- SEÇÃO 1: COMO VOCÊ ATUA NA ÁREA DE SERVIÇOS? --}}
                            <div id="section-services-flow" class="publish-flow-section mb-4">
                                <h5 class="fw-bold text-dark mb-3" style="font-size: 1.15rem;">Como você atua na área de serviços?</h5>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('professional')">
                                            <div class="service-role-icon">
                                                <i class="fa-regular fa-user"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Prestador de serviços</strong>
                                            <small class="service-role-desc text-muted">Você trabalha por conta própria e oferece seus serviços.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('service_company')">
                                            <div class="service-role-icon">
                                                <i class="fa-regular fa-building"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Empresa de serviços</strong>
                                            <small class="service-role-desc text-muted">Você representa uma empresa que oferece serviços.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('liberal_professional')">
                                            <div class="service-role-icon">
                                                <i class="fa-solid fa-user-graduate"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Profissional liberal</strong>
                                            <small class="service-role-desc text-muted">Você atua de forma autônoma com formação técnica ou superior.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('cultural_artist')">
                                            <div class="service-role-icon">
                                                <i class="fa-solid fa-palette"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Artista / Profissional da cultura</strong>
                                            <small class="service-role-desc text-muted">Você atua nas áreas artísticas ou culturais.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('agro_producer')">
                                            <div class="service-role-icon">
                                                <i class="fa-solid fa-tractor"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Produtor rural / Agro</strong>
                                            <small class="service-role-desc text-muted">Produtor, criador, agricultor ou comércio rural.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('store_commerce')">
                                            <div class="service-role-icon">
                                                <i class="fa-solid fa-store"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Loja ou comércio</strong>
                                            <small class="service-role-desc text-muted">Ponto comercial físico, virtual ou mercadinho.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('real_estate_agency')">
                                            <div class="service-role-icon">
                                                <i class="fa-solid fa-house-chimney"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Imobiliária</strong>
                                            <small class="service-role-desc text-muted">Corretoras e administradoras de imóveis.</small>
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-3">
                                        <button type="button" class="service-role-card w-100 text-start" onclick="chooseProfessionalProfile('hiring_company')">
                                            <div class="service-role-icon">
                                                <i class="fa-solid fa-briefcase"></i>
                                            </div>
                                            <strong class="service-role-title d-block text-dark">Empresa contratante</strong>
                                            <small class="service-role-desc text-muted">Empresas e negócios publicando vagas.</small>
                                        </button>
                                    </div>
                                </div>

                                {{-- BOX INFORMATIVO AZUL SUAVE --}}
                                <div class="info-help-callout d-flex align-items-center gap-3 p-3.5 rounded-4 mb-4">
                                    <div class="info-help-icon flex-shrink-0">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </div>
                                    <div class="info-help-copy">
                                        <strong class="d-block text-dark fw-bold mb-0.5">Atenção: A categoria e o tipo de atuação não poderão ser alterados após a publicação.</strong>
                                        <span class="text-muted small">Essa regra garante a integridade dos filtros, busca local, SEO e a confiança dos clientes que encontram o seu perfil.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- SEÇÃO 2: VENDAS E ANÚNCIOS (GRID 3x2 IDÊNTICO AO MOCKUP) --}}
                            <div id="section-items-flow" class="publish-flow-section mb-4 d-none">
                                <h5 class="fw-bold text-dark mb-3" style="font-size: 1.15rem;">Escolha a categoria do seu anúncio:</h5>

                                <div class="row g-3 mb-4">
                                    {{-- 1. PRODUTO --}}
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <input type="radio" class="btn-check d-none" name="module" id="mod_products" value="products" {{ old('module', $requestedModule) === 'products' ? 'checked' : '' }} onchange="selectModule('products')">
                                        <label class="item-choice-card w-100 {{ old('module', $requestedModule) === 'products' ? 'is-active' : '' }}" for="mod_products" id="label-mod-products">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="item-choice-icon">
                                                    <i class="fa-solid fa-cart-shopping"></i>
                                                </div>
                                                <div class="item-choice-copy flex-grow-1">
                                                    <strong class="item-choice-title text-dark d-block">Produto</strong>
                                                    <small class="item-choice-desc text-muted">Novos, usados, agro e produtos da roça.</small>
                                                </div>
                                                <div class="item-choice-check">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- 2. IMÓVEL --}}
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <input type="radio" class="btn-check d-none" name="module" id="mod_real_estate" value="real_estate" {{ old('module', $requestedModule) === 'real_estate' ? 'checked' : '' }} onchange="selectModule('real_estate')">
                                        <label class="item-choice-card w-100 {{ old('module', $requestedModule) === 'real_estate' ? 'is-active' : '' }}" for="mod_real_estate" id="label-mod-real_estate">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="item-choice-icon">
                                                    <i class="fa-solid fa-house"></i>
                                                </div>
                                                <div class="item-choice-copy flex-grow-1">
                                                    <strong class="item-choice-title text-dark d-block">Imóvel</strong>
                                                    <small class="item-choice-desc text-muted">Aluguel, venda, chácaras ou temporada.</small>
                                                </div>
                                                <div class="item-choice-check">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- 3. VEÍCULO --}}
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <input type="radio" class="btn-check d-none" name="module" id="mod_vehicles" value="vehicles" {{ old('module', $requestedModule) === 'vehicles' ? 'checked' : '' }} onchange="selectModule('vehicles')">
                                        <label class="item-choice-card w-100 {{ old('module', $requestedModule) === 'vehicles' ? 'is-active' : '' }}" for="mod_vehicles" id="label-mod-vehicles">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="item-choice-icon">
                                                    <i class="fa-solid fa-car-side"></i>
                                                </div>
                                                <div class="item-choice-copy flex-grow-1">
                                                    <strong class="item-choice-title text-dark d-block">Veículo</strong>
                                                    <small class="item-choice-desc text-muted">Carros, motos, caminhões e máquinas.</small>
                                                </div>
                                                <div class="item-choice-check">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- 4. EMPREGO --}}
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <input type="radio" class="btn-check d-none" name="module" id="mod_jobs" value="jobs" {{ old('module', $requestedModule) === 'jobs' ? 'checked' : '' }} onchange="selectModule('jobs')">
                                        <label class="item-choice-card w-100 {{ old('module', $requestedModule) === 'jobs' ? 'is-active' : '' }}" for="mod_jobs" id="label-mod-jobs">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="item-choice-icon">
                                                    <i class="fa-solid fa-briefcase"></i>
                                                </div>
                                                <div class="item-choice-copy flex-grow-1">
                                                    <strong class="item-choice-title text-dark d-block">Emprego</strong>
                                                    <small class="item-choice-desc text-muted">Vagas ou busca por oportunidades.</small>
                                                </div>
                                                <div class="item-choice-check">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    {{-- 5. LOJA --}}
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <a href="{{ route('store.create') }}" class="item-choice-card w-100 text-decoration-none d-block" id="label-mod-store" title="Criar sua Loja ou Comércio Local no Conectado em Sergipe">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="item-choice-icon">
                                                    <i class="fa-solid fa-store"></i>
                                                </div>
                                                <div class="item-choice-copy flex-grow-1">
                                                    <strong class="item-choice-title text-dark d-block">Loja</strong>
                                                    <small class="item-choice-desc text-muted">Crie sua página de loja e divulgue seu comércio.</small>
                                                </div>
                                                <div class="item-choice-check">
                                                    <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    {{-- 6. AGRO --}}
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <input type="radio" class="btn-check d-none" name="module" id="mod_agro" value="agro" {{ old('module', $requestedModule) === 'agro' ? 'checked' : '' }} onchange="selectModule('agro')">
                                        <label class="item-choice-card w-100 {{ old('module', $requestedModule) === 'agro' ? 'is-active' : '' }}" for="mod_agro" id="label-mod-agro">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="item-choice-icon">
                                                    <i class="fa-solid fa-tractor"></i>
                                                </div>
                                                <div class="item-choice-copy flex-grow-1">
                                                    <strong class="item-choice-title text-dark d-block">Agro</strong>
                                                    <small class="item-choice-desc text-muted">Produtos, animais, máquinas e serviços rurais.</small>
                                                </div>
                                                <div class="item-choice-check">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- BOX INFORMATIVO AZUL SUAVE --}}
                                <div class="info-help-callout d-flex align-items-center gap-3 p-3.5 rounded-4 mb-4">
                                    <div class="info-help-icon flex-shrink-0 text-primary">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <div class="info-help-copy">
                                        <span class="text-dark fw-medium small">Sua escolha define os campos do próximo passo para te ajudar a anunciar mais rápido e melhor.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top wizard-actions">
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-3 px-4 py-2.5 fw-semibold wizard-action">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Voltar
                                </a>
                                <button type="button" class="btn btn-primary rounded-3 px-5 py-2.5 fw-bold shadow-sm wizard-action" onclick="goToStep(2)">
                                    Continuar <i class="fa-solid fa-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= ETAPA 2: INFORMAÇÕES & CONTATO ================= -->
                        <div class="wizard-step d-none" id="wizard-step-2">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark mb-1" id="details-heading">Informações do seu serviço</h3>
                                <p class="text-muted" id="details-subtitle">Preencha os detalhes para que os clientes encontrem você.</p>
                            </div>

                            <div class="mb-4 bg-light p-3 rounded-4 border d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <small class="text-muted d-block">Categoria selecionada:</small>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold fs-6" id="badge-cat-name">🛠️ Serviço</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-bold text-nowrap align-self-center" onclick="goToStep(1)" style="font-size: 0.82rem; height: 36px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-arrow-left"></i> Alterar categoria
                                </button>
                            </div>

                            <div class="mb-3 d-none" id="profile-kind-field">
                                <label for="profile_kind_select" class="form-label fw-semibold">
                                    <i class="fa-solid fa-id-card-clip text-primary me-1"></i> Como você atua? (Tipo de anunciante) *
                                </label>
                                <select class="form-select form-select-lg rounded-3" id="profile_kind_select" name="profile_kind" onchange="updateProfileKindContext(this.value)">
                                    @foreach($profileKinds ?? \App\Models\Ad::PROFILE_KINDS as $kindKey => $kindData)
                                        <option value="{{ $kindKey }}" @selected(old('profile_kind', 'professional') === $kindKey)>
                                            {{ $kindData['label'] }} — {{ $kindData['subtitle'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="category_select" class="form-label fw-semibold">Especifique a Subcategoria *</label>
                                <select class="form-select form-select-lg rounded-3" id="category_select" name="category_name" onchange="updateSuggestedTitle()" required>
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-end mb-1">
                                    <label for="title" class="form-label fw-semibold mb-0" id="title-label">Nome do perfil profissional *</label>
                                </div>
                                <input type="text" class="form-control form-control-lg rounded-3 shadow-sm" id="title" name="title" value="{{ old('title') }}" placeholder="Ex: iPhone 13 Pro Max 128GB Lacrado, Sofá Retrátil, etc." required oninput="updatePreview()">
                                <small class="text-muted d-block mt-1" id="title-help">Dica: seja claro e direto no nome do seu anúncio.</small>
                            </div>

                            <div class="mb-3 d-none" id="price-field">
                                <label for="price" class="form-label fw-semibold" id="price-label">Preço (R$)</label>
                                <input type="text" inputmode="decimal" class="form-control form-control-lg rounded-3" id="price" name="price" value="{{ old('price') }}" placeholder="Ex: 80.000,00" oninput="formatPriceInput(this); updatePreview();">
                                <small class="text-muted" id="price-help">Digite o valor do produto ou anúncio.</small>
                            </div>

                            <div class="store-product-link mb-3 d-none" id="store-product-field">
                                <div class="store-product-link-icon" aria-hidden="true">
                                    <i class="fa-solid fa-store"></i>
                                </div>
                                <div class="store-product-link-content">
                                    <strong>Exibir este produto na minha loja</strong>
                                    @if($availableStores->isNotEmpty())
                                        @php
                                            $storesWithCapacity = $availableStores->filter(
                                                fn ($store) => $storeProductLimit === null || $store->products_count < $storeProductLimit
                                            );
                                        @endphp
                                        <p>
                                            O produto também aparecerá na sua vitrine comercial.
                                            Plano {{ auth()->user()->subscriptionPlanLabel() }}:
                                            {{ $storeProductLimit === null ? 'produtos ilimitados' : "até {$storeProductLimit} produtos por loja" }}.
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
                                            {{ old('include_in_store', $storesWithCapacity->isNotEmpty() ? '1' : '0') === '1' ? 'checked' : '' }}
                                            {{ $storesWithCapacity->isEmpty() ? 'disabled' : '' }}
                                            onchange="updateStoreSelection()"
                                        >
                                            <label class="form-check-label fw-semibold" for="include_in_store">Incluir na loja</label>
                                        </div>
                                        <select class="form-select rounded-3" id="store_id" name="store_id">
                                            @foreach($availableStores as $store)
                                                @php
                                                    $storeIsFull = $storeProductLimit !== null && $store->products_count >= $storeProductLimit;
                                                @endphp
                                                <option
                                                    value="{{ $store->id }}"
                                                    {{ (string) old('store_id', $storesWithCapacity->count() === 1 ? $storesWithCapacity->first()->id : '') === (string) $store->id ? 'selected' : '' }}
                                                    {{ $storeIsFull ? 'disabled' : '' }}
                                                >
                                                    {{ $store->name }} · {{ $store->products_count }}/{{ $storeProductLimit === null ? '∞' : $storeProductLimit }} produtos{{ $storeIsFull ? ' (limite atingido)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($storesWithCapacity->isEmpty())
                                            <div class="alert alert-warning small mt-2 mb-0">
                                                O limite de produtos das suas lojas foi atingido.
                                                <a href="{{ route('page.plans') }}">Ver planos</a>
                                            </div>
                                        @endif
                                        @error('store_id')
                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    @else
                                        <p>Crie e ative sua loja para adicionar produtos à vitrine.</p>
                                        <a href="{{ route('store.create') }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener">
                                            <i class="fa-solid fa-plus me-1"></i> Criar minha loja
                                        </a>
                                    @endif
                                    <div class="mt-3">
                                        <label for="display_mode" class="form-label fw-semibold mb-1">Forma de exibição</label>
                                        <select class="form-select rounded-3" id="display_mode" name="display_mode">
                                            <option value="default" @selected(old('display_mode', 'default') === 'default')>Usar padrão da loja</option>
                                            <option value="catalog" @selected(old('display_mode') === 'catalog')>Compra rápida no catálogo</option>
                                            <option value="individual" @selected(old('display_mode') === 'individual')>Página individual completa</option>
                                        </select>
                                        <small class="text-muted">A configuração pode ser alterada depois.</small>
                                    </div>
                                    @include('ads._commerce-fields', ['product' => null])
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6" id="region-field">
                                    <label for="city" class="form-label fw-semibold">Cidade *</label>
                                    <select class="form-select form-select-lg rounded-3" id="city" name="city" onchange="updateSuggestedTitle()" required>
                                        @foreach(\App\Core\SergipeCities::getAll() as $cityName)
                                            <option value="{{ $cityName }}" {{ old('city', 'Aracaju') === $cityName ? 'selected' : '' }}>{{ $cityName }} - SE</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold">Atende em quais regiões?</label>
                                    <input type="text" class="form-control form-control-lg rounded-3" id="region" name="region" value="{{ old('region') }}" placeholder="Ex: Centro, Atalaia, Farolândia, Jardins">
                                </div>
                            </div>

                            <div class="mb-3" id="public-address-field">
                                <label for="public_address" class="form-label fw-semibold">Endereço público do local de atendimento (opcional)</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="public_address" name="public_address" value="{{ old('public_address') }}" maxlength="255" placeholder="Ex: Rua das Flores, 120, Centro">
                                <small class="text-muted">Preencha somente se clientes puderem ir ao local. O endereço aparecerá no perfil com o botão “Como chegar”.</small>
                            </div>

                            <!-- CANAIS DE CONTATO E ATENDIMENTO -->
                            <div class="border rounded-4 p-3 p-md-4 mb-4 bg-light">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="fa-solid fa-address-book text-primary me-2"></i> Informações de contato e atendimento
                                </h6>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="whatsapp" class="form-label fw-semibold">
                                            <i class="fa-brands fa-whatsapp text-success me-1"></i> WhatsApp *
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', auth()->user()->whatsapp ?? '') }}" placeholder="(79) 99999-9999" required oninput="updatePreview()">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="phone" class="form-label fw-semibold">
                                            <i class="fa-solid fa-phone text-primary me-1"></i> Telefone de Contato *
                                        </label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="(79) 3333-3333" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="telegram" class="form-label fw-semibold"><i class="fa-brands fa-telegram text-info me-1"></i> Telegram (opcional)</label>
                                        <input type="text" class="form-control rounded-3" id="telegram" name="telegram" value="{{ old('telegram') }}" placeholder="@seutelegram ou (79) 99999-9999">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="instagram" class="form-label fw-semibold"><i class="fa-brands fa-instagram text-danger me-1"></i> Instagram (opcional)</label>
                                        <input type="text" class="form-control rounded-3" id="instagram" name="instagram" value="{{ old('instagram') }}" placeholder="@seudoinstagram">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="facebook" class="form-label fw-semibold"><i class="fa-brands fa-facebook text-primary me-1"></i> Facebook (opcional)</label>
                                        <input type="text" class="form-control rounded-3" id="facebook" name="facebook" value="{{ old('facebook') }}" placeholder="/seudofacebook">
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top">
                                    <label for="cnpj" class="form-label fw-semibold"><i class="fa-solid fa-id-card text-secondary me-1"></i> CNPJ (opcional)</label>
                                    <input type="text" class="form-control rounded-3" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" placeholder="00.000.000/0001-00" style="max-width: 320px;">
                                    <small class="text-muted">Preencha apenas se você tem empresa registrada</small>
                                </div>
                            </div>

                            <!-- HORÁRIOS DE ATENDIMENTO (EXCLUSIVO PARA SERVIÇOS) -->
                            <div class="border rounded-4 p-3 p-md-4 mb-4 bg-light d-none" id="business-hours-field">
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
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="p-2.5 bg-white rounded-3 border">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <strong class="text-dark small">{{ $dayInfo['label'] }}</strong>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="closed_{{ $dayKey }}" name="business_hours[{{ $dayKey }}][closed]" value="1" {{ old("business_hours.$dayKey.closed", $dayInfo['closed'] ? '1' : '0') === '1' ? 'checked' : '' }} onchange="toggleDayHours('{{ $dayKey }}')">
                                                        <label class="form-check-label text-muted" for="closed_{{ $dayKey }}" style="font-size: 0.75rem;">Fechado</label>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5" id="hours_wrap_{{ $dayKey }}">
                                                    <input type="time" class="form-control form-control-sm rounded-2 text-center" name="business_hours[{{ $dayKey }}][open]" value="{{ old("business_hours.$dayKey.open", $dayInfo['default_open']) }}">
                                                    <span class="text-muted small">às</span>
                                                    <input type="time" class="form-control form-control-sm rounded-2 text-center" name="business_hours[{{ $dayKey }}][close]" value="{{ old("business_hours.$dayKey.close", $dayInfo['default_close']) }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold" id="description-label">Sobre o profissional e seus serviços *</label>
                                <textarea class="form-control rounded-3" id="description" name="description" rows="5" maxlength="1000" placeholder="Descreva os detalhes do seu anúncio ou serviço..." oninput="updateCharCount(this); updatePreview();" required>{{ old('description') }}</textarea>
                                <div class="text-end text-muted small mt-1"><span id="char-count">0</span>/1000 caracteres</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top wizard-actions">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold wizard-action" onclick="goToStep(1)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Voltar
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm wizard-action" onclick="goToStep(3)">
                                    Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= ETAPA 3: FOTOS (COM OPÇÃO DE REMOVER FOTO) ================= -->
                        <div class="wizard-step d-none" id="wizard-step-3">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark mb-1" id="photos-heading">Fotos dos seus trabalhos</h3>
                                <p class="text-muted" id="photos-subtitle">Monte seu portfólio para destacar o trabalho e atrair clientes.</p>
                                <span class="badge bg-warning bg-opacity-10 text-dark border px-3 py-2 rounded-pill fw-bold">
                                    <i class="fa-solid fa-image text-warning me-1"></i> Plano Gratuito: Limite de até 5 fotos na galeria
                                </span>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" id="main-photo-label">Foto principal / Logo *</label>
                                <div class="border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative" id="main-photo-dropzone">
                                    <i class="fa-solid fa-cloud-arrow-up fs-1 text-primary mb-2"></i>
                                    <p class="mb-1 fw-bold text-dark">Arraste sua foto ou <span class="text-primary text-decoration-underline cursor-pointer">clique para selecionar</span></p>
                                    <small class="text-muted d-block mb-3">jpg, png ou webp (otimização automática)</small>
                                    <input type="file" class="form-control d-none" id="logo" name="logo" accept="image/*" onchange="previewMainPhoto(this)">
                                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="document.getElementById('logo').click()">Selecionar arquivo</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" onclick="importImageByUrl('logo')"><i class="fa-solid fa-link"></i> Importar por Link</button>
                                    </div>
                                    
                                    <!-- Container da Foto Principal com Botão de Remover X -->
                                    <div id="main-photo-preview-box" class="d-none mt-3 position-relative">
                                        <img id="main-photo-img" src="" class="rounded-3 shadow-sm object-fit-cover" style="max-height: 180px; max-width: 100%;">
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 translate-middle shadow" style="width: 28px; height: 28px; padding: 0; line-height: 28px;" onclick="removeMainPhoto()" title="Remover Foto">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4" id="cover-photo-field">
                                <label class="form-label fw-semibold" id="cover-photo-label">Capa do perfil profissional (opcional)</label>
                                <div class="border-2 border-dashed rounded-4 p-4 text-center bg-light position-relative">
                                    <i class="fa-solid fa-panorama fs-1 text-primary mb-2"></i>
                                    <p class="mb-1 fw-bold text-dark" id="cover-photo-title">Envie uma capa para destacar seu perfil</p>
                                    <small class="text-muted d-block mb-3" id="cover-photo-help">Se não enviar, será usada uma imagem da cidade escolhida.</small>
                                    <input type="file" class="form-control d-none" id="banner" name="banner" accept="image/*" onchange="previewBannerPhoto(this)">
                                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="document.getElementById('banner').click()">Selecionar capa</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" onclick="importImageByUrl('banner')"><i class="fa-solid fa-link"></i> Importar por Link</button>
                                    </div>
                                    <div id="banner-photo-preview-box" class="d-none mt-3 position-relative">
                                        <img id="banner-photo-img" src="" class="rounded-3 shadow-sm object-fit-cover w-100" style="max-height: 180px;">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-semibold mb-0">Galeria de fotos (Clique no 'X' para remover qualquer imagem)</label>
                                    <span class="badge bg-primary bg-opacity-10 text-primary" id="gallery-counter">0 / 5 fotos</span>
                                </div>
                                <div class="border rounded-4 p-3 bg-light">
                                    <input type="file" class="form-control d-none" id="images" name="images[]" multiple accept="image/*" onchange="previewGalleryPhotos(this)">
                                    <div class="d-flex flex-wrap gap-3 align-items-center" id="gallery-preview-container">
                                        <button type="button" class="btn btn-outline-dashed border-2 rounded-3 p-4 d-flex flex-column align-items-center justify-content-center bg-white" style="width: 110px; height: 110px;" onclick="document.getElementById('images').click()">
                                            <i class="fa-solid fa-plus text-primary fs-3 mb-1"></i>
                                            <small class="fw-bold text-muted" style="font-size: 0.7rem;">Adicionar fotos</small>
                                        </button>
                                        <button type="button" class="btn btn-outline-dashed border-2 rounded-3 p-4 d-flex flex-column align-items-center justify-content-center bg-white" style="width: 110px; height: 110px;" onclick="importImageByUrl('images')">
                                            <i class="fa-solid fa-link text-primary fs-3 mb-1"></i>
                                            <small class="fw-bold text-muted" style="font-size: 0.7rem;">Importar link</small>
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block"><i class="fa-solid fa-circle-info me-1 text-primary"></i> Selecionou a foto errada? Basta clicar no botão vermelho "X" sobre a foto para apagá-la.</small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top wizard-actions">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold wizard-action" onclick="goToStep(2)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Voltar
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm wizard-action" onclick="goToStep(4)">
                                    Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= ETAPA 4: PUBLICAR & REVISAR ================= -->
                        <div class="wizard-step d-none" id="wizard-step-4">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark mb-1" id="review-heading">Revise e crie seu perfil profissional</h3>
                                <p class="text-muted">Confira os detalhes antes de publicar.</p>
                            </div>

                            <div class="row g-4 mb-4">
                                <!-- Coluna Esquerda: Resumo dos Dados -->
                                <div class="col-12 col-lg-7">
                                    <div class="bg-light p-4 rounded-4 border h-100">
                                        <div class="mb-3" id="review-region-item">
                                            <small class="text-muted d-block">Categoria</small>
                                            <span class="fw-bold text-dark" id="rev-cat">Eletricista</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Título</small>
                                            <span class="fw-bold text-dark" id="rev-title">Eletricista Residencial e Comercial em Aracaju</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Cidade</small>
                                            <span class="fw-bold text-dark" id="rev-city">Aracaju - SE</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Regiões que atende</small>
                                            <span class="fw-bold text-dark" id="rev-region">Centro, Atalaia, Farolândia, Jardins</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Descrição</small>
                                            <p class="text-secondary small mb-0" id="rev-desc">Trabalho com instalações elétricas residenciais e comerciais...</p>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Contato</small>
                                            <span class="fw-bold text-success" id="rev-whatsapp"><i class="fa-brands fa-whatsapp me-1"></i> (79) 99999-9999</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Coluna Direita: PREVIA EM TEMPO REAL -->
                                <div class="col-12 col-lg-5">
                                    <div class="card border shadow-sm rounded-4 overflow-hidden">
                                        <div class="bg-light text-center py-2 border-bottom">
                                            <small class="fw-bold text-muted text-uppercase" id="preview-heading">Prévia do perfil profissional</small>
                                        </div>
                                        <div class="position-relative bg-light text-center overflow-hidden" style="height: 180px;">
                                            <img id="prev-card-img" src="{{ asset('images/logo.png') }}" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold text-dark mb-1" id="prev-card-title">Eletricista Residencial e Comercial em Aracaju</h6>
                                            <span class="badge bg-primary bg-opacity-10 text-primary mb-2" id="prev-card-cat">Eletricista</span>
                                            <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1"></i> <span id="prev-card-city">Aracaju - SE</span></p>
                                            <button type="button" class="btn btn-success btn-sm w-100 rounded-pill fw-bold" id="prev-card-btn"><i class="fa-brands fa-whatsapp me-1"></i> (79) 99999-9999</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top wizard-actions">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold wizard-action" onclick="goToStep(3)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Voltar
                                </button>
                                <div class="text-end wizard-submit-wrap">
                                    <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg fs-5 wizard-submit-button">
                                        <i class="fa-solid fa-paper-plane me-2"></i> <span id="submit-label">Criar perfil profissional</span>
                                    </button>
                                    <small class="text-muted d-block mt-1" id="submit-help">Seu perfil ficará disponível na área de Prestadores de Serviços.</small>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.publish-page {
    --publish-blue: #1265f5;
    --publish-violet: #7138ef;
}

/* CARDS MESTRES (LAYOUT IDÊNTICO AO MOCKUP) */
.master-choice-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 13px 16px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.master-choice-card:hover {
    border-color: #93c5fd;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(18, 101, 245, 0.08) !important;
}
.master-choice-card.is-active {
    border-color: #1265f5 !important;
    background: #ffffff;
    box-shadow: 0 6px 18px rgba(18, 101, 245, 0.12) !important;
}
.master-choice-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #1265f5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.master-choice-icon.is-items {
    background: #f1f5f9;
    color: #475569;
}
.master-choice-card.is-active .master-choice-icon {
    background: #eff6ff;
    color: #1265f5;
}
.master-choice-title {
    font-size: 0.98rem;
    font-weight: 700;
    margin-bottom: 1px;
    letter-spacing: -0.2px;
}
.master-choice-text {
    font-size: 0.77rem;
    line-height: 1.3;
    display: block;
}
.master-choice-check {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e2e8f0;
    color: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.master-choice-card.is-active .master-choice-check {
    background: #1265f5;
    color: #ffffff;
}

/* CARDS DE CATEGORIAS DE ITENS / VENDAS (GRID 3x2 IDÊNTICO AO MOCKUP) */
.item-choice-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 11px 13px;
    height: 100%;
    cursor: pointer;
    transition: all 0.2s ease;
    display: block;
    text-align: left;
    position: relative;
}
.item-choice-card:hover {
    border-color: #93c5fd;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(18, 101, 245, 0.08);
}
.item-choice-card.is-active,
.btn-check:checked + .item-choice-card {
    border-color: #1265f5 !important;
    background: #ffffff;
    box-shadow: 0 6px 18px rgba(18, 101, 245, 0.12) !important;
}
.item-choice-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1265f5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.item-choice-title {
    font-size: 0.90rem;
    font-weight: 700;
    margin-bottom: 1px;
    letter-spacing: -0.2px;
}
.item-choice-desc {
    font-size: 0.72rem;
    line-height: 1.25;
    display: block;
}
.item-choice-check {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1.5px solid #cbd5e1;
    background: transparent;
    color: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.item-choice-card.is-active .item-choice-check,
.btn-check:checked + .item-choice-card .item-choice-check {
    border-color: #1265f5;
    background: #1265f5;
    color: #ffffff;
}

/* CARDS DE ATUAÇÃO EM SERVIÇOS */
.service-role-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 13px 12px;
    height: 100%;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.service-role-card:hover {
    border-color: #1265f5;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(18, 101, 245, 0.08);
}
.service-role-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1265f5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    margin-bottom: 8px;
}
.service-role-title {
    font-size: 0.88rem;
    font-weight: 700;
    margin-bottom: 2px;
    letter-spacing: -0.2px;
}
.service-role-desc {
    font-size: 0.72rem;
    line-height: 1.25;
}

/* BOX INFORMATIVO AZUL SUAVE */
.info-help-callout {
    background: #eff6ff;
    border: 1px solid #dbeafe;
    padding: 10px 14px !important;
    border-radius: 12px !important;
}
.info-help-icon {
    color: #1265f5;
    font-size: 1.25rem;
}

/* AJUSTES PARA MODO ESCURO */
html[data-theme="dark"] .master-choice-card,
[data-bs-theme="dark"] .master-choice-card,
.dark-theme .master-choice-card {
    background: var(--card, #1e293b) !important;
    border-color: var(--border, #334155) !important;
}
html[data-theme="dark"] .master-choice-card:hover,
[data-bs-theme="dark"] .master-choice-card:hover,
.dark-theme .master-choice-card:hover {
    border-color: #3b82f6 !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
}
html[data-theme="dark"] .master-choice-card.is-active,
[data-bs-theme="dark"] .master-choice-card.is-active,
.dark-theme .master-choice-card.is-active {
    background: var(--card, #1e293b) !important;
    border-color: #1265f5 !important;
    box-shadow: 0 8px 24px rgba(18, 101, 245, 0.25) !important;
}
html[data-theme="dark"] .master-choice-icon,
[data-bs-theme="dark"] .master-choice-icon,
.dark-theme .master-choice-icon {
    background: #0f172a !important;
    color: #60a5fa !important;
}
html[data-theme="dark"] .master-choice-icon.is-items,
[data-bs-theme="dark"] .master-choice-icon.is-items,
.dark-theme .master-choice-icon.is-items {
    background: #0f172a !important;
    color: #94a3b8 !important;
}
html[data-theme="dark"] .master-choice-title,
[data-bs-theme="dark"] .master-choice-title,
.dark-theme .master-choice-title {
    color: #ffffff !important;
}
html[data-theme="dark"] .master-choice-text,
[data-bs-theme="dark"] .master-choice-text,
.dark-theme .master-choice-text {
    color: #94a3b8 !important;
}
html[data-theme="dark"] .master-choice-check,
[data-bs-theme="dark"] .master-choice-check,
.dark-theme .master-choice-check {
    background: #334155 !important;
}
html[data-theme="dark"] .master-choice-card.is-active .master-choice-check,
[data-bs-theme="dark"] .master-choice-card.is-active .master-choice-check,
.dark-theme .master-choice-card.is-active .master-choice-check {
    background: #1265f5 !important;
    color: #ffffff !important;
}

html[data-theme="dark"] .item-choice-card,
[data-bs-theme="dark"] .item-choice-card,
.dark-theme .item-choice-card {
    background: var(--card, #1e293b) !important;
    border-color: var(--border, #334155) !important;
}
html[data-theme="dark"] .item-choice-card:hover,
[data-bs-theme="dark"] .item-choice-card:hover,
.dark-theme .item-choice-card:hover {
    border-color: #3b82f6 !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4) !important;
}
html[data-theme="dark"] .item-choice-card.is-active,
[data-bs-theme="dark"] .item-choice-card.is-active,
.dark-theme .item-choice-card.is-active,
html[data-theme="dark"] .btn-check:checked + .item-choice-card,
[data-bs-theme="dark"] .btn-check:checked + .item-choice-card,
.dark-theme .btn-check:checked + .item-choice-card {
    background: var(--card, #1e293b) !important;
    border-color: #1265f5 !important;
    box-shadow: 0 8px 24px rgba(18, 101, 245, 0.25) !important;
}
html[data-theme="dark"] .item-choice-icon,
[data-bs-theme="dark"] .item-choice-icon,
.dark-theme .item-choice-icon {
    background: rgba(18, 101, 245, 0.2) !important;
    color: #60a5fa !important;
}
html[data-theme="dark"] .item-choice-title,
[data-bs-theme="dark"] .item-choice-title,
.dark-theme .item-choice-title {
    color: #ffffff !important;
}
html[data-theme="dark"] .item-choice-desc,
[data-bs-theme="dark"] .item-choice-desc,
.dark-theme .item-choice-desc {
    color: #94a3b8 !important;
}
html[data-theme="dark"] .item-choice-check,
[data-bs-theme="dark"] .item-choice-check,
.dark-theme .item-choice-check {
    border-color: #475569 !important;
}
html[data-theme="dark"] .item-choice-card.is-active .item-choice-check,
[data-bs-theme="dark"] .item-choice-card.is-active .item-choice-check,
.dark-theme .item-choice-card.is-active .item-choice-check,
html[data-theme="dark"] .btn-check:checked + .item-choice-card .item-choice-check,
[data-bs-theme="dark"] .btn-check:checked + .item-choice-card .item-choice-check,
.dark-theme .btn-check:checked + .item-choice-card .item-choice-check {
    border-color: #1265f5 !important;
    background: #1265f5 !important;
    color: #ffffff !important;
}

html[data-theme="dark"] .service-role-card,
[data-bs-theme="dark"] .service-role-card,
.dark-theme .service-role-card {
    background: var(--card, #1e293b) !important;
    border-color: var(--border, #334155) !important;
}
html[data-theme="dark"] .service-role-card:hover,
[data-bs-theme="dark"] .service-role-card:hover,
.dark-theme .service-role-card:hover {
    border-color: #3b82f6 !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4) !important;
}
html[data-theme="dark"] .service-role-icon,
[data-bs-theme="dark"] .service-role-icon,
.dark-theme .service-role-icon {
    background: rgba(18, 101, 245, 0.2) !important;
    color: #60a5fa !important;
}
html[data-theme="dark"] .service-role-title,
[data-bs-theme="dark"] .service-role-title,
.dark-theme .service-role-title {
    color: #ffffff !important;
}
html[data-theme="dark"] .service-role-desc,
[data-bs-theme="dark"] .service-role-desc,
.dark-theme .service-role-desc {
    color: #94a3b8 !important;
}

html[data-theme="dark"] .info-help-callout,
[data-bs-theme="dark"] .info-help-callout,
.dark-theme .info-help-callout {
    background: rgba(18, 101, 245, 0.12) !important;
    border-color: rgba(18, 101, 245, 0.35) !important;
}
html[data-theme="dark"] .info-help-callout strong,
[data-bs-theme="dark"] .info-help-callout strong,
.dark-theme .info-help-callout strong {
    color: #ffffff !important;
}
html[data-theme="dark"] .info-help-callout span,
[data-bs-theme="dark"] .info-help-callout span,
.dark-theme .info-help-callout span {
    color: #94a3b8 !important;
}
html[data-theme="dark"] .info-help-icon,
[data-bs-theme="dark"] .info-help-icon,
.dark-theme .info-help-icon {
    color: #60a5fa !important;
}

html[data-theme="dark"] .publish-form-card .bg-light,
[data-bs-theme="dark"] .publish-form-card .bg-light,
.dark-theme .publish-form-card .bg-light {
    background-color: rgba(255, 255, 255, 0.04) !important;
    border-color: var(--border, #334155) !important;
}

html[data-theme="dark"] #business-hours-field .bg-white,
[data-bs-theme="dark"] #business-hours-field .bg-white,
.dark-theme #business-hours-field .bg-white {
    background-color: var(--card, #1e293b) !important;
    border-color: var(--border, #334155) !important;
}

.publish-toolbar .btn {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    color: var(--foreground);
    background: color-mix(in srgb, var(--card) 88%, transparent);
    border-color: var(--border);
}
.publish-form-card {
    color: var(--foreground);
    background:
        radial-gradient(circle at 50% 0, color-mix(in srgb, var(--publish-blue) 8%, transparent), transparent 38%),
        var(--card);
    border: 1px solid var(--border) !important;
}
.publish-stepper-header {
    border-bottom: 1px solid var(--border);
    background: color-mix(in srgb, var(--publish-blue) 3%, transparent);
}
.publish-step-track::before {
    content: "";
    position: absolute;
    z-index: 0;
    top: 19px;
    left: 10%;
    right: 10%;
    height: 1px;
    background: var(--border);
}
.publish-step-track .step-item {
    position: relative;
    z-index: 1;
    min-width: 74px;
}
.publish-form-card {
    box-shadow: 0 22px 55px rgba(15, 23, 42, .1) !important;
}
.publish-form-card .text-dark,
.publish-stepper-header .step-label {
    color: var(--foreground) !important;
}
.publish-step-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    color: var(--muted-foreground);
    background: color-mix(in srgb, var(--card) 85%, transparent);
    border: 1px solid var(--border);
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
}
.publish-module-grid > div {
    min-width: 0;
}
.module-card {
    --motion-color: #1265f5;
    --motion-color-soft: #6d91ff;
    position: relative;
    min-height: 90px;
    display: grid !important;
    grid-template-columns: 60px minmax(0, 1fr) 28px;
    align-items: center;
    gap: 12px;
    padding: 10px 12px !important;
    overflow: hidden;
    color: var(--foreground) !important;
    text-align: left;
    background:
        linear-gradient(135deg, color-mix(in srgb, var(--motion-color) 7%, transparent), transparent 48%),
        var(--card) !important;
    border: 1px solid var(--border) !important;
    transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
}
/* All module cards use the same primary color */
.module-card:hover {
    transform: translateY(-3px);
    border-color: color-mix(in srgb, var(--motion-color) 68%, var(--border)) !important;
    box-shadow: 0 14px 30px color-mix(in srgb, var(--motion-color) 16%, transparent) !important;
}
.btn-check:checked + .module-card {
    border-color: var(--motion-color) !important;
    box-shadow:
        0 0 0 1px var(--motion-color),
        0 14px 34px color-mix(in srgb, var(--motion-color) 20%, transparent) !important;
}
.btn-check:checked + .module-card::after {
    display: none !important;
}
.module-motion-icon {
    position: relative;
    width: 60px;
    height: 60px;
    display: grid;
    place-items: center;
    isolation: isolate;
}
.module-motion-icon i {
    color: var(--motion-color) !important;
    font-size: 2rem;
}
.module-card-copy {
    min-width: 0;
}
.module-card-copy > span {
    font-size: 1rem;
}
.module-card-copy small {
    display: block;
    margin-top: 4px;
    color: var(--muted-foreground) !important;
    font-size: .72rem;
    line-height: 1.42;
    white-space: normal;
}
.module-card-arrow {
    width: 27px;
    height: 27px;
    display: grid;
    place-items: center;
    color: var(--muted-foreground);
    background: var(--muted-bg);
    border: 1px solid var(--border);
    border-radius: 50%;
    font-size: .68rem;
}
.btn-check:checked + .module-card .module-card-arrow {
    color: #fff;
    background: var(--motion-color);
    border-color: var(--motion-color);
}
.professional-motion-panel {
    position: relative;
    display: grid;
    grid-template-columns: 100px minmax(0, 1fr) auto;
    align-items: center;
    gap: 15px;
    min-height: 90px;
    padding: 12px 18px;
    overflow: hidden;
    color: var(--foreground);
    background: color-mix(in srgb, #1265f5 3%, var(--card));
    border: 1px solid var(--border);
    border-radius: 16px;
}
.professional-motion-icon {
    position: relative;
    height: 60px;
    display: grid;
    place-items: center;
    color: #1265f5;
}
.professional-motion-icon .fa-user-tie {
    position: relative;
    z-index: 1;
    font-size: 2.5rem;
}
.professional-motion-icon .fa-check,
.professional-motion-icon .fa-star {
    position: absolute;
    z-index: 2;
    right: 15px;
    bottom: 0px;
    width: 22px;
    height: 22px;
    display: grid;
    place-items: center;
    color: #ffffff;
    background: #1265f5;
    border-radius: 50%;
    font-size: 0.7rem;
}
.professional-motion-copy h4 {
    margin: 0 0 5px;
    font-size: 1rem;
    font-weight: 800;
}
.professional-motion-copy p {
    max-width: 480px;
    margin: 0;
    color: var(--muted-foreground);
    font-size: .76rem;
    line-height: 1.5;
}
.professional-motion-copy {
    min-width: 0;
}
.advertiser-type-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}
.advertiser-type-option {
    display: grid;
    grid-template-columns: 34px minmax(0, 1fr) 24px;
    align-items: center;
    gap: 10px;
    width: 100%;
    min-width: 0;
    min-height: 66px;
    padding: 10px 12px;
    color: var(--foreground);
    background: color-mix(in srgb, var(--card) 92%, #1265f5 8%);
    border: 1px solid color-mix(in srgb, var(--border) 78%, #1265f5 22%);
    border-radius: 12px;
    font: inherit;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.advertiser-type-option:hover,
.advertiser-type-option:focus-visible {
    color: var(--foreground);
    border-color: #1265f5;
    box-shadow: 0 8px 20px rgba(18, 101, 245, .14);
    transform: translateY(-2px);
}
.advertiser-type-option:focus-visible {
    outline: 3px solid rgba(18, 101, 245, .22);
    outline-offset: 2px;
}
.advertiser-type-option > i:first-child {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    color: #1265f5;
    background: color-mix(in srgb, #1265f5 12%, var(--card));
    border-radius: 10px;
    font-size: .95rem;
}
.advertiser-type-option > span {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 2px;
}
.advertiser-type-option strong,
.advertiser-type-option small {
    display: block;
    overflow-wrap: anywhere;
}
.advertiser-type-option strong {
    font-size: .79rem;
    line-height: 1.25;
}
.advertiser-type-option small {
    color: var(--muted-foreground);
    font-size: .66rem;
    line-height: 1.35;
}
.advertiser-type-option > i:last-child {
    color: #1265f5;
    font-size: .72rem;
    text-align: center;
}
.professional-motion-cta {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    padding: 10px 17px;
    color: #fff;
    background: linear-gradient(90deg, #7138ef, #087df4);
    border: 0;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 800;
    white-space: nowrap;
    box-shadow: 0 10px 25px rgba(50, 90, 245, .25);
}
.professional-motion-cta:hover {
    color: #fff;
    transform: translateY(-2px);
}
@keyframes moduleMotionFloat {
    0%, 100% { transform: perspective(120px) rotateY(-8deg) translateY(0); }
    50% { transform: perspective(120px) rotateY(-4deg) translateY(-5px); }
}
@keyframes moduleMotionPulse {
    0%, 100% { transform: scale(.86); opacity: .55; }
    50% { transform: scale(1.12); opacity: .9; }
}
@keyframes moduleMotionBurst {
    0% { transform: perspective(120px) rotateY(-8deg) translateX(0); }
    45% { transform: perspective(120px) rotateY(4deg) translateX(5px) scale(1.06); }
    100% { transform: perspective(120px) rotateY(-8deg) translateX(0); }
}
.publish-design-design5 {
    --publish-blue: #256c2b;
}
.publish-design-design5 .publish-stepper-card,
.publish-design-design5 .publish-form-card {
    background:
        radial-gradient(circle at 50% 0, rgba(67, 125, 59, .08), transparent 40%),
        var(--card);
}
.publish-design-design5 .step-item.active .step-icon {
    background: #256c2b;
    box-shadow: 0 0 0 4px rgba(37, 108, 43, .14);
}
.publish-design-design5 .step-item.active .step-label {
    color: #256c2b;
}
.publish-design-design5 .publish-step-track::before {
    background: color-mix(in srgb, #256c2b 24%, var(--border));
}
.publish-design-design5 .module-card {
    --motion-color: #337b34;
    --motion-color-soft: #7da875;
    min-height: 205px;
    grid-template-columns: 1fr;
    grid-template-rows: 100px auto 28px;
    gap: 4px;
    padding: 16px 12px !important;
    text-align: center;
    background:
        radial-gradient(circle at 50% 34%, rgba(62, 122, 55, .1), transparent 31%),
        var(--card) !important;
    border-radius: 10px !important;
}
.publish-design-design5 .module-motion-icon {
    width: 116px;
    height: 96px;
    margin: 0 auto;
    background: rgba(65, 125, 58, .07);
    border-radius: 48% 52% 45% 55%;
}
.publish-design-design5 .module-motion-icon::after {
    display: none;
}
.publish-design-design5 .module-motion-icon i {
    color: #337b34 !important;
    font-size: 3.5rem;
    filter: drop-shadow(0 6px 9px rgba(47, 107, 47, .18));
    animation: none;
    transform: none;
}
.publish-design-design5 .module-card-copy > span {
    color: #173f1b !important;
    font-size: 1rem;
}
.publish-design-design5 .module-card-copy small {
    font-size: .7rem;
}
.publish-design-design5 .module-card-arrow {
    color: #256c2b;
    background: #fff;
    border-color: rgba(37, 108, 43, .35);
}
.publish-design-design5 .btn-check:checked + .module-card {
    border-color: #337b34 !important;
    box-shadow: 0 0 0 1px #337b34, 0 12px 28px rgba(51, 123, 52, .12) !important;
}
.publish-design-design5 .professional-motion-panel {
    background:
        linear-gradient(90deg, rgba(61, 124, 53, .11), rgba(90, 150, 75, .035)),
        var(--card);
    border-color: rgba(51, 123, 52, .2);
}
.publish-design-design5 .professional-motion-icon,
.publish-design-design5 .professional-motion-icon .fa-star {
    color: #337b34;
}
.publish-design-design5 .professional-motion-icon .fa-star {
    color: #d7efc6;
    background: #337b34;
}
.publish-design-design5 .professional-motion-cta {
    background: linear-gradient(90deg, #1f641f, #2f812f);
    box-shadow: 0 10px 24px rgba(47, 129, 47, .2);
}
.publish-design-design5 .wizard-actions .btn-primary {
    background: #256c2b;
    border-color: #256c2b;
}
@media (min-width: 1200px) {
    .publish-design-design5 .publish-module-grid > div {
        flex: 0 0 20%;
        width: 20%;
        max-width: 20%;
    }
}
.step-icon {
    width: 38px;
    height: 38px;
    background-color: #e2e8f0;
    color: #64748b;
    font-size: 1rem;
    transition: all 0.3s ease;
}
.step-item.active .step-icon {
    background-color: #1265f5;
    color: #ffffff;
}
.step-item.completed .step-icon {
    background-color: #10b981;
    color: #ffffff;
}
.step-item.active .step-label {
    color: #1265f5;
    font-weight: 700 !important;
}
#mod_services:checked + .module-card .service-module-icon {
    color: var(--motion-color) !important;
}
.border-dashed {
    border-style: dashed !important;
}
.btn-outline-dashed {
    border: 2px dashed #cbd5e1;
    transition: all 0.2s ease;
}
.btn-outline-dashed:hover {
    border-color: #4f46e5;
    background-color: #f8fafc !important;
}
.wizard-actions {
    gap: 0.75rem;
}
.wizard-action,
.wizard-submit-button {
    display: inline-flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}
.wizard-submit-wrap {
    min-width: 0;
}
.store-product-link {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr);
    gap: 12px;
    padding: 14px;
    background: color-mix(in srgb, var(--primary) 6%, var(--card));
    border: 1px solid color-mix(in srgb, var(--primary) 24%, var(--border));
    border-radius: 14px;
}
.store-product-link.d-none {
    display: none !important;
}
.store-product-link-icon {
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    color: var(--primary);
    background: color-mix(in srgb, var(--primary) 12%, var(--card));
    border-radius: 12px;
}
.store-product-link-content p {
    margin: 2px 0 10px;
    color: var(--muted-foreground);
    font-size: .84rem;
}
@media (max-width: 575.98px) {
    .publish-page {
        padding-top: 1rem !important;
    }
    .publish-toolbar {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto;
        margin-bottom: 1rem !important;
        gap: .55rem;
    }
    .publish-toolbar > div {
        min-width: 0;
    }
    .publish-toolbar > div:last-child {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    .publish-toolbar .btn {
        min-width: 0;
        padding-right: .7rem !important;
        padding-left: .7rem !important;
        font-size: .72rem;
        white-space: nowrap;
    }
    .publish-toolbar .btn-close {
        display: none;
    }
    .publish-stepper-header {
        padding: .8rem !important;
    }
    .publish-step-track {
        padding-right: 0 !important;
        padding-left: 0 !important;
    }
    .publish-step-track::before {
        right: 10%;
        left: 10%;
        top: 17px;
    }
    .publish-step-track .step-item {
        flex: 1 1 20%;
        min-width: 0;
    }
    .publish-step-track .step-icon {
        width: 34px;
        height: 34px;
        font-size: .85rem;
    }
    .publish-form-card .card-body {
        padding: 1rem !important;
    }
    #wizard-step-1 > .text-center h3 {
        font-size: clamp(1.45rem, 8vw, 1.85rem);
        line-height: 1.15;
    }
    #wizard-step-1 > .text-center p {
        font-size: .9rem;
        line-height: 1.45;
    }
    .module-card {
        min-height: 156px;
        grid-template-columns: 1fr;
        grid-template-rows: 66px auto 24px;
        gap: 4px;
        padding: 10px 8px !important;
        text-align: center;
    }
    .module-motion-icon {
        width: 76px;
        height: 62px;
        margin: 0 auto;
    }
    .module-motion-icon i {
        font-size: 2.25rem;
    }
    .module-card-copy > span {
        font-size: .84rem;
    }
    .module-card-copy small {
        font-size: .62rem;
        line-height: 1.3;
    }
    .module-card-arrow {
        width: 24px;
        height: 24px;
        margin: 0 auto;
    }
    .professional-motion-panel {
        grid-template-columns: 74px minmax(0, 1fr);
        gap: 10px;
        padding: 13px;
    }
    .professional-motion-icon {
        height: 68px;
    }
    .professional-motion-icon .fa-user-tie {
        font-size: 2.8rem;
    }
    .professional-motion-icon .fa-star {
        right: 2px;
    }
    .professional-motion-copy h4 {
        font-size: .86rem;
    }
    .professional-motion-copy p {
        font-size: .66rem;
    }
    .advertiser-type-grid {
        grid-column: 1 / -1;
        grid-template-columns: 1fr;
        margin-top: 10px;
    }
    .advertiser-type-option {
        min-height: 60px;
        padding: 9px 10px;
    }
    .professional-motion-cta {
        grid-column: 1 / -1;
        justify-content: center;
        width: 100%;
    }
    .wizard-actions {
        gap: 0.5rem;
        align-items: flex-start !important;
    }
    .wizard-action {
        padding: 0.625rem 1rem !important;
        font-size: 0.9rem;
    }
    .wizard-action .me-2,
    .wizard-submit-button .me-2 {
        margin-right: 0.4rem !important;
    }
    .wizard-action .ms-2 {
        margin-left: 0.4rem !important;
    }
    .wizard-submit-button {
        padding: 0.65rem 1rem !important;
        font-size: 0.95rem !important;
    }
    .wizard-submit-wrap {
        flex: 1 1 auto;
    }
    #wizard-step-5 .wizard-actions {
        flex-direction: column;
        align-items: stretch !important;
    }
    #wizard-step-5 .wizard-action,
    #wizard-step-5 .wizard-submit-wrap,
    #wizard-step-5 .wizard-submit-button {
        width: 100%;
    }
    #wizard-step-5 .wizard-submit-wrap {
        text-align: center !important;
    }
}
@media (prefers-reduced-motion: reduce) {
    .module-motion-icon::after,
    .module-motion-icon i,
    .professional-motion-icon .fa-user-tie {
        animation: none !important;
    }
}
</style>

@push('scripts')
<script>
    let currentStep = 1;
    let selectedGalleryFiles = [];
    let imageProcessingCount = 0;
    const previousCategory = @json(old('category_name'));
    const initialModule = @json(old('module', $requestedModule));

    const moduleNames = {
        services: '🛠️ Serviço',
        products: '📱 Produto',
        real_estate: '🏢 Imóvel',
        vehicles: '🚗 Veículo',
        jobs: '💼 Emprego',
        agro: '🚜 Agro'
    };

    const databaseCategoryLists = @json(
        $categories->groupBy('module')->map(fn ($cats) => $cats->pluck('name')->values()->all())
    );

    const fallbackCategoryLists = {
        services: ['Eletricista', 'Encanador', 'Pintor', 'Mecânico', 'Advogado', 'Faxineira / Diarista', 'Marcenaria', 'TI / Informática', 'Frete e Mudanças', 'Restaurante / Pizzaria', 'Pedreiro', 'Jardineiro'],
        products: [
            'Celulares & Telefonia',
            'Computadores & Informática',
            'Eletrodomésticos & Eletrônicos',
            'Móveis, Casa & Decoração',
            'Moda, Roupas & Calçados',
            'Beleza, Cosméticos & Perfumaria',
            'Produtos Agrícolas & Agropecuária (Sementes, Mudas e Insumos)',
            'Produtos da Roça & Hortifrúti',
            'Gado, Animais & Pecuária',
            'Ferramentas, Jardim & Indústria',
            'Alimentos, Bebidas & Supermercado',
            'Esportes, Fitness & Ciclismo',
            'Automotivo & Acessórios',
            'Artesanato, Antiguidades & Colecionáveis',
            'Outros Produtos'
        ],
        real_estate: ['Casas para Venda', 'Casas para Aluguel', 'Apartamentos para Venda', 'Apartamentos para Aluguel', 'Terrenos, Lotes & Chácaras', 'Sítios, Fazendas & Agronegócio', 'Salas Comerciais, Lojas & Galpões', 'Aluguel por Temporada & Pousadas'],
        vehicles: ['Carros & Utilitários', 'Motos & Ciclomotores', 'Caminhões, Ônibus & Vans', 'Tratores, Máquinas & Implementos Agrícolas', 'Náutica, Barcos & Lanchas', 'Peças, Pneus & Acessórios'],
        jobs: ['Vagas Operacionais & Serviços Gerais', 'Vagas Comerciais & Vendas', 'Vagas em Tecnologia & TI', 'Vagas no Campo & Agro', 'Estágios & Jovem Aprendiz', 'Freelancers, Bicos & Autônomos'],
        agro: ['Produtos Agrícolas & Agropecuária', 'Sementes, Mudas & Adubos', 'Produtos da Roça & Hortifrúti', 'Gado, Cavalos & Pecuária', 'Tratores, Máquinas & Implementos']
    };

    function selectPublishMode(mode) {
        const cardServices = document.getElementById('card-choice-services');
        const cardItems = document.getElementById('card-choice-items');
        const sectionServices = document.getElementById('section-services-flow');
        const sectionItems = document.getElementById('section-items-flow');

        if (!cardServices || !cardItems || !sectionServices || !sectionItems) return;

        if (mode === 'services') {
            cardServices.classList.add('is-active');
            cardItems.classList.remove('is-active');
            sectionServices.classList.remove('d-none');
            sectionItems.classList.add('d-none');
        } else {
            cardItems.classList.add('is-active');
            cardServices.classList.remove('is-active');
            sectionItems.classList.remove('d-none');
            sectionServices.classList.add('d-none');
            
            const activeModule = document.querySelector('input[name="module"]:checked')?.value;
            if (!activeModule || activeModule === 'services') {
                const prodRadio = document.getElementById('mod_products');
                if (prodRadio) {
                    prodRadio.checked = true;
                    selectModule('products');
                }
            }
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

    function chooseProfessionalProfile(profileKind = 'professional') {
        const profileKindInput = document.getElementById('service-profile-kind');
        const profileKindSelect = document.getElementById('profile_kind_select');
        const serviceOption = document.getElementById('mod_services');
        if (profileKindInput) profileKindInput.value = profileKind;
        if (profileKindSelect) profileKindSelect.value = profileKind;
        if (serviceOption) serviceOption.checked = true;
        selectModule('services');
        updateProfileKindContext(profileKind);
        goToStep(2);
    }

    function updatePriceFieldConfig(modKey, profileKind = null) {
        const priceField = document.getElementById('price-field');
        const priceInput = document.getElementById('price');
        const priceLabel = document.getElementById('price-label');
        const priceHelp = document.getElementById('price-help');

        if (!priceField || !priceInput) return;

        const isService = modKey === 'services';
        const isHiringCompany = profileKind === 'hiring_company';
        const isRealEstateAgency = profileKind === 'real_estate_agency';
        const isCulturalArtist = profileKind === 'cultural_artist';

        // Ocultar preço para TODOS os perfis de serviços/empresas contratantes/imobiliárias
        if (isService || isHiringCompany || isRealEstateAgency || isCulturalArtist) {
            priceField.classList.add('d-none');
            priceInput.value = '';
            return;
        }

        priceField.classList.remove('d-none');

        if (modKey === 'jobs') {
            if (priceLabel) priceLabel.textContent = 'Salário / Remuneração oferecida (R$ - opcional)';
            priceInput.placeholder = 'Ex: 2.500,00 (deixe em branco se a combinar)';
            if (priceHelp) priceHelp.textContent = 'Informe o salário ou remuneração da vaga, ou deixe em branco se for a combinar.';
        } else if (modKey === 'real_estate') {
            if (priceLabel) priceLabel.textContent = 'Valor do Imóvel / Aluguel (R$)';
            priceInput.placeholder = 'Ex: 250.000,00 ou 1.200,00';
            if (priceHelp) priceHelp.textContent = 'Valor de venda ou mensalidade do aluguel.';
        } else if (modKey === 'vehicles') {
            if (priceLabel) priceLabel.textContent = 'Valor do Veículo (R$)';
            priceInput.placeholder = 'Ex: 45.000,00';
            if (priceHelp) priceHelp.textContent = 'Valor de venda do veículo ou máquina.';
        } else {
            // products
            if (priceLabel) priceLabel.textContent = 'Preço do Produto (R$)';
            priceInput.placeholder = 'Ex: 150,00';
            if (priceHelp) priceHelp.textContent = 'Digite o valor de venda do produto.';
        }
    }

    function updateProfileKindContext(profileKind) {
        const titleLabel = document.getElementById('title-label');
        const descLabel = document.getElementById('description-label');
        const detailsHeading = document.getElementById('details-heading');
        const detailsSubtitle = document.getElementById('details-subtitle');

        if (profileKind === 'hiring_company') {
            if (detailsHeading) detailsHeading.textContent = 'Informações da empresa e da vaga';
            if (detailsSubtitle) detailsSubtitle.textContent = 'Apresente sua empresa e publique oportunidades de emprego.';
            if (titleLabel) titleLabel.textContent = 'Nome da empresa contratante / Vaga *';
            if (descLabel) descLabel.textContent = 'Sobre a empresa e requisitos das oportunidades *';
        } else if (profileKind === 'real_estate_agency') {
            if (detailsHeading) detailsHeading.textContent = 'Informações da Imobiliária';
            if (detailsSubtitle) detailsSubtitle.textContent = 'Apresente sua imobiliária e carteira de serviços.';
            if (titleLabel) titleLabel.textContent = 'Nome da Imobiliária / Corretora *';
            if (descLabel) descLabel.textContent = 'Sobre a imobiliária e serviços oferecidos *';
        } else if (profileKind === 'cultural_artist') {
            if (detailsHeading) detailsHeading.textContent = 'Perfil do Artista / Projeto Cultural';
            if (detailsSubtitle) detailsSubtitle.textContent = 'Apresente sua arte, projetos e portfólio cultural.';
            if (titleLabel) titleLabel.textContent = 'Nome artístico / Grupo / Projeto *';
            if (descLabel) descLabel.textContent = 'Sobre a trajetória artística e apresentações *';
        } else {
            if (detailsHeading) detailsHeading.textContent = 'Informações do seu perfil profissional';
            if (detailsSubtitle) detailsSubtitle.textContent = 'Apresente seu trabalho para que os clientes encontrem você.';
            if (titleLabel) titleLabel.textContent = 'Nome do perfil profissional *';
            if (descLabel) descLabel.textContent = 'Sobre o profissional e seus serviços *';
        }

        const modKey = document.querySelector('input[name="module"]:checked')?.value || 'services';
        updatePriceFieldConfig(modKey, profileKind);
    }

    function selectModule(modKey, preserveTitle = false) {
        const catSelect = document.getElementById('category_select');
        catSelect.innerHTML = '';
        const list = (databaseCategoryLists[modKey] && databaseCategoryLists[modKey].length > 0)
            ? databaseCategoryLists[modKey]
            : (fallbackCategoryLists[modKey] || fallbackCategoryLists.products);
        
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            opt.textContent = item;
            catSelect.appendChild(opt);
        });

        if (previousCategory && list.includes(previousCategory)) {
            catSelect.value = previousCategory;
        }

        const badgeCat = document.getElementById('badge-cat-name');
        if (badgeCat) {
            badgeCat.textContent = moduleNames[modKey] || '🛠️ Serviço';
        }

        document.querySelectorAll('.item-choice-card').forEach(card => card.classList.remove('is-active'));
        const activeLabel = document.getElementById('label-mod-' + modKey);
        if (activeLabel) {
            activeLabel.classList.add('is-active');
        }

        updateModuleLanguage(modKey);

        if (preserveTitle) {
            updatePreview();
        } else {
            updateSuggestedTitle();
        }
    }

    function formatPriceInput(input) {
        let value = input.value.replace(/\D/g, '');
        if (!value) {
            input.value = '';
            return;
        }
        let number = (parseInt(value, 10) / 100).toFixed(2);
        let parts = number.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        input.value = parts.join(',');
    }

    const categoryPlaceholders = {
        services: 'Ex: Eletricista Residencial e Comercial em Aracaju',
        products: 'Ex: iPhone 13 Pro Max 128GB Lacrado, Sofá Retrátil, etc.',
        real_estate: 'Ex: Casa 3 Quartos com Suíte no Luzia, Terreno 250m², etc.',
        vehicles: 'Ex: Honda Civic 2.0 Flex 2020 Automático, Moto Fazer 250, etc.',
        jobs: 'Ex: Vaga para Atendente de Loja, Vendedor Interno, etc.',
        agro: 'Ex: Trator Massey Ferguson 275, Sementes de Milho, etc.'
    };

    const descriptionPlaceholders = {
        services: 'Descreva aqui os seus serviços prestados, experiência, diferenciais, formas de atendimento e horários...',
        products: 'Descreva o produto: marca, modelo, estado de conservação (novo/usado), itens inclusos e especificações principais...',
        real_estate: 'Descreva o imóvel: quantidade de quartos, suítes, banheiros, vagas de garagem, metragem (m²), condomínio e diferenciais da localização...',
        vehicles: 'Descreva o veículo: ano/modelo, quilometragem, tipo de câmbio, combustível, itens de série, opcionais e estado de conservação...',
        jobs: 'Descreva os detalhes da vaga ou busca: principais atribuições, requisitos exigidos, jornada de trabalho e benefícios...',
        agro: 'Descreva o item agrícola: raça/especificação, quantidade, ano/modelo do maquinário, conservação e detalhes de entrega...'
    };

    function updateModuleLanguage(modKey) {
        const isService = modKey === 'services';
        const isProduct = modKey === 'products';
        const profileKindSelect = document.getElementById('profile_kind_select');
        const profileKind = profileKindSelect ? profileKindSelect.value : null;

        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.placeholder = categoryPlaceholders[modKey] || categoryPlaceholders.products;
        }

        const descInput = document.getElementById('description');
        if (descInput) {
            descInput.placeholder = descriptionPlaceholders[modKey] || descriptionPlaceholders.products;
        }

        if (isService && profileKind) {
            updateProfileKindContext(profileKind);
        } else {
            document.getElementById('details-heading').textContent = isService
                ? 'Informações do seu perfil profissional'
                : 'Informações do anúncio';
            document.getElementById('details-subtitle').textContent = isService
                ? 'Apresente seu trabalho para que os clientes encontrem você.'
                : 'Preencha os detalhes do item que deseja anunciar.';
            document.getElementById('title-label').textContent = isService
                ? 'Nome do perfil profissional *'
                : 'Título do anúncio *';
            document.getElementById('title-help').textContent = isService
                ? 'Dica: seja claro e direto no nome do seu perfil.'
                : 'Dica: descreva claramente o que está anunciando.';
            document.getElementById('description-label').textContent = isService
                ? 'Sobre o profissional e seus serviços *'
                : 'Descrição do anúncio *';
        }

        updatePriceFieldConfig(modKey, profileKind);
        document.getElementById('profile-kind-field').classList.toggle('d-none', !isService);
        document.getElementById('region-field').classList.toggle('d-none', !isService);
        document.getElementById('public-address-field').classList.toggle('d-none', !isService);
        const businessHoursField = document.getElementById('business-hours-field');
        if (businessHoursField) businessHoursField.classList.toggle('d-none', !isService);
        document.getElementById('store-product-field').classList.toggle('d-none', !isProduct);
        document.getElementById('review-region-item').classList.toggle('d-none', !isService);
        document.getElementById('main-photo-label').textContent = isService
            ? 'Foto principal / Logo *'
            : 'Foto principal do anúncio *';
        document.getElementById('cover-photo-label').textContent = isService
            ? 'Capa do perfil profissional (opcional)'
            : 'Capa do anúncio (opcional)';
        document.getElementById('cover-photo-title').textContent = isService
            ? 'Envie uma capa para destacar seu perfil'
            : 'Envie uma capa para destacar seu anúncio';
        document.getElementById('cover-photo-help').textContent = isService
            ? 'Se não enviar, será usada uma imagem da cidade escolhida.'
            : 'A capa será exibida no topo da página do anúncio.';
        document.getElementById('photos-heading').textContent = isService
            ? 'Fotos dos seus trabalhos'
            : 'Fotos do anúncio';
        document.getElementById('photos-subtitle').textContent = isService
            ? 'Monte seu portfólio para destacar o trabalho e atrair clientes.'
            : 'Adicione fotos para apresentar melhor o item anunciado.';
        document.getElementById('review-heading').textContent = isService
            ? 'Revise e crie seu perfil profissional'
            : 'Revise e publique seu anúncio';
        document.getElementById('preview-heading').textContent = isService
            ? 'Prévia do perfil profissional'
            : 'Prévia do anúncio';
        document.getElementById('submit-label').textContent = isService
            ? 'Criar perfil profissional'
            : 'Publicar anúncio';
        document.getElementById('submit-help').textContent = isService
            ? 'Seu perfil ficará disponível na área de Prestadores de Serviços.'
            : 'Seu anúncio será publicado assim que o envio for concluído.';

        updateStoreSelection();
    }

    function updateStoreSelection() {
        const includeInStore = document.getElementById('include_in_store');
        const storeSelect = document.getElementById('store_id');
        const isProduct = document.querySelector('input[name="module"]:checked')?.value === 'products';

        if (!storeSelect) {
            return;
        }

        const enabled = isProduct && Boolean(includeInStore?.checked);
        storeSelect.disabled = !enabled;
        storeSelect.required = enabled && storeSelect.options.length > 1;
    }

    function updateSuggestedTitle() {
        const catSelect = document.getElementById('category_select');
        const citySelect = document.getElementById('city');
        const titleInput = document.getElementById('title');
        const modKey = document.querySelector('input[name="module"]:checked')?.value || 'services';
        const cityName = citySelect ? citySelect.value.replace(' - SE', '') : 'Sergipe';

        if (!catSelect.value) return;

        const currentTitle = titleInput.value.trim();
        const isDefaultPattern = !currentTitle || 
            currentTitle.includes('Residencial e Comercial em') || 
            currentTitle.endsWith(` em ${cityName}`) || 
            currentTitle.startsWith('Vaga: ');

        if (isDefaultPattern) {
            if (modKey === 'services') {
                titleInput.value = `${catSelect.value} em ${cityName}`;
            } else if (modKey === 'jobs') {
                titleInput.value = `Vaga: ${catSelect.value} em ${cityName}`;
            } else {
                titleInput.value = `${catSelect.value} em ${cityName}`;
            }
        }
        updatePreview();
    }

    function goToStep(stepNumber) {
        if (stepNumber > currentStep) {
            const currentStepElement = document.getElementById(`wizard-step-${currentStep}`);
            const invalidField = currentStepElement.querySelector(':invalid');

            if (invalidField) {
                invalidField.reportValidity();
                return;
            }
        }

        document.querySelectorAll('.wizard-step').forEach(el => el.classList.add('d-none'));
        document.getElementById(`wizard-step-${stepNumber}`).classList.remove('d-none');
        
        currentStep = stepNumber;

        for (let i = 1; i <= 4; i++) {
            const item = document.getElementById(`step-nav-${i}`);
            if (!item) continue;
            item.classList.remove('active', 'completed');
            if (i < stepNumber) {
                item.classList.add('completed');
                item.querySelector('.step-icon').innerHTML = '<i class="fa-solid fa-check"></i>';
            } else if (i === stepNumber) {
                item.classList.add('active');
                item.querySelector('.step-icon').textContent = i;
            } else {
                item.querySelector('.step-icon').textContent = i;
            }
        }

        const progressPercent = ((stepNumber - 1) / 3) * 100;
        const progressLine = document.getElementById('step-progress-line');
        if (progressLine) {
            progressLine.style.width = `${progressPercent}%`;
        }

        const stepPill = document.getElementById('publish-step-pill');
        if (stepPill) {
            stepPill.textContent = `Etapa ${stepNumber} de 4`;
        }

        if (stepNumber === 4) {
            updateReviewSummary();
        }

        window.scrollTo({ top: 150, behavior: 'smooth' });
    }

    function updateCharCount(el) {
        document.getElementById('char-count').textContent = el.value.length;
    }

    function readFileAsDataUrl(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    function readFileAsArrayBuffer(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = () => reject(reader.error || new Error('Falha ao ler a imagem selecionada.'));
            reader.readAsArrayBuffer(file);
        });
    }

    async function snapshotFileForUpload(file) {
        const contents = await readFileAsArrayBuffer(file);

        // Mantém uma cópia em memória. No Android, arquivos vindos da galeria,
        // WhatsApp ou editores podem apontar para URIs temporárias que mudam
        // antes do envio e fazem o Chrome abortar com ERR_UPLOAD_FILE_CHANGED.
        return new File([contents], file.name, {
            type: file.type || 'application/octet-stream',
            lastModified: file.lastModified || Date.now()
        });
    }

    function loadImage(source) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = reject;
            image.src = source;
        });
    }

    function canvasToBlob(canvas, quality) {
        return new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', quality));
    }

    async function optimizeImageForUpload(file) {
        try {
            const stableFile = await snapshotFileForUpload(file);
            if (!stableFile.type.startsWith('image/') || stableFile.size <= 900 * 1024) {
                return stableFile;
            }

            const source = await readFileAsDataUrl(stableFile);
            const image = await loadImage(source);
            const maxDimension = 1600;
            const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.width * scale));
            canvas.height = Math.max(1, Math.round(image.height * scale));
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

            let blob = null;
            for (const quality of [0.82, 0.68, 0.52]) {
                blob = await canvasToBlob(canvas, quality);
                if (blob && blob.size <= 900 * 1024) {
                    break;
                }
            }

            if (!blob) {
                return stableFile;
            }

            const baseName = stableFile.name.replace(/\.[^.]+$/, '');
            return new File([blob], `${baseName}.webp`, {
                type: 'image/webp',
                lastModified: Date.now()
            });
        } catch (error) {
            console.warn('Não foi possível preparar a imagem.', error);
            throw new Error('Não foi possível preparar esta imagem. Selecione-a novamente ou escolha outra foto.');
        }
    }

    function replaceFileInput(input, files) {
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    }

    async function previewMainPhoto(input) {
        if (input.files && input.files[0]) {
            imageProcessingCount++;
            try {
                const optimizedFile = await optimizeImageForUpload(input.files[0]);
                replaceFileInput(input, [optimizedFile]);
                const source = await readFileAsDataUrl(optimizedFile);
                document.getElementById('main-photo-img').src = source;
                document.getElementById('main-photo-preview-box').classList.remove('d-none');
                document.getElementById('prev-card-img').src = source;
            } catch (error) {
                input.value = '';
                alert(error.message);
            } finally {
                imageProcessingCount--;
            }
        }
    }

    async function previewBannerPhoto(input) {
        if (input.files && input.files[0]) {
            imageProcessingCount++;
            try {
                const optimizedFile = await optimizeImageForUpload(input.files[0]);
                replaceFileInput(input, [optimizedFile]);
                const source = await readFileAsDataUrl(optimizedFile);
                document.getElementById('banner-photo-img').src = source;
                document.getElementById('banner-photo-preview-box').classList.remove('d-none');
            } catch (error) {
                input.value = '';
                alert(error.message);
            } finally {
                imageProcessingCount--;
            }
        }
    }

    function removeMainPhoto() {
        document.getElementById('logo').value = '';
        document.getElementById('main-photo-img').src = '';
        document.getElementById('main-photo-preview-box').classList.add('d-none');
        document.getElementById('prev-card-img').src = "{{ asset('images/logo.png') }}";
    }

    async function previewGalleryPhotos(input) {
        if (input.files) {
            let filesArr = Array.from(input.files);
            
            if (selectedGalleryFiles.length + filesArr.length > 5) {
                alert('Atenção: O limite máximo no Plano Gratuito é de 5 fotos na galeria.');
                filesArr = filesArr.slice(0, 5 - selectedGalleryFiles.length);
            }

            imageProcessingCount++;
            try {
                filesArr = await Promise.all(filesArr.map(optimizeImageForUpload));

                filesArr.forEach(file => {
                    selectedGalleryFiles.push(file);
                });

                syncGalleryInput();
                renderGalleryPreviews();
            } catch (error) {
                input.value = '';
                alert(error.message);
            } finally {
                imageProcessingCount--;
            }
        }
    }

    function renderGalleryPreviews() {
        const container = document.getElementById('gallery-preview-container');
        const counter = document.getElementById('gallery-counter');

        // Remove imagens de preview mantendo o botão de adicionar foto
        const items = container.querySelectorAll('.gallery-item-preview');
        items.forEach(item => item.remove());

        counter.textContent = `${selectedGalleryFiles.length} / 5 fotos`;

        selectedGalleryFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'gallery-item-preview position-relative d-inline-block';
                wrapper.style.width = '110px';
                wrapper.style.height = '110px';

                wrapper.innerHTML = `
                    <img src="${e.target.result}" class="rounded-3 border object-fit-cover shadow-sm w-100 h-100">
                    <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 translate-middle shadow" style="width: 26px; height: 26px; padding: 0; line-height: 26px;" onclick="removeGalleryPhoto(${index})" title="Remover Foto">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;

                container.insertBefore(wrapper, container.lastElementChild);
            }
            reader.readAsDataURL(file);
        });
    }

    function removeGalleryPhoto(index) {
        selectedGalleryFiles.splice(index, 1);
        syncGalleryInput();
        renderGalleryPreviews();
    }

    function syncGalleryInput() {
        const input = document.getElementById('images');
        const transfer = new DataTransfer();

        selectedGalleryFiles.forEach(file => transfer.items.add(file));
        input.files = transfer.files;
    }

    function updatePreview() {
        const title = document.getElementById('title').value || 'Título do Anúncio';
        const cat = document.getElementById('category_select').value || 'Categoria';
        const city = document.getElementById('city').value || 'Sergipe';
        const whatsapp = document.getElementById('whatsapp').value || '(79) 99999-9999';

        document.getElementById('prev-card-title').textContent = title;
        document.getElementById('prev-card-cat').textContent = cat;
        document.getElementById('prev-card-city').textContent = city;
        document.getElementById('prev-card-btn').innerHTML = `<i class="fa-brands fa-whatsapp me-1"></i> ${whatsapp}`;
    }

    function updateReviewSummary() {
        const cat = document.getElementById('category_select').value || 'Eletricista';
        const title = document.getElementById('title').value || 'Eletricista Residencial em Aracaju';
        const city = document.getElementById('city').value || 'Aracaju - SE';
        const region = document.getElementById('region').value || 'Não informada';
        const desc = document.getElementById('description').value || 'Sem descrição';
        const whatsapp = document.getElementById('whatsapp').value || '(79) 99999-9999';

        document.getElementById('rev-cat').textContent = cat;
        document.getElementById('rev-title').textContent = title;
        document.getElementById('rev-city').textContent = city;
        document.getElementById('rev-region').textContent = region;
        document.getElementById('rev-desc').textContent = desc;
        document.getElementById('rev-whatsapp').innerHTML = `<i class="fa-brands fa-whatsapp me-1"></i> ${whatsapp}`;

        updatePreview();
    }

    function saveDraft() {
        alert('Rascunho salvo temporariamente no seu navegador!');
    }

    function formatBrazilianPrice(input) {
        let value = input.value.trim().replace(/[^\d,.]/g, '');
        if (!value) {
            input.value = '';
            return;
        }

        if (value.includes(',')) {
            value = value.replace(/\./g, '').replace(',', '.');
        } else if ((value.match(/\./g) || []).length > 1 || /^\d+\.\d{3}$/.test(value)) {
            value = value.replace(/\./g, '');
        }

        const amount = Number(value);
        if (!Number.isFinite(amount)) {
            return;
        }

        input.value = amount.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    async function importImageByUrl(inputId) {
        let url = prompt("Cole o link (URL) da imagem que você copiou do Google ou de outro site:");
        if (!url || !url.trim()) return;
        url = url.trim();

        try {
            document.body.style.cursor = 'wait';
            
            // Usando um proxy CORS gratuito para permitir baixar imagens de outros domínios
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
                // Se for galeria, preserva os arquivos que já estão lá
                for (let i = 0; i < fileInput.files.length; i++) {
                    dataTransfer.items.add(fileInput.files[i]);
                }
                dataTransfer.items.add(file);
            } else {
                // Se for único (logo, banner) apenas adiciona
                dataTransfer.items.add(file);
            }
            
            fileInput.files = dataTransfer.files;
            
            // Dispara o evento onchange para rodar o preview
            let event = new Event('change', { bubbles: true });
            fileInput.dispatchEvent(event);
            
        } catch(e) {
            alert("Erro ao importar a imagem: " + e.message);
        } finally {
            document.body.style.cursor = 'default';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (initialModule && initialModule !== 'services') {
            selectPublishMode('items');
        } else {
            selectPublishMode('services');
        }

        selectModule(initialModule, true);
        if (initialModule === 'services') {
            requestAnimationFrame(() => goToStep(2));
        }
        updateCharCount(document.getElementById('description'));
        document.getElementById('price').addEventListener('blur', function() {
            formatBrazilianPrice(this);
        });

        document.getElementById('wizardForm').addEventListener('submit', function(event) {
            if (imageProcessingCount > 0) {
                event.preventDefault();
                alert('Aguarde alguns segundos enquanto as imagens são preparadas para o envio.');
                return;
            }

            const invalidField = this.querySelector(':invalid');
            if (invalidField) {
                event.preventDefault();
                const invalidStep = invalidField.closest('.wizard-step');
                const stepNumber = Number(invalidStep?.id?.replace('wizard-step-', '')) || 1;
                goToStep(stepNumber);
                setTimeout(() => invalidField.reportValidity(), 100);
                return;
            }

            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            document.getElementById('submit-label').textContent = 'Publicando...';
        });
    });
</script>
@endpush
@endsection
