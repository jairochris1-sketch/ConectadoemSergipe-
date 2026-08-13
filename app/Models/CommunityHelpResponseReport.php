<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityHelpResponseReport extends Model
{
    protected $fillable = [
        'community_help_response_id',
        'reporter_user_id',
        'reviewed_by',
        'reason',
        'details',
        'status',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function response()
    {
        return $this->belongsTo(CommunityHelpResponse::class, 'community_help_response_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
