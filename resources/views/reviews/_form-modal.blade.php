@php
    $editingReview = !is_null($review);
    $reviewingStore = $reviewable instanceof \App\Models\Store;
    $reviewStoreRoute = $reviewingStore ? route('store.reviews.store', $reviewable) : null;
    $reviewFormAction = $editingReview
        ? route('reviews.update', $review)
        : ($reviewStoreRoute ?: route('reviews.store', $reviewable));
@endphp
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ $reviewFormAction }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow">
            @csrf
            @if($editingReview) @method('PUT') @endif
            <div class="modal-header border-0">
                <div><h2 class="modal-title h5 fw-bold">{{ $editingReview ? 'Editar sua avaliação' : ($reviewingStore ? 'Avalie esta loja' : 'Avalie este conteúdo') }}</h2><small class="text-muted">Conte como foi sua experiência.</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Sua nota *</label>
                <div class="review-rating-input mb-4">
                    @foreach(range(5, 1) as $rating)
                        <input type="radio" id="{{ $modalId }}Rating{{ $rating }}" name="rating" value="{{ $rating }}" required @checked(old('rating', $review?->rating) == $rating)>
                        <label for="{{ $modalId }}Rating{{ $rating }}" title="{{ $rating }} estrelas"><i class="fa-solid fa-star"></i></label>
                    @endforeach
                </div>
                <label for="{{ $modalId }}Comment" class="form-label fw-semibold">Conte um pouco sobre sua experiência *</label>
                <textarea id="{{ $modalId }}Comment" name="comment" class="form-control rounded-3 mb-3" rows="5" minlength="10" maxlength="2000" required>{{ old('comment', $review?->comment) }}</textarea>

                @if($editingReview && !empty($review->image_paths))
                    <div class="row g-2 mb-3">
                        @foreach($review->image_paths as $path)
                            <div class="col-4"><img src="{{ asset($path) }}" class="w-100 rounded-3 object-fit-cover" style="height: 90px;"><label class="form-check small text-danger mt-1"><input type="checkbox" class="form-check-input" name="remove_review_images[]" value="{{ $path }}">Remover</label></div>
                        @endforeach
                    </div>
                @endif
                <label for="{{ $modalId }}Images" class="form-label fw-semibold">Adicionar fotos (opcional)</label>
                <input id="{{ $modalId }}Images" type="file" name="review_images[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
                <small class="text-muted">Até 3 imagens, 5 MB cada.</small>
            </div>
            <div class="modal-footer border-0"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary rounded-pill px-4 fw-bold">{{ $editingReview ? 'Salvar alterações' : 'Publicar avaliação' }}</button></div>
        </form>
    </div>
</div>
