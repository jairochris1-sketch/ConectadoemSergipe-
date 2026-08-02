@php
    $product = $product ?? null;
    $variationRows = old('variations', $product?->variations?->map(fn ($variation) => [
        'id' => $variation->id,
        'name' => $variation->name,
        'sku' => $variation->sku,
        'price' => $variation->price,
        'price_adjustment' => $variation->price_adjustment,
        'stock_quantity' => $variation->stock_quantity,
        'track_stock' => $variation->track_stock,
        'active' => $variation->active,
        'image' => $variation->image,
    ])->all() ?? []);
    $addonRows = old('addons', $product?->addons?->map(fn ($addon) => [
        'id' => $addon->id,
        'name' => $addon->name,
        'price' => $addon->price,
        'active' => $addon->active,
    ])->all() ?? []);
@endphp

<div class="border rounded-4 p-3 p-md-4 mt-4 product-commerce-fields">
    <h3 class="h5 fw-bold mb-3">Preço e estoque</h3>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label" for="sku">SKU</label>
            <input class="form-control" id="sku" name="sku" maxlength="100" value="{{ old('sku', $product?->sku) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="video_url">Vídeo do produto</label>
            <input class="form-control" type="url" id="video_url" name="video_url" value="{{ old('video_url', $product?->video_url) }}" placeholder="https://youtube.com/...">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="technical_specs_text">Ficha técnica</label>
            <textarea class="form-control" id="technical_specs_text" name="technical_specs_text" rows="3" placeholder="Material: Algodão&#10;Peso: 500 g">{{ old('technical_specs_text', collect($product?->technical_specs)->map(fn ($value, $label) => "{$label}: {$value}")->implode("\n")) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="sale_price">Preço promocional</label>
            <input class="form-control" id="sale_price" name="sale_price" inputmode="decimal" value="{{ old('sale_price', $product?->sale_price) }}" placeholder="Ex.: 89,90">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="minimum_quantity">Pedido mínimo do item</label>
            <input class="form-control" type="number" id="minimum_quantity" name="minimum_quantity" min="1" max="99" value="{{ old('minimum_quantity', $product?->minimum_quantity ?? 1) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="stock_quantity">Estoque</label>
            <input class="form-control" type="number" id="stock_quantity" name="stock_quantity" min="0" value="{{ old('stock_quantity', $product?->stock_quantity ?? 0) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="low_stock_threshold">Avisar últimas unidades em</label>
            <input class="form-control" type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? 5) }}">
        </div>
        <div class="col-md-4 d-flex flex-column justify-content-end gap-2">
            <label>
                <input type="hidden" name="track_stock" value="0">
                <input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', $product?->track_stock ?? false))>
                Controlar estoque
            </label>
            <label>
                <input type="hidden" name="allow_backorders" value="0">
                <input type="checkbox" name="allow_backorders" value="1" @checked(old('allow_backorders', $product?->allow_backorders ?? false))>
                Permitir encomenda sem estoque
            </label>
        </div>
    </div>

    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4 class="h6 fw-bold mb-0">Variações</h4>
                <small class="text-muted">Ex.: Pizza grande, Camiseta preta / M.</small>
            </div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-add-product-row="variations">Adicionar variação</button>
        </div>
        <div data-product-rows="variations">
            @foreach($variationRows as $index => $variation)
                <div class="row g-2 mb-2 align-items-end" data-product-row>
                    <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variation['id'] ?? '' }}">
                    <input type="hidden" name="variations[{{ $index }}][track_stock]" value="1">
                    <input type="hidden" name="variations[{{ $index }}][active]" value="1">
                    <div class="col-md-3"><label class="form-label small">Nome<input class="form-control" name="variations[{{ $index }}][name]" value="{{ $variation['name'] ?? '' }}"></label></div>
                    <div class="col-md-2"><label class="form-label small">SKU<input class="form-control" name="variations[{{ $index }}][sku]" value="{{ $variation['sku'] ?? '' }}"></label></div>
                    <div class="col-md-2"><label class="form-label small">Preço próprio<input class="form-control" type="number" step="0.01" min="0" name="variations[{{ $index }}][price]" value="{{ $variation['price'] ?? '' }}"></label></div>
                    <div class="col-md-2"><label class="form-label small">Acréscimo<input class="form-control" type="number" step="0.01" name="variations[{{ $index }}][price_adjustment]" value="{{ $variation['price_adjustment'] ?? 0 }}"></label></div>
                    <div class="col-md-2"><label class="form-label small">Estoque<input class="form-control" type="number" min="0" name="variations[{{ $index }}][stock_quantity]" value="{{ $variation['stock_quantity'] ?? 0 }}"></label></div>
                    <div class="col-md-1"><button class="btn btn-outline-danger" type="button" data-remove-product-row aria-label="Remover">×</button></div>
                    <div class="col-12">
                        <label class="form-label small">Imagem específica
                            @if($variation['image'] ?? null)<img src="{{ asset($variation['image']) }}" alt="" class="d-block rounded border mb-1" style="width:72px;height:72px;object-fit:cover">@endif
                            <input class="form-control" type="file" name="variations[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp">
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h4 class="h6 fw-bold mb-0">Adicionais</h4>
                <small class="text-muted">Ex.: Bacon extra, embalagem para presente.</small>
            </div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-add-product-row="addons">Adicionar adicional</button>
        </div>
        <div data-product-rows="addons">
            @foreach($addonRows as $index => $addon)
                <div class="row g-2 mb-2 align-items-end" data-product-row>
                    <input type="hidden" name="addons[{{ $index }}][id]" value="{{ $addon['id'] ?? '' }}">
                    <input type="hidden" name="addons[{{ $index }}][active]" value="1">
                    <div class="col-md-7"><label class="form-label small">Nome<input class="form-control" name="addons[{{ $index }}][name]" value="{{ $addon['name'] ?? '' }}"></label></div>
                    <div class="col-md-4"><label class="form-label small">Preço<input class="form-control" type="number" step="0.01" min="0" name="addons[{{ $index }}][price]" value="{{ $addon['price'] ?? 0 }}"></label></div>
                    <div class="col-md-1"><button class="btn btn-outline-danger" type="button" data-remove-product-row aria-label="Remover">×</button></div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (() => {
                const templates = {
                    variations: (index) => `<div class="row g-2 mb-2 align-items-end" data-product-row>
                        <input type="hidden" name="variations[${index}][track_stock]" value="1">
                        <input type="hidden" name="variations[${index}][active]" value="1">
                        <div class="col-md-3"><label class="form-label small">Nome<input class="form-control" name="variations[${index}][name]"></label></div>
                        <div class="col-md-2"><label class="form-label small">SKU<input class="form-control" name="variations[${index}][sku]"></label></div>
                        <div class="col-md-2"><label class="form-label small">Preço próprio<input class="form-control" type="number" step="0.01" min="0" name="variations[${index}][price]"></label></div>
                        <div class="col-md-2"><label class="form-label small">Acréscimo<input class="form-control" type="number" step="0.01" name="variations[${index}][price_adjustment]" value="0"></label></div>
                        <div class="col-md-2"><label class="form-label small">Estoque<input class="form-control" type="number" min="0" name="variations[${index}][stock_quantity]" value="0"></label></div>
                        <div class="col-md-1"><button class="btn btn-outline-danger" type="button" data-remove-product-row>×</button></div>
                        <div class="col-12"><label class="form-label small">Imagem específica<input class="form-control" type="file" name="variations[${index}][image]" accept="image/jpeg,image/png,image/webp"></label></div>
                    </div>`,
                    addons: (index) => `<div class="row g-2 mb-2 align-items-end" data-product-row>
                        <input type="hidden" name="addons[${index}][active]" value="1">
                        <div class="col-md-7"><label class="form-label small">Nome<input class="form-control" name="addons[${index}][name]"></label></div>
                        <div class="col-md-4"><label class="form-label small">Preço<input class="form-control" type="number" step="0.01" min="0" name="addons[${index}][price]" value="0"></label></div>
                        <div class="col-md-1"><button class="btn btn-outline-danger" type="button" data-remove-product-row>×</button></div>
                    </div>`,
                };
                document.addEventListener('click', (event) => {
                    const addButton = event.target.closest('[data-add-product-row]');
                    if (addButton) {
                        const type = addButton.dataset.addProductRow;
                        const target = document.querySelector(`[data-product-rows="${type}"]`);
                        target.insertAdjacentHTML('beforeend', templates[type](target.children.length + Date.now()));
                    }
                    const removeButton = event.target.closest('[data-remove-product-row]');
                    if (removeButton) removeButton.closest('[data-product-row]').remove();
                });
            })();
        </script>
    @endpush
@endonce
