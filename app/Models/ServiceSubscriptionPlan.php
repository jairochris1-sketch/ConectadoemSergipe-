<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceSubscriptionPlan extends Model
{
    protected $fillable = ['ad_id', 'name', 'description', 'price', 'cycle', 'terms', 'active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $plan): void {
            $plan->public_id ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function procedures()
    {
        return $this->belongsToMany(ServiceProcedure::class, 'service_subscription_plan_procedure')
            ->withPivot('included_uses');
    }

    public function subscriptions()
    {
        return $this->hasMany(ServiceClientSubscription::class);
    }
}
