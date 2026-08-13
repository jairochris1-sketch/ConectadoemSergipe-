<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubscriptionUsage extends Model
{
    protected $fillable = [
        'service_client_subscription_id',
        'service_appointment_id',
        'service_procedure_id',
        'cycle_start',
        'cycle_end',
        'units',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cycle_start' => 'date',
            'cycle_end' => 'date',
            'units' => 'integer',
        ];
    }

    public function subscription()
    {
        return $this->belongsTo(ServiceClientSubscription::class, 'service_client_subscription_id');
    }

    public function appointment()
    {
        return $this->belongsTo(ServiceAppointment::class, 'service_appointment_id');
    }

    public function procedure()
    {
        return $this->belongsTo(ServiceProcedure::class, 'service_procedure_id');
    }
}
