@extends('layouts.app')

@section('title', 'Denúncia registrada - Conectado em Sergipe')

@section('content')
<div class="container py-5">
    <div class="mx-auto bg-white border rounded-4 shadow-sm p-4 p-md-5 text-center" style="max-width: 680px;">
        <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 74px; height: 74px;">
            <i class="fa-solid fa-check fs-2"></i>
        </div>
        <h1 class="h2 fw-bold">Obrigado!</h1>
        <p class="lead text-muted">Sua denúncia foi registrada.</p>
        <div class="bg-light rounded-4 p-4 my-4">
            <div class="text-muted small">Número da denúncia</div>
            <div class="display-6 fw-bold text-primary">{{ $report->reference }}</div>
        </div>
        <p>A equipe do Conectado em Sergipe fará a análise.</p>
        @if($report->wants_notification)
            <p class="small text-muted">Você receberá uma atualização no painel quando a análise for concluída.</p>
        @endif
        @if($report->subject_type === 'store' && $report->store)
            <a href="{{ route('store.show', $report->store->slug) }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar à loja
            </a>
        @elseif($report->ad)
            <a href="{{ $report->subject_type === 'service' ? route('provider.show', $report->ad->slug) : route('ad.show', $report->ad->slug) }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar {{ $report->subject_type === 'service' ? 'ao serviço' : 'ao anúncio' }}
            </a>
        @else
            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 fw-bold">Voltar ao início</a>
        @endif
    </div>
</div>
@endsection
