@extends('layouts.app')

@section('title', 'Reivindicar ' . $ad->title . ' - Conectado em Sergipe')

@section('content')
<section class="py-5" style="background: var(--background); min-height: 70vh;">
    <div class="container" style="max-width: 760px;">
        <a href="{{ route('provider.show', $ad->slug) }}" class="text-decoration-none d-inline-flex align-items-center gap-2 mb-4">
            <i class="fa-solid fa-arrow-left"></i>Voltar ao perfil
        </a>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3">Reivindicação gratuita</span>
                <h1 class="h3 fw-bold mb-2">Assumir o perfil de {{ $ad->title }}</h1>
                <p class="text-muted mb-4">Após confirmarmos seu vínculo, este mesmo perfil será transferido para sua conta com fotos, avaliações, endereço e histórico preservados.</p>

                @if(session('info'))
                    <div class="alert alert-info rounded-3">{{ session('info') }}</div>
                @endif

                @if($pendingClaim)
                    <div class="alert alert-warning border-0 rounded-4 p-4 mb-0">
                        <div class="d-flex gap-3">
                            <i class="fa-regular fa-clock fs-4"></i>
                            <div>
                                <strong class="d-block mb-1">Sua solicitação está em análise</strong>
                                <span>Enviada em {{ $pendingClaim->created_at->format('d/m/Y H:i') }}. A transferência só será realizada depois da confirmação dos dados.</span>
                            </div>
                        </div>
                    </div>
                @else
                    @if($errors->any())
                        <div class="alert alert-danger rounded-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('provider.claim.store', $ad) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="relationship" class="form-label fw-semibold">Qual é a sua relação com este perfil? *</label>
                            <select class="form-select rounded-3" id="relationship" name="relationship" required>
                                <option value="">Selecione</option>
                                <option value="professional" @selected(old('relationship') === 'professional')>Eu sou o profissional anunciado</option>
                                <option value="owner" @selected(old('relationship') === 'owner')>Sou proprietário(a) do negócio</option>
                                <option value="employee" @selected(old('relationship') === 'employee')>Sou funcionário(a)</option>
                                <option value="representative" @selected(old('relationship') === 'representative')>Sou representante autorizado(a)</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="verification_phone" class="form-label fw-semibold">Telefone com DDD</label>
                                <input type="text" class="form-control rounded-3" id="verification_phone" name="verification_phone" value="{{ old('verification_phone', auth()->user()->phone) }}" maxlength="30" placeholder="(79) 99999-9999">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="verification_email" class="form-label fw-semibold">E-mail para confirmação</label>
                                <input type="email" class="form-control rounded-3" id="verification_email" name="verification_email" value="{{ old('verification_email', auth()->user()->email) }}" maxlength="255" placeholder="voce@exemplo.com">
                            </div>
                        </div>
                        <div class="form-text mb-3">Informe pelo menos um contato que possa ser usado para comprovar o vínculo.</div>

                        <div class="mb-4">
                            <label for="explanation" class="form-label fw-semibold">Como podemos confirmar que o perfil é seu? <span class="text-muted fw-normal">(opcional)</span></label>
                            <textarea class="form-control rounded-3" id="explanation" name="explanation" rows="4" maxlength="1000" placeholder="Ex.: posso confirmar pelo WhatsApp exibido no perfil ou pelo Instagram comercial.">{{ old('explanation') }}</textarea>
                        </div>

                        <div class="bg-light border rounded-3 p-3 mb-4 small text-muted">
                            <i class="fa-solid fa-shield-halved text-primary me-2"></i>
                            O perfil não será transferido automaticamente. Um administrador verificará os dados antes da aprovação.
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                            Enviar solicitação
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
