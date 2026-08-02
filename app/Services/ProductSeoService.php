<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Store;
use Illuminate\Support\Str;

class ProductSeoService
{
    public function forProduct(Store $store, Ad $product, array $reviewData): array
    {
        $canonical = route('store.products.show', [$store, $product]);
        $title = "{$product->title} em {$product->city} | {$store->name}";
        $description = Str::limit(
            "Compre {$product->title} da {$store->name} em {$product->city}. Consulte preço, avaliações e disponibilidade.",
            155,
            ''
        );
        $image = $product->card_image ? asset($product->card_image) : asset('images/logo.png');
        $offer = [
            '@type' => 'Offer',
            'url' => $canonical,
            'priceCurrency' => 'BRL',
            'price' => number_format($product->effective_price, 2, '.', ''),
            'availability' => $product->is_out_of_stock
                ? 'https://schema.org/OutOfStock'
                : 'https://schema.org/InStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => [
                '@type' => 'Organization',
                'name' => $store->name,
            ],
        ];
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => Str::limit(strip_tags($product->description), 500),
            'image' => $product->images->pluck('image_path')->map(fn ($path) => asset($path))->prepend($image)->unique()->values()->all(),
            'sku' => $product->sku ?: 'CES-'.$product->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => $store->name,
            ],
            'offers' => $offer,
        ];
        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Lojas',
                    'item' => route('stores.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $store->name,
                    'item' => route('store.show', $store->slug),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $product->title,
                    'item' => $canonical,
                ],
            ],
        ];

        if (($reviewData['count'] ?? 0) > 0) {
            $jsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $reviewData['average'],
                'reviewCount' => $reviewData['count'],
            ];
        }

        return compact('canonical', 'title', 'description', 'image', 'jsonLd', 'breadcrumbJsonLd');
    }
}
