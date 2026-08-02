<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreBusinessHour extends Model
{
    public const WEEKDAYS = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        0 => 'Domingo',
    ];

    protected $fillable = [
        'store_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'is_closed',
        'is_24_hours',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_closed' => 'boolean',
        'is_24_hours' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getDisplayHoursAttribute(): string
    {
        if ($this->is_closed) {
            return 'Fechado';
        }
        if ($this->is_24_hours) {
            return 'Aberto 24 horas';
        }

        return $this->shortTime($this->opens_at).' às '.$this->shortTime($this->closes_at);
    }

    public function openingMinutes(): ?int
    {
        return $this->timeToMinutes($this->opens_at);
    }

    public function closingMinutes(): ?int
    {
        return $this->timeToMinutes($this->closes_at);
    }

    private function shortTime(?string $time): string
    {
        return $time ? substr($time, 0, 5) : '--:--';
    }

    private function timeToMinutes(?string $time): ?int
    {
        if (! $time || ! preg_match('/^(\d{2}):(\d{2})/', $time, $matches)) {
            return null;
        }

        return ((int) $matches[1] * 60) + (int) $matches[2];
    }
}
