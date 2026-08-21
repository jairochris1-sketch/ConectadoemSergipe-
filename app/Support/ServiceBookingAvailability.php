<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\ServiceProcedure;
use App\Models\ServiceStaff;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ServiceBookingAvailability
{
    public function slots(
        Ad $ad,
        ServiceStaff $staff,
        ServiceProcedure $procedure,
        string $date,
        ?int $excludeAppointmentId = null,
        bool $enforceLeadTime = true
    ): array {
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
        $appointments = $ad->serviceAppointments()
            ->where('service_staff_id', $staff->id)
            ->whereNotIn('status', ['cancelled'])
            ->when($excludeAppointmentId, fn ($query) => $query->where('id', '!=', $excludeAppointmentId))
            ->whereDate('starts_at', $date)
            ->get();
        $blocks = $ad->serviceScheduleBlocks()
            ->where(fn ($query) => $query->whereNull('service_staff_id')->orWhere('service_staff_id', $staff->id))
            ->where('starts_at', '<', $limit)
            ->where('ends_at', '>', $cursor)
            ->get();
        $slots = [];

        while ($cursor->copy()->addMinutes($procedure->duration_minutes)->lte($limit)) {
            $end = $cursor->copy()->addMinutes($procedure->duration_minutes);
            $conflict = $appointments->contains(fn ($item) => $item->starts_at->lt($end) && $item->ends_at->gt($cursor));
            $blocked = $blocks->contains(fn ($block) => $block->starts_at->lt($end) && $block->ends_at->gt($cursor));
            $leadTimeOk = ! $enforceLeadTime || $cursor->gt(now('America/Fortaleza')->addHours(2));

            if (! $conflict && ! $blocked && $leadTimeOk) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes(10);
        }

        return $slots;
    }

    public function upcoming(Ad $ad, int $limit = 4): Collection
    {
        $procedure = $ad->serviceProcedures()->where('active', true)->orderBy('name')->first();
        if (! $procedure) {
            return collect();
        }

        $staffMembers = $ad->serviceStaff()
            ->where('active', true)
            ->whereHas('procedures', fn ($query) => $query->whereKey($procedure->id))
            ->get();
        if ($staffMembers->isEmpty()) {
            return collect();
        }

        $results = collect();
        foreach (range(0, 14) as $daysAhead) {
            $date = now('America/Fortaleza')->addDays($daysAhead);
            foreach ($staffMembers as $staff) {
                foreach ($this->slots($ad, $staff, $procedure, $date->toDateString()) as $time) {
                    $results->push([
                        'date' => $date->toDateString(),
                        'day_label' => $daysAhead === 0 ? 'Hoje' : ($daysAhead === 1 ? 'Amanhã' : ucfirst($date->translatedFormat('D d/m'))),
                        'time' => $time,
                        'procedure_id' => $procedure->id,
                        'staff_id' => $staff->id,
                    ]);

                    if ($results->count() >= $limit) {
                        return $results;
                    }
                }
            }
        }

        return $results;
    }
}
