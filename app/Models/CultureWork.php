<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CultureWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_path',
        'category',
        'theme',
        'external_url',
        'embed_media_url',
        'status',
        'version',
        'ad_id',
        'views_count',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($work) {
            if (empty($work->slug)) {
                $work->slug = Str::slug($work->title) . '-' . Str::random(5);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'cordel' => 'Cordel',
            'literatura' => 'Literatura',
            'musica' => 'Música / Áudio',
            'arte_visual' => 'Artes Visuais',
            default => 'Obra Cultural',
        };
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'cordel' => 'bg-warning text-dark',
            'literatura' => 'bg-primary text-white',
            'musica' => 'bg-success text-white',
            'arte_visual' => 'bg-purple text-white',
            default => 'bg-secondary text-white',
        };
    }
}
