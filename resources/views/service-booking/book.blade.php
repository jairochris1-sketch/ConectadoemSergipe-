@extends('layouts.app')

@section('title', 'Agendar com '.$ad->title.' - Conectado em Sergipe')

@section('content')
<div class="container py-4 py-md-5 booking-page">
    <div class="booking-public-shell">
        <header class="booking-hero">
            <a href="{{ route('provider.show', $ad->slug) }}" class="booking-back"><i class="fa-solid fa-arrow-left"></i> Voltar ao perfil</a>
            <span>Agendamento online</span>
            <h1>Escolha seu atendimento com {{ $ad->title }}</h1>
            <p>Selecione o procedimento, o profissional e um dos horários realmente disponíveis.</p>
        </header>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @if($customerSubscriptions->isNotEmpty())
            <section class="booking-subscriptions booking-card">
                <div class="booking-subscription-heading"><div><span>Suas assinaturas</span><h2>Planos com {{ $ad->title }}</h2></div></div>
                <div class="booking-subscription-grid">
                    @foreach($customerSubscriptions as $subscription)
                        @php
                            $latestPayment = $subscription->payments->first();
                            $invoiceUrl = $latestPayment?->invoice_url;
                            $invoiceHost = $invoiceUrl ? strtolower((string) parse_url($invoiceUrl, PHP_URL_HOST)) : '';
                            $safeInvoice = $invoiceUrl && str_starts_with($invoiceUrl, 'https://') && ($invoiceHost === 'asaas.com' || str_ends_with($invoiceHost, '.asaas.com')) ? $invoiceUrl : null;
                            $statusLabel = ['active' => 'Ativo', 'pending_payment' => 'Aguardando pagamento', 'past_due' => 'Pagamento atrasado', 'cancelled' => 'Cancelado, sem novas cobranças'][$subscription->status] ?? ucfirst($subscription->status);
                        @endphp
                        <article>
                            <div><strong>{{ $subscription->plan->name }}</strong><span>{{ $statusLabel }}</span></div>
                            @if($subscription->paid_through)<small>Benefícios liberados até {{ $subscription->paid_through->format('d/m/Y') }}</small>@endif
                            <div class="booking-subscription-actions">
                                @if($safeInvoice && $subscription->status !== 'active')<a href="{{ $safeInvoice }}" rel="noopener noreferrer">Pagar no Asaas</a>@endif
                                @if($subscription->status !== 'cancelled')<form method="POST" action="{{ route('service-subscriptions.cancel', $subscription) }}" onsubmit="return confirm('Cancelar este plano e impedir novas cobranças?')">@csrf @method('DELETE')<button>Cancelar plano</button></form>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($subscriptionPlans->isNotEmpty())
            <section class="booking-subscriptions booking-card">
                <div class="booking-subscription-heading"><div><span>Economize todo mês</span><h2>Planos mensais</h2><p>O pagamento é processado diretamente na conta Asaas deste profissional.</p></div></div>
                <div class="booking-subscription-grid">
                    @foreach($subscriptionPlans as $plan)
                        <article>
                            <div class="booking-plan-price"><strong>{{ $plan->name }}</strong><b>R$ {{ number_format($plan->price, 2, ',', '.') }}<small>/mês</small></b></div>
                            @if($plan->description)<p>{{ $plan->description }}</p>@endif
                            <ul>
                                @foreach($plan->procedures as $includedProcedure)
                                    <li><i class="fa-solid fa-check"></i> {{ $includedProcedure->name }} · {{ $includedProcedure->pivot->included_uses ? $includedProcedure->pivot->included_uses.' uso(s)' : 'uso ilimitado' }}</li>
                                @endforeach
                            </ul>
                            @if($plan->terms)<details><summary>Regras do plano</summary><p>{{ $plan->terms }}</p></details>@endif
                            @auth
                                <form method="POST" action="{{ route('service-subscriptions.store', [$ad, $plan]) }}" class="booking-subscribe-form">
                                    @csrf
                                    <label>Forma de pagamento
                                        <select name="billing_type" required><option value="PIX">PIX</option><option value="CREDIT_CARD">Cartão de crédito</option><option value="BOLETO">Boleto</option></select>
                                    </label>
                                    <label>CPF ou CNPJ
                                        <input name="cpf_cnpj" value="{{ old('cpf_cnpj', auth()->user()->cpf_cnpj) }}" inputmode="numeric" required maxlength="20" autocomplete="off">
                                    </label>
                                    <label class="booking-subscribe-consent"><input type="checkbox" name="accept_terms" value="1" required><span>Li e aceito as regras, o valor e a renovação mensal deste plano.</span></label>
                                    <button>Assinar plano</button>
                                    <small>Você será enviado à página segura do Asaas. O benefício só é ativado após a confirmação do pagamento.</small>
                                </form>
                            @else
                                <a class="booking-login-plan" href="{{ route('login') }}">Entrar para assinar</a>
                            @endauth
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="booking-card">
            <form method="GET" action="{{ route('service-booking.book', $ad) }}" class="booking-selection-form">
                <label>Procedimento
                    <select name="procedure" required onchange="this.form.submit()">
                        <option value="">Escolha...</option>
                        @foreach($ad->serviceProcedures as $item)
                            <option value="{{ $item->id }}" @selected($procedure?->id === $item->id)>{{ $item->name }} · R$ {{ number_format($item->price, 2, ',', '.') }} · {{ $item->duration_minutes }} min</option>
                        @endforeach
                    </select>
                </label>
                <label>Profissional
                    <select name="staff" required onchange="this.form.submit()">
                        <option value="">Escolha...</option>
                        @foreach($ad->serviceStaff as $person)
                            @if(!$procedure || $person->procedures->contains($procedure))
                                <option value="{{ $person->id }}" @selected($staff?->id === $person->id)>{{ $person->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </label>
                <label>Data
                    <input type="date" name="date" value="{{ $date }}" min="{{ now('America/Fortaleza')->toDateString() }}" max="{{ now('America/Fortaleza')->addDays(60)->toDateString() }}" onchange="this.form.submit()">
                </label>
                <noscript><button class="btn btn-primary">Consultar horários</button></noscript>
            </form>

            @if($procedure && $staff)
                <form method="POST" action="{{ route('service-booking.store', $ad) }}" class="booking-confirm-form">
                    @csrf
                    <input type="hidden" name="procedure_id" value="{{ $procedure->id }}">
                    <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                    <h2>Horários disponíveis em {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h2>
                    @if($slots)
                        <div class="booking-slots">
                            @foreach($slots as $slot)
                                <label><input type="radio" name="starts_at" value="{{ $date }} {{ $slot }}" required><span>{{ $slot }}</span></label>
                            @endforeach
                        </div>
                        <div class="booking-customer-fields">
                            <label>Telefone para contato<input name="phone" value="{{ old('phone', auth()->user()?->phone) }}" maxlength="20"></label>
                            <label>Observação (opcional)<textarea name="notes" maxlength="1000" rows="3">{{ old('notes') }}</textarea></label>
                        </div>
                        @if($coveringSubscription)
                            <div class="booking-plan-covered"><i class="fa-solid fa-circle-check"></i><div><strong>Incluído no seu plano {{ $coveringSubscription->plan->name }}</strong><span>Uma utilização será reservada agora e devolvida se o atendimento for cancelado.</span></div></div>
                        @endif
                        <button class="booking-primary-button"><i class="fa-regular fa-calendar-check"></i> {{ $coveringSubscription ? 'Agendar usando meu plano' : 'Solicitar agendamento' }}</button>
                        <small>O horário fica pendente até a confirmação do profissional.</small>
                    @else
                        <div class="booking-empty">Nenhum horário disponível para essa combinação. Escolha outra data ou profissional.</div>
                    @endif
                </form>
            @endif
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
.booking-page{color:var(--foreground)}.booking-public-shell{width:min(100%,900px);margin:auto}.booking-hero{padding:28px;border-radius:22px 22px 0 0;background:linear-gradient(135deg,#0b244d,#1167c9);color:#fff}.booking-hero>span{display:block;margin-top:18px;color:#9bc8ff;font-size:.75rem;font-weight:800;text-transform:uppercase}.booking-hero h1{margin:6px 0;font-size:clamp(1.5rem,3vw,2.2rem);font-weight:900}.booking-hero p{margin:0;color:#dae8fb}.booking-back{color:#fff;text-decoration:none;font-weight:800}.booking-card{padding:24px;border:1px solid var(--border);border-top:0;border-radius:0 0 22px 22px;background:var(--card);box-shadow:0 18px 45px rgba(13,45,90,.12)}.booking-selection-form{display:grid;grid-template-columns:1.4fr 1fr .8fr;gap:14px}.booking-selection-form label,.booking-customer-fields label{display:grid;gap:6px;font-size:.78rem;font-weight:800}.booking-selection-form select,.booking-selection-form input,.booking-customer-fields input,.booking-customer-fields textarea{width:100%;padding:11px;border:1px solid var(--border);border-radius:10px;background:var(--background);color:var(--foreground)}.booking-confirm-form{margin-top:24px;padding-top:22px;border-top:1px solid var(--border)}.booking-confirm-form h2{font-size:1.05rem;font-weight:900}.booking-slots{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0}.booking-slots input{position:absolute;opacity:0}.booking-slots span{display:block;padding:9px 13px;border:1px solid #8eb5e8;border-radius:9px;color:#1556a2;cursor:pointer}.booking-slots input:checked+span{background:#0d6efd;color:#fff;border-color:#0d6efd}.booking-customer-fields{display:grid;grid-template-columns:1fr 1.5fr;gap:14px;margin:18px 0}.booking-primary-button{min-height:45px;padding:10px 18px;border:0;border-radius:10px;background:#0d6efd;color:#fff;font-weight:900}.booking-confirm-form>small{display:block;margin-top:8px;color:var(--muted)}.booking-empty{padding:18px;border-radius:12px;background:var(--background);color:var(--muted)}@media(max-width:767px){.booking-selection-form,.booking-customer-fields{grid-template-columns:1fr}.booking-hero,.booking-card{padding:18px}.booking-primary-button{width:100%}}
.booking-subscriptions{margin-top:18px;border-top:1px solid var(--border);border-radius:20px}.booking-subscription-heading span{color:#1767c5;font-size:.72rem;font-weight:900;text-transform:uppercase}.booking-subscription-heading h2{margin:4px 0;font-size:1.25rem;font-weight:900}.booking-subscription-heading p{margin:0;color:var(--muted);font-size:.78rem}.booking-subscription-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:16px}.booking-subscription-grid>article{padding:17px;border:1px solid var(--border);border-radius:14px;background:var(--background)}.booking-plan-price{display:flex;align-items:start;justify-content:space-between;gap:12px}.booking-plan-price strong{font-size:1rem}.booking-plan-price b{color:#1264c0;white-space:nowrap}.booking-plan-price b small{font-size:.65rem}.booking-subscription-grid p,.booking-subscription-grid details{color:var(--muted);font-size:.75rem}.booking-subscription-grid ul{display:grid;gap:6px;margin:12px 0;padding:0;list-style:none;font-size:.76rem}.booking-subscription-grid li i{color:#16834c}.booking-subscribe-form{display:grid;gap:9px;margin-top:14px;padding-top:14px;border-top:1px solid var(--border)}.booking-subscribe-form label{display:grid;gap:5px;font-size:.72rem;font-weight:800}.booking-subscribe-form input,.booking-subscribe-form select{width:100%;padding:9px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--foreground)}.booking-subscribe-form button,.booking-login-plan,.booking-subscription-actions a{display:block;padding:10px;border:0;border-radius:9px;background:#0d6efd;color:#fff;text-align:center;text-decoration:none;font-weight:900}.booking-subscribe-form>small{color:var(--muted);font-size:.66rem}.booking-subscription-grid article>div:first-child span{display:block;color:#1767c5;font-size:.7rem;font-weight:800}.booking-subscription-grid article>small{color:var(--muted)}.booking-subscription-actions{display:flex;align-items:center;gap:8px;margin-top:12px}.booking-subscription-actions form button{padding:8px;border:1px solid #c84a55;border-radius:8px;background:transparent;color:#a92e3b;font-size:.72rem;font-weight:800}.booking-subscription-actions a{padding:8px;font-size:.72rem}@media(max-width:767px){.booking-subscription-grid{grid-template-columns:1fr}.booking-subscription-actions{align-items:stretch;flex-direction:column}.booking-subscription-actions form,.booking-subscription-actions button{width:100%}}
.booking-subscribe-form .booking-subscribe-consent{display:flex;align-items:flex-start;gap:8px}.booking-subscribe-form .booking-subscribe-consent input{width:auto;margin-top:2px}.booking-plan-covered{display:flex;align-items:flex-start;gap:9px;margin:0 0 14px;padding:12px;border:1px solid #91d5ad;border-radius:10px;background:#e9f8ef;color:#176b3d}.booking-plan-covered span{display:block;font-size:.7rem}html[data-theme="dark"] .booking-plan-covered{border-color:#246541;background:#173627;color:#9fe0b9}
</style>
@endpush
