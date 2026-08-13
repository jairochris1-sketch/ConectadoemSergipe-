<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProcedure extends Model
{
    protected $fillable = ['ad_id', 'name', 'price', 'duration_minutes', 'active'];
    protected $casts = ['price' => 'decimal:2', 'duration_minutes' => 'integer', 'active' => 'boolean'];
    public function ad() { return $this->belongsTo(Ad::class); }
    public function staff() { return $this->belongsToMany(ServiceStaff::class, 'service_staff_procedure'); }
    public function appointments() { return $this->hasMany(ServiceAppointment::class); }
    public function subscriptionPlans() { return $this->belongsToMany(ServiceSubscriptionPlan::class, 'service_subscription_plan_procedure')->withPivot('included_uses'); }
}
