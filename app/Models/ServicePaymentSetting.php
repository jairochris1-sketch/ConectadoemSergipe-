<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServicePaymentSetting extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'environment',
        'api_key',
        'api_key_hint',
        'account_status',
        'verified_at',
        'online_payments_enabled',
        'subscriptions_enabled',
        'webhook_id',
        'webhook_token',
        'webhook_registered_at',
    ];

    protected $hidden = ['api_key', 'webhook_token'];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'webhook_token' => 'encrypted',
            'verified_at' => 'datetime',
            'webhook_registered_at' => 'datetime',
            'online_payments_enabled' => 'boolean',
            'subscriptions_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $setting): void {
            $setting->public_id ??= (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(ServiceClientSubscription::class, 'payment_setting_id');
    }

    public function isReadyForSubscriptions(): bool
    {
        return $this->verified_at !== null
            && $this->webhook_registered_at !== null
            && ($this->environment === 'sandbox' || $this->account_status === 'APPROVED')
            && $this->online_payments_enabled
            && $this->subscriptions_enabled;
    }
}
