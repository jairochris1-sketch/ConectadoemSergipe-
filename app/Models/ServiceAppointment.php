<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceAppointment extends Model
{
    public const STATUSES = ['pending' => 'Pendente', 'confirmed' => 'Confirmado', 'completed' => 'Concluído', 'cancelled' => 'Cancelado'];
    protected $fillable = ['ad_id', 'service_procedure_id', 'service_staff_id', 'customer_user_id', 'service_client_subscription_id', 'customer_name', 'customer_phone', 'starts_at', 'ends_at', 'service_price', 'status', 'notes'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'service_price' => 'decimal:2'];
    public function ad() { return $this->belongsTo(Ad::class); }
    public function procedure() { return $this->belongsTo(ServiceProcedure::class, 'service_procedure_id'); }
    public function staff() { return $this->belongsTo(ServiceStaff::class, 'service_staff_id'); }
    public function customer() { return $this->belongsTo(User::class, 'customer_user_id'); }
    public function clientSubscription() { return $this->belongsTo(ServiceClientSubscription::class, 'service_client_subscription_id'); }
    public function subscriptionUsage() { return $this->hasOne(ServiceSubscriptionUsage::class, 'service_appointment_id'); }
    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status] ?? ucfirst($this->status); }
}
