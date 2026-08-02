<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportNotification extends Model
{
    protected $fillable = ['user_id', 'report_id', 'kind', 'message', 'action_url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public static function sendTo(int $userId, array $attributes): ?self
    {
        $user = User::query()->find($userId);

        if (! $user || ! $user->notifications_enabled) {
            return null;
        }

        $preference = match ($attributes['kind'] ?? '') {
            'message_received' => 'notification_messages_enabled',
            'review_received', 'review_replied' => 'notification_reviews_enabled',
            'change_request', 'report_result' => 'notification_reports_enabled',
            default => null,
        };

        if ($preference && ! $user->{$preference}) {
            return null;
        }

        return self::create(['user_id' => $userId] + $attributes);
    }
}
