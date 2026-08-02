<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\ReportNotification;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\Store;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Ad $ad)
    {
        abort_unless($ad->module === 'services', 404);

        $user = $request->user();

        if ($ad->user_id === $user->id) {
            return back()->withErrors(['review' => 'Você não pode avaliar o próprio anúncio ou perfil.']);
        }

        if (Review::where('ad_id', $ad->id)->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['review' => 'Você já avaliou este anúncio ou perfil. Edite a avaliação existente.']);
        }

        if (Review::where('user_id', $user->id)->where('created_at', '>=', now()->subMinutes(10))->count() >= 3) {
            return back()->withErrors(['review' => 'Muitas avaliações foram enviadas em pouco tempo. Tente novamente mais tarde.']);
        }

        $validated = $this->validateReview($request);
        $contentHash = $this->contentHash($validated['comment']);

        if (Review::where('user_id', $user->id)->where('content_hash', $contentHash)->exists()) {
            return back()->withErrors(['comment' => 'Você já publicou este mesmo texto em outra avaliação.']);
        }

        $imagePaths = $this->storeImages($request->file('review_images', []));

        $review = Review::create([
            'ad_id' => $ad->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => trim($validated['comment']),
            'image_paths' => $imagePaths,
            'content_hash' => $contentHash,
            'status' => 'approved',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'abuse_fingerprint' => hash('sha256', $request->ip().'|'.$request->userAgent()),
        ]);

        ReportNotification::sendTo($ad->user_id, [
            'kind' => 'review_received',
            'message' => $user->name.' publicou uma nova avaliação no seu perfil profissional.',
            'action_url' => route('provider.show', $ad->slug, false).'#avaliacao-'.$review->id,
        ]);

        return back()->with('review_success', 'Sua avaliação foi publicada.');
    }

    public function storeReview(Request $request, Store $store)
    {
        abort_unless($store->active, 404);

        $user = $request->user();

        if ($store->user_id === $user->id) {
            return back()->withErrors(['review' => 'Você não pode avaliar a própria loja.']);
        }

        if (Review::where('store_id', $store->id)->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['review' => 'Você já avaliou esta loja. Edite a avaliação existente.']);
        }

        if (Review::where('user_id', $user->id)->where('created_at', '>=', now()->subMinutes(10))->count() >= 3) {
            return back()->withErrors(['review' => 'Muitas avaliações foram enviadas em pouco tempo. Tente novamente mais tarde.']);
        }

        $validated = $this->validateReview($request);
        $contentHash = $this->contentHash($validated['comment']);

        if (Review::where('user_id', $user->id)->where('content_hash', $contentHash)->exists()) {
            return back()->withErrors(['comment' => 'Você já publicou este mesmo texto em outra avaliação.']);
        }

        $review = Review::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => trim($validated['comment']),
            'image_paths' => $this->storeImages($request->file('review_images', [])),
            'content_hash' => $contentHash,
            'status' => 'approved',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'abuse_fingerprint' => hash('sha256', $request->ip().'|'.$request->userAgent()),
        ]);

        ReportNotification::sendTo($store->user_id, [
            'kind' => 'review_received',
            'message' => $user->name.' publicou uma nova avaliação na sua loja.',
            'action_url' => route('store.show', $store->slug, false).'#avaliacao-'.$review->id,
        ]);

        return back()->with('review_success', 'Sua avaliação da loja foi publicada.');
    }

    public function update(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $validated = $this->validateReview($request);
        $contentHash = $this->contentHash($validated['comment']);

        if (Review::where('user_id', $request->user()->id)
            ->where('content_hash', $contentHash)
            ->whereKeyNot($review->id)
            ->exists()) {
            return back()->withErrors(['comment' => 'Você já publicou este mesmo texto em outra avaliação.']);
        }

        $existingPaths = collect($review->image_paths ?? []);
        $pathsToRemove = collect($request->input('remove_review_images', []))
            ->filter(fn ($path) => $existingPaths->contains($path));
        $remainingPaths = $existingPaths->diff($pathsToRemove)->values();
        $newFiles = $request->file('review_images', []);

        if ($remainingPaths->count() + count($newFiles) > 3) {
            throw ValidationException::withMessages([
                'review_images' => 'A avaliação pode ter no máximo 3 imagens.',
            ]);
        }

        $newPaths = collect($this->storeImages($newFiles));
        $pathsToRemove->each(fn ($path) => $this->deleteImage($path));

        $review->update([
            'rating' => $validated['rating'],
            'comment' => trim($validated['comment']),
            'image_paths' => $remainingPaths->merge($newPaths)->values()->all(),
            'content_hash' => $contentHash,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'abuse_fingerprint' => hash('sha256', $request->ip().'|'.$request->userAgent()),
            'edited_at' => now(),
        ]);

        return back()->with('review_success', 'Sua avaliação foi atualizada.');
    }

    public function destroy(Request $request, Review $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        foreach ($review->image_paths ?? [] as $path) {
            $this->deleteImage($path);
        }

        $review->delete();

        return back()->with('review_success', 'Sua avaliação foi excluída.');
    }

    public function storeProfessionalReply(Request $request, Review $review)
    {
        $this->authorizeProfessionalReply($request, $review);
        abort_if($review->professional_reply, 422, 'Esta avaliação já possui uma resposta.');

        $validated = $this->validateProfessionalReply($request);

        $review->update([
            'professional_reply' => trim($validated['reply']),
            'professional_reply_user_id' => $request->user()->id,
            'professional_replied_at' => now(),
            'professional_reply_edited_at' => null,
        ]);

        ReportNotification::sendTo($review->user_id, [
            'kind' => 'review_replied',
            'message' => $request->user()->name.' respondeu à sua avaliação.',
            'action_url' => $this->reviewDestination($review, '#resposta-avaliacao-'.$review->id),
        ]);

        return back()->with('review_success', 'Sua resposta foi publicada.');
    }

    public function updateProfessionalReply(Request $request, Review $review)
    {
        $this->authorizeProfessionalReply($request, $review);
        abort_unless($review->professional_reply, 404);

        $validated = $this->validateProfessionalReply($request);

        $review->update([
            'professional_reply' => trim($validated['reply']),
            'professional_reply_user_id' => $request->user()->id,
            'professional_reply_edited_at' => now(),
        ]);

        return back()->with('review_success', 'Sua resposta foi atualizada.');
    }

    public function destroyProfessionalReply(Request $request, Review $review)
    {
        $this->authorizeProfessionalReply($request, $review);

        $review->update([
            'professional_reply' => null,
            'professional_reply_user_id' => null,
            'professional_replied_at' => null,
            'professional_reply_edited_at' => null,
        ]);

        return back()->with('review_success', 'Sua resposta foi excluída.');
    }

    public function report(Request $request, Review $review)
    {
        $review->loadMissing(['ad', 'store']);
        abort_unless(
            $this->reviewOwnerId($review) === $request->user()->id || $request->user()->role === 'admin',
            403
        );

        if (ReviewReport::where('review_id', $review->id)->where('reporter_user_id', $request->user()->id)->exists()) {
            return back()->withErrors(['review_report' => 'Esta avaliação já foi denunciada por você.']);
        }

        $validated = $request->validate([
            'reason' => ['required', Rule::in(array_keys(ReviewReport::REASONS))],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        ReviewReport::create([
            'review_id' => $review->id,
            'reporter_user_id' => $request->user()->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('review_success', 'Avaliação enviada para análise administrativa.');
    }

    private function validateReview(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'review_images' => ['nullable', 'array', 'max:3'],
            'review_images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'remove_review_images' => ['nullable', 'array'],
            'remove_review_images.*' => ['string'],
        ]);
    }

    private function authorizeProfessionalReply(Request $request, Review $review): void
    {
        $review->loadMissing(['ad', 'store']);
        abort_unless(
            $review->status === 'approved' && $this->reviewOwnerId($review) === $request->user()->id,
            403
        );
    }

    private function reviewOwnerId(Review $review): ?int
    {
        return $review->ad?->user_id ?? $review->store?->user_id;
    }

    private function reviewDestination(Review $review, string $fragment): string
    {
        $review->loadMissing(['ad', 'store']);

        if ($review->store) {
            return route('store.show', $review->store->slug, false).$fragment;
        }

        return route('provider.show', $review->ad->slug, false).$fragment;
    }

    private function validateProfessionalReply(Request $request): array
    {
        return $request->validate([
            'reply' => ['required', 'string', 'min:2', 'max:1500'],
        ]);
    }

    private function contentHash(string $comment): string
    {
        return hash('sha256', Str::lower(Str::ascii(Str::squish($comment))));
    }

    private function storeImages(array $images): array
    {
        return collect($images)
            ->map(fn ($image) => ImageOptimizer::convertToWebp($image, 'review'))
            ->filter()
            ->values()
            ->all();
    }

    private function deleteImage(string $path): void
    {
        $relative = ltrim($path, '/\\');
        if (str_starts_with($relative, 'uploads/')) {
            File::delete(public_path($relative));
        }
    }
}
