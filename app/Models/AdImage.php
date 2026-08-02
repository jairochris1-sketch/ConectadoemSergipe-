<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ad_id',
        'image_path',
        'is_main',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
