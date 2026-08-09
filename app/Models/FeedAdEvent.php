<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedAdEvent extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_key',
        'ad_id',
        'event_type',
        'is_sponsored',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'is_sponsored' => 'boolean',
            'context' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
