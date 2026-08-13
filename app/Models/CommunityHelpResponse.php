<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityHelpResponse extends Model
{
    protected $fillable = [
        'community_help_request_id',
        'user_id',
        'message',
        'status',
        'is_selected',
        'moderation_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['is_selected' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function helpRequest()
    {
        return $this->belongsTo(CommunityHelpRequest::class, 'community_help_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reports()
    {
        return $this->hasMany(CommunityHelpResponseReport::class, 'community_help_response_id');
    }
}
