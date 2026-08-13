<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommunityHelpRequest extends Model
{
    public const CATEGORIES = [
        'service' => 'Serviço ou profissional',
        'job' => 'Emprego e oportunidade',
        'donation' => 'Doação e solidariedade',
        'lost_pet' => 'Animal perdido ou encontrado',
        'transport' => 'Transporte e deslocamento',
        'information' => 'Informação local',
        'urban_issue' => 'Problema no bairro',
        'other' => 'Outra necessidade',
    ];

    public const URGENCIES = [
        'normal' => 'Sem pressa',
        'today' => 'Preciso hoje',
        'urgent' => 'Urgente',
    ];

    public const PUBLIC_STATUSES = ['open', 'in_progress', 'resolved'];

    protected $fillable = [
        'public_id',
        'user_id',
        'reviewed_by',
        'category',
        'title',
        'description',
        'city',
        'neighborhood',
        'urgency',
        'status',
        'duration_days',
        'expires_at',
        'published_at',
        'reviewed_at',
        'resolved_at',
        'moderation_reason',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function responses()
    {
        return $this->hasMany(CommunityHelpResponse::class);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->whereIn('status', self::PUBLIC_STATUSES)
            ->where(function (Builder $builder) {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this->status, self::PUBLIC_STATUSES, true)
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function canBeViewedBy(?User $user): bool
    {
        return $this->isPubliclyVisible()
            || $user?->id === $this->user_id
            || $user?->role === 'admin';
    }

    public function canBeManagedBy(?User $user): bool
    {
        return $user?->id === $this->user_id || $user?->role === 'admin';
    }
}
