<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanFeature extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'sort_order',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(PlanFeatureValue::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_feature_values')
            ->withPivot('value', 'show_on_page')
            ->withTimestamps();
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
