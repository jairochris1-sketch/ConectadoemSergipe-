<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\ReportNotification;
use App\Models\Store;
use App\Models\StorePromotion;

class StoreFollowerNotifier
{
    public function productPublished(Ad $product): int
    {
        $product->loadMissing('store');
        if (! $product->store
            || $product->module !== 'products'
            || $product->status !== 'active'
            || ! $product->store->active
            || ! $product->store->isModerationApproved()) {
            return 0;
        }

        return $this->send(
            $product->store,
            'store_product',
            "Novo produto na {$product->store->name}: {$product->title}.",
            route('ad.show', $product->slug)
        );
    }

    public function promotionPublished(StorePromotion $promotion): int
    {
        $promotion->loadMissing('store');
        if (! $promotion->store
            || ! $promotion->store->active
            || ! $promotion->store->isModerationApproved()
            || ! $promotion->store->promotions()->currentlyActive()->whereKey($promotion->id)->exists()) {
            return 0;
        }

        $coupon = $promotion->coupon_code ? " Use o cupom {$promotion->coupon_code}." : '';

        return $this->send(
            $promotion->store,
            'store_promotion',
            "{$promotion->store->name} lançou {$promotion->discount_label}: {$promotion->title}.{$coupon}",
            route('store.show', $promotion->store->slug)
        );
    }

    private function send(Store $store, string $kind, string $message, string $url): int
    {
        $sent = 0;
        $store->followers()
            ->where('users.id', '!=', $store->user_id)
            ->pluck('users.id')
            ->each(function (int $userId) use ($kind, $message, $url, &$sent) {
                if (ReportNotification::sendTo($userId, [
                    'kind' => $kind,
                    'message' => $message,
                    'action_url' => $url,
                ])) {
                    $sent++;
                }
            });

        return $sent;
    }
}
