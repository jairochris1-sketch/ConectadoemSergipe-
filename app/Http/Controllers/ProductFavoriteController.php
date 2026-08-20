<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $result = DB::transaction(function () use ($request, $product): string {
            $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $favorites = DB::table('favorites')->where('user_id', $request->user()->id);

            if ((clone $favorites)->where('ad_id', $product->id)->exists()) {
                (clone $favorites)->where('ad_id', $product->id)->delete();

                return 'removed';
            }

            if ((clone $favorites)->count() >= AdFavoriteController::MAX_FAVORITES) {
                return 'limit';
            }

            $request->user()->favorites()->attach($product->id, ['created_at' => now()]);

            return 'added';
        });

        if ($result === 'limit') {
            return back()->withErrors([
                'favorite' => 'Você atingiu o limite de 20 favoritos. Apague um favorito para salvar um novo.',
            ]);
        }

        return back()->with('success', $result === 'removed'
            ? 'Produto removido dos favoritos.'
            : 'Produto adicionado aos favoritos.');
    }
}
