<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreBusinessHoursController extends Controller
{
    public function update(Request $request, Store $store)
    {
        abort_unless($store->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
            'hours.*.is_closed' => ['nullable', 'boolean'],
            'hours.*.is_24_hours' => ['nullable', 'boolean'],
        ]);

        $rows = [];
        $errors = [];

        foreach ($validated['hours'] as $index => $hour) {
            $isClosed = $request->boolean("hours.{$index}.is_closed");
            $is24Hours = ! $isClosed && $request->boolean("hours.{$index}.is_24_hours");
            $opensAt = $hour['opens_at'] ?? null;
            $closesAt = $hour['closes_at'] ?? null;

            if (! $isClosed && ! $is24Hours) {
                if (! $opensAt) {
                    $errors["hours.{$index}.opens_at"] = 'Informe o horário de abertura.';
                }
                if (! $closesAt) {
                    $errors["hours.{$index}.closes_at"] = 'Informe o horário de fechamento.';
                }
                if ($opensAt && $closesAt && $opensAt === $closesAt) {
                    $errors["hours.{$index}.closes_at"] = 'A abertura e o fechamento não podem ter o mesmo horário.';
                }
            }

            $rows[] = [
                'day_of_week' => (int) $hour['day_of_week'],
                'opens_at' => (! $isClosed && ! $is24Hours) ? $opensAt : null,
                'closes_at' => (! $isClosed && ! $is24Hours) ? $closesAt : null,
                'is_closed' => $isClosed,
                'is_24_hours' => $is24Hours,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($store, $rows) {
            foreach ($rows as $row) {
                $store->businessHours()->updateOrCreate(
                    ['day_of_week' => $row['day_of_week']],
                    $row
                );
            }
        });

        return back()->with('store_success', 'Horário de funcionamento atualizado.');
    }
}
