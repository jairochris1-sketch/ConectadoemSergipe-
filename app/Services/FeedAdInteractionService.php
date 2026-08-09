<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\FeedAdEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeedAdInteractionService
{
    public function visitorKey(Request $request): string
    {
        return hash_hmac('sha256', $request->session()->getId(), (string) config('app.key'));
    }

    public function forVisitor(Builder $query, Request $request): Builder
    {
        if ($request->user()) {
            return $query->where('user_id', $request->user()->id);
        }

        return $query
            ->whereNull('user_id')
            ->where('visitor_key', $this->visitorKey($request));
    }

    public function track(Request $request, Ad $ad, string $eventType, array $context = []): FeedAdEvent
    {
        $this->pruneExpiredEvents();

        $deduplicationWindow = match ($eventType) {
            'impression' => now()->subHours(6),
            'click' => now()->subMinutes(5),
            'dismiss' => now()->subDays((int) config('feed.dismissal_days', 90)),
            default => now()->subMinute(),
        };

        $existing = $this->forVisitor(
            FeedAdEvent::query()
                ->where('ad_id', $ad->id)
                ->where('event_type', $eventType)
                ->where('created_at', '>=', $deduplicationWindow),
            $request
        )->latest()->first();

        if ($existing) {
            return $existing;
        }

        return FeedAdEvent::create([
            'user_id' => $request->user()?->id,
            'visitor_key' => $request->user() ? null : $this->visitorKey($request),
            'ad_id' => $ad->id,
            'event_type' => $eventType,
            'is_sponsored' => $this->isSponsored($ad),
            'context' => array_filter([
                'mode' => $context['mode'] ?? null,
                'city' => $context['city'] ?? null,
            ]),
        ]);
    }

    public function isSponsored(Ad $ad): bool
    {
        $planSlug = $ad->user?->subscription_plan ?? 'free';

        return DB::table('plans')
            ->join('plan_feature_values', 'plan_feature_values.plan_id', '=', 'plans.id')
            ->join('plan_features', 'plan_features.id', '=', 'plan_feature_values.plan_feature_id')
            ->where('plans.slug', $planSlug)
            ->where('plans.is_active', true)
            ->where('plan_features.key', 'feed_sponsored')
            ->where(function ($query) {
                $query->where('plan_feature_values.value', '1')
                    ->orWhereNull('plan_feature_values.value');
            })
            ->exists();
    }

    private function pruneExpiredEvents(): void
    {
        if (! Cache::add('feed-ad-events-pruned', true, now()->addHour())) {
            return;
        }

        FeedAdEvent::query()
            ->whereNull('user_id')
            ->where('created_at', '<', now()->subDays((int) config('feed.guest_event_retention_days', 30)))
            ->delete();

        FeedAdEvent::query()
            ->whereNotNull('user_id')
            ->where('created_at', '<', now()->subDays((int) config('feed.user_event_retention_days', 90)))
            ->delete();
    }
}
