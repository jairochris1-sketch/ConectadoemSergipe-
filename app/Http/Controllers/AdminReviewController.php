<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'reported');
        $query = Review::with(['user', 'ad.user', 'store.user', 'reports.reporter']);

        if ($status === 'reported') {
            $query->whereHas('reports', fn ($reportQuery) => $reportQuery->where('status', 'pending'));
        } elseif (in_array($status, ['approved', 'hidden'], true)) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();
        $pendingReportsCount = ReviewReport::where('status', 'pending')->count();

        return view('admin.reviews.index', compact('reviews', 'status', 'pendingReportsCount'));
    }

    public function action(Request $request, Review $review)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['hide', 'approve', 'dismiss_reports'])],
        ]);

        if ($validated['action'] === 'hide') {
            $review->update(['status' => 'hidden']);
            $this->finishReports($review, 'actioned', $request->user()->id);
            $message = 'Avaliação ocultada por violação das regras.';
        } elseif ($validated['action'] === 'approve') {
            $review->update(['status' => 'approved']);
            $this->finishReports($review, 'dismissed', $request->user()->id);
            $message = 'Avaliação aprovada e visível novamente.';
        } else {
            $this->finishReports($review, 'dismissed', $request->user()->id);
            $message = 'Denúncias arquivadas sem ocultar a avaliação.';
        }

        return back()->with('success', $message);
    }

    private function finishReports(Review $review, string $status, int $adminId): void
    {
        $review->reports()->where('status', 'pending')->update([
            'status' => $status,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);
    }
}
