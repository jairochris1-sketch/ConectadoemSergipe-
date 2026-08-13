<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\ReportNotification;
use App\Models\ServiceAppointment;
use App\Models\ServiceClientSubscription;
use App\Models\ServicePaymentSetting;
use App\Models\ServiceProcedure;
use App\Models\ServiceStaff;
use App\Models\ServiceSubscriptionPayment;
use App\Support\ServiceBookingCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceBookingController extends Controller
{
    public function manage(Request $request, Ad $ad)
    {
        $this->authorizeManagement($ad, $request);

        $ad->load([
            'serviceProcedures' => fn ($query) => $query->orderBy('name'),
            'serviceStaff.procedures',
            'serviceStaff.availabilities',
            'serviceSubscriptionPlans.procedures',
            'store',
        ]);
        $ownerStores = $ad->user->stores()->orderBy('name')->get();
        $paymentSetting = ServicePaymentSetting::firstOrNew([
            'user_id' => $ad->user_id,
        ], [
            'provider' => 'asaas',
            'environment' => 'sandbox',
        ]);

        $appointments = $ad->serviceAppointments()
            ->with(['procedure', 'staff', 'customer', 'clientSubscription.plan'])
            ->orderByDesc('starts_at')
            ->limit(100)
            ->get();
        $entries = $ad->serviceFinancialEntries()->latest('occurred_on')->latest('id')->limit(100)->get();
        $monthStart = now('America/Fortaleza')->startOfMonth();
        $monthEnd = now('America/Fortaleza')->endOfMonth();
        $serviceRevenue = (float) $ad->serviceAppointments()
            ->where('status', 'completed')
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->sum('service_price');
        $otherIncome = (float) $ad->serviceFinancialEntries()
            ->where('type', 'income')->whereBetween('occurred_on', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');
        $expenses = (float) $ad->serviceFinancialEntries()
            ->where('type', 'expense')->whereBetween('occurred_on', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');
        $productRevenue = $ad->store
            ? (float) $ad->store->orders()->where('status', 'completed')->whereBetween('placed_at', [$monthStart, $monthEnd])->sum('total')
            : 0.0;
        $subscriptionRevenue = $paymentSetting->exists
            ? (float) ServiceSubscriptionPayment::query()
                ->where('payment_setting_id', $paymentSetting->id)
                ->whereHas('subscription', fn ($query) => $query->where('ad_id', $ad->id))
                ->whereIn('status', ['confirmed', 'received'])
                ->whereBetween('paid_at', [$monthStart, $monthEnd])
                ->sum('value')
            : 0.0;
        $financial = [
            'service_revenue' => $serviceRevenue,
            'product_revenue' => $productRevenue,
            'subscription_revenue' => $subscriptionRevenue,
            'other_income' => $otherIncome,
            'expenses' => $expenses,
            'balance' => $serviceRevenue + $productRevenue + $subscriptionRevenue + $otherIncome - $expenses,
        ];
        $financialHistory = collect(range(5, 0))->map(function (int $monthsAgo) use ($ad, $paymentSetting) {
            $start = now('America/Fortaleza')->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $services = (float) $ad->serviceAppointments()->where('status', 'completed')->whereBetween('starts_at', [$start, $end])->sum('service_price');
            $income = (float) $ad->serviceFinancialEntries()->where('type', 'income')->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->sum('amount');
            $costs = (float) $ad->serviceFinancialEntries()->where('type', 'expense')->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->sum('amount');
            $products = $ad->store ? (float) $ad->store->orders()->where('status', 'completed')->whereBetween('placed_at', [$start, $end])->sum('total') : 0.0;
            $subscriptions = $paymentSetting->exists
                ? (float) ServiceSubscriptionPayment::query()
                    ->where('payment_setting_id', $paymentSetting->id)
                    ->whereHas('subscription', fn ($query) => $query->where('ad_id', $ad->id))
                    ->whereIn('status', ['confirmed', 'received'])
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('value')
                : 0.0;

            return ['month' => ucfirst($start->translatedFormat('M/Y')), 'revenue' => $services + $income + $products + $subscriptions, 'costs' => $costs, 'balance' => $services + $income + $products + $subscriptions - $costs];
        });

        return view('service-booking.manage', compact('ad', 'appointments', 'entries', 'financial', 'financialHistory', 'ownerStores', 'paymentSetting'));
    }

    public function toggle(Request $request, Ad $ad)
    {
        $this->authorizeManagement($ad, $request);
        $ad->update(['booking_enabled' => $request->boolean('booking_enabled')]);

        return back()->with('success', $ad->booking_enabled ? 'Agendamento ativado.' : 'Agendamento pausado.');
    }

    public function linkStore(Request $request, Ad $ad)
    {
        $this->authorizeManagement($ad, $request);
        $data = $request->validate([
            'store_id' => ['nullable', 'integer', Rule::exists('stores', 'id')->where('user_id', $ad->user_id)],
        ]);
        $ad->update(['store_id' => $data['store_id'] ?? null]);

        return back()->with('success', $ad->store_id ? 'Vitrine vinculada ao perfil.' : 'Vitrine desvinculada.');
    }

    public function storeProcedure(Request $request, Ad $ad)
    {
        $this->authorizeManagement($ad, $request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'duration_minutes' => ['required', 'integer', Rule::in([15, 20, 30, 40, 45, 60, 75, 90, 120, 150, 180, 240])],
        ]);
        $procedure = $ad->serviceProcedures()->create($data + ['active' => true]);
        $ad->serviceStaff()->where('active', true)->get()->each(fn ($staff) => $staff->procedures()->syncWithoutDetaching([$procedure->id]));

        return back()->with('success', 'Procedimento cadastrado.');
    }

    public function destroyProcedure(Request $request, Ad $ad, ServiceProcedure $procedure)
    {
        $this->authorizeManagement($ad, $request);
        abort_unless($procedure->ad_id === $ad->id, 404);
        if ($procedure->appointments()->exists()) {
            $procedure->update(['active' => false]);
        } else {
            $procedure->delete();
        }

        return back()->with('success', 'Procedimento removido da agenda.');
    }

    public function storeStaff(Request $request, Ad $ad)
    {
        $this->authorizeManagement($ad, $request);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $staff = $ad->serviceStaff()->create($data + ['active' => true]);
        $staff->procedures()->sync($ad->serviceProcedures()->where('active', true)->pluck('id'));

        return back()->with('success', 'Profissional adicionado à agenda.');
    }

    public function updateStaff(Request $request, Ad $ad, ServiceStaff $staff)
    {
        $this->authorizeManagement($ad, $request);
        abort_unless($staff->ad_id === $ad->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'procedure_ids' => ['nullable', 'array'],
            'procedure_ids.*' => ['integer', Rule::exists('service_procedures', 'id')->where('ad_id', $ad->id)],
            'hours' => ['nullable', 'array'],
            'hours.*.enabled' => ['nullable', 'boolean'],
            'hours.*.starts_at' => ['nullable', 'date_format:H:i'],
            'hours.*.ends_at' => ['nullable', 'date_format:H:i'],
        ]);

        DB::transaction(function () use ($staff, $data) {
            $staff->update(['name' => $data['name']]);
            $staff->procedures()->sync($data['procedure_ids'] ?? []);
            $staff->availabilities()->delete();
            foreach ($data['hours'] ?? [] as $day => $hours) {
                if (empty($hours['enabled'])) {
                    continue;
                }
                if (empty($hours['starts_at']) || empty($hours['ends_at']) || $hours['ends_at'] <= $hours['starts_at']) {
                    throw ValidationException::withMessages(['hours' => 'Confira os horários: o término deve ser posterior ao início.']);
                }
                $staff->availabilities()->create([
                    'day_of_week' => (int) $day,
                    'starts_at' => $hours['starts_at'],
                    'ends_at' => $hours['ends_at'],
                ]);
            }
        });

        return back()->with('success', 'Profissional e horários atualizados.');
    }

    public function booking(Request $request, Ad $ad)
    {
        $this->ensurePublicBooking($ad);
        $ad->load(['serviceProcedures' => fn ($q) => $q->where('active', true)->orderBy('name'), 'serviceStaff' => fn ($q) => $q->where('active', true)->with(['procedures', 'availabilities'])]);
        $paymentSetting = ServicePaymentSetting::where('user_id', $ad->user_id)->first();
        $subscriptionPlans = $paymentSetting?->isReadyForSubscriptions()
            ? $ad->serviceSubscriptionPlans()->where('active', true)->with('procedures')->orderBy('price')->get()
            : collect();
        $customerSubscriptions = $request->user()
            ? $ad->serviceClientSubscriptions()
                ->where('customer_user_id', $request->user()->id)
                ->whereIn('status', ['pending_payment', 'active', 'past_due', 'cancelled'])
                ->with(['plan', 'payments' => fn ($query) => $query->latest('due_date')])
                ->latest('id')
                ->get()
            : collect();
        $procedure = $ad->serviceProcedures->firstWhere('id', (int) $request->query('procedure'));
        $staff = $ad->serviceStaff->firstWhere('id', (int) $request->query('staff'));
        $date = $request->query('date', now('America/Fortaleza')->addDay()->toDateString());
        $slots = ($procedure && $staff && $staff->procedures->contains($procedure))
            ? $this->availableSlots($ad, $staff, $procedure, $date)
            : [];
        $coveringSubscription = ($procedure && $request->user())
            ? $this->availableSubscriptionFor($ad, $request->user()->id, $procedure, Carbon::parse($date, 'America/Fortaleza'))
            : null;

        return view('service-booking.book', compact('ad', 'procedure', 'staff', 'date', 'slots', 'subscriptionPlans', 'customerSubscriptions', 'coveringSubscription'));
    }

    public function storeAppointment(Request $request, Ad $ad)
    {
        $this->ensurePublicBooking($ad);
        $data = $request->validate([
            'procedure_id' => ['required', 'integer'],
            'staff_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $procedure = $ad->serviceProcedures()->where('active', true)->findOrFail($data['procedure_id']);
        $staff = $ad->serviceStaff()->where('active', true)->findOrFail($data['staff_id']);
        abort_unless($staff->procedures()->whereKey($procedure->id)->exists(), 422);
        $start = Carbon::parse($data['starts_at'], 'America/Fortaleza');
        $slot = $start->format('H:i');
        abort_unless(in_array($slot, $this->availableSlots($ad, $staff, $procedure, $start->toDateString()), true), 422, 'Este horário não está mais disponível.');
        $end = $start->copy()->addMinutes($procedure->duration_minutes);

        $appointment = DB::transaction(function () use ($ad, $procedure, $staff, $request, $data, $start, $end) {
            $conflict = ServiceAppointment::query()
                ->where('service_staff_id', $staff->id)
                ->whereNotIn('status', ['cancelled'])
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->lockForUpdate()
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['starts_at' => 'Esse horário acabou de ser reservado. Escolha outro.']);
            }
            $coveringSubscription = $this->availableSubscriptionFor($ad, $request->user()->id, $procedure, $start, true);
            $appointment = $ad->serviceAppointments()->create([
                'service_procedure_id' => $procedure->id,
                'service_staff_id' => $staff->id,
                'customer_user_id' => $request->user()->id,
                'service_client_subscription_id' => $coveringSubscription?->id,
                'customer_name' => $request->user()->name,
                'customer_phone' => preg_replace('/\D+/', '', (string) (($data['phone'] ?? null) ?: $request->user()->phone)),
                'starts_at' => $start,
                'ends_at' => $end,
                'service_price' => $coveringSubscription ? 0 : $procedure->price,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
            if ($coveringSubscription) {
                $coveringSubscription->usages()->create([
                    'service_appointment_id' => $appointment->id,
                    'service_procedure_id' => $procedure->id,
                    'cycle_start' => $coveringSubscription->current_period_start,
                    'cycle_end' => $coveringSubscription->current_period_end,
                    'units' => 1,
                    'status' => 'reserved',
                ]);
            }

            return $appointment;
        });

        ReportNotification::sendTo($ad->user_id, [
            'kind' => 'service_appointment',
            'message' => $request->user()->name.' solicitou '.$procedure->name.' para '.$start->format('d/m/Y H:i').'.',
            'action_url' => route('service-booking.manage', $ad),
        ]);

        return redirect()->route('service-booking.book', $ad)->with('success', $appointment->service_client_subscription_id
            ? 'Agendamento solicitado e benefício do plano reservado. Aguarde a confirmação do profissional.'
            : 'Agendamento solicitado. Aguarde a confirmação do profissional.');
    }

    public function updateAppointment(Request $request, Ad $ad, ServiceAppointment $appointment)
    {
        $this->authorizeManagement($ad, $request);
        abort_unless($appointment->ad_id === $ad->id, 404);
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(ServiceAppointment::STATUSES))]]);
        if ($appointment->status === 'cancelled' && $data['status'] !== 'cancelled') {
            throw ValidationException::withMessages(['status' => 'Um agendamento cancelado não pode ser reativado. Crie um novo horário.']);
        }
        DB::transaction(function () use ($appointment, $data): void {
            $appointment->update($data);
            if ($appointment->subscriptionUsage) {
                $appointment->subscriptionUsage->update(['status' => match ($data['status']) {
                    'cancelled' => 'released',
                    'completed' => 'consumed',
                    default => 'reserved',
                }]);
            }
        });
        ReportNotification::sendTo($appointment->customer_user_id, [
            'kind' => 'service_appointment',
            'message' => 'Seu agendamento de '.$appointment->procedure->name.' foi atualizado para '.$appointment->status_label.'.',
            'action_url' => route('service-booking.book', $ad),
        ]);

        return back()->with('success', 'Status do atendimento atualizado.');
    }

    public function storeFinancialEntry(Request $request, Ad $ad)
    {
        $this->authorizeManagement($ad, $request);
        $data = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['required', 'string', 'max:180'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'occurred_on' => ['required', 'date'],
        ]);
        $ad->serviceFinancialEntries()->create($data);

        return back()->with('success', $data['type'] === 'expense' ? 'Custo registrado.' : 'Receita registrada.');
    }

    private function availableSlots(Ad $ad, ServiceStaff $staff, ServiceProcedure $procedure, string $date): array
    {
        try {
            $day = Carbon::createFromFormat('Y-m-d', $date, 'America/Fortaleza')->startOfDay();
        } catch (\Throwable) {
            return [];
        }
        if ($day->lt(now('America/Fortaleza')->startOfDay()) || $day->gt(now('America/Fortaleza')->addDays(60))) {
            return [];
        }
        $availability = $staff->availabilities()->where('day_of_week', $day->dayOfWeek)->first();
        if (! $availability) {
            return [];
        }
        $cursor = Carbon::parse($date.' '.$availability->starts_at, 'America/Fortaleza');
        $limit = Carbon::parse($date.' '.$availability->ends_at, 'America/Fortaleza');
        $appointments = $ad->serviceAppointments()->where('service_staff_id', $staff->id)->whereNotIn('status', ['cancelled'])->whereDate('starts_at', $date)->get();
        $slots = [];
        while ($cursor->copy()->addMinutes($procedure->duration_minutes)->lte($limit)) {
            $end = $cursor->copy()->addMinutes($procedure->duration_minutes);
            $conflict = $appointments->contains(fn ($item) => $item->starts_at->lt($end) && $item->ends_at->gt($cursor));
            if (! $conflict && $cursor->gt(now('America/Fortaleza')->addHours(2))) {
                $slots[] = $cursor->format('H:i');
            }
            $cursor->addMinutes(10);
        }

        return $slots;
    }

    private function availableSubscriptionFor(Ad $ad, int $customerId, ServiceProcedure $procedure, Carbon $date, bool $lock = false): ?ServiceClientSubscription
    {
        $query = $ad->serviceClientSubscriptions()
            ->where('customer_user_id', $customerId)
            ->whereIn('status', ['active', 'cancelled'])
            ->whereDate('current_period_start', '<=', $date->toDateString())
            ->whereDate('current_period_end', '>=', $date->toDateString())
            ->whereDate('paid_through', '>=', $date->toDateString())
            ->whereHas('plan.procedures', fn ($plans) => $plans->whereKey($procedure->id))
            ->with('plan.procedures')
            ->latest('id');
        if ($lock) {
            $query->lockForUpdate();
        }

        foreach ($query->get() as $subscription) {
            $included = $subscription->plan->procedures->firstWhere('id', $procedure->id)?->pivot?->included_uses;
            if ($included === null) {
                return $subscription;
            }
            $reserved = $subscription->usages()
                ->where('service_procedure_id', $procedure->id)
                ->whereDate('cycle_start', $subscription->current_period_start)
                ->whereDate('cycle_end', $subscription->current_period_end)
                ->whereIn('status', ['reserved', 'consumed'])
                ->sum('units');
            if ($reserved < $included) {
                return $subscription;
            }
        }

        return null;
    }

    private function authorizeManagement(Ad $ad, Request $request): void
    {
        abort_unless($request->user() && ($request->user()->role === 'admin' || $ad->user_id === $request->user()->id), 403);
        abort_unless(ServiceBookingCatalog::eligible($ad), 404);
    }

    private function ensurePublicBooking(Ad $ad): void
    {
        abort_unless($ad->status === 'active' && $ad->booking_enabled && ServiceBookingCatalog::eligible($ad), 404);
    }
}
