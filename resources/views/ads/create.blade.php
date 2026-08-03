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

            <!-- STEPPER HEADER PROGRESS BAR -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 publish-stepper-card">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center position-relative px-2 px-md-5 publish-step-track">
                        
                        <!-- Etapa 1 -->
                        <div class="text-center z-index-2 step-item active" id="step-nav-1">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">1</div>
                            <small class="step-label d-none d-md-block fw-semibold">Categoria</small>
                        </div>
                        <!-- Etapa 2 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-2">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">2</div>
                            <small class="step-label d-none d-md-block fw-semibold">Informações</small>
                        </div>
                        <!-- Etapa 3 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-3">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">3</div>
                            <small class="step-label d-none d-md-block fw-semibold">Fotos</small>
                        </div>
                        <!-- Etapa 4 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-4">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">4</div>
                            <small class="step-label d-none d-md-block fw-semibold">Contato</small>
                        </div>
                        <!-- Etapa 5 -->
                        <div class="text-center z-index-2 step-item" id="step-nav-5">
                            <div class="step-icon rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-1">5</div>
                            <small class="step-label d-none d-md-block fw-semibold">Publicar</small>
                        </div>

                    </div>
                </div>
            </div>

            <!-- FORM CARD PRINCIPAL -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5 publish-form-card">
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

                        <!-- ================= ETAPA 1: CATEGORIA ================= -->
                        <div class="wizard-step" id="wizard-step-1">
                            <div class="text-center mb-4">
                                <span class="publish-step-pill mb-3" id="publish-step-pill">Etapa 1 de 5</span>
                                <h3 class="fw-bold text-dark mb-1">O que você deseja anunciar?</h3>
                                <p class="text-muted">Escolha a categoria que melhor representa o seu anúncio.</p>
                            </div>

                            <input type="radio" class="d-none" name="module" id="mod_services" value="services" {{ old('module', $requestedModule) === 'services' ? 'checked' : '' }} onchange="selectModule('services')">

                            <div class="row g-3 mb-4 publish-module-grid">
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check" name="module" id="mod_products" value="products" {{ old('module', $requestedModule) === 'products' ? 'checked' : '' }} onchange="selectModule('products')">
                                    <label class="btn btn-outline-primary w-100 rounded-4 shadow-sm h-100 module-card module-card-products" for="mod_products">
                                        <span class="module-motion-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                                        <span class="module-card-copy">
                                            <span class="fw-bold text-dark d-block">Produto</span>
                                            <small class="text-muted">Venda produtos novos ou usados.</small>
                                        </span>
                                        <span class="module-card-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check" name="module" id="mod_real_estate" value="real_estate" {{ old('module', $requestedModule) === 'real_estate' ? 'checked' : '' }} onchange="selectModule('real_estate')">
                                    <label class="btn btn-outline-primary w-100 rounded-4 shadow-sm h-100 module-card module-card-real-estate" for="mod_real_estate">
                                        <span class="module-motion-icon"><i class="fa-solid fa-house-chimney"></i></span>
                                        <span class="module-card-copy">
                                            <span class="fw-bold text-dark d-block">Imóvel</span>
                                            <small class="text-muted">Aluguel, venda ou temporada.</small>
                                        </span>
                                        <span class="module-card-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check" name="module" id="mod_vehicles" value="vehicles" {{ old('module', $requestedModule) === 'vehicles' ? 'checked' : '' }} onchange="selectModule('vehicles')">
                                    <label class="btn btn-outline-primary w-100 rounded-4 shadow-sm h-100 module-card module-card-vehicles" for="mod_vehicles">
                                        <span class="module-motion-icon"><i class="fa-solid fa-car-side"></i></span>
                                        <span class="module-card-copy">
                                            <span class="fw-bold text-dark d-block">Veículo</span>
                                            <small class="text-muted">Carros, motos, caminhões e muito mais.</small>
                                        </span>
                                        <span class="module-card-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check" name="module" id="mod_jobs" value="jobs" {{ old('module', $requestedModule) === 'jobs' ? 'checked' : '' }} onchange="selectModule('jobs')">
                                    <label class="btn btn-outline-primary w-100 rounded-4 shadow-sm h-100 module-card module-card-jobs" for="mod_jobs">
                                        <span class="module-motion-icon"><i class="fa-solid fa-briefcase"></i></span>
                                        <span class="module-card-copy">
                                            <span class="fw-bold text-dark d-block">Emprego</span>
                                            <small class="text-muted">Vagas ou busca por oportunidades.</small>
                                        </span>
                                        <span class="module-card-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4">
                                    <input type="radio" class="btn-check" name="module" id="mod_agro" value="agro" {{ old('module', $requestedModule) === 'agro' ? 'checked' : '' }} onchange="selectModule('agro')">
                                    <label class="btn btn-outline-primary w-100 rounded-4 shadow-sm h-100 module-card module-card-agro" for="mod_agro">
                                        <span class="module-motion-icon"><i class="fa-solid fa-tractor"></i></span>
                                        <span class="module-card-copy">
                                            <span class="fw-bold text-dark d-block">Agro</span>
                                            <small class="text-muted">Produtos e serviços agrícolas.</small>
                                        </span>
                                        <span class="module-card-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                                    </label>
                                </div>
                                <div class="col-6 col-md-4">
                                    <a href="{{ route('store.create') }}" class="btn btn-outline-primary w-100 rounded-4 shadow-sm h-100 module-card module-card-store text-decoration-none d-flex flex-column justify-content-between text-start" title="Criar sua Loja ou Comércio Local no Conectado em Sergipe">
                                        <span class="module-motion-icon"><i class="fa-solid fa-store"></i></span>
                                        <span class="module-card-copy">
                                            <span class="fw-bold text-dark d-block">Loja</span>
                                            <small class="text-muted">Crie sua loja ou comércio local.</small>
                                        </span>
                                        <span class="module-card-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                                    </a>
                                </div>
                            </div>

                            <div class="professional-motion-panel mb-3">
                                <span class="professional-motion-icon" aria-hidden="true">
                                    <i class="fa-solid fa-user-tie"></i>
                                    <i class="fa-solid fa-star"></i>
                                </span>
                                <div class="professional-motion-copy">
                                    <h4>Trabalha por conta própria?</h4>
                                    <p>Crie seu perfil profissional, mostre seus serviços e conecte-se com clientes em Sergipe.</p>
                                </div>
                                <button type="button" class="btn professional-motion-cta" onclick="chooseProfessionalProfile()">
                                    Criar meu perfil profissional
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top wizard-actions">
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold wizard-action">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Voltar
                                </a>
                                <button type="button" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm wizard-action" onclick="goToStep(2)">
                                    Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= ETAPA 2: INFORMAÇÕES ================= -->
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

                            <div class="mb-3">
                                <label for="category_select" class="form-label fw-semibold">Especifique a Subcategoria *</label>
                                <select class="form-select form-select-lg rounded-3" id="category_select" name="category_name" onchange="updateSuggestedTitle()" required>
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-end mb-1">
                                    <label for="title" class="form-label fw-semibold mb-0" id="title-label">Nome do perfil profissional *</label>
                                </div>
                                <div class="input-group input-group-lg shadow-sm">
                                    <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="Ex: iPhone 13 Pro Max 128GB Lacrado, Sofá Retrátil, etc." required oninput="updatePreview()" style="border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem;">
                                    <button class="btn btn-white border d-flex align-items-center" type="button" id="btn-search-google" title="Pesquisar dados deste produto no Google" onclick="searchOnGoogle()" style="border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; background: #fff;">
                                        <i class="fa-brands fa-google me-2" style="color: #4285F4;"></i> <span class="d-none d-sm-inline fw-semibold text-dark" style="font-size: 0.9rem;">Buscar infos</span>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1" id="title-help">Dica: seja claro e direto no nome. Use o botão do Google para achar ficha técnica facilmente.</small>
                            </div>

                            <div class="mb-3" id="price-field">
                                <label for="price" class="form-label fw-semibold">Preço (R$)</label>
                                <input type="text" inputmode="decimal" class="form-control form-control-lg rounded-3" id="price" name="price" value="{{ old('price') }}" placeholder="Ex: 80.000,00">
                                <small class="text-muted">Você pode digitar 80000 ou 80.000,00.</small>
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
                                                @php($storeIsFull = $storeProductLimit !== null && $store->products_count >= $storeProductLimit)
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

                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold" id="description-label">Sobre o profissional e seus serviços *</label>
                                <textarea class="form-control rounded-3" id="description" name="description" rows="5" maxlength="1000" placeholder="Trabalho com instalações elétricas residenciais e comerciais, manutenção, troca de fiação, disjuntores, tomadas, iluminação, entre outros serviços. Atendimento rápido e com qualidade!" oninput="updateCharCount(this); updatePreview();" required>{{ old('description') }}</textarea>
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
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="document.getElementById('logo').click()">Selecionar arquivo</button>
                                    
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
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="document.getElementById('banner').click()">Selecionar capa</button>
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

                        <!-- ================= ETAPA 4: CONTATO ================= -->
                        <div class="wizard-step d-none" id="wizard-step-4">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark mb-1">Informações de contato</h3>
                                <p class="text-muted">Como os clientes podem falar com você?</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-brands fa-whatsapp text-success me-2"></i> Contato principal</h6>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="whatsapp" class="form-label fw-semibold">WhatsApp *</label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', auth()->user()->whatsapp ?? '') }}" placeholder="(79) 99999-9999" required oninput="updatePreview()">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="phone" class="form-label fw-semibold">Telefone (opcional)</label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="(79) 3333-3333">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-share-nodes text-primary me-2"></i> Redes sociais (opcional)</h6>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="instagram" class="form-label fw-semibold">Instagram</label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="instagram" name="instagram" value="{{ old('instagram') }}" placeholder="@seudoinstagram">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="facebook" class="form-label fw-semibold">Facebook</label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="facebook" name="facebook" value="{{ old('facebook') }}" placeholder="/seudofacebook">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-4 rounded-4 border mb-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-id-card text-secondary me-2"></i> Dados comerciais (opcional)</h6>
                                <label for="cnpj" class="form-label fw-semibold">CNPJ</label>
                                <input type="text" class="form-control rounded-3" id="cnpj" name="cnpj" value="{{ old('cnpj') }}" placeholder="00.000.000/0001-00">
                                <small class="text-muted">Preencha apenas se você tem empresa registrada</small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top wizard-actions">
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold wizard-action" onclick="goToStep(3)">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Voltar
                                </button>
                                <button type="button" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm wizard-action" onclick="goToStep(5)">
                                    Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ================= ETAPA 5: PUBLICAR & REVISAR ================= -->
                        <div class="wizard-step d-none" id="wizard-step-5">
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
                                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold wizard-action" onclick="goToStep(4)">
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
.publish-toolbar .btn {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    color: var(--foreground);
    background: color-mix(in srgb, var(--card) 88%, transparent);
    border-color: var(--border);
}
.publish-stepper-card,
.publish-form-card {
    color: var(--foreground);
    background:
        radial-gradient(circle at 50% 0, color-mix(in srgb, var(--publish-blue) 8%, transparent), transparent 38%),
        var(--card);
    border: 1px solid var(--border) !important;
}
.publish-stepper-card {
    overflow: hidden;
    box-shadow: 0 12px 32px rgba(15, 23, 42, .07) !important;
}
.publish-step-track::before {
    content: "";
    position: absolute;
    z-index: 0;
    top: 19px;
    left: calc(10% + 12px);
    right: calc(10% + 12px);
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
.publish-stepper-card .step-label {
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
    min-height: 118px;
    display: grid !important;
    grid-template-columns: 92px minmax(0, 1fr) 28px;
    align-items: center;
    gap: 12px;
    padding: 14px 15px !important;
    overflow: hidden;
    color: var(--foreground) !important;
    text-align: left;
    background:
        linear-gradient(135deg, color-mix(in srgb, var(--motion-color) 7%, transparent), transparent 48%),
        var(--card) !important;
    border: 1px solid var(--border) !important;
    transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
}
.module-card-products {
    --motion-color: #1265f5;
    --motion-color-soft: #6d91ff;
}
.module-card-real-estate {
    --motion-color: #1265f5;
    --motion-color-soft: #6d91ff;
}
.module-card-vehicles {
    --motion-color: #1265f5;
    --motion-color-soft: #6d91ff;
}
.module-card-jobs {
    --motion-color: #1265f5;
    --motion-color-soft: #6d91ff;
}
.module-card-agro {
    --motion-color: #24a148;
    --motion-color-soft: #7ad957;
}
.module-card-store {
    --motion-color: #8b5cf6;
    --motion-color-soft: #a78bfa;
}
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
    content: "✓";
    position: absolute;
    top: 10px;
    right: 10px;
    width: 22px;
    height: 22px;
    display: grid;
    place-items: center;
    color: #fff;
    background: var(--motion-color);
    border-radius: 50%;
    font-size: .72rem;
    font-weight: 900;
    box-shadow: 0 5px 13px color-mix(in srgb, var(--motion-color) 38%, transparent);
}
.module-motion-icon {
    position: relative;
    width: 88px;
    height: 76px;
    display: grid;
    place-items: center;
    isolation: isolate;
}
.module-motion-icon::after {
    content: "";
    position: absolute;
    z-index: -1;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: radial-gradient(circle, color-mix(in srgb, var(--motion-color) 35%, transparent), transparent 68%);
    filter: blur(5px);
    animation: moduleMotionPulse 2.4s ease-in-out infinite;
}
.module-motion-icon i {
    color: var(--motion-color) !important;
    font-size: 2.85rem;
    filter: drop-shadow(0 5px 8px color-mix(in srgb, var(--motion-color) 30%, transparent));
    transform: perspective(120px) rotateY(-8deg);
    animation: moduleMotionFloat 2.8s ease-in-out infinite;
}
.module-card:hover .module-motion-icon i {
    animation: moduleMotionBurst .55s ease both;
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
    grid-template-columns: 150px minmax(0, 1fr) auto;
    align-items: center;
    gap: 20px;
    min-height: 110px;
    padding: 16px 22px;
    overflow: hidden;
    color: var(--foreground);
    background:
        linear-gradient(100deg, color-mix(in srgb, #4e35ec 12%, transparent), transparent 36%),
        var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
}
.professional-motion-icon {
    position: relative;
    height: 78px;
    display: grid;
    place-items: center;
    color: #1265f5;
}
.professional-motion-icon .fa-user-tie {
    position: relative;
    z-index: 1;
    font-size: 3.8rem;
    filter: drop-shadow(0 6px 10px rgba(18, 101, 245, .32));
    animation: moduleMotionFloat 2.7s ease-in-out infinite;
}
.professional-motion-icon .fa-star {
    position: absolute;
    z-index: 2;
    right: 28px;
    bottom: 4px;
    padding: 6px;
    color: #ffd028;
    background: #4730c8;
    border-radius: 50%;
    font-size: 1rem;
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
    background-color: #4f46e5;
    color: #ffffff;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.2);
}
.step-item.completed .step-icon {
    background-color: #10b981;
    color: #ffffff;
}
.step-item.active .step-label {
    color: #4f46e5;
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
    .publish-stepper-card .card-body {
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

    const categoryLists = {
        services: ['Eletricista', 'Encanador', 'Pintor', 'Mecânico', 'Advogado', 'Faxineira / Diarista', 'Marcenaria', 'TI / Informática', 'Frete e Mudanças', 'Restaurante / Pizzaria', 'Pedreiro', 'Jardineiro'],
        products: ['Celulares & Telefonia', 'Informática', 'Roupas & Calçados', 'Móveis & Decoração', 'Eletrodomésticos', 'Esporte & Lazer'],
        real_estate: ['Casas Aluguel/Venda', 'Apartamentos', 'Terrenos & Lotes', 'Salas Comerciais', 'Sítios & Chácaras'],
        vehicles: ['Carros Usados/Seminovos', 'Motos', 'Caminhões', 'Peças & Acessórios'],
        jobs: ['Vagas de Emprego', 'Currículos / Procurando', 'Estágios'],
        agro: ['Animais & Pecuária', 'Tratores & Máquinas', 'Insumos & Sementes']
    };

    function chooseProfessionalProfile() {
        const serviceOption = document.getElementById('mod_services');
        serviceOption.checked = true;
        selectModule('services');
        goToStep(2);
    }

    function selectModule(modKey, preserveTitle = false) {
        const catSelect = document.getElementById('category_select');
        catSelect.innerHTML = '';
        const list = categoryLists[modKey] || categoryLists.services;
        
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

        updateModuleLanguage(modKey);

        if (preserveTitle) {
            updatePreview();
        } else {
            updateSuggestedTitle();
        }
    }

    const categoryPlaceholders = {
        services: 'Ex: Eletricista Residencial e Comercial em Aracaju',
        products: 'Ex: iPhone 13 Pro Max 128GB Lacrado, Sofá Retrátil, etc.',
        real_estate: 'Ex: Casa 3 Quartos com Suíte no Luzia, Terreno 250m², etc.',
        vehicles: 'Ex: Honda Civic 2.0 Flex 2020 Automático, Moto Fazer 250, etc.',
        jobs: 'Ex: Vaga para Atendente de Loja, Vendedor Interno, etc.',
        agro: 'Ex: Trator Massey Ferguson 275, Sementes de Milho, etc.'
    };

    function updateModuleLanguage(modKey) {
        const isService = modKey === 'services';
        const isProduct = modKey === 'products';

        const titleInput = document.getElementById('title');
        if (titleInput) {
            titleInput.placeholder = categoryPlaceholders[modKey] || categoryPlaceholders.products;
        }

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
        document.getElementById('price-field').classList.toggle('d-none', isService);
        document.getElementById('region-field').classList.toggle('d-none', !isService);
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

        for (let i = 1; i <= 5; i++) {
            const item = document.getElementById(`step-nav-${i}`);
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

        const progressPercent = ((stepNumber - 1) / 4) * 100;
        const progressLine = document.getElementById('step-progress-line');
        if (progressLine) {
            progressLine.style.width = `${progressPercent}%`;
        }

        if (stepNumber === 5) {
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
        if (!file?.type?.startsWith('image/') || file.size <= 900 * 1024) {
            return file;
        }

        try {
            const source = await readFileAsDataUrl(file);
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
                return file;
            }

            const baseName = file.name.replace(/\.[^.]+$/, '');
            return new File([blob], `${baseName}.webp`, {
                type: 'image/webp',
                lastModified: Date.now()
            });
        } catch (error) {
            console.warn('Não foi possível otimizar a imagem.', error);
            return file;
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
            const optimizedFile = await optimizeImageForUpload(input.files[0]);
            replaceFileInput(input, [optimizedFile]);
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('main-photo-img').src = e.target.result;
                document.getElementById('main-photo-preview-box').classList.remove('d-none');
                document.getElementById('prev-card-img').src = e.target.result;
                imageProcessingCount--;
            }
            reader.onerror = () => imageProcessingCount--;
            reader.readAsDataURL(optimizedFile);
        }
    }

    async function previewBannerPhoto(input) {
        if (input.files && input.files[0]) {
            imageProcessingCount++;
            const optimizedFile = await optimizeImageForUpload(input.files[0]);
            replaceFileInput(input, [optimizedFile]);
            const source = await readFileAsDataUrl(optimizedFile);
            document.getElementById('banner-photo-img').src = source;
            document.getElementById('banner-photo-preview-box').classList.remove('d-none');
            imageProcessingCount--;
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
            filesArr = await Promise.all(filesArr.map(optimizeImageForUpload));
            imageProcessingCount--;

            filesArr.forEach(file => {
                selectedGalleryFiles.push(file);
            });

            syncGalleryInput();
            renderGalleryPreviews();
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

    function searchOnGoogle() {
        const titleInput = document.getElementById('title');
        const titleValue = titleInput.value.trim();
        if (titleValue.length > 0) {
            window.open('https://www.google.com/search?q=' + encodeURIComponent(titleValue), '_blank');
        } else {
            alert('Por favor, digite o nome do produto antes de buscar no Google.');
            titleInput.focus();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
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
