<?php

namespace App\Http\Controllers;

use App\Models\FeedPost;
use App\Services\FeedNoticeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminFeedController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array((string) $request->query('status'), ['pending', 'reported', 'published', 'rejected', 'hidden'], true)
            ? (string) $request->query('status')
            : 'pending';
        $posts = FeedPost::with(['user', 'images', 'reports.reporter'])
            ->withCount(['likes', 'comments', 'reports'])
            ->when($status === 'reported', fn ($query) => $query
                ->whereHas('reports', fn ($reports) => $reports->where('status', 'pending')))
            ->when(in_array($status, ['pending', 'published', 'rejected', 'hidden'], true), fn ($query) => $query->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.feed.index', compact('posts', 'status'));
    }

    public function action(Request $request, FeedPost $post, FeedNoticeService $notices)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'hide', 'dismiss_reports'])],
            'moderation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'dismiss_reports') {
            $post->reports()->where('status', 'pending')->update(['status' => 'dismissed', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
            return back()->with('success', 'Denúncias arquivadas.');
        }

        $wasPublished = $post->status === 'published';
        $status = ['approve' => 'published', 'reject' => 'rejected', 'hide' => 'hidden'][$validated['action']];
        $post->update([
            'status' => $status,
            'moderation_reason' => $validated['moderation_reason'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'published_at' => $status === 'published' ? ($post->published_at ?? now()) : $post->published_at,
        ]);
        $post->images()->update(['moderation_status' => $status === 'published' ? 'approved' : 'rejected']);
        $post->reports()->where('status', 'pending')->update(['status' => 'actioned', 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        if (! $wasPublished && $status === 'published') {
            $notices->notifyMembers($post);
        }

        return back()->with('success', 'Moderação da publicação atualizada.');
    }
}
