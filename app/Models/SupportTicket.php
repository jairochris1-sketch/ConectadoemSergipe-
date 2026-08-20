<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'department_id',
        'agent_id',
        'subject',
        'status',
        'current_page_url',
        'rating',
        'feedback',
        'started_at',
        'closed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(SupportDepartment::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    public function publicMessages()
    {
        return $this->messages()->where('is_internal_note', false);
    }

    public function getQueuePositionAttribute(): int
    {
        if ($this->status !== 'waiting') {
            return 0;
        }

        return self::where('status', 'waiting')
            ->where('created_at', '<=', $this->created_at)
            ->count();
    }

    public static function generateProtocol(): string
    {
        return 'SE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
