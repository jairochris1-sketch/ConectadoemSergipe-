<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteFolder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ads()
    {
        return $this->belongsToMany(Ad::class, 'favorites', 'folder_id', 'ad_id')
            ->withPivot(['user_id', 'created_at']);
    }
}
