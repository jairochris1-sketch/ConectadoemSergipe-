<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\ProductQuestion;
use Illuminate\Http\Request;

class ProductQuestionController extends Controller
{
    public function store(Request $request, Ad $product)
    {
        $product->loadMissing('store');
        abort_unless(
            $product->module === 'products'
            && $product->status === 'active'
            && $product->store?->active
            && $product->store->isModerationApproved(),
            404
        );
        abort_if($product->user_id === $request->user()->id, 422, 'O proprietário não pode perguntar no próprio produto.');
        $validated = $request->validate(['question' => ['required', 'string', 'min:5', 'max:1000']]);

        $product->questions()->create([
            'user_id' => $request->user()->id,
            'question' => $validated['question'],
            'active' => true,
        ]);

        return back()->with('success', 'Pergunta enviada para a loja.');
    }

    public function answer(Request $request, ProductQuestion $question)
    {
        $question->loadMissing('product');
        abort_unless(
            $question->product
            && $question->active
            && ($question->product->user_id === $request->user()->id || $request->user()->role === 'admin'),
            403
        );
        $validated = $request->validate(['answer' => ['required', 'string', 'min:2', 'max:2000']]);
        $question->update([
            'answer' => $validated['answer'],
            'answered_by' => $request->user()->id,
            'answered_at' => now(),
        ]);

        return back()->with('success', 'Resposta publicada.');
    }
}
