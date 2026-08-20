<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportCannedResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'shortcut',
        'title',
        'content',
    ];
}
