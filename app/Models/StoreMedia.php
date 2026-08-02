<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreMedia extends Model
{
    protected $table = 'store_media';

    protected $fillable = [
        'store_id',
        'type',
        'path',
        'sort_order',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
