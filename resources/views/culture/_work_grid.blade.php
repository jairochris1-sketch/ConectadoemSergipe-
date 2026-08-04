@foreach($works as $work)
    <div class="col-6 col-md-4 col-lg-3 cordel-item-col" data-aos="fade-up">
        <div class="cordel-card shadow-sm h-100 d-flex flex-column rounded-4 overflow-hidden position-relative border">
            <!-- Pregador / Clipes de Cordel (Design exclusivo) -->
            <div class="cordel-pegador-clip" title="Pendurado no Varal de Cordel"></div>

            <div class="cordel-cover-wrapper position-relative text-center p-2 border-bottom d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #fdfbf7 0%, #f4ebd9 100%); min-height: 170px; max-height: 170px;">
                @if($work->cover_path)
                    <img src="{{ asset($work->cover_path) }}" alt="{{ $work->title }}" class="cordel-cover-img rounded-3 shadow-sm" style="max-height: 155px; width: auto; max-width: 100%; object-fit: contain; display: block; margin: 0 auto;">
                @else
                    <div class="cordel-cover-placeholder rounded-3 d-flex flex-column align-items-center justify-content-center text-muted w-100" style="height: 150px; background: rgba(255,255,255,0.6);">
                        <i class="fa-solid fa-book-open fs-2 mb-1 text-warning"></i>
                        <span class="fw-bold small text-dark px-2 text-center text-truncate max-w-100" style="font-size: 0.78rem;">{{ $work->title }}</span>
                        <small class="text-muted" style="font-size: 0.7rem;">{{ $work->user->name }}</small>
                    </div>
                @endif

                <span class="badge {{ $work->category_badge_class }} position-absolute top-0 start-0 m-2 shadow-sm" style="font-size: 0.65rem;">
                    {{ $work->category_label }}
                </span>

                @if($work->ad)
                    <span class="badge bg-success text-white position-absolute top-0 end-0 m-2 shadow-sm fw-bold" style="font-size: 0.65rem;">
                        <i class="fa-solid fa-bag-shopping me-1"></i> R$ {{ number_format($work->ad->price, 2, ',', '.') }}
                    </span>
                @elseif($work->embed_media_url)
                    <span class="badge bg-danger text-white position-absolute top-0 end-0 m-2 shadow-sm fw-bold" style="font-size: 0.65rem;">
                        <i class="fa-solid fa-play me-1"></i> Mídia
                    </span>
                @elseif($work->theme)
                    <span class="badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 end-0 m-2 shadow-sm" style="font-size: 0.65rem;">
                        #{{ $work->theme }}
                    </span>
                @endif
            </div>

            <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1 bg-white">
                <div>
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <div class="avatar-circle-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 22px; height: 22px; font-size: 0.65rem;">
                            {{ strtoupper(substr($work->user->name, 0, 1)) }}
                        </div>
                        <a href="{{ route('culture.author', $work->user->username ?: $work->user->id) }}" class="small text-muted text-truncate text-decoration-none hover-primary" style="font-size: 0.75rem;">{{ $work->user->name }}</a>
                    </div>

                    <h4 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.9rem;" title="{{ $work->title }}">
                        <a href="{{ route('culture.show', $work->slug) }}" class="text-dark text-decoration-none hover-primary">
                            {{ $work->title }}
                        </a>
                    </h4>

                    @if($work->summary)
                        <p class="text-muted mb-2 line-clamp-2" style="font-size: 0.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.3;">
                            {{ $work->summary }}
                        </p>
                    @endif
                </div>

                <div class="pt-2 border-top d-flex align-items-center justify-content-between mt-2">
                    <span class="text-muted" style="font-size: 0.7rem;" title="Visualizações"><i class="fa-regular fa-eye me-1"></i>{{ $work->views_count }}</span>

                    @if($work->ad)
                        <a href="{{ route('culture.show', $work->slug) }}" class="btn btn-success btn-sm rounded-pill fw-bold text-white px-2 py-0" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-cart-shopping me-1"></i> Pedir
                        </a>
                    @elseif($work->embed_media_url || $work->category === 'musica')
                        <a href="{{ route('culture.show', $work->slug) }}" class="btn btn-outline-danger btn-sm rounded-pill fw-bold px-2 py-0" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-play me-1"></i> Ver
                        </a>
                    @else
                        <a href="{{ route('culture.show', $work->slug) }}" class="btn btn-outline-warning btn-sm rounded-pill fw-bold text-dark px-2 py-0" style="font-size: 0.72rem;">
                            Ler <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
