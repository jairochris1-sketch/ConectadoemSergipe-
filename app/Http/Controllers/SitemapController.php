<?php

namespace App\Http\Controllers;

use App\Models\Ad;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $products = Ad::query()
            ->with('store')
            ->where('module', 'products')
            ->where('status', 'active')
            ->whereHas('store', fn ($query) => $query->publiclyVisible())
            ->latest('updated_at')
            ->get();

        return response()
            ->view('sitemap', compact('products'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
