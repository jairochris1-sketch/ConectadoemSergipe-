<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Store;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewDisplayService
{
    public function forAd(Ad $ad, ?string $sort = null): array
    {
        return $this->buildReviewData($ad->reviews(), $sort);
    }

    public function forStore(Store $store, ?string $sort = null): array
    {
        return $this->buildReviewData($store->reviews(), $sort);
    }

    private function buildReviewData(HasMany $relation, ?string $sort): array
    {
        $query = (clone $relation)
            ->where('status', 'approved')
            ->with(['user', 'professionalReplyUser']);

        match ($sort) {
            'recent' => $query->latest(),
            'highest' => $query->orderByDesc('rating')->latest(),
            'lowest' => $query->orderBy('rating')->latest(),
            default => $query->orderByDesc('rating')->latest(),
        };

        $reviews = $query->get();
        $count = $reviews->count();
        $average = $count ? round($reviews->avg('rating'), 1) : 0;
        $distribution = collect(range(5, 1))->mapWithKeys(function ($rating) use ($reviews, $count) {
            $ratingCount = $reviews->where('rating', $rating)->count();

            return [$rating => [
                'count' => $ratingCount,
                'percent' => $count ? (int) round(($ratingCount / $count) * 100) : 0,
            ]];
        });
        $userReview = auth()->check()
            ? (clone $relation)->where('user_id', auth()->id())->first()
            : null;

        return compact('reviews', 'count', 'average', 'distribution', 'userReview');
    }
}
