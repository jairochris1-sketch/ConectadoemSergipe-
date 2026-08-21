@extends('layouts.app')

@section('title', 'Agenda e financeiro - '.$ad->title)

@php
    $weekdays = [0 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'];
    $isConsultation = \App\Support\ServiceBookingCatalog::isConsultation($ad);
    $attendanceModes = \App\Support\ServiceBookingCatalog::allowedAttendanceModes($ad);
@endphp

@section('content')
<div class="container py-4 booking-manage">
    <header class="booking-manage-head">
        <div><span>{{ $isConsultation ? 'Gestão de consultas' : 'Gestão do atendimento' }}</span><h1>{{ $isConsultation ? 'Agenda de consultas' : 'Agenda e financeiro' }}</h1><p>{{ $ad->title }} · {{ $ad->display_category }}</p></div>
        <div class="booking-head-actions">
            <a href="{{ route('provider.show', $ad->slug) }}" class="btn btn-outline-light">Ver perfil</a>
            @if($ad->store)<a href="{{ route('store.manage', $ad->store) }}" class="btn btn-light"><i class="fa-solid fa-store me-1"></i> Produtos da vitrine</a>@else<a href="{{ route('store.create') }}" class="btn btn-light"><i class="fa-solid fa-store me-1"></i> Criar vitrine de produtos</a>@endif
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif

    <nav class="booking-tabs" aria-label="Seções da agenda">
        <a href="#resumo">Resumo</a><a href="#agendamentos">Agendamentos</a><a href="#operacao-agenda">Bloqueios e encaixes</a><a href="#procedimentos">Procedimentos</a><a href="#equipe">Equipe e horários</a><a href="#pagamentos">Pagamentos</a><a href="#planos-clientes">Planos de clientes</a><a href="#assinantes">Assinantes</a><a href="#financeiro">Financeiro</a>
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
        <div class="booking-table-wrap"><table><thead><tr><th>Data</th><th>Cliente</th><th>{{ $isConsultation ? 'Consulta' : 'Procedimento' }}</th><th>Modalidade</th><th>Profissional</th><th>Valor</th><th>Status</th></tr></thead><tbody>
            @forelse($appointments as $appointment)<tr><td>{{ $appointment->starts_at->format('d/m/Y H:i') }}</td><td><strong>{{ $appointment->customer_name }}</strong><small>{{ $appointment->customer_phone }}</small></td><td>{{ $appointment->procedure->name }}</td><td>{{ $appointment->attendance_mode_label ?? 'Não informada' }}</td><td>{{ $appointment->staff->name }}</td><td>@if($appointment->clientSubscription)<strong>Incluído no plano</strong><small>{{ $appointment->clientSubscription->plan->name }}</small>@else R$ {{ number_format($appointment->service_price,2,',','.') }} @endif</td><td><form method="POST" action="{{ route('service-booking.appointments.update', [$ad,$appointment]) }}">@csrf @method('PATCH')<select name="status" onchange="this.form.submit()">@foreach(\App\Models\ServiceAppointment::STATUSES as $key=>$label)<option value="{{ $key }}" @selected($appointment->status===$key)>{{ $label }}</option>@endforeach</select></form></td></tr>
            @empty<tr><td colspan="7">Nenhum agendamento registrado.</td></tr>@endforelse
        </tbody></table></div>
    </section>

    <section id="operacao-agenda" class="booking-section">
        <div class="booking-section-title"><div><h2>Bloqueios e agendamentos manuais</h2><p>Registre férias, folgas, almoço, feriados e clientes atendidos por telefone ou WhatsApp.</p></div></div>
        <div class="booking-payment-grid">
            <div class="booking-form-card">
                <form method="POST" action="{{ route('service-booking.blocks.store', $ad) }}">
                @csrf
                <h3>Novo bloqueio</h3>
                <label>Profissional<select name="service_staff_id"><option value="">Toda a equipe</option>@foreach($ad->serviceStaff->where('active', true) as $person)<option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach</select></label>
                <div class="booking-form-grid"><label>Início<input type="datetime-local" name="starts_at" required></label><label>Fim<input type="datetime-local" name="ends_at" required></label></div>
                <label>Motivo<input name="reason" maxlength="180" placeholder="Ex.: almoço, feriado ou férias"></label>
                <button>Bloquear período</button>
                </form>
                <div class="booking-block-list">
                    @forelse($scheduleBlocks as $block)
                        <div><span><strong>{{ $block->staff?->name ?? 'Toda a equipe' }}</strong> · {{ $block->starts_at->format('d/m/Y H:i') }} até {{ $block->ends_at->format('d/m/Y H:i') }}<small>{{ $block->reason }}</small></span><form method="POST" action="{{ route('service-booking.blocks.destroy', [$ad, $block]) }}">@csrf @method('DELETE')<button aria-label="Remover bloqueio"><i class="fa-solid fa-xmark"></i></button></form></div>
                    @empty<p class="booking-help">Nenhum bloqueio futuro.</p>@endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('service-booking.appointments.manual', $ad) }}" class="booking-form-card">
                @csrf
                <h3>Novo agendamento manual</h3>
                <div class="booking-form-grid"><label>Cliente<input name="customer_name" required maxlength="255"></label><label>Telefone<input name="customer_phone" maxlength="20" inputmode="tel"></label></div>
                <label>E-mail cadastrado (opcional)<input type="email" name="customer_email" maxlength="255" placeholder="Vincula ao usuário quando já existe no site"></label>
                <div class="booking-form-grid"><label>Procedimento<select name="procedure_id" required>@foreach($ad->serviceProcedures->where('active', true) as $procedure)<option value="{{ $procedure->id }}">{{ $procedure->name }}</option>@endforeach</select></label><label>Profissional<select name="staff_id" required>@foreach($ad->serviceStaff->where('active', true) as $person)<option value="{{ $person->id }}">{{ $person->name }}</option>@endforeach</select></label></div>
                @if($isConsultation)<label>Modalidade<select name="attendance_mode" required>@foreach($attendanceModes as $mode)<option value="{{ $mode }}">{{ $mode === 'online' ? 'Online / teleconsulta' : 'Presencial' }}</option>@endforeach</select></label>@endif
                <label>Data e horário<input type="datetime-local" name="starts_at" required></label>
                <label>Observação<textarea name="notes" maxlength="1000" rows="3"></textarea></label>
                <button>Registrar agendamento</button>
            </form>
        </div>
    </section>

    <section id="procedimentos" class="booking-section booking-two-columns">
        <div><h2>Procedimentos</h2><div class="booking-list">@forelse($ad->serviceProcedures as $procedure)<article class="booking-procedure-card"><div class="booking-procedure-summary"><strong>{{ $procedure->name }}</strong>@if($procedure->description)<p>{{ $procedure->description }}</p>@endif<span>R$ {{ number_format($procedure->price,2,',','.') }} · {{ $procedure->duration_minutes }} minutos</span></div><div class="booking-procedure-actions"><details><summary aria-label="Editar {{ $procedure->name }}"><i class="fa-solid fa-pen"></i><span>Editar</span></summary><form method="POST" action="{{ route('service-booking.procedures.update',[$ad,$procedure]) }}" class="booking-procedure-edit-form">@csrf @method('PUT')<label>Nome<input name="name" value="{{ $procedure->name }}" required maxlength="120"></label><label>Descrição do que está incluído<textarea name="description" required maxlength="1000" rows="4">{{ $procedure->description }}</textarea><small>Explique claramente o que está e o que não está incluído no valor.</small></label><div class="booking-form-grid"><label>Valor (R$)<input type="number" name="price" value="{{ $procedure->price }}" min="0" step="0.01" required></label><label>Tempo aproximado<select name="duration_minutes" required>@foreach([15,20,30,40,45,60,75,90,120,150,180,240] as $minutes)<option value="{{ $minutes }}" @selected($procedure->duration_minutes === $minutes)>{{ $minutes }} minutos</option>@endforeach</select></label></div><button>Salvar alterações</button></form></details><form method="POST" action="{{ route('service-booking.procedures.destroy',[$ad,$procedure]) }}" onsubmit="return confirm('Remover este procedimento da agenda?')">@csrf @method('DELETE')<button aria-label="Remover {{ $procedure->name }}"><i class="fa-solid fa-trash"></i><span>Excluir</span></button></form></div></article>@empty<p>Cadastre o primeiro procedimento.</p>@endforelse</div></div>
        <form method="POST" action="{{ route('service-booking.procedures.store',$ad) }}" class="booking-form-card">@csrf<h3>Novo procedimento</h3><label>Nome<input name="name" value="{{ old('name') }}" required maxlength="120" placeholder="Ex.: Mão tradicional"></label><label>Descrição do que está incluído<textarea name="description" required maxlength="1000" rows="4" placeholder="Ex.: Limpeza, lixamento e pintura tradicional, sem decoração.">{{ old('description') }}</textarea><small>Explique claramente o que está e o que não está incluído no valor.</small></label><div class="booking-form-grid"><label>Valor (R$)<input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" required></label><label>Tempo aproximado<select name="duration_minutes" required>@foreach([15,20,30,40,45,60,75,90,120,150,180,240] as $minutes)<option value="{{ $minutes }}" @selected((int) old('duration_minutes', 15) === $minutes)>{{ $minutes }} minutos</option>@endforeach</select></label></div><button>Cadastrar procedimento</button></form>
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

    <section id="assinantes" class="booking-section">
        <div class="booking-section-title"><div><h2>Assinantes e pagamentos</h2><p>Acompanhe cliente, plano, situação, período liberado e últimas cobranças recebidas pelo Asaas.</p></div></div>
        <div class="booking-table-wrap"><table><thead><tr><th>Cliente</th><th>Plano</th><th>Situação</th><th>Benefícios até</th><th>Última cobrança</th></tr></thead><tbody>
            @forelse($clientSubscriptions as $subscription)
                @php
                    $latestPayment = $subscription->payments->first();
                    $subscriptionStatus = ['creating'=>'Criando','pending_payment'=>'Aguardando pagamento','active'=>'Ativo','past_due'=>'Em atraso','cancelled'=>'Cancelado','failed'=>'Falhou'][$subscription->status] ?? ucfirst($subscription->status);
                    $paymentStatus = $latestPayment ? (['pending'=>'Pendente','confirmed'=>'Confirmado','received'=>'Recebido','overdue'=>'Vencido','refunded'=>'Estornado','chargeback'=>'Contestação','deleted'=>'Excluído'][$latestPayment->status] ?? ucfirst($latestPayment->status)) : 'Sem cobrança';
                @endphp
                <tr><td><strong>{{ $subscription->customer->name }}</strong><small>{{ $subscription->customer->email }}</small></td><td>{{ $subscription->plan->name }}<small>R$ {{ number_format($subscription->plan->price,2,',','.') }}/mês</small></td><td>{{ $subscriptionStatus }}</td><td>{{ $subscription->paid_through?->format('d/m/Y') ?? 'Não liberado' }}</td><td>{{ $paymentStatus }}@if($latestPayment)<small>R$ {{ number_format($latestPayment->value,2,',','.') }} · {{ $latestPayment->due_date?->format('d/m/Y') ?? 'sem vencimento' }}</small>@endif</td></tr>
            @empty<tr><td colspan="5">Nenhum cliente assinou um plano ainda.</td></tr>@endforelse
        </tbody></table></div>
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
.booking-form-card>form>button{padding:10px 14px;border:0;border-radius:9px;background:#0d6efd;color:#fff;font-weight:900}.booking-block-list{display:grid;gap:7px;margin-top:14px}.booking-block-list>div{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px;border:1px solid var(--border);border-radius:9px}.booking-block-list span,.booking-block-list small{display:block}.booking-block-list small{color:var(--muted);font-size:.68rem}.booking-block-list form button{border:0;background:transparent;color:#b72d3a}
.booking-list article>div{min-width:0}.booking-list article p{margin:4px 12px 5px 0;color:var(--muted);font-size:.75rem;white-space:pre-line}.booking-form-card label>small{color:var(--muted);font-size:.68rem;font-weight:600}
.booking-list .booking-procedure-card{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:start;gap:12px}.booking-procedure-actions{display:flex;align-items:flex-start;gap:5px}.booking-procedure-actions details{position:relative}.booking-procedure-actions summary,.booking-procedure-actions>form button{display:flex;align-items:center;gap:5px;padding:7px;border:0;border-radius:7px;background:transparent;color:#1767c5;font-size:.7rem;font-weight:800;cursor:pointer;list-style:none}.booking-procedure-actions summary::-webkit-details-marker{display:none}.booking-procedure-actions>form button{color:#c4313c}.booking-procedure-edit-form{position:absolute;z-index:5;top:38px;right:0;width:min(420px,calc(100vw - 64px));padding:15px;border:1px solid var(--border);border-radius:12px;background:var(--card);box-shadow:0 16px 36px rgba(15,36,65,.2)}.booking-procedure-edit-form label{display:grid;gap:5px;margin:8px 0;font-size:.72rem;font-weight:800}.booking-procedure-edit-form input,.booking-procedure-edit-form select,.booking-procedure-edit-form textarea{width:100%;padding:9px;border:1px solid var(--border);border-radius:8px;background:var(--background);color:var(--foreground)}.booking-procedure-edit-form textarea{resize:vertical}.booking-procedure-edit-form small{color:var(--muted)}.booking-procedure-edit-form>button{padding:10px 14px;border:0;border-radius:9px;background:#0d6efd;color:#fff;font-weight:900}@media(max-width:575px){.booking-list .booking-procedure-card{grid-template-columns:1fr}.booking-procedure-actions{flex-wrap:wrap;justify-content:flex-end;border-top:1px solid var(--border);padding-top:6px}.booking-procedure-actions details[open]{order:3;width:100%}.booking-procedure-actions details[open] summary{justify-content:flex-end}.booking-procedure-edit-form{position:static;width:100%;margin-top:6px;box-shadow:none}.booking-procedure-actions summary,.booking-procedure-actions>form button{padding:9px 11px}.booking-procedure-actions summary span,.booking-procedure-actions>form button span{display:inline}}
</style>
@endpush
