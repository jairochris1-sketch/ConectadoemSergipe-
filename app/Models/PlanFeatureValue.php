<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeatureValue extends Model
{
    protected $fillable = [
        'plan_id',
        'plan_feature_id',
        'value',
        'show_on_page',
    ];

    protected $casts = [
        'show_on_page' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(PlanFeature::class, 'plan_feature_id');
    }

    /**
     * Verifica se o valor representa acesso ilimitado.
     */
    public function isUnlimited(): bool
    {
        return is_null($this->value);
    }

    /**
     * Verifica se o valor representa acesso bloqueado.
     */
    public function isBlocked(): bool
    {
        return $this->value === '0';
    }

    /**
     * Retorna o valor formatado para exibição na UI.
     */
    public function displayValue(): string
    {
        if ($this->isUnlimited()) {
            return 'Ilimitado';
        }

        if ($this->isBlocked()) {
            return '—';
        }

        if ($this->feature?->type === 'boolean') {
            return $this->value === '1' ? 'Sim' : 'Não';
        }

        // Adiciona sufixo para analytics_days
        if ($this->feature?->key === 'analytics_days') {
            return $this->value . ' dias';
        }

        return $this->value;
    }
}
