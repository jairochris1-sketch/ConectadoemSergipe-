<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePaymentWebhookEvent extends Model
{
    protected $fillable = ['payment_setting_id', 'event_id', 'event_type', 'resource_id', 'processed_at'];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }
}
