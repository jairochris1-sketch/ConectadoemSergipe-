<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuestion extends Model
{
    protected $fillable = [
        'ad_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'active',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
