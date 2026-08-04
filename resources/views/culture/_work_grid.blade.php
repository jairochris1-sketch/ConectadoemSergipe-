@foreach($works as $work)
    <div class="col-12 col-md-6 col-lg-4 cordel-item-col" data-aos="fade-up">
        <div class="cordel-card shadow-sm h-100 d-flex flex-column rounded-4 overflow-hidden position-relative border">
            <!-- Pregador / Clipes de Cordel (Design exclusivo) -->
            <div class="cordel-pegador-clip" title="Pendurado no Varal de Cordel"></div>

            <div class="cordel-cover-wrapper position-relative text-center bg-light p-3 border-bottom">
                @if($work->cover_path)
                    <img src="{{ asset($work->cover_path) }}" alt="{{ $work->title }}" class="cordel-cover-img rounded-3 shadow-sm object-fit-cover" style="height: 220px; width: 100%;">
                @else
                    <div class="cordel-cover-placeholder rounded-3 d-flex flex-column align-items-center justify-content-center text-muted" style="height: 220px; background: linear-gradient(135deg, #fdfbf7 0%, #e6d5b8 100%);">
                        <i class="fa-solid fa-book-open fs-1 mb-2 text-warning"></i>
                        <span class="fw-bold small text-dark px-2 text-center">{{ $work->title }}</span>
                        <small class="text-muted mt-1">{{ $work->user->name }}</small>
                    </div>
                @endif

                <span class="badge {{ $work->category_badge_class }} position-absolute top-0 start-0 m-3 shadow-sm">
                    {{ $work->category_label }}
                </span>

                @if($work->theme)
                    <span class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 end-0 m-3 shadow-sm">
                        <i class="fa-solid fa-hashtag me-1"></i>{{ $work->theme }}
                    </span>
                @endif
            </div>

            <div class="card-body p-4 d-flex flex-column justify-content-between flex-grow-1 bg-white">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="avatar-circle-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                            {{ strtoupper(substr($work->user->name, 0, 1)) }}
                        </div>
                        <span class="small fw-semibold text-dark">{{ $work->user->name }}</span>
                    </div>

                    <h3 class="h5 fw-bold text-dark mb-2 text-truncate" title="{{ $work->title }}">
                        <a href="{{ route('culture.show', $work->slug) }}" class="text-dark text-decoration-none hover-primary">
                            {{ $work->title }}
                        </a>
                    </h3>

                    @if($work->summary)
                        <p class="text-muted small mb-3 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $work->summary }}
                        </p>
                    @endif
                </div>

                <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-3">
                    <div class="d-flex align-items-center gap-3 text-muted small">
                        <span title="Visualizações"><i class="fa-regular fa-eye me-1"></i>{{ $work->views_count }}</span>
                        @if($work->embed_media_url)
                            <span title="Mídia Incorporada" class="text-success"><i class="fa-solid fa-play me-1"></i>Áudio/Vídeo</span>
                        @endif
                    </div>

                    <a href="{{ route('culture.show', $work->slug) }}" class="btn btn-outline-warning btn-sm rounded-pill fw-bold text-dark px-3">
                        Ler Obra <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endforeach
