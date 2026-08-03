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
                            <div class="input-group input-group-lg shadow-sm">
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $ad->title) }}" required style="border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem;">
                                <button class="btn btn-white border d-flex align-items-center" type="button" id="btn-search-google" title="Pesquisar dados deste produto no Google" onclick="searchOnGoogle()" style="border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; background: #fff;">
                                    <i class="fa-brands fa-google me-2" style="color: #4285F4;"></i> <span class="d-none d-sm-inline fw-semibold text-dark" style="font-size: 0.9rem;">Buscar infos</span>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="category_name" class="form-label fw-semibold">Categoria *</label>
                            <select class="form-select form-select-lg rounded-3" id="category_name" name="category_name" required>
                                @foreach($availableCategories as $categoryName)
                                    <option value="{{ $categoryName }}" {{ old('category_name', $ad->display_category) === $categoryName ? 'selected' : '' }}>{{ $categoryName }}</option>
                                @endforeach
                                @if(!in_array($ad->display_category, $availableCategories, true))
                                    <option value="{{ $ad->display_category }}" selected>{{ $ad->display_category }}</option>
                                @endif
                            </select>
                        </div>

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
                        @endif

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">{{ $isService ? 'Sobre o profissional e seus serviços' : 'Descrição completa' }} *</label>
                            <textarea class="form-control rounded-3" id="description" name="description" rows="5" required>{{ old('description', $ad->description) }}</textarea>
                        </div>

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
                                    <input type="file" class="form-control rounded-3" id="logo" name="logo" accept="image/*">
                                    <small class="text-muted">Escolha outra imagem para substituir a atual.</small>
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
                                    <input type="file" class="form-control rounded-3" id="banner" name="banner" accept="image/*">
                                    <small class="text-muted">Escolha outra capa para substituir a atual.</small>
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
                            <input type="file" class="form-control rounded-3" id="images" name="images[]" accept="image/*" multiple>
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
</script>
@endpush

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
