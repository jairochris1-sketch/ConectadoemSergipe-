<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFinancialEntry extends Model
{
    protected $fillable = ['ad_id', 'type', 'category', 'description', 'amount', 'occurred_on'];
    protected $casts = ['amount' => 'decimal:2', 'occurred_on' => 'date'];
    public function ad() { return $this->belongsTo(Ad::class); }
}
