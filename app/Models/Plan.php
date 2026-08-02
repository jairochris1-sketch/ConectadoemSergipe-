<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'badge_label',
        'headline',
        'description',
        'price',
        'color',
        'is_active',
        'is_highlighted',
        'sort_order',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'is_active'      => 'boolean',
        'is_highlighted' => 'boolean',
    ];

    // ─── Relacionamentos ────────────────────────────────────────────────────

    public function featureValues(): HasMany
    {
        return $this->hasMany(PlanFeatureValue::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PlanFeature::class, 'plan_feature_values')
            ->withPivot('value', 'show_on_page')
            ->withTimestamps();
    }

    // ─── Escopos ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /**
     * Retorna o valor de uma feature específica neste plano.
     * null = ilimitado | '0' = bloqueado | string numérica = limite
     */
    public function featureValue(string $key): ?string
    {
        $value = $this->featureValues()
            ->whereHas('feature', fn ($q) => $q->where('key', $key))
            ->value('value');

        // Se a chave não existe no plano, retorna '0' (sem acesso)
        return $value ?? '0';
    }

    public function isPaid(): bool
    {
        return $this->price > 0;
    }

    public function formattedPrice(): string
    {
        if ($this->price == 0) {
            return 'Gratuito';
        }

        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }
}
