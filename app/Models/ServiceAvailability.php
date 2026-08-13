<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAvailability extends Model
{
    protected $fillable = ['service_staff_id', 'day_of_week', 'starts_at', 'ends_at'];
    protected $casts = ['day_of_week' => 'integer'];
    public function staff() { return $this->belongsTo(ServiceStaff::class, 'service_staff_id'); }
}
