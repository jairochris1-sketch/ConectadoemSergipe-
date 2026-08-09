<?php

namespace App\Services;

use App\Models\FeedPost;
use App\Models\FeedPostImage;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FeedModerationService
{
    public function assess(User $user, ?string $body, array $imageHashes): array
    {
        $recentCount = FeedPost::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($user->role !== 'admin' && $recentCount >= config('feed.posts_per_hour', 5)) {
            throw ValidationException::withMessages([
                'body' => 'Você atingiu o limite de publicações desta hora. Aguarde antes de publicar novamente.',
            ]);
        }

        $normalizedBody = mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $body)));
        $contentHash = hash('sha256', $normalizedBody.'|'.implode('|', $imageHashes));

        if (FeedPost::where('user_id', $user->id)
            ->where('content_hash', $contentHash)
            ->where('created_at', '>=', now()->subDay())
            ->exists()) {
            throw ValidationException::withMessages(['body' => 'Esta publicação já foi enviada recentemente.']);
        }

        $signals = [];
        if ($user->role !== 'admin' && preg_match_all('/https?:\/\//i', (string) $body) > 2) {
            $signals[] = 'Muitos links no texto';
        }

        if ($imageHashes && FeedPostImage::whereIn('file_hash', $imageHashes)
            ->whereHas('post', fn ($query) => $query->where('user_id', $user->id)->where('created_at', '>=', now()->subDays(30)))
            ->exists()) {
            $signals[] = 'Imagem repetida recentemente';
        }

        $requiresImageReview = $user->role !== 'admin'
            && $imageHashes !== []
            && config('feed.image_moderation_driver') === 'manual';
        if ($requiresImageReview) {
            $signals[] = 'Imagem aguardando análise administrativa';
        }

        return [
            'content_hash' => $contentHash,
            'status' => ($signals === []) ? 'published' : 'pending',
            'reason' => $signals ? implode('; ', $signals) : null,
        ];
    }
}
