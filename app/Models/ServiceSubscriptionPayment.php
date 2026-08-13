<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubscriptionPayment extends Model
{
    protected $fillable = [
        'payment_setting_id',
        'service_client_subscription_id',
        'asaas_payment_id',
        'status',
        'billing_type',
        'value',
        'net_value',
        'due_date',
        'paid_at',
        'invoice_url',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'net_value' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function subscription()
    {
        return $this->belongsTo(ServiceClientSubscription::class, 'service_client_subscription_id');
    }

    public function paymentSetting()
    {
        return $this->belongsTo(ServicePaymentSetting::class, 'payment_setting_id');
    }
}
