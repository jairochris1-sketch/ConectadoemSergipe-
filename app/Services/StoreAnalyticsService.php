<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class StoreAnalyticsService
{
    private const CONTACT_EVENTS = [
        'whatsapp_click',
        'phone_click',
        'instagram_click',
        'website_click',
    ];

    public function forStore(Store $store, User $owner): array
    {
        $days = $owner->storeAnalyticsPeriodDays();
        $periodStart = now()->startOfDay()->subDays($days - 1);
        $previousStart = $periodStart->copy()->subDays($days);
        $previousEnd = $periodStart->copy()->subSecond();

        $current = $store->events()
            ->where('created_at', '>=', $periodStart)
            ->get(['event_type', 'ad_id', 'created_at']);
        $previous = $store->events()
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->get(['event_type']);

        $views = $current->where('event_type', 'page_view')->count();
        $contacts = $current->whereIn('event_type', self::CONTACT_EVENTS)->count();
        $shares = $current->where('event_type', 'share_click')->count();
        $productClicks = $current->where('event_type', 'product_click')->count();

        return [
            'days' => $days,
            'period_label' => $days === 1 ? 'Hoje' : "Últimos {$days} dias",
            'summary' => [
                'views' => $this->metric($views, $previous->where('event_type', 'page_view')->count()),
                'contacts' => $this->metric($contacts, $previous->whereIn('event_type', self::CONTACT_EVENTS)->count()),
                'product_clicks' => $this->metric($productClicks, $previous->where('event_type', 'product_click')->count()),
                'shares' => $this->metric($shares, $previous->where('event_type', 'share_click')->count()),
            ],
            'conversion_rate' => $views > 0 ? round(($contacts / $views) * 100, 1) : 0,
            'daily_views' => $this->dailyViews($current, $days),
            'contact_breakdown' => [
                ['label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'value' => $current->where('event_type', 'whatsapp_click')->count()],
                ['label' => 'Ligações', 'icon' => 'fa-solid fa-phone', 'value' => $current->where('event_type', 'phone_click')->count()],
                ['label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'value' => $current->where('event_type', 'instagram_click')->count()],
                ['label' => 'Site', 'icon' => 'fa-solid fa-globe', 'value' => $current->where('event_type', 'website_click')->count()],
            ],
            'top_products' => $this->topProducts($store, $current),
        ];
    }

    private function metric(int $current, int $previous): array
    {
        $change = $previous === 0
            ? ($current > 0 ? 100 : 0)
            : round((($current - $previous) / $previous) * 100);

        return [
            'value' => $current,
            'previous' => $previous,
            'change' => $change,
        ];
    }

    private function dailyViews(Collection $events, int $days): array
    {
        $visibleDays = min($days, 30);
        $chartStart = now()->startOfDay()->subDays($visibleDays - 1);
        $viewsByDay = $events
            ->where('event_type', 'page_view')
            ->filter(fn (StoreEvent $event) => $event->created_at->greaterThanOrEqualTo($chartStart))
            ->groupBy(fn (StoreEvent $event) => $event->created_at->format('Y-m-d'))
            ->map->count();

        return collect(range(0, $visibleDays - 1))
            ->map(function (int $offset) use ($chartStart, $viewsByDay) {
                $date = $chartStart->copy()->addDays($offset);

                return [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('d/m'),
                    'value' => (int) ($viewsByDay[$date->format('Y-m-d')] ?? 0),
                ];
            })
            ->all();
    }

    private function topProducts(Store $store, Collection $events): Collection
    {
        $clicks = $events
            ->where('event_type', 'product_click')
            ->whereNotNull('ad_id')
            ->countBy('ad_id')
            ->sortDesc()
            ->take(5);

        if ($clicks->isEmpty()) {
            return collect();
        }

        $products = $store->ads()
            ->whereIn('id', $clicks->keys())
            ->get(['id', 'title', 'slug'])
            ->keyBy('id');

        return $clicks
            ->map(function (int $count, int $adId) use ($products) {
                $product = $products->get($adId);

                return $product ? [
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'clicks' => $count,
                ] : null;
            })
            ->filter()
            ->values();
    }
}
