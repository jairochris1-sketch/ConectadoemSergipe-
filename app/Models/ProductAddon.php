<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAddon extends Model
{
    protected $fillable = ['ad_id', 'name', 'price', 'active'];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
}
