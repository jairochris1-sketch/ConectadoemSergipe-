<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'ad_id',
        'store_id',
        'user_id',
        'rating',
        'comment',
        'professional_reply',
        'professional_reply_user_id',
        'professional_replied_at',
        'professional_reply_edited_at',
        'image_paths',
        'content_hash',
        'status',
        'ip_address',
        'user_agent',
        'abuse_fingerprint',
        'edited_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'image_paths' => 'array',
        'edited_at' => 'datetime',
        'professional_replied_at' => 'datetime',
        'professional_reply_edited_at' => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(ReviewReport::class);
    }

    public function professionalReplyUser()
    {
        return $this->belongsTo(User::class, 'professional_reply_user_id');
    }
}
