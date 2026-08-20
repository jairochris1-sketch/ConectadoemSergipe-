<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceScheduleBlock extends Model
{
    protected $fillable = ['ad_id', 'service_staff_id', 'starts_at', 'ends_at', 'reason'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function staff()
    {
        return $this->belongsTo(ServiceStaff::class, 'service_staff_id');
    }
}
