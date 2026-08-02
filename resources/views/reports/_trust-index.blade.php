<section class="trust-index border rounded-4 p-3 mt-3 bg-light">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
        <div>
            <div class="fw-bold"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Índice de confiança</div>
            <small class="text-muted">{{ $trust['label'] }}</small>
        </div>
        <strong class="text-primary fs-5">{{ $trust['score'] }}%</strong>
    </div>
    <div class="progress mb-3" role="progressbar" aria-label="Índice de confiança" aria-valuenow="{{ $trust['score'] }}" aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
        <div class="progress-bar bg-primary" style="width: {{ $trust['score'] }}%"></div>
    </div>
    <ul class="list-unstyled small mb-0">
        @foreach($trust['checks'] as $check)
            <li class="mb-1"><i class="fa-solid fa-check text-success me-2"></i>{{ $check }}</li>
        @endforeach
    </ul>
</section>
