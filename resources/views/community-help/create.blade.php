@extends('layouts.app')
@section('title', ($helpRequest ? 'Corrigir pedido' : 'Preciso agora').' - Comunidade')
@push('styles')<link rel="stylesheet" href="{{ asset('css/community-help.css') }}?v=1">@endpush
@section('content')
<main class="help-page">
    <div class="help-shell">
        <nav class="help-topnav"><a class="help-back" href="{{ route('community-help.index') }}"><i class="fa-solid fa-arrow-left"></i> Pedidos locais</a></nav>
        @if($errors->any())<div class="alert alert-danger"><strong>Revise as informações:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="help-form-shell">
            <section class="help-panel help-form">
                <div class="help-panel-body">
                    <h1>{{ $helpRequest ? 'Corrija e reenvie seu pedido' : 'Conte o que você precisa' }}</h1>
                    <p class="help-lead">Informações claras ajudam a comunidade a responder mais rápido.</p>
                    <form action="{{ $helpRequest ? route('community-help.update', $helpRequest) : route('community-help.store') }}" method="POST">@csrf @if($helpRequest)@method('PUT')@endif
                        <div class="help-form-grid">
                            <div class="help-form-field"><label for="category">Tipo de necessidade</label><select class="form-select" id="category" name="category" required><option value="">Selecione</option>@foreach($categories as $value => $label)<option value="{{ $value }}" @selected(old('category', $helpRequest?->category) === $value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="help-form-field"><label for="urgency">Quando precisa?</label><select class="form-select" id="urgency" name="urgency" required>@foreach($urgencies as $value => $label)<option value="{{ $value }}" @selected(old('urgency', $helpRequest?->urgency ?? 'normal') === $value)>{{ $label }}</option>@endforeach</select></div>
                            <div class="help-form-field is-wide"><label for="title">Título direto</label><input class="form-control" id="title" name="title" value="{{ old('title', $helpRequest?->title) }}" minlength="8" maxlength="120" required placeholder="Ex.: Preciso de eletricista hoje na Farolândia"><small class="help-form-help">Não coloque telefone, endereço completo, CPF ou outros dados pessoais.</small></div>
                            <div class="help-form-field is-wide"><label for="description">Explique a necessidade</label><textarea class="form-control" id="description" name="description" minlength="20" maxlength="1500" required placeholder="Diga o que aconteceu, que tipo de ajuda procura e outras informações úteis.">{{ old('description', $helpRequest?->description) }}</textarea></div>
                            <div class="help-form-field"><label for="city">Cidade</label><select class="form-select" id="city" name="city" required><option value="">Selecione</option>@foreach($cities as $cityName)<option value="{{ $cityName }}" @selected(old('city', $suggestedCity) === $cityName)>{{ $cityName }}</option>@endforeach</select></div>
                            <div class="help-form-field"><label for="neighborhood">Bairro ou povoado</label><input class="form-control" id="neighborhood" name="neighborhood" value="{{ old('neighborhood', $helpRequest?->neighborhood) }}" minlength="2" maxlength="120" required placeholder="Somente a região aproximada"></div>
                            <div class="help-form-field"><label for="duration_days">Por quanto tempo?</label><select class="form-select" id="duration_days" name="duration_days" required><option value="2" @selected(old('duration_days') == 2)>2 dias</option><option value="7" @selected(old('duration_days', 7) == 7)>7 dias</option><option value="14" @selected(old('duration_days') == 14)>14 dias</option><option value="30" @selected(old('duration_days') == 30)>30 dias</option></select></div>
                            <div class="help-form-field is-wide"><div class="form-check"><input class="form-check-input" id="safety_acknowledged" name="safety_acknowledged" value="1" type="checkbox" required @checked(old('safety_acknowledged'))><label class="form-check-label" for="safety_acknowledged">Confirmo que não informei endereço completo, documentos, dados bancários ou informações médicas sensíveis.</label></div></div>
                        </div>
                        <div class="help-submit-row"><a class="help-secondary-action" href="{{ $helpRequest ? route('community-help.show', $helpRequest) : route('community-help.index') }}">Cancelar</a><button class="help-submit" type="submit"><i class="fa-solid fa-shield-halved"></i> {{ $helpRequest ? 'Reenviar para análise' : 'Enviar para análise' }}</button></div>
                    </form>
                </div>
            </section>
            <aside class="help-safety"><h2><i class="fa-solid fa-shield-heart"></i> Segurança em primeiro lugar</h2><ul><li>Combine detalhes pelo chat da plataforma.</li><li>Não envie dinheiro antecipadamente a desconhecidos.</li><li>Em risco imediato, procure os serviços públicos de emergência.</li><li>A equipe pode pedir ajustes antes da publicação.</li></ul></aside>
        </div>
    </div>
</main>
@endsection
