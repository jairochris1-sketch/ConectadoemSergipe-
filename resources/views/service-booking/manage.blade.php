@extends('layouts.app')

@section('title', 'Agenda e financeiro - '.$ad->title)

@php $weekdays = [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado']; @endphp

@section('content')
<div class="container py-4 booking-manage">
    <header class="booking-manage-head">
        <div><span>Gestão do atendimento</span><h1>Agenda e financeiro</h1><p>{{ $ad->title }} · {{ $ad->display_category }}</p></div>
        <div class="booking-head-actions">
            <a href="{{ route('provider.show', $ad->slug) }}" class="btn btn-outline-light">Ver perfil</a>
            @if($ad->store)<a href="{{ route('store.manage', $ad->store) }}" class="btn btn-light"><i class="fa-solid fa-store me-1"></i> Produtos da vitrine</a>@else<a href="{{ route('store.create') }}" class="btn btn-light"><i class="fa-solid fa-store me-1"></i> Criar vitrine de produtos</a>@endif
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif

    <nav class="booking-tabs" aria-label="Seções da agenda">
        <a href="#resumo">Resumo</a><a href="#agendamentos">Agendamentos</a><a href="#procedimentos">Procedimentos</a><a href="#equipe">Equipe e horários</a><a href="#pagamentos">Pagamentos</a><a href="#planos-clientes">Planos de clientes</a><a href="#financeiro">Financeiro</a>
    </nav>

    <section id="resumo" class="booking-section">
        <div class="booking-section-title"><div><h2>Visão do mês</h2><p>Receitas registradas e custos lançados no mês atual.</p></div>
            <form method="POST" action="{{ route('service-booking.toggle', $ad) }}">@csrf @method('PATCH')<input type="hidden" name="booking_enabled" value="{{ $ad->booking_enabled ? 0 : 1 }}"><button class="btn {{ $ad->booking_enabled ? 'btn-outline-danger' : 'btn-success' }}">{{ $ad->booking_enabled ? 'Pausar agendamentos' : 'Ativar agendamentos' }}</button></form>
        </div>
        <div class="booking-metrics">
            <article><span>Procedimentos concluídos</span><strong>R$ {{ number_format($financial['service_revenue'], 2, ',', '.') }}</strong></article>
            <article><span>Produtos vendidos</span><strong>R$ {{ number_format($financial['product_revenue'], 2, ',', '.') }}</strong><small>{{ $ad->store ? 'Pedidos concluídos da vitrine' : 'Vincule uma vitrine ao perfil' }}</small></article>
            <article><span>Assinaturas recebidas</span><strong>R$ {{ number_format($financial['subscription_revenue'], 2, ',', '.') }}</strong></article>
            <article><span>Outras receitas</span><strong>R$ {{ number_format($financial['other_income'], 2, ',', '.') }}</strong></article>
            <article class="is-expense"><span>Custos</span><strong>R$ {{ number_format($financial['expenses'], 2, ',', '.') }}</strong></article>
            <article class="is-balance"><span>Resultado estimado</span><strong>R$ {{ number_format($financial['balance'], 2, ',', '.') }}</strong></article>
        </div>
        <p class="booking-financial-note"><i class="fa-solid fa-circle-info"></i> Este resumo é gerencial e não substitui contabilidade, fluxo de caixa oficial ou obrigações fiscais.</p>
        <div class="booking-financial-history"><h3>Desenvolvimento financeiro · últimos 6 meses</h3><div class="booking-table-wrap"><table><thead><tr><th>Mês</th><th>Receitas</th><th>Custos</th><th>Resultado</th></tr></thead><tbody>@foreach($financialHistory as $month)<tr><td>{{ $month['month'] }}</td><td class="text-success">R$ {{ number_format($month['revenue'],2,',','.') }}</td><td class="text-danger">R$ {{ number_format($month['costs'],2,',','.') }}</td><td><strong>R$ {{ number_format($month['balance'],2,',','.') }}</strong></td></tr>@endforeach</tbody></table></div></div>
        <form method="POST" action="{{ route('service-booking.store-link',$ad) }}" class="booking-store-link">@csrf @method('PATCH')<label>Vitrine usada para vender produtos e contabilizar pedidos concluídos<select name="store_id"><option value="">Nenhuma vitrine vinculada</option>@foreach($ownerStores as $store)<option value="{{ $store->id }}" @selected($ad->store_id===$store->id)>{{ $store->name }}</option>@endforeach</select></label><button>Salvar vitrine</button></form>
    </section>

    <section id="agendamentos" class="booking-section">
        <div class="booking-section-title"><div><h2>Agendamentos</h2><p>Cliente, procedimento, profissional, valor e data ficam registrados.</p></div></div>
        <div class="booking-table-wrap"><table><thead><tr><th>Data</th><th>Cliente</th><th>Procedimento</th><th>Profissional</th><th>Valor</th><th>Status</th></tr></thead><tbody>
            @forelse($appointments as $appointment)<tr><td>{{ $appointment->starts_at->format('d/m/Y H:i') }}</td><td><strong>{{ $appointment->customer_name }}</strong><small>{{ $appointment->customer_phone }}</small></td><td>{{ $appointment->procedure->name }}</td><td>{{ $appointment->staff->name }}</td><td>@if($appointment->clientSubscription)<strong>Incluído no plano</strong><small>{{ $appointment->clientSubscription->plan->name }}</small>@else R$ {{ number_format($appointment->service_price,2,',','.') }} @endif</td><td><form method="POST" action="{{ route('service-booking.appointments.update', [$ad,$appointment]) }}">@csrf @method('PATCH')<select name="status" onchange="this.form.submit()">@foreach(\App\Models\ServiceAppointment::STATUSES as $key=>$label)<option value="{{ $key }}" @selected($appointment->status===$key)>{{ $label }}</option>@endforeach</select></form></td></tr>
            @empty<tr><td colspan="6">Nenhum agendamento registrado.</td></tr>@endforelse
        </tbody></table></div>
    </section>

    <section id="procedimentos" class="booking-section booking-two-columns">
        <div><h2>Procedimentos</h2><div class="booking-list">@forelse($ad->serviceProcedures as $procedure)<article><div><strong>{{ $procedure->name }}</strong><span>R$ {{ number_format($procedure->price,2,',','.') }} · {{ $procedure->duration_minutes }} minutos</span></div><form method="POST" action="{{ route('service-booking.procedures.destroy',[$ad,$procedure]) }}">@csrf @method('DELETE')<button aria-label="Remover procedimento"><i class="fa-solid fa-trash"></i></button></form></article>@empty<p>Cadastre o primeiro procedimento.</p>@endforelse</div></div>
        <form method="POST" action="{{ route('service-booking.procedures.store',$ad) }}" class="booking-form-card">@csrf<h3>Novo procedimento</h3><label>Nome<input name="name" required maxlength="120" placeholder="Ex.: Manicure simples"></label><div class="booking-form-grid"><label>Valor (R$)<input type="number" name="price" min="0" step="0.01" required></label><label>Tempo aproximado<select name="duration_minutes" required>@foreach([15,20,30,40,45,60,75,90,120,150,180,240] as $minutes)<option value="{{ $minutes }}">{{ $minutes }} minutos</option>@endforeach</select></label></div><button>Cadastrar procedimento</button></form>
    </section>

    <section id="equipe" class="booking-section">
        <div class="booking-section-title"><div><h2>Equipe e disponibilidade</h2><p>Cada profissional possui seus procedimentos e horários.</p></div><form method="POST" action="{{ route('service-booking.staff.store',$ad) }}" class="booking-inline-form">@csrf<input name="name" required maxlength="120" placeholder="Nome do profissional"><button>Adicionar</button></form></div>
        <div class="booking-staff-grid">
            @forelse($ad->serviceStaff as $person)
                <form method="POST" action="{{ route('service-booking.staff.update',[$ad,$person]) }}" class="booking-form-card">@csrf @method('PUT')<h3>{{ $person->name }}</h3><label>Nome<input name="name" value="{{ $person->name }}" required></label><fieldset><legend>Procedimentos realizados</legend>@foreach($ad->serviceProcedures->where('active',true) as $procedure)<label class="booking-check"><input type="checkbox" name="procedure_ids[]" value="{{ $procedure->id }}" @checked($person->procedures->contains($procedure))><span>{{ $procedure->name }}</span></label>@endforeach</fieldset><fieldset><legend>Horários semanais</legend>@foreach($weekdays as $day=>$label) @php $hours=$person->availabilities->firstWhere('day_of_week',$day); @endphp <div class="booking-hours"><label class="booking-check"><input type="checkbox" name="hours[{{ $day }}][enabled]" value="1" @checked($hours)><span>{{ $label }}</span></label><input type="time" name="hours[{{ $day }}][starts_at]" value="{{ $hours ? substr($hours->starts_at,0,5) : '08:00' }}"><span>até</span><input type="time" name="hours[{{ $day }}][ends_at]" value="{{ $hours ? substr($hours->ends_at,0,5) : '18:00' }}"></div>@endforeach</fieldset><button>Salvar profissional</button></form>
            @empty<p>Adicione pelo menos um profissional para liberar horários.</p>@endforelse
        </div>
    </section>

    <section id="pagamentos" class="booking-section">
        <div class="booking-section-title">
            <div>
                <h2>Pagamentos e assinaturas pelo Asaas</h2>
                <p>A conta do profissional recebe diretamente. Esta configuração não interfere na conta Asaas da plataforma.</p>
            </div>
            <span class="booking-payment-status {{ $paymentSetting->isReadyForSubscriptions() ? 'is-ready' : '' }}">
                {{ $paymentSetting->isReadyForSubscriptions() ? 'Ativo para clientes' : 'Desativado para clientes' }}
            </span>
        </div>

        <div class="booking-payment-grid">
            <form method="POST" action="{{ route('service-payments.settings.update', $ad) }}" class="booking-form-card">
                @csrf @method('PUT')
                <h3>1. Credencial e ativação</h3>
                <p class="booking-help">Comece no Sandbox. A chave fica criptografada e nunca é mostrada novamente.</p>
                <label>Ambiente
                    <select name="environment">
                        <option value="sandbox" @selected($paymentSetting->environment !== 'production')>Sandbox (testes)</option>
                        <option value="production" @selected($paymentSetting->environment === 'production')>Produção (dinheiro real)</option>
                    </select>
                </label>
                <label>Chave da API Asaas
                    <input type="password" name="api_key" autocomplete="new-password" placeholder="{{ $paymentSetting->api_key_hint ? 'Configurada: '.$paymentSetting->api_key_hint.' · deixe vazio para manter' : '$aact_hmlg_...' }}">
                </label>
                <div class="booking-connection-summary">
                    <span>Conexão: <strong>{{ $paymentSetting->verified_at ? 'verificada' : 'não verificada' }}</strong></span>
                    <span>Webhook: <strong>{{ $paymentSetting->webhook_registered_at ? 'registrado' : 'não registrado' }}</strong></span>
                </div>
                <label class="booking-check"><input type="checkbox" name="online_payments_enabled" value="1" @checked($paymentSetting->online_payments_enabled) @disabled(!$paymentSetting->verified_at || !$paymentSetting->webhook_registered_at)><span>Aceitar pagamentos online</span></label>
                <label class="booking-check"><input type="checkbox" name="subscriptions_enabled" value="1" @checked($paymentSetting->subscriptions_enabled) @disabled(!$paymentSetting->verified_at || !$paymentSetting->webhook_registered_at)><span>Mostrar planos mensais aos clientes</span></label>
                <button>Salvar configuração</button>
            </form>

            <div class="booking-form-card booking-payment-steps">
                <h3>2. Verificação segura</h3>
                <p class="booking-help">Salve a credencial, teste a conexão e depois registre o webhook usando uma URL HTTPS pública.</p>
                <form method="POST" action="{{ route('service-payments.settings.verify', $ad) }}">@csrf<button @disabled(!$paymentSetting->exists)>Testar conexão Asaas</button></form>
                <form method="POST" action="{{ route('service-payments.settings.webhook', $ad) }}">@csrf<button @disabled(!$paymentSetting->verified_at)>Registrar webhook seguro</button></form>
                <small>Não envie sua chave por mensagem. Gere uma chave exclusiva para esta integração e faça a rotação se suspeitar de exposição.</small>
            </div>
        </div>
    </section>

    <section id="planos-clientes" class="booking-section">
        <div class="booking-section-title"><div><h2>Planos mensais para clientes</h2><p>Defina o valor e quantas utilizações de cada procedimento entram no ciclo mensal. Campo vazio significa ilimitado.</p></div></div>
        <div class="booking-plan-grid">
            @foreach($ad->serviceSubscriptionPlans as $plan)
                <form method="POST" action="{{ route('service-subscription-plans.update', [$ad, $plan]) }}" class="booking-form-card">
                    @csrf @method('PUT')
                    <h3>{{ $plan->name }}</h3>
                    <label>Nome<input name="name" value="{{ $plan->name }}" required maxlength="120"></label>
                    <label>Descrição<input name="description" value="{{ $plan->description }}" maxlength="500"></label>
                    <label>Mensalidade (R$)<input type="number" name="price" value="{{ $plan->price }}" min="1" step="0.01" required></label>
                    <fieldset><legend>Procedimentos incluídos</legend>
                        @foreach($ad->serviceProcedures->where('active', true) as $procedure)
                            @php $included = $plan->procedures->firstWhere('id', $procedure->id); @endphp
                            <div class="booking-plan-procedure">
                                <label class="booking-check"><input type="checkbox" name="procedures[{{ $procedure->id }}][enabled]" value="1" @checked($included)><span>{{ $procedure->name }}</span></label>
                                <input type="number" name="procedures[{{ $procedure->id }}][included_uses]" value="{{ $included?->pivot?->included_uses }}" min="1" max="99" placeholder="Ilimitado" aria-label="Usos mensais de {{ $procedure->name }}">
                            </div>
                        @endforeach
                    </fieldset>
                    <label>Regras do plano<textarea name="terms" maxlength="3000" rows="3" placeholder="Cancelamento, faltas e limites de uso">{{ $plan->terms }}</textarea></label>
                    <label class="booking-check"><input type="checkbox" name="active" value="1" @checked($plan->active)><span>Publicado para clientes</span></label>
                    <button>Salvar plano</button>
                </form>
            @endforeach

            <form method="POST" action="{{ route('service-subscription-plans.store', $ad) }}" class="booking-form-card">
                @csrf
                <h3>Novo plano mensal</h3>
                <label>Nome<input name="name" required maxlength="120" placeholder="Ex.: 3 unhas por mês"></label>
                <label>Descrição<input name="description" maxlength="500" placeholder="Explique o benefício em uma frase"></label>
                <label>Mensalidade (R$)<input type="number" name="price" min="1" step="0.01" required></label>
                <fieldset><legend>Procedimentos incluídos</legend>
                    @foreach($ad->serviceProcedures->where('active', true) as $procedure)
                        <div class="booking-plan-procedure">
                            <label class="booking-check"><input type="checkbox" name="procedures[{{ $procedure->id }}][enabled]" value="1"><span>{{ $procedure->name }}</span></label>
                            <input type="number" name="procedures[{{ $procedure->id }}][included_uses]" min="1" max="99" placeholder="Ilimitado" aria-label="Usos mensais de {{ $procedure->name }}">
                        </div>
                    @endforeach
                </fieldset>
                <label>Regras do plano<textarea name="terms" maxlength="3000" rows="3" placeholder="Ex.: créditos não acumulam e faltas sem aviso consomem um uso"></textarea></label>
                <label class="booking-check"><input type="checkbox" name="active" value="1"><span>Publicar agora</span></label>
                <button>Criar plano</button>
            </form>
        </div>
    </section>

    <section id="financeiro" class="booking-section booking-two-columns">
        <div><h2>Lançamentos financeiros</h2><div class="booking-table-wrap"><table><thead><tr><th>Data</th><th>Descrição</th><th>Tipo</th><th>Valor</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->occurred_on->format('d/m/Y') }}</td><td><strong>{{ $entry->description }}</strong><small>{{ $entry->category }}</small></td><td>{{ $entry->type==='expense'?'Custo':'Receita' }}</td><td class="{{ $entry->type==='expense'?'text-danger':'text-success' }}">R$ {{ number_format($entry->amount,2,',','.') }}</td></tr>@empty<tr><td colspan="4">Nenhum lançamento manual.</td></tr>@endforelse</tbody></table></div></div>
        <form method="POST" action="{{ route('service-booking.financial.store',$ad) }}" class="booking-form-card">@csrf<h3>Novo lançamento</h3><label>Tipo<select name="type"><option value="expense">Custo</option><option value="income">Outra receita</option></select></label><label>Categoria<input name="category" required maxlength="60" placeholder="Ex.: Material, aluguel, produto"></label><label>Descrição<input name="description" required maxlength="180"></label><div class="booking-form-grid"><label>Valor<input type="number" name="amount" min="0.01" step="0.01" required></label><label>Data<input type="date" name="occurred_on" value="{{ now()->toDateString() }}" required></label></div><button>Registrar lançamento</button></form>
    </section>
</div>
@endsection

@push('styles')
<style>
.booking-manage{color:var(--foreground)}.booking-manage-head{display:flex;align-items:end;justify-content:space-between;gap:20px;padding:28px;border-radius:20px;background:linear-gradient(135deg,#09234c,#1267c8);color:#fff}.booking-manage-head span{color:#9dcaff;font-size:.72rem;font-weight:900;text-transform:uppercase}.booking-manage-head h1{margin:4px 0;font-weight:900}.booking-manage-head p{margin:0;color:#dae8fb}.booking-head-actions{display:flex;gap:8px}.booking-tabs{display:flex;gap:8px;overflow:auto;margin:16px 0;padding:6px;border:1px solid var(--border);border-radius:14px;background:var(--card)}.booking-tabs a{padding:9px 13px;color:var(--foreground);font-size:.78rem;font-weight:800;text-decoration:none;white-space:nowrap}.booking-section{margin-top:18px;padding:22px;border:1px solid var(--border);border-radius:18px;background:var(--card);box-shadow:0 8px 24px rgba(24,56,94,.06)}.booking-section h2{font-size:1.2rem;font-weight:900}.booking-section-title{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:16px}.booking-section-title p{margin:2px 0 0;color:var(--muted);font-size:.78rem}.booking-metrics{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.booking-metrics article{padding:15px;border-radius:13px;background:var(--background);border:1px solid var(--border)}.booking-metrics span,.booking-metrics small{display:block;color:var(--muted);font-size:.68rem}.booking-metrics strong{display:block;margin-top:6px;color:#15713f;font-size:1.15rem}.booking-metrics .is-expense strong{color:#c4313c}.booking-metrics .is-balance{background:#eaf3ff}.booking-metrics .is-balance strong{color:#0d5fc5}.booking-financial-note{margin:13px 0 0;color:var(--muted);font-size:.7rem}.booking-table-wrap{overflow:auto}.booking-table-wrap table{width:100%;border-collapse:collapse;font-size:.76rem}.booking-table-wrap th,.booking-table-wrap td{padding:11px 9px;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}.booking-table-wrap td small{display:block;color:var(--muted)}.booking-table-wrap select{padding:6px;border:1px solid var(--border);border-radius:7px;background:var(--background);color:var(--foreground)}.booking-two-columns{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(280px,.6fr);gap:20px}.booking-list{display:grid;gap:8px}.booking-list article{display:flex;align-items:center;justify-content:space-between;padding:12px;border:1px solid var(--border);border-radius:10px}.booking-list span{display:block;color:var(--muted);font-size:.72rem}.booking-list button{border:0;background:transparent;color:#c4313c}.booking-form-card{padding:17px;border:1px solid var(--border);border-radius:14px;background:var(--background)}.booking-form-card h3{font-size:1rem;font-weight:900}.booking-form-card label{display:grid;gap:5px;margin:9px 0;font-size:.72rem;font-weight:800}.booking-form-card input,.booking-form-card select{width:100%;padding:9px;border:1px solid var(--border);border-radius:8px;background:var(--card);color:var(--foreground)}.booking-form-card>button,.booking-inline-form button{padding:10px 14px;border:0;border-radius:9px;background:#0d6efd;color:#fff;font-weight:900}.booking-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.booking-inline-form{display:flex;gap:8px}.booking-inline-form input{padding:9px;border:1px solid var(--border);border-radius:9px;background:var(--background);color:var(--foreground)}.booking-staff-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.booking-form-card fieldset{margin:14px 0;padding:12px;border:1px solid var(--border);border-radius:10px}.booking-form-card legend{float:none;width:auto;padding:0 5px;font-size:.75rem;font-weight:900}.booking-check{display:flex!important;grid-template-columns:none!important;align-items:center;gap:7px!important}.booking-check input{width:auto}.booking-hours{display:grid;grid-template-columns:100px 1fr auto 1fr;align-items:center;gap:7px}.booking-hours input{min-width:0}.booking-hours>span{font-size:.65rem;color:var(--muted)}@media(max-width:991px){.booking-metrics{grid-template-columns:repeat(2,1fr)}.booking-two-columns,.booking-staff-grid{grid-template-columns:1fr}.booking-manage-head{align-items:flex-start;flex-direction:column}}@media(max-width:575px){.booking-section,.booking-manage-head{padding:16px}.booking-metrics{grid-template-columns:1fr}.booking-section-title{align-items:flex-start;flex-direction:column}.booking-head-actions,.booking-inline-form{width:100%;flex-direction:column}.booking-hours{grid-template-columns:1fr 1fr 1fr}.booking-hours .booking-check{grid-column:1/-1}.booking-hours>span{text-align:center}}
.booking-financial-history{margin-top:18px}.booking-financial-history h3{font-size:.9rem;font-weight:900}.booking-store-link{display:flex;align-items:end;gap:10px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border)}.booking-store-link label{display:grid;flex:1;gap:5px;color:var(--muted);font-size:.72rem;font-weight:800}.booking-store-link select{padding:9px;border:1px solid var(--border);border-radius:9px;background:var(--background);color:var(--foreground)}.booking-store-link button{padding:10px 14px;border:0;border-radius:9px;background:#0d6efd;color:#fff;font-weight:900}@media(max-width:575px){.booking-store-link{align-items:stretch;flex-direction:column}}
.booking-payment-grid,.booking-plan-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.booking-payment-status{padding:7px 11px;border-radius:999px;background:#f4e9eb;color:#a92e3b;font-size:.72rem;font-weight:900;white-space:nowrap}.booking-payment-status.is-ready{background:#e2f5ea;color:#176b3d}.booking-help{color:var(--muted);font-size:.74rem}.booking-connection-summary{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}.booking-connection-summary span{padding:7px 9px;border-radius:8px;background:var(--card);color:var(--muted);font-size:.7rem}.booking-payment-steps{display:flex;flex-direction:column;gap:12px}.booking-payment-steps form button{width:100%;padding:10px 14px;border:0;border-radius:9px;background:#0d6efd;color:#fff;font-weight:900}.booking-payment-steps button:disabled,.booking-form-card button:disabled{cursor:not-allowed;opacity:.5}.booking-payment-steps small{color:var(--muted);font-size:.7rem}.booking-plan-procedure{display:grid;grid-template-columns:minmax(0,1fr) 105px;align-items:center;gap:10px}.booking-form-card textarea{width:100%;padding:9px;border:1px solid var(--border);border-radius:8px;background:var(--card);color:var(--foreground);resize:vertical}@media(max-width:991px){.booking-payment-grid,.booking-plan-grid{grid-template-columns:1fr}}@media(max-width:575px){.booking-plan-procedure{grid-template-columns:1fr}.booking-payment-status{white-space:normal}}
.booking-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}@media(max-width:991px){.booking-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:575px){.booking-metrics{grid-template-columns:1fr}}
</style>
@endpush
