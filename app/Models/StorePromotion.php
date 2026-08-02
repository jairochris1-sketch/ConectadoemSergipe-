<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StorePromotion extends Model
{
    protected $fillable = [
        'store_id',
        'title',
        'coupon_code',
        'discount_type',
        'discount_value',
        'description',
        'terms',
        'starts_at',
        'ends_at',
        'active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where(fn (Builder $starts) => $starts
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now()))
            ->where('ends_at', '>', now());
    }

    public function getDiscountLabelAttribute(): string
    {
        return $this->discount_type === 'percentage'
            ? rtrim(rtrim(number_format((float) $this->discount_value, 2, ',', '.'), '0'), ',').'% OFF'
            : 'R$ '.number_format((float) $this->discount_value, 2, ',', '.').' OFF';
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->active) {
            return 'Pausada';
        }
        if ($this->ends_at?->isPast()) {
            return 'Encerrada';
        }
        if ($this->starts_at?->isFuture()) {
            return 'Agendada';
        }

        return 'Ativa';
    }
}
