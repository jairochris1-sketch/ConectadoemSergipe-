<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\FeedAdEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeedRecommendationService
{
    public function __construct(private readonly FeedAdInteractionService $interactions)
    {
    }

    public function recommend(Request $request, string $mode = 'for_you', int $limit = 4): Collection
    {
        if (! config('feed.recommendations_enabled', true) || $limit < 1) {
            return collect();
        }

        $mode = in_array($mode, ['for_you', 'recent', 'nearby'], true) ? $mode : 'for_you';
        $user = $request->user();
        $city = trim((string) ($request->input('city')
            ?: $request->session()->get('location_filter.city')
            ?: $user?->city));

        $dismissedAdIds = $this->interactions->forVisitor(
            FeedAdEvent::query()
                ->where('event_type', 'dismiss')
                ->where('created_at', '>=', now()->subDays((int) config('feed.dismissal_days', 90))),
            $request
        )->pluck('ad_id');

        $dailyImpressions = $this->interactions->forVisitor(
            FeedAdEvent::query()
                ->where('event_type', 'impression')
                ->where('created_at', '>=', now()->startOfDay()),
            $request
        )->selectRaw('ad_id, count(*) as total')
            ->groupBy('ad_id')
            ->pluck('total', 'ad_id');

        $favoriteAds = $user
            ? $user->favorites()->get(['ads.id', 'ads.module', 'ads.category_id'])
            : collect();

        $recentInteractionAds = $this->recentInteractionAds($request);
        $interestModules = $favoriteAds->pluck('module')
            ->merge($recentInteractionAds->pluck('module'))
            ->filter()
            ->countBy();
        $interestCategories = $favoriteAds->pluck('category_id')
            ->merge($recentInteractionAds->pluck('category_id'))
            ->filter()
            ->countBy();
        $sponsoredPlanSlugs = $this->sponsoredPlanSlugs();

        $dailyCap = (int) config('feed.ad_impressions_per_day', 3);
        $candidates = Ad::query()
            ->with(['user:id,name,subscription_plan', 'mainImage', 'category:id,name'])
            ->where('status', 'active')
            ->whereHas('user')
            ->when($user, fn ($query) => $query->where('user_id', '!=', $user->id))
            ->when($dismissedAdIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $dismissedAdIds))
            ->when($mode === 'nearby' && $city !== '', fn ($query) => $query->where('city', $city))
            ->latest()
            ->limit(100)
            ->get()
            ->reject(fn (Ad $ad) => (int) ($dailyImpressions[$ad->id] ?? 0) >= $dailyCap)
            ->map(function (Ad $ad) use ($city, $dailyImpressions, $interestModules, $interestCategories, $mode, $sponsoredPlanSlugs) {
                $isSponsored = $sponsoredPlanSlugs->contains($ad->user?->subscription_plan);
                $sameCity = $city !== '' && mb_strtolower((string) $ad->city) === mb_strtolower($city);
                $moduleInterest = (int) ($interestModules[$ad->module] ?? 0);
                $categoryInterest = (int) ($interestCategories[$ad->category_id] ?? 0);
                $ageInDays = max(0, $ad->created_at?->diffInDays(now()) ?? 365);
                $frequencyPenalty = (int) ($dailyImpressions[$ad->id] ?? 0) * 12;

                $score = match ($mode) {
                    'recent' => max(0, 40 - ($ageInDays * 2)),
                    'nearby' => $sameCity ? 45 : 0,
                    default => min(25, ($moduleInterest * 7) + ($categoryInterest * 10)),
                };

                $score += $sameCity ? 20 : 0;
                $score += $isSponsored ? 15 : 0;
                $score += $ad->card_image ? 7 : 0;
                $score += mb_strlen((string) $ad->description) >= 80 ? 4 : 0;
                $score += max(0, 12 - ($ageInDays / 3));
                $score += min(8, log10(max(1, (int) $ad->views) + 1) * 3);
                $score -= $frequencyPenalty;

                $reason = match (true) {
                    $mode === 'recent' => 'Publicado recentemente em Sergipe',
                    $sameCity => 'Perto de você em '.$ad->city,
                    $moduleInterest > 0 || $categoryInterest > 0 => 'Com base nos seus interesses',
                    $isSponsored => 'Anúncio patrocinado relevante',
                    default => 'Descoberta em Sergipe',
                };

                $ad->setAttribute('feed_score', round($score, 2));
                $ad->setAttribute('feed_is_sponsored', $isSponsored);
                $ad->setAttribute('feed_reason', $reason);

                return $ad;
            })
            ->sortByDesc('feed_score')
            ->values();

        return $this->balancedSelection($candidates, $limit);
    }

    private function recentInteractionAds(Request $request): Collection
    {
        $events = $this->interactions->forVisitor(
            FeedAdEvent::query()
                ->whereIn('event_type', ['click', 'impression'])
                ->where('created_at', '>=', now()->subDays(30)),
            $request
        )->latest()->limit(40)->pluck('ad_id');

        if ($events->isEmpty()) {
            return collect();
        }

        return Ad::query()
            ->whereIn('id', $events)
            ->get(['id', 'module', 'category_id']);
    }

    private function sponsoredPlanSlugs(): Collection
    {
        return DB::table('plans')
            ->join('plan_feature_values', 'plan_feature_values.plan_id', '=', 'plans.id')
            ->join('plan_features', 'plan_features.id', '=', 'plan_feature_values.plan_feature_id')
            ->where('plans.is_active', true)
            ->where('plan_features.key', 'feed_sponsored')
            ->where(function ($query) {
                $query->where('plan_feature_values.value', '1')
                    ->orWhereNull('plan_feature_values.value');
            })
            ->pluck('plans.slug');
    }

    private function balancedSelection(Collection $candidates, int $limit): Collection
    {
        $sponsored = $candidates->where('feed_is_sponsored', true)->values();
        $organic = $candidates->where('feed_is_sponsored', false)->values();
        $selected = collect();
        $ownerIds = collect();

        while ($selected->count() < $limit && ($sponsored->isNotEmpty() || $organic->isNotEmpty())) {
            foreach ([$organic, $sponsored] as $pool) {
                $candidate = $pool->first(fn (Ad $ad) => ! $ownerIds->contains($ad->user_id));
                $candidate ??= $pool->first();

                if (! $candidate || $selected->contains('id', $candidate->id)) {
                    continue;
                }

                $selected->push($candidate);
                $ownerIds->push($candidate->user_id);

                if ($selected->count() >= $limit) {
                    break 2;
                }
            }

            $sponsored = $sponsored->reject(fn (Ad $ad) => $selected->contains('id', $ad->id))->values();
            $organic = $organic->reject(fn (Ad $ad) => $selected->contains('id', $ad->id))->values();
        }

        return $selected->values();
    }
}
