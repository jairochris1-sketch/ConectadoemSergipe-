<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'module',
        'icon',
        'color',
        'image',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'parent_id' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Retorna a trilha/árvore completa de categorias ascendentes (ex: Pai > Filho > Neto)
     */
    public function getCategoryTrailAttribute()
    {
        $trail = collect([$this]);
        $current = $this->parent;

        while ($current) {
            $trail->prepend($current);
            $current = $current->parent;
        }

        return $trail;
    }
}
