<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Aguardando confirmação',
        'confirmed' => 'Confirmado',
        'preparing' => 'Em preparação',
        'ready' => 'Pronto',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado',
    ];

    protected $fillable = [
        'public_id',
        'user_id',
        'store_id',
        'store_name',
        'store_promotion_id',
        'coupon_code',
        'discount_type',
        'discount_value',
        'status',
        'fulfillment_method',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_city',
        'delivery_neighborhood',
        'delivery_state',
        'delivery_zipcode',
        'notes',
        'subtotal',
        'discount_total',
        'delivery_fee',
        'total',
        'placed_at',
        'stock_deducted_at',
        'stock_restored_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'placed_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'stock_restored_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotion()
    {
        return $this->belongsTo(StorePromotion::class, 'store_promotion_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getFulfillmentLabelAttribute(): string
    {
        return $this->fulfillment_method === 'delivery' ? 'Entrega' : 'Retirada na loja';
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
