<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceStaff extends Model
{
    protected $table = 'service_staff';
    protected $fillable = ['ad_id', 'name', 'active'];
    protected $casts = ['active' => 'boolean'];
    public function ad() { return $this->belongsTo(Ad::class); }
    public function procedures() { return $this->belongsToMany(ServiceProcedure::class, 'service_staff_procedure'); }
    public function availabilities() { return $this->hasMany(ServiceAvailability::class); }
    public function scheduleBlocks() { return $this->hasMany(ServiceScheduleBlock::class); }
}
