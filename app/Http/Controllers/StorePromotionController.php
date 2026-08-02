<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StorePromotion;
use App\Services\StoreFollowerNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StorePromotionController extends Controller
{
    public function store(Request $request, Store $store, StoreFollowerNotifier $notifier)
    {
        $this->authorizeOwnership($request, $store);
        $validated = $this->validated($request, $store);

        $promotion = $store->promotions()->create($validated);
        $notifier->promotionPublished($promotion);

        return back()->with('store_success', 'Promoção criada e adicionada à sua loja.');
    }

    public function update(Request $request, Store $store, StorePromotion $promotion, StoreFollowerNotifier $notifier)
    {
        $this->authorizePromotion($request, $store, $promotion);
        $validated = $this->validated($request, $store, $promotion);

        $wasActive = $promotion->active;
        $promotion->update($validated);
        if (! $wasActive && $promotion->active) {
            $notifier->promotionPublished($promotion);
        }

        return back()->with('store_success', 'Promoção atualizada.');
    }

    public function toggle(Request $request, Store $store, StorePromotion $promotion, StoreFollowerNotifier $notifier)
    {
        $this->authorizePromotion($request, $store, $promotion);

        if (! $promotion->active) {
            if ($promotion->ends_at->isPast()) {
                throw ValidationException::withMessages([
                    'promotion' => 'Atualize a data final antes de reativar uma promoção encerrada.',
                ]);
            }

            $this->ensurePromotionLimit($request, $store, $promotion->id);
        }

        $promotion->update(['active' => ! $promotion->active]);
        if ($promotion->active) {
            $notifier->promotionPublished($promotion);
        }

        return back()->with(
            'store_success',
            $promotion->active ? 'Promoção ativada.' : 'Promoção pausada.'
        );
    }

    public function destroy(Request $request, Store $store, StorePromotion $promotion)
    {
        $this->authorizePromotion($request, $store, $promotion);
        $promotion->delete();

        return back()->with('store_success', 'Promoção excluída.');
    }

    private function validated(Request $request, Store $store, ?StorePromotion $promotion = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'coupon_code' => [
                'nullable',
                'string',
                'max:40',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('store_promotions', 'coupon_code')
                    ->where('store_id', $store->id)
                    ->ignore($promotion?->id),
            ],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => [
                'required',
                'numeric',
                'min:0.01',
                Rule::when(
                    $request->input('discount_type') === 'percentage',
                    ['max:100'],
                    ['max:999999.99']
                ),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'terms' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => [
                'required',
                'date',
                'after:now',
                Rule::when($request->filled('starts_at'), ['after:starts_at']),
            ],
            'active' => ['nullable', 'boolean'],
        ], [
            'coupon_code.regex' => 'O código pode conter apenas letras, números, hífen e sublinhado.',
            'coupon_code.unique' => 'Esta loja já possui uma promoção com esse código.',
            'ends_at.after' => 'A data final precisa estar no futuro.',
            'discount_value.max' => $request->input('discount_type') === 'percentage'
                ? 'O desconto percentual não pode ser maior que 100%.'
                : 'O valor informado é maior que o permitido.',
        ]);

        $validated['coupon_code'] = filled($validated['coupon_code'] ?? null)
            ? strtoupper(trim($validated['coupon_code']))
            : null;
        $validated['active'] = $request->boolean('active');

        if ($validated['active']) {
            $this->ensurePromotionLimit($request, $store, $promotion?->id);
        }

        return $validated;
    }

    private function ensurePromotionLimit(Request $request, Store $store, ?int $exceptPromotionId = null): void
    {
        if ($request->user()->canActivateStorePromotion($store, $exceptPromotionId)) {
            return;
        }

        $limit = $request->user()->storePromotionLimit();
        throw ValidationException::withMessages([
            'active' => "Seu plano permite até {$limit} promoção(ões) ativa(s) ao mesmo tempo. Pause uma promoção ou veja os planos.",
        ]);
    }

    private function authorizeOwnership(Request $request, Store $store): void
    {
        abort_unless($store->user_id === $request->user()->id, 403);
    }

    private function authorizePromotion(Request $request, Store $store, StorePromotion $promotion): void
    {
        $this->authorizeOwnership($request, $store);
        abort_unless($promotion->store_id === $store->id, 404);
    }
}
