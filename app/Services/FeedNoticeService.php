<?php

namespace App\Services;

use App\Models\FeedPost;
use App\Models\ReportNotification;
use App\Models\User;

class FeedNoticeService
{
    public function notifyMembers(FeedPost $post): void
    {
        $now = now();
        $notification = match ($post->type) {
            'notice' => ['kind' => 'community_notice', 'message' => 'Novo aviso: '.$post->title],
            'poll' => ['kind' => 'community_poll', 'message' => 'Nova enquete: '.$post->title],
            default => ['kind' => 'community_update', 'message' => 'Nova atualização na Comunidade Sergipana.'],
        };
        User::query()
            ->where('id', '!=', $post->user_id)
            ->where('notifications_enabled', true)
            ->select('id')
            ->chunkById(500, function ($users) use ($post, $now, $notification) {
                ReportNotification::insert($users->map(fn ($user) => [
                    'user_id' => $user->id,
                    'report_id' => null,
                    'kind' => $notification['kind'],
                    'message' => $notification['message'],
                    'action_url' => route('feed.index', absolute: false).'#publicacao-'.$post->id,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });
    }
}
