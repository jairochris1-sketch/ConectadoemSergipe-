<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceClientSubscription extends Model
{
    public const ACTIVE_STATUSES = ['active', 'pending_payment'];

    protected $fillable = [
        'service_subscription_plan_id',
        'ad_id',
        'payment_setting_id',
        'customer_user_id',
        'status',
        'billing_type',
        'terms_snapshot',
        'consented_at',
        'asaas_customer_id',
        'asaas_subscription_id',
        'current_period_start',
        'current_period_end',
        'paid_through',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'current_period_start' => 'date',
            'current_period_end' => 'date',
            'paid_through' => 'date',
            'consented_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscription): void {
            $subscription->public_id ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function plan()
    {
        return $this->belongsTo(ServiceSubscriptionPlan::class, 'service_subscription_plan_id');
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function paymentSetting()
    {
        return $this->belongsTo(ServicePaymentSetting::class, 'payment_setting_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function payments()
    {
        return $this->hasMany(ServiceSubscriptionPayment::class);
    }

    public function usages()
    {
        return $this->hasMany(ServiceSubscriptionUsage::class);
    }

    public function latestInvoiceUrl(): ?string
    {
        return $this->payments()->latest('due_date')->value('invoice_url');
    }
}
