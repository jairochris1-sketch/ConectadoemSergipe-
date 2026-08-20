@extends('layouts.app')

@php
    $content = match($event) {
        'cancelled' => ['icon' => 'fa-calendar-xmark', 'eyebrow' => 'Cancelamento registrado', 'title' => 'Avise o profissional', 'text' => 'O horário já foi cancelado no site. Agora vamos abrir o WhatsApp com a mensagem pronta.'],
        'rescheduled' => ['icon' => 'fa-calendar-day', 'eyebrow' => 'Novo horário solicitado', 'title' => 'Falta avisar o profissional', 'text' => 'Sua remarcação está registrada e aguarda confirmação. A mensagem já está pronta para envio.'],
        default => ['icon' => 'fa-calendar-check', 'eyebrow' => 'Solicitação registrada', 'title' => 'Seu horário está quase confirmado', 'text' => 'Envie o resumo pelo WhatsApp para o profissional confirmar mais rápido.'],
    };
    $status = match($event) {'cancelled' => 'Cancelado', 'rescheduled' => 'Remarcação pendente', default => 'Aguardando confirmação'};
@endphp

@section('title', $content['title'].' - Conectado em Sergipe')

@section('content')
<main class="booking-whatsapp-page">
    <section class="booking-whatsapp-card">
        <div class="booking-whatsapp-mark"><i class="fa-solid {{ $content['icon'] }}"></i></div>
        <p class="booking-whatsapp-eyebrow">{{ $content['eyebrow'] }}</p>
        <h1>{{ $content['title'] }}</h1>
        <p class="booking-whatsapp-intro">{{ $content['text'] }}</p>

        <div class="booking-whatsapp-summary">
            <div><span>Procedimento</span><strong>{{ $appointment->procedure->name }}</strong></div>
            <div><span>Profissional</span><strong>{{ $appointment->staff->name }}</strong></div>
            <div><span>Valor</span><strong class="is-price">{{ $appointment->service_client_subscription_id ? 'Incluído no plano' : 'R$ '.number_format($appointment->service_price, 2, ',', '.') }}</strong></div>
            <div><span>Data</span><strong>{{ $appointment->starts_at->format('d/m/Y') }}</strong></div>
            <div><span>Horário</span><strong>{{ $appointment->starts_at->format('H:i') }}</strong></div>
            <div><span>Situação</span><strong>{{ $status }}</strong></div>
        </div>

        @if($whatsappUrl)
            <div class="booking-whatsapp-countdown" aria-live="polite">
                <div class="booking-whatsapp-countdown-ring"><strong data-whatsapp-seconds>10</strong><small>seg</small></div>
                <p>O WhatsApp abrirá automaticamente.<br><button type="button" data-whatsapp-stop>Cancelar abertura automática</button></p>
            </div>
            <a class="booking-whatsapp-button" href="{{ $whatsappUrl }}" rel="noopener noreferrer" data-whatsapp-link><i class="fa-brands fa-whatsapp"></i><span>{{ $event === 'cancelled' ? 'Enviar cancelamento pelo WhatsApp' : ($event === 'rescheduled' ? 'Enviar remarcação pelo WhatsApp' : 'Enviar agendamento pelo WhatsApp') }}</span></a>
            <small class="booking-whatsapp-note"><i class="fa-solid fa-shield-heart"></i> A mensagem será aberta pronta para você revisar e tocar em enviar.</small>
        @else
            <div class="booking-whatsapp-unavailable"><i class="fa-solid fa-circle-info"></i><div><strong>WhatsApp não informado</strong><span>O profissional ainda não cadastrou um número válido. O registro no site continua salvo.</span></div></div>
        @endif

        <div class="booking-whatsapp-links"><a href="{{ route('service-booking.book', $ad) }}">Ver meus agendamentos</a><a href="{{ route('provider.show', $ad->slug) }}">Voltar ao perfil</a></div>
    </section>
</main>
@endsection

@push('styles')
<style>
.booking-whatsapp-page{display:grid;place-items:start center;padding:24px 14px 38px}.booking-whatsapp-card{width:min(100%,470px);padding:24px;border:1px solid var(--border);border-radius:18px;background:var(--card);box-shadow:0 8px 24px rgba(24,56,94,.08);color:var(--foreground);text-align:center}.booking-whatsapp-mark{display:grid;place-items:center;width:50px;height:50px;margin:0 auto 12px;border-radius:14px;background:#eaf3ff;color:#0d6efd;font-size:1.35rem}.booking-whatsapp-eyebrow{margin:0;color:#1767c5;font-size:.66rem;font-weight:900;letter-spacing:.06em;text-transform:uppercase}.booking-whatsapp-card h1{margin:5px 0;font-size:clamp(1.3rem,4vw,1.65rem);font-weight:900}.booking-whatsapp-intro{max-width:390px;margin:0 auto 16px;color:var(--muted);font-size:.8rem}.booking-whatsapp-summary{padding:4px 14px;border:1px solid var(--border);border-radius:13px;background:var(--background);text-align:left}.booking-whatsapp-summary div{display:grid;grid-template-columns:100px minmax(0,1fr);gap:10px;padding:8px 0;border-bottom:1px solid var(--border)}.booking-whatsapp-summary div:last-child{border:0}.booking-whatsapp-summary span{color:var(--muted);font-size:.7rem}.booking-whatsapp-summary strong{text-align:right;font-size:.76rem}.booking-whatsapp-summary .is-price{color:#118c49}.booking-whatsapp-countdown{display:flex;align-items:center;justify-content:center;gap:10px;margin:15px 0 10px;color:var(--muted);font-size:.7rem}.booking-whatsapp-countdown-ring{display:grid;place-items:center;width:45px;height:45px;border:3px solid #cdeed9;border-top-color:#20b85a;border-radius:50%;line-height:1}.booking-whatsapp-countdown-ring strong{font-size:.95rem;color:#118c49}.booking-whatsapp-countdown-ring small{font-size:.48rem}.booking-whatsapp-countdown p{margin:0;text-align:left}.booking-whatsapp-countdown button{padding:0;border:0;background:transparent;color:#1767c5;font-size:.66rem;font-weight:800}.booking-whatsapp-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:auto;max-width:100%;min-height:44px;padding:9px 16px;border-radius:10px;background:#20bd5b;color:#fff;text-decoration:none;font-size:.78rem;font-weight:900}.booking-whatsapp-button:hover{background:#18a94e;color:#fff}.booking-whatsapp-button i{font-size:1.05rem}.booking-whatsapp-note{display:block;max-width:350px;margin:8px auto 0;color:var(--muted);font-size:.63rem}.booking-whatsapp-unavailable{display:flex;align-items:flex-start;gap:9px;margin-top:15px;padding:12px;border-radius:10px;background:#fff4d9;color:#745000;text-align:left}.booking-whatsapp-unavailable span{display:block;font-size:.68rem}.booking-whatsapp-links{display:flex;justify-content:center;gap:16px;margin-top:16px}.booking-whatsapp-links a{color:#1767c5;font-size:.7rem;font-weight:800;text-decoration:none}html[data-theme="dark"] .booking-whatsapp-mark{background:#173a60;color:#8dc0ff}@media(max-width:575px){.booking-whatsapp-page{padding:16px 10px 28px}.booking-whatsapp-card{padding:19px 14px;border-radius:15px}.booking-whatsapp-summary{padding:3px 11px}.booking-whatsapp-summary div{grid-template-columns:88px minmax(0,1fr);padding:7px 0}.booking-whatsapp-button{padding:9px 13px;font-size:.73rem}.booking-whatsapp-links{gap:12px}}
</style>
@endpush

@if($whatsappUrl)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const link=document.querySelector('[data-whatsapp-link]');
    const secondsElement=document.querySelector('[data-whatsapp-seconds]');
    const stopButton=document.querySelector('[data-whatsapp-stop]');
    if(!link||!secondsElement||!stopButton)return;
    let seconds=10;
    let stopped=false;
    const timer=window.setInterval(function(){
        if(stopped)return;
        seconds-=1;
        secondsElement.textContent=String(seconds);
        if(seconds<=0){window.clearInterval(timer);window.location.assign(link.href);}
    },1000);
    stopButton.addEventListener('click',function(){stopped=true;window.clearInterval(timer);secondsElement.textContent='—';stopButton.textContent='Abertura automática cancelada';});
    link.addEventListener('click',function(){window.clearInterval(timer);});
});
</script>
@endpush
@endif
