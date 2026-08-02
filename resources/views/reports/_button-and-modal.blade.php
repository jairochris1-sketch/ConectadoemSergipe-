@php
    $isStoreReport = $reportable instanceof \App\Models\Store;
    $isServiceReport = !$isStoreReport && $reportable->module === 'services';
    $reportReasons = $isStoreReport
        ? \App\Models\Report::STORE_REASONS
        : ($isServiceReport ? \App\Models\Report::SERVICE_REASONS : \App\Models\Report::AD_REASONS);
    $reportModalId = 'reportModal' . ($isStoreReport ? 'Store' : 'Ad') . $reportable->id;
    $reportAction = $isStoreReport
        ? route('store.reports.store', $reportable)
        : route('reports.store', $reportable);
    $reportTargetLabel = $isStoreReport
        ? 'esta loja'
        : ($isServiceReport ? 'este serviço' : 'este anúncio');
@endphp

<button type="button" class="btn btn-link text-danger-emphasis text-decoration-none report-trigger px-1" data-bs-toggle="modal" data-bs-target="#{{ $reportModalId }}">
    <i class="fa-regular fa-flag me-1"></i>
    Reportar {{ $reportTargetLabel }}
</button>

@push('report-modals')
<div class="modal fade report-modal" id="{{ $reportModalId }}" tabindex="-1" aria-labelledby="{{ $reportModalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form action="{{ $reportAction }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow">
            @csrf
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h2 class="modal-title h4 fw-bold mb-1" id="{{ $reportModalId }}Label">
                        <i class="fa-solid fa-flag text-danger me-2"></i>
                        Reportar {{ $reportTargetLabel }}
                    </h2>
                    <p class="text-muted small mb-0">Encontrou algum problema? Informe-nos. A análise normalmente acontece em até 24 horas.</p>
                </div>
                <button type="button" class="btn-close align-self-start" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body px-4">
                    <fieldset class="mb-4">
                        <legend class="h6 fw-bold">O que aconteceu?</legend>
                        <p class="text-muted small">Escolha uma opção:</p>
                        <div class="row g-2">
                            @foreach($reportReasons as $value => $label)
                                <div class="col-12 col-md-6">
                                    <label class="report-option">
                                        <input type="radio" name="reason" value="{{ $value }}" required @checked(old('reason') === $value)>
                                        <span>{{ $label }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset class="mb-4">
                        <legend class="h6 fw-bold">Nível de gravidade</legend>
                        <p class="text-muted small">Isso ajuda a priorizar as denúncias.</p>
                        @foreach([
                            'error' => 'Apenas um erro',
                            'misleading' => ($isServiceReport || $isStoreReport) ? 'Pode enganar clientes' : 'Pode enganar compradores',
                            'critical' => 'Muito grave',
                        ] as $value => $label)
                            <label class="report-severity d-flex align-items-center gap-2 mb-2">
                                <input type="radio" name="severity" value="{{ $value }}" required @checked(old('severity') === $value)>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </fieldset>

                    <div class="mb-4">
                        <label for="report-details-{{ $reportable->id }}" class="form-label fw-bold">Conte mais detalhes</label>
                        <textarea id="report-details-{{ $reportable->id }}" name="details" class="form-control rounded-3" rows="5" maxlength="1000" placeholder="Descreva o problema...">{{ old('details') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Não inclua senhas ou dados bancários.</small>
                            <small class="text-muted"><span class="report-character-count">0</span>/1000</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="report-evidence-{{ $reportable->id }}" class="form-label fw-bold">Anexar provas</label>
                        <label class="report-upload-zone" for="report-evidence-{{ $reportable->id }}">
                            <i class="fa-solid fa-camera fs-3 text-primary mb-2"></i>
                            <strong>Arraste imagens aqui</strong>
                            <span>ou selecione arquivos</span>
                            <small>Até 3 imagens · JPG, PNG ou WEBP · 5 MB cada</small>
                        </label>
                        <input id="report-evidence-{{ $reportable->id }}" type="file" name="evidence[]" class="visually-hidden report-evidence-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple>
                        <div class="report-file-summary text-muted small mt-2" aria-live="polite"></div>
                    </div>

                    <fieldset class="mb-4">
                        <legend class="h6 fw-bold">Você deseja receber o resultado da análise?</legend>
                        @auth
                            <div class="d-flex gap-4">
                                <label><input type="radio" name="wants_notification" value="1" @checked(old('wants_notification') === '1')> Sim</label>
                                <label><input type="radio" name="wants_notification" value="0" @checked(old('wants_notification', '0') === '0')> Não</label>
                            </div>
                            <small class="text-muted d-block mt-2">A atualização aparecerá no seu painel.</small>
                        @else
                            <input type="hidden" name="wants_notification" value="0">
                            <p class="text-muted small mb-0">Para receber o resultado, entre na sua conta antes de enviar. A denúncia anônima continua disponível.</p>
                        @endauth
                    </fieldset>

                    <div class="form-check rounded-3 bg-light border p-3 ps-5">
                        <input class="form-check-input" type="checkbox" name="truth_confirmation" value="1" id="truth-confirmation-{{ $reportable->id }}" required>
                        <label class="form-check-label fw-semibold" for="truth-confirmation-{{ $reportable->id }}">
                            Confirmo que esta denúncia é verdadeira.
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fa-solid fa-paper-plane me-2"></i>Enviar denúncia
                    </button>
                </div>
        </form>
    </div>
</div>
@endpush

@once
    @push('styles')
        <style>
            .report-trigger { font-size: .82rem; font-weight: 600; opacity: .78; }
            .report-trigger:hover, .report-trigger:focus { opacity: 1; }
            .report-modal { color: var(--foreground); }
            .report-modal .modal-content { color: var(--foreground); background: var(--card); }
            .report-option { display: flex; gap: .65rem; align-items: center; padding: .75rem; color: var(--foreground); background: var(--card); border: 1px solid var(--border); border-radius: .75rem; cursor: pointer; height: 100%; }
            .report-option:has(input:checked) { color: var(--foreground); border-color: #dc3545; background: color-mix(in srgb, #dc3545 12%, var(--card)); }
            .report-option input, .report-severity input { accent-color: #dc3545; }
            .report-upload-zone { min-height: 150px; color: var(--foreground); border: 2px dashed var(--border); border-radius: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .25rem; cursor: pointer; text-align: center; padding: 1rem; background: var(--muted-bg); }
            .report-upload-zone span, .report-upload-zone small { color: var(--muted-foreground); }
            .report-upload-zone:hover { border-color: #0d6efd; background: color-mix(in srgb, #0d6efd 9%, var(--card)); }
            [data-theme="dark"] .report-modal .btn-close { filter: invert(1) grayscale(100%) brightness(180%); }
            .report-modal .modal-dialog { height: calc(100% - 1.5rem); margin-top: .75rem; margin-bottom: .75rem; }
            .report-modal .modal-content { max-height: calc(100dvh - 1.5rem); overflow: hidden; }
            .report-modal .modal-body { min-height: 0; overflow-y: auto; overscroll-behavior: contain; scrollbar-gutter: stable; }
            .report-modal .modal-header, .report-modal .modal-footer { flex: 0 0 auto; }
            .modal-open .theme-toggle-container { visibility: hidden; }
            @media (max-width: 575.98px) {
                .report-modal .modal-dialog { width: auto; height: calc(100% - 1rem); margin: .5rem; }
                .report-modal .modal-content { max-height: calc(100dvh - 1rem); border-radius: 1rem !important; }
                .report-modal .modal-header, .report-modal .modal-body, .report-modal .modal-footer { padding-right: 1rem !important; padding-left: 1rem !important; }
                .report-modal .modal-footer { display: grid; grid-template-columns: 1fr 1fr; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.querySelectorAll('[id^="reportModal"]').forEach((modal) => {
                const textarea = modal.querySelector('textarea[name="details"]');
                const counter = modal.querySelector('.report-character-count');
                const input = modal.querySelector('.report-evidence-input');
                const summary = modal.querySelector('.report-file-summary');

                const updateCounter = () => counter.textContent = textarea.value.length;
                textarea.addEventListener('input', updateCounter);
                updateCounter();

                input.addEventListener('change', () => {
                    if (input.files.length > 3) {
                        input.value = '';
                        summary.textContent = 'Selecione no máximo 3 imagens.';
                        summary.classList.add('text-danger');
                        return;
                    }
                    summary.classList.remove('text-danger');
                    summary.textContent = input.files.length ? `${input.files.length} arquivo(s) selecionado(s).` : '';
                });
            });
            @if($errors->any())
                bootstrap.Modal.getOrCreateInstance(document.getElementById(@json($reportModalId))).show();
            @endif
        </script>
    @endpush
@endonce
