<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request, CartService $cart)
    {
        return view('cart.index', ['cart' => $cart->summary($request)]);
    }

    public function add(Request $request, Ad $product, CartService $cart)
    {
        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'variation_id' => ['nullable', 'integer'],
            'addon_ids' => ['nullable', 'array', 'max:30'],
            'addon_ids.*' => ['integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $cart->add(
            $request,
            $product,
            (int) ($validated['quantity'] ?? 1),
            isset($validated['variation_id']) ? (int) $validated['variation_id'] : null,
            $validated['addon_ids'] ?? [],
            $validated['note'] ?? null
        );

        return redirect()
            ->route($request->boolean('buy_now') ? 'checkout.index' : 'cart.index')
            ->with('success', 'Produto adicionado ao carrinho.');
    }

    public function update(Request $request, Ad $product, CartService $cart)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'line_key' => ['nullable', 'string', 'max:80'],
        ]);
        $cart->update($request, $product, (int) $validated['quantity'], $validated['line_key'] ?? null);

        return back()->with('success', 'Quantidade atualizada.');
    }

    public function remove(Request $request, Ad $product, CartService $cart)
    {
        $validated = $request->validate(['line_key' => ['nullable', 'string', 'max:80']]);
        $cart->remove($request, $product, $validated['line_key'] ?? null);

        return back()->with('success', 'Produto removido do carrinho.');
    }

    public function clear(Request $request, CartService $cart)
    {
        $cart->clear($request);

        return redirect()->route('cart.index')->with('success', 'Carrinho esvaziado.');
    }

    public function applyCoupon(Request $request, CartService $cart)
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);
        $promotion = $cart->applyCoupon($request, $validated['coupon_code']);

        return back()->with('success', "Cupom {$promotion->coupon_code} aplicado.");
    }

    public function removeCoupon(Request $request, CartService $cart)
    {
        $cart->removeCoupon($request);

        return back()->with('success', 'Cupom removido.');
    }
}
