<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class ProductFavoriteController extends Controller
{
    public function toggle(Request $request, Ad $product)
    {
        $product->loadMissing('store');
        abort_unless(
            $product->module === 'products'
            && $product->status === 'active'
            && $product->store?->active
            && $product->store->isModerationApproved(),
            404
        );
        $attached = $request->user()->favorites()->whereKey($product->id)->exists();

        $attached
            ? $request->user()->favorites()->detach($product->id)
            : $request->user()->favorites()->attach($product->id, ['created_at' => now()]);

        return back()->with('success', $attached
            ? 'Produto removido dos favoritos.'
            : 'Produto adicionado aos favoritos.');
    }
}
