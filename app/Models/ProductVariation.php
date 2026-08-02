<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $fillable = [
        'ad_id',
        'name',
        'attributes',
        'sku',
        'price',
        'price_adjustment',
        'stock_quantity',
        'track_stock',
        'image',
        'active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'price_adjustment' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_stock' => 'boolean',
        'active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Ad::class, 'ad_id');
    }
}
