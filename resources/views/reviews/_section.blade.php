@php
    $reviewCount = $reviewData['count'];
    $reviewAverage = $reviewData['average'];
    $reviewableOwner = auth()->check() && auth()->id() === $reviewable->user_id;
    $currentUserReview = $reviewData['userReview'];
    $reviewModalId = 'reviewModal' . $reviewable->id;
    $reviewingStore = $reviewable instanceof \App\Models\Store;
    $ownerResponseLabel = $reviewingStore ? 'Resposta da loja' : 'Resposta do profissional';
@endphp

<section class="reviews-section mt-5" id="avaliacoes">
    @if(session('review_success'))
        <div class="alert alert-success rounded-4">{{ session('review_success') }}</div>
    @endif
    @if($errors->hasAny(['review', 'rating', 'comment', 'review_images', 'review_report', 'reply']))
        <div class="alert alert-danger rounded-4">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="reviews-summary-card bg-white border rounded-4 shadow-sm p-4 p-md-5">
        <h2 class="h4 fw-bold mb-4">Avaliações dos usuários</h2>
        <div class="row g-4 align-items-center">
            <div class="col-12 col-md-4 text-center text-md-start">
                <div class="reviews-average">{{ $reviewCount ? number_format($reviewAverage, 1, ',', '.') : '—' }}</div>
                <div class="reviews-stars fs-4 mb-2" aria-label="{{ $reviewAverage }} de 5 estrelas">
                    @foreach(range(1, 5) as $star)
                        <i class="fa-{{ $star <= round($reviewAverage) ? 'solid' : 'regular' }} fa-star"></i>
                    @endforeach
                </div>
                <div class="fw-semibold">{{ $reviewCount ? ($reviewAverage >= 4.5 ? 'Excelente' : ($reviewAverage >= 3.5 ? 'Muito bom' : ($reviewAverage >= 2.5 ? 'Regular' : 'Precisa melhorar'))) : 'Ainda sem avaliações' }}</div>
                @if($reviewCount >= 5)
                    <a href="#avaliacoes" class="reviews-platform-count text-decoration-none fw-semibold">
                        {{ $reviewCount }} Avaliações no Conectado em Sergipe
                    </a>
                @else
                    <div class="text-muted">{{ $reviewCount }} {{ $reviewCount === 1 ? 'avaliação' : 'avaliações' }}</div>
                @endif
            </div>
            <div class="col-12 col-md-8">
                @foreach($reviewData['distribution'] as $rating => $ratingData)
                    <div class="review-distribution-row">
                        <span>{{ $rating }} {{ $rating === 1 ? 'estrela' : 'estrelas' }}</span>
                        <div class="progress"><div class="progress-bar bg-warning" style="width: {{ $ratingData['percent'] }}%"></div></div>
                        <span>{{ $ratingData['count'] }} ({{ $ratingData['percent'] }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="text-center border-top pt-4 mt-4">
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-5 fw-bold">Entre para avaliar</a>
            @elseif($reviewableOwner)
                <span class="text-muted small"><i class="fa-solid fa-shield-halved me-1"></i>O proprietário não pode avaliar o próprio conteúdo.</span>
            @elseif($currentUserReview)
                <button type="button" class="btn btn-outline-primary rounded-pill px-5 fw-bold" data-bs-toggle="modal" data-bs-target="#{{ $reviewModalId }}Edit">Editar minha avaliação</button>
                @if($currentUserReview->status === 'hidden')<div class="small text-danger mt-2">Esta avaliação está oculta por moderação.</div>@endif
            @else
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold review-write-button" data-bs-toggle="modal" data-bs-target="#{{ $reviewModalId }}">Escrever uma avaliação</button>
            @endguest
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 my-4">
        @foreach(['relevant' => 'Mais relevantes', 'recent' => 'Mais recentes', 'highest' => 'Maior nota', 'lowest' => 'Menor nota'] as $sortValue => $sortLabel)
            <a href="{{ request()->fullUrlWithQuery(['reviews_sort' => $sortValue]) }}#avaliacoes" class="btn btn-sm {{ request('reviews_sort', 'relevant') === $sortValue ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">{{ $sortLabel }}</a>
        @endforeach
    </div>

    <div class="reviews-list">
        @forelse($reviewData['reviews'] as $review)
            <article class="review-card bg-white border rounded-4 p-4 mb-3" id="avaliacao-{{ $review->id }}">
                <div class="d-flex align-items-start gap-3">
                    <div class="review-avatar">
                        @if($review->user->avatar)
                            <img src="{{ asset($review->user->avatar) }}" alt="">
                        @else
                            {{ strtoupper(substr($review->user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <strong class="review-author-name">{{ $review->user->name }}</strong>
                                @if($review->user->city)
                                    <span class="review-author-city">em {{ $review->user->city }}</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $review->created_at->format('d/m/Y') }} · {{ $review->created_at->diffForHumans() }}
                                @if($review->edited_at) · editada @endif
                            </small>
                        </div>
                        <div class="reviews-stars my-2" aria-label="{{ $review->rating }} de 5 estrelas">
                            @foreach(range(1, 5) as $star)<i class="fa-{{ $star <= $review->rating ? 'solid' : 'regular' }} fa-star"></i>@endforeach
                            <span class="text-dark ms-1">{{ number_format($review->rating, 1, ',', '.') }}</span>
                        </div>
                        <p class="review-comment mb-3">{{ $review->comment }}</p>

                        @if(!empty($review->image_paths))
                            <div class="d-flex gap-2 overflow-auto mb-3">
                                @foreach($review->image_paths as $path)
                                    <a href="{{ asset($path) }}" target="_blank"><img src="{{ asset($path) }}" class="review-image" alt="Foto da avaliação"></a>
                                @endforeach
                            </div>
                        @endif

                        @if($review->professional_reply)
                            <section class="professional-review-reply" id="resposta-avaliacao-{{ $review->id }}">
                                <div class="professional-review-reply-header">
                                    <span class="professional-review-reply-icon">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </span>
                                    <div>
                                        <strong>{{ $ownerResponseLabel }}</strong>
                                        <small>
                                            {{ $review->professionalReplyUser?->name ?? $reviewable->user->name }}
                                            · {{ $review->professional_replied_at?->format('d/m/Y H:i') }}
                                            @if($review->professional_reply_edited_at) · editada @endif
                                        </small>
                                    </div>
                                </div>
                                <p>{{ $review->professional_reply }}</p>

                                @if($reviewableOwner)
                                    <div class="professional-review-reply-actions">
                                        <details>
                                            <summary class="btn btn-sm btn-outline-primary rounded-pill">Editar resposta</summary>
                                            <form action="{{ route('reviews.reply.update', $review) }}" method="POST" class="professional-review-reply-form mt-2">
                                                @csrf
                                                @method('PUT')
                                                <label for="reply-edit-{{ $review->id }}" class="visually-hidden">Editar resposta</label>
                                                <textarea id="reply-edit-{{ $review->id }}" name="reply" class="form-control" rows="3" minlength="2" maxlength="1500" required>{{ $review->professional_reply }}</textarea>
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 mt-2">Salvar resposta</button>
                                            </form>
                                        </details>

                                        <form action="{{ route('reviews.reply.destroy', $review) }}" method="POST" onsubmit="return confirm('Excluir sua resposta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Excluir resposta</button>
                                        </form>
                                    </div>
                                @endif
                            </section>
                        @elseif($reviewableOwner)
                            <form action="{{ route('reviews.reply.store', $review) }}" method="POST" class="professional-review-reply-form">
                                @csrf
                                <label for="reply-{{ $review->id }}" class="form-label fw-semibold small">{{ $reviewingStore ? 'Responder como loja' : 'Responder como profissional' }}</label>
                                <textarea id="reply-{{ $review->id }}" name="reply" class="form-control" rows="3" minlength="2" maxlength="1500" placeholder="Agradeça ao cliente ou esclareça publicamente esta avaliação." required></textarea>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="fa-solid fa-reply me-1"></i>Publicar resposta
                                    </button>
                                </div>
                            </form>
                        @endif

                        <div class="d-flex flex-wrap gap-2">
                            @if(auth()->id() === $review->user_id)
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#{{ $reviewModalId }}Edit">Editar</button>
                                <form action="{{ route('reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Excluir sua avaliação?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Excluir</button>
                                </form>
                            @elseif($reviewableOwner || auth()->user()?->role === 'admin')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#reportReview{{ $review->id }}"><i class="fa-regular fa-flag me-1"></i>Denunciar avaliação</button>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

            @if($reviewableOwner || auth()->user()?->role === 'admin')
                <div class="modal fade" id="reportReview{{ $review->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="{{ route('reviews.report', $review) }}" method="POST" class="modal-content border-0 rounded-4">
                            @csrf
                            <div class="modal-header border-0"><h2 class="modal-title h5 fw-bold">Denunciar avaliação</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <label class="form-label fw-semibold">Motivo</label>
                                <select name="reason" class="form-select mb-3" required>
                                    @foreach(\App\Models\ReviewReport::REASONS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                </select>
                                <label class="form-label fw-semibold">Detalhes</label>
                                <textarea name="details" class="form-control" rows="4" maxlength="1000"></textarea>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-danger rounded-pill">Enviar para análise</button></div>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center bg-white border rounded-4 p-5 text-muted">Ainda não há avaliações aprovadas.</div>
        @endforelse
    </div>
</section>

@if(auth()->check() && !$reviewableOwner && !$currentUserReview)
    @include('reviews._form-modal', ['modalId' => $reviewModalId, 'reviewable' => $reviewable, 'review' => null])
@endif
@if($currentUserReview)
    @include('reviews._form-modal', ['modalId' => $reviewModalId . 'Edit', 'reviewable' => $reviewable, 'review' => $currentUserReview])
@endif

@once
@push('styles')
<style>
    .reviews-section { scroll-margin-top: 90px; }
    .reviews-average { font-size: clamp(3.2rem, 8vw, 5rem); line-height: 1; font-weight: 800; color: #0f172a; }
    .reviews-stars { color: #ffb800; white-space: nowrap; }
    .review-distribution-row { display: grid; grid-template-columns: 78px minmax(100px, 1fr) 82px; gap: .75rem; align-items: center; margin-bottom: .65rem; font-size: .82rem; }
    .review-distribution-row .progress { height: 9px; background: #edf0f4; }
    .review-card { scroll-margin-top: 100px; box-shadow: 0 5px 18px rgba(15,23,42,.04); }
    .review-card:target {
        outline: 2px solid #0d6efd;
        outline-offset: 4px;
        animation: review-target-highlight 2.4s ease-out;
    }
    @keyframes review-target-highlight {
        0%, 35% { background-color: rgba(13, 110, 253, .12); }
        100% { background-color: transparent; }
    }
    .review-author-name { color: var(--foreground); font-size: .98rem; }
    .review-author-city { color: #0d6efd; font-weight: 600; }
    .reviews-platform-count { color: #0d6efd; }
    .reviews-platform-count:hover { text-decoration: underline !important; }
    .review-avatar { width: 48px; height: 48px; flex: 0 0 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; background: #0d6efd; font-weight: 800; }
    .review-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .review-comment { white-space: pre-line; overflow-wrap: anywhere; }
    .professional-review-reply,
    .professional-review-reply-form {
        margin: 1rem 0;
        padding: 1rem;
        background: var(--muted-bg);
        border: 1px solid var(--border);
        border-left: 4px solid #0d6efd;
        border-radius: 12px;
    }
    .professional-review-reply {
        scroll-margin-top: 110px;
    }
    .professional-review-reply:target {
        outline: 2px solid #0d6efd;
        outline-offset: 4px;
        animation: review-target-highlight 2.4s ease-out;
    }
    .professional-review-reply-header {
        display: flex;
        align-items: center;
        gap: .7rem;
        margin-bottom: .7rem;
    }
    .professional-review-reply-header > div {
        display: grid;
        gap: .1rem;
    }
    .professional-review-reply-header small {
        color: var(--muted);
        font-size: .75rem;
    }
    .professional-review-reply-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        color: #fff;
        background: #0d6efd;
        border-radius: 50%;
    }
    .professional-review-reply > p {
        margin: 0;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }
    .professional-review-reply-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: .6rem;
        margin-top: .85rem;
    }
    .professional-review-reply-actions details {
        flex: 1 1 300px;
    }
    .professional-review-reply-actions summary {
        display: inline-flex;
        list-style: none;
        cursor: pointer;
    }
    .professional-review-reply-actions summary::-webkit-details-marker {
        display: none;
    }
    .professional-review-reply-form textarea {
        resize: vertical;
        background: var(--card);
        color: var(--foreground);
        border-color: var(--border);
    }
    .review-image { width: 120px; height: 90px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border); }
    .min-width-0 { min-width: 0; }
    .review-rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: .3rem; }
    .review-rating-input input { position: absolute; opacity: 0; }
    .review-rating-input label { color: #d5dbe3; font-size: 2rem; cursor: pointer; }
    .review-rating-input input:checked ~ label, .review-rating-input label:hover, .review-rating-input label:hover ~ label { color: #ffb800; }
    .review-write-button { padding-top: .55rem; padding-bottom: .55rem; font-size: .9rem; }
    @media (max-width: 575.98px) {
        .review-distribution-row { grid-template-columns: 66px minmax(70px, 1fr) 68px; gap: .4rem; font-size: .72rem; }
        .review-avatar { width: 42px; height: 42px; flex-basis: 42px; }
    }
</style>
@endpush
@endonce
