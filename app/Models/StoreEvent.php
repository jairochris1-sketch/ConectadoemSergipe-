<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreEvent extends Model
{
    public const TYPES = [
        'page_view',
        'whatsapp_click',
        'phone_click',
        'instagram_click',
        'website_click',
        'share_click',
        'product_click',
    ];

    protected $fillable = [
        'store_id',
        'ad_id',
        'user_id',
        'event_type',
        'visitor_hash',
        'occurred_on',
        'metadata',
    ];

    protected $casts = [
        'occurred_on' => 'date',
        'metadata' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
