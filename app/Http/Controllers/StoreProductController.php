<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Store;
use App\Services\ProductSalesService;
use App\Services\ProductSeoService;
use App\Services\ReviewDisplayService;
use Illuminate\Http\Request;

class StoreProductController extends Controller
{
    public function show(
        Request $request,
        Store $store,
        Ad $product,
        ReviewDisplayService $reviews,
        ProductSeoService $seo,
        ProductSalesService $sales
    ) {
        abort_unless($store->active && $store->isModerationApproved(), 404);
        abort_unless(
            $product->store_id === $store->id
            && $product->module === 'products'
            && $product->status === 'active',
            404
        );

        $product->loadMissing([
            'store.user',
            'user',
            'images',
            'mainImage',
            'category',
            'activeVariations',
            'activeAddons',
            'questions.user',
            'questions.respondent',
        ]);
        if ($request->user()?->id !== $product->user_id) {
            $product->increment('views');
        }

        $reviewData = $reviews->forAd($product, $request->query('reviews_sort'));
        $relatedProducts = $store->products()
            ->with(['mainImage', 'store'])
            ->where('status', 'active')
            ->whereKeyNot($product->id)
            ->latest()
            ->take(4)
            ->get();
        $seoData = $seo->forProduct($store, $product, $reviewData);
        $isFavorite = $request->user()
            ? $request->user()->favorites()->whereKey($product->id)->exists()
            : false;
        $videoEmbedUrl = $this->videoEmbedUrl($product->video_url);
        $salesCount = $sales->confirmedQuantity($product);
        $businessStatus = $store->businessStatus();

        return view('store-products.show', compact(
            'store',
            'product',
            'reviewData',
            'relatedProducts',
            'seoData',
            'isFavorite',
            'videoEmbedUrl',
            'salesCount',
            'businessStatus'
        ));
    }

    private function videoEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        if (str_contains($host, 'youtu.be')) {
            return 'https://www.youtube-nocookie.com/embed/'.rawurlencode($path);
        }
        if (str_contains($host, 'youtube.com')) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            if (filled($query['v'] ?? null)) {
                return 'https://www.youtube-nocookie.com/embed/'.rawurlencode($query['v']);
            }
        }

        return null;
    }
}
