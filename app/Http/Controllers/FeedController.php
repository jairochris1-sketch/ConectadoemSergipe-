<?php

namespace App\Http\Controllers;

use App\Core\SergipeCities;
use App\Models\Ad;
use App\Models\FeedComment;
use App\Models\FeedPost;
use App\Models\FeedPostReport;
use App\Models\FeedPollVote;
use App\Models\User;
use App\Services\FeedModerationService;
use App\Services\FeedNoticeService;
use App\Services\FeedAdInteractionService;
use App\Services\FeedRecommendationService;
use App\Services\ImageOptimizer;
use App\Services\VideoDurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FeedController extends Controller
{
    private const TOPICS = ['urgent', 'important', 'informative', 'updates', 'security', 'culture'];

    public function index(Request $request, FeedRecommendationService $recommendations)
    {
        $search = trim((string) $request->input('q', ''));
        $feedMode = in_array($request->input('mode'), ['for_you', 'recent', 'nearby'], true)
            ? $request->input('mode')
            : 'for_you';
        $isAdmin = $request->user()?->role === 'admin';
        $sharedPostId = $request->filled('post') && ctype_digit((string) $request->input('post'))
            ? (int) $request->input('post')
            : null;

        $posts = FeedPost::with([
                'user', 'images', 'pollOptions' => function ($query) use ($isAdmin) {
                    $query->withCount('votes');
                    if ($isAdmin) {
                        $query->with(['votes.user:id,name,username,role']);
                    }
                },
                'comments' => fn ($query) => $query->where('status', 'published')->with('user')->latest()->limit(3),
            ])
            ->withCount(['likes', 'comments' => fn ($query) => $query->where('status', 'published')])
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('city'), fn ($query) => $query->where('city', $request->string('city')))
            ->when($sharedPostId, fn ($query) => $query->orderByRaw('CASE WHEN feed_posts.id = ? THEN 0 ELSE 1 END', [$sharedPostId]))
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest('published_at')
            ->simplePaginate(10)
            ->withQueryString();

        $likedPostIds = auth()->check()
            ? DB::table('feed_post_likes')->where('user_id', auth()->id())->pluck('feed_post_id')->all()
            : [];
        $votedOptions = auth()->check()
            ? FeedPollVote::where('user_id', auth()->id())->pluck('feed_poll_option_id', 'feed_post_id')->all()
            : [];

        $canPublish = auth()->check() && $this->canPublish(auth()->user());
        $cityOptions = SergipeCities::getAll();

        $mentionUsernames = $posts->getCollection()
            ->flatMap(fn ($post) => collect([$post->body])
                ->merge($post->comments->pluck('body')))
            ->filter()
            ->flatMap(function ($text) {
                preg_match_all('/(?<![\\pL\\pN._])@([a-z0-9._]{3,30})/iu', $text, $matches);

                return $matches[1] ?? [];
            })
            ->map(fn ($username) => mb_strtolower($username))
            ->unique()
            ->values();

        $mentionUsers = User::query()
            ->whereIn('username', $mentionUsernames)
            ->get(['id', 'name', 'username', 'role'])
            ->keyBy(fn (User $user) => mb_strtolower($user->username));

        $sharedPost = null;
        if ($sharedPostId) {
            $sharedPost = FeedPost::with(['user', 'images'])
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->find($sharedPostId);
        }

        $recommendedAds = $search === '' && ! $sharedPostId
            ? $recommendations->recommend(
                $request,
                $feedMode,
                (int) config('feed.recommended_ads_per_page', 4)
            )
            : collect();

        return view('feed.index', compact('posts', 'likedPostIds', 'votedOptions', 'canPublish', 'mentionUsers', 'search', 'cityOptions', 'sharedPost', 'recommendedAds', 'feedMode'));
    }

    public function trackAdEvent(Request $request, Ad $ad, FeedAdInteractionService $interactions)
    {
        abort_unless($ad->status === 'active', 404);

        $validated = $request->validate([
            'event_type' => ['required', Rule::in(['impression', 'click', 'dismiss'])],
            'mode' => ['nullable', Rule::in(['for_you', 'recent', 'nearby'])],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        $event = $interactions->track(
            $request,
            $ad->loadMissing('user:id,subscription_plan'),
            $validated['event_type'],
            [
                'mode' => $validated['mode'] ?? null,
                'city' => $validated['city'] ?? null,
            ]
        );

        return response()->json([
            'recorded' => true,
            'event_id' => $event->id,
        ]);
    }

    public function store(Request $request, FeedModerationService $moderation, FeedNoticeService $notices, VideoDurationService $videoDuration)
    {
        abort_unless($this->canPublish($request->user()), 403, 'No momento, somente administradores e colaboradores podem publicar neste espaço.');

        $request->merge(['type' => $request->input('type', 'post')]);
        if ($request->input('type') === 'poll') {
            $request->merge(['poll_options' => array_values(array_filter(
                array_map(fn ($option) => trim((string) $option), (array) $request->input('poll_options', []))
            ))]);
        } else {
            $request->request->remove('poll_options');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(['post', 'notice', 'poll'])],
            'title' => [Rule::requiredIf(fn () => in_array($request->input('type'), ['notice', 'poll'], true)), 'nullable', 'string', 'max:180'],
            'notice_level' => [Rule::requiredIf(fn () => $request->input('type') === 'notice'), 'nullable', Rule::in(['information', 'important', 'urgent'])],
            'topic' => ['nullable', Rule::in(self::TOPICS)],
            'text_alignment' => ['nullable', Rule::in(['left', 'justify'])],
            'expires_in' => ['nullable', Rule::in(['never', '24_hours', '48_hours', '10_days'])],
            'poll_options' => [Rule::requiredIf(fn () => $request->input('type') === 'poll'), 'nullable', 'array', 'min:2', 'max:6'],
            'poll_options.*' => ['required', 'string', 'max:180', 'distinct'],
            'poll_ends_at' => ['nullable', 'date', 'after:now'],
            'body' => [Rule::requiredIf(fn () => $request->input('type') === 'notice'), 'nullable', 'string', 'max:1500'],
            'city' => ['nullable', 'string', 'max:120', Rule::in(array_merge(['Sergipe'], SergipeCities::getAll()))],
            'images' => ['nullable', 'array', 'max:'.config('feed.images_per_post', 4)],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'video' => ['nullable', 'file', 'mimes:mp4,m4v,mov', 'max:'.config('feed.video_max_kb', 51200)],
            'video_url' => ['nullable', 'url:http,https', 'max:2048'],
            'video_url_duration' => ['nullable', 'required_with:video_url', 'numeric', 'min:0.1', 'max:'.config('feed.video_max_seconds', 60)],
        ]);

        $videoUpload = $request->file('video');
        if ($videoUpload && filled($validated['video_url'] ?? null)) {
            throw ValidationException::withMessages(['video' => 'Escolha apenas uma opção: upload ou URL do vídeo.']);
        }

        $videoSeconds = null;
        if ($videoUpload) {
            $videoSeconds = $videoDuration->seconds($videoUpload->getRealPath());
            if ($videoSeconds === null) {
                throw ValidationException::withMessages(['video' => 'Não foi possível identificar a duração. Envie um vídeo MP4, M4V ou MOV válido.']);
            }
            if ($videoSeconds > config('feed.video_max_seconds', 60)) {
                throw ValidationException::withMessages(['video' => 'O vídeo deve ter no máximo 1 minuto.']);
            }
        } elseif (filled($validated['video_url'] ?? null)) {
            $videoSeconds = (float) $validated['video_url_duration'];
        }

        if ($validated['type'] === 'post'
            && blank($validated['body'] ?? null)
            && $request->file('images', []) === []
            && ! $videoUpload
            && blank($validated['video_url'] ?? null)) {
            throw ValidationException::withMessages(['body' => 'Escreva uma mensagem ou selecione uma imagem ou vídeo.']);
        }

        $validated['topic'] = $validated['topic'] ?? match ($validated['notice_level'] ?? null) {
            'urgent' => 'urgent',
            'important' => 'important',
            'information' => 'informative',
            default => 'updates',
        };

        $uploads = $request->file('images', []);
        $hashes = collect($uploads)->map(fn ($file) => hash_file('sha256', $file->getRealPath()))->all();
        $videoHash = $videoUpload ? hash_file('sha256', $videoUpload->getRealPath()) : null;
        $moderationText = implode(' ', array_filter([
            $validated['type'],
            $validated['topic'],
            $validated['title'] ?? null,
            $validated['body'] ?? null,
            $validated['video_url'] ?? null,
            isset($validated['poll_options']) ? implode(' ', $validated['poll_options']) : null,
        ]));
        $assessment = $moderation->assess($request->user(), $moderationText, array_values(array_filter([...$hashes, $videoHash])));
        $createdPaths = [];

        try {
            $post = DB::transaction(function () use ($request, $validated, $uploads, $hashes, $videoUpload, $videoSeconds, $assessment, &$createdPaths) {
                $videoPath = null;
                if ($videoUpload) {
                    $directory = public_path('uploads/feed/videos');
                    File::ensureDirectoryExists($directory);
                    $filename = Str::uuid().'.'.strtolower($videoUpload->getClientOriginalExtension());
                    $videoUpload->move($directory, $filename);
                    $videoPath = 'uploads/feed/videos/'.$filename;
                    $createdPaths[] = $videoPath;
                }

                $post = FeedPost::create([
                    'user_id' => $request->user()->id,
                    'body' => trim($validated['body'] ?? '') ?: null,
                    'video_path' => $videoPath,
                    'video_url' => $videoPath ? null : (trim($validated['video_url'] ?? '') ?: null),
                    'video_duration_seconds' => $videoSeconds !== null ? (int) ceil($videoSeconds) : null,
                    'city' => $validated['city'] ?? $request->user()->city,
                    'type' => $validated['type'],
                    'title' => $validated['title'] ?? null,
                    'notice_level' => $validated['notice_level'] ?? null,
                    'topic' => $validated['topic'],
                    'text_alignment' => $validated['text_alignment'] ?? 'justify',
                    'expires_at' => $this->expirationFor($validated['expires_in'] ?? 'never'),
                    'poll_ends_at' => $validated['poll_ends_at'] ?? null,
                    'content_hash' => $assessment['content_hash'],
                    'status' => $assessment['status'],
                    'moderation_reason' => $assessment['reason'],
                    'published_at' => $assessment['status'] === 'published' ? now() : null,
                ]);

                foreach ($uploads as $position => $file) {
                    $path = ImageOptimizer::convertToWebp($file, 'feed');
                    if (! $path) {
                        throw ValidationException::withMessages(['images' => 'Não foi possível processar uma das imagens.']);
                    }
                    $createdPaths[] = $path;
                    $post->images()->create([
                        'path' => $path,
                        'file_hash' => $hashes[$position],
                        'position' => $position,
                        'moderation_status' => $assessment['status'] === 'published' ? 'approved' : 'manual_review',
                    ]);
                }

                if ($post->type === 'poll') {
                    foreach (array_values($validated['poll_options']) as $position => $label) {
                        $post->pollOptions()->create(['label' => trim($label), 'position' => $position]);
                    }
                }

                return $post;
            });
        } catch (\Throwable $exception) {
            foreach ($createdPaths as $path) File::delete(public_path($path));
            throw $exception;
        }

        if ($post->status === 'published') {
            $notices->notifyMembers($post);
        }

        return redirect()->route('feed.index')->with('success', $post->status === 'published'
            ? 'Publicação enviada para o feed.'
            : 'Publicação recebida e encaminhada para análise antes de aparecer no feed.');
    }

    public function toggleLike(Request $request, FeedPost $post)
    {
        abort_unless($post->status === 'published', 404);
        abort_if($post->expires_at?->isPast(), 404);
        $liked = $post->likes()->where('user_id', $request->user()->id)->exists();
        $liked ? $post->likes()->detach($request->user()->id) : $post->likes()->attach($request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'liked' => ! $liked,
                'likes_count' => $post->likes()->count(),
            ]);
        }

        return back();
    }

    public function comment(Request $request, FeedPost $post)
    {
        abort_unless($post->status === 'published', 404);
        abort_if($post->expires_at?->isPast(), 404);
        abort_if($post->user()->where('role', 'admin')->exists(), 403, 'Publicações oficiais não recebem comentários.');
        $validated = $request->validate(['body' => ['required', 'string', 'max:500']]);
        $post->comments()->create(['user_id' => $request->user()->id, 'body' => trim($validated['body'])]);
        return back()->with('success', 'Comentário publicado.');
    }

    public function report(Request $request, FeedPost $post)
    {
        abort_unless($post->status === 'published', 404);
        abort_if($post->expires_at?->isPast(), 404);
        abort_if($post->user()->where('role', 'admin')->exists(), 403, 'Publicações oficiais não podem ser denunciadas.');
        $validated = $request->validate([
            'reason' => ['required', Rule::in(['spam', 'inappropriate', 'scam', 'harassment', 'other'])],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        FeedPostReport::firstOrCreate(
            ['feed_post_id' => $post->id, 'reporter_user_id' => $request->user()->id],
            ['reason' => $validated['reason'], 'details' => $validated['details'] ?? null]
        );
        return back()->with('success', 'Denúncia enviada para análise.');
    }

    public function vote(Request $request, FeedPost $post)
    {
        abort_unless($post->status === 'published' && $post->type === 'poll', 404);
        abort_if($post->expires_at?->isPast(), 404);
        abort_if($post->poll_ends_at && $post->poll_ends_at->isPast(), 422, 'Esta enquete já foi encerrada.');
        $validated = $request->validate(['option_id' => ['required', 'integer']]);
        abort_unless($post->pollOptions()->whereKey($validated['option_id'])->exists(), 422, 'Opção inválida.');

        FeedPollVote::updateOrCreate(
            ['feed_post_id' => $post->id, 'user_id' => $request->user()->id],
            ['feed_poll_option_id' => $validated['option_id']]
        );

        if ($request->expectsJson()) {
            $options = $post->pollOptions()
                ->withCount('votes')
                ->when($request->user()->role === 'admin', fn ($query) => $query->with(['votes.user:id,name,username,role']))
                ->orderBy('position')
                ->get();
            $total = $options->sum('votes_count');

            return response()->json([
                'selected_option_id' => (int) $validated['option_id'],
                'total' => $total,
                'options' => $options->map(fn ($option) => [
                    'id' => $option->id,
                    'votes' => $option->votes_count,
                    'percentage' => $total > 0
                        ? (int) round(($option->votes_count / $total) * 100)
                        : 0,
                    'voters' => $request->user()->role === 'admin'
                        ? $option->votes->map(fn ($vote) => [
                            'name' => $vote->user?->role === 'admin'
                                ? 'Conectado em Sergipe'
                                : $vote->user?->name,
                            'url' => $vote->user?->username
                                ? route('profile.show', $vote->user->username)
                                : null,
                        ])->values()
                        : null,
                ])->values(),
            ]);
        }

        return back()->with('success', 'Seu voto foi registrado.');
    }

    public function update(Request $request, FeedPost $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'title' => [Rule::requiredIf(fn () => in_array($post->type, ['notice', 'poll'], true)), 'nullable', 'string', 'max:180'],
            'notice_level' => [Rule::requiredIf(fn () => $post->type === 'notice'), 'nullable', Rule::in(['information', 'important', 'urgent'])],
            'topic' => ['nullable', Rule::in(self::TOPICS)],
            'text_alignment' => ['nullable', Rule::in(['left', 'justify'])],
            'expires_in' => ['nullable', Rule::in(['keep', 'never', '24_hours', '48_hours', '10_days'])],
            'body' => [$post->type === 'notice' ? 'required' : 'nullable', 'string', 'max:1500'],
            'city' => ['nullable', 'string', 'max:120', Rule::in(array_merge(['Sergipe'], SergipeCities::getAll()))],
        ]);

        if ($post->type === 'post'
            && blank($validated['body'] ?? null)
            && ! $post->images()->exists()
            && blank($post->video_path)
            && blank($post->video_url)) {
            throw ValidationException::withMessages(['body' => 'Escreva uma mensagem ou mantenha uma imagem ou vídeo na publicação.']);
        }

        $post->fill([
            'title' => in_array($post->type, ['notice', 'poll'], true)
                ? trim($validated['title'])
                : null,
            'notice_level' => $post->type === 'notice' ? $validated['notice_level'] : null,
            'topic' => $validated['topic'] ?? $post->topic ?? 'updates',
            'text_alignment' => $validated['text_alignment'] ?? $post->text_alignment ?? 'justify',
            'body' => trim($validated['body'] ?? '') ?: null,
            'city' => trim($validated['city'] ?? '') ?: null,
        ]);

        $imageHashes = $post->images()->pluck('file_hash')->filter()->implode('|');
        $post->content_hash = hash('sha256', implode('|', [
            $post->type,
            $post->topic,
            mb_strtolower((string) $post->title),
            mb_strtolower((string) $post->body),
            mb_strtolower((string) $post->city),
            $imageHashes,
            $post->video_path,
            $post->video_url,
        ]));

        if (($validated['expires_in'] ?? 'keep') !== 'keep') {
            $post->expires_at = $this->expirationFor($validated['expires_in']);
        }

        $post->save();

        return back()->with('success', 'Publicação atualizada.');
    }

    public function togglePin(Request $request, FeedPost $post)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $post->is_pinned = ! $post->is_pinned;
        $post->pinned_at = $post->is_pinned ? now() : null;
        $post->save();

        $message = $post->is_pinned ? 'Post fixado no topo.' : 'Post removido do topo.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'post_id' => $post->id,
                'is_pinned' => $post->is_pinned,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, FeedPost $post)
    {
        abort_unless($post->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $paths = $post->images()->pluck('path');
        if ($post->video_path) {
            $paths->push($post->video_path);
        }
        $postId = $post->id;
        $post->delete();
        $paths->each(fn ($path) => File::delete(public_path($path)));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Publicação excluída.',
                'post_id' => $postId,
            ]);
        }

        return back()->with('success', 'Publicação excluída.');
    }

    private function canPublish($user): bool
    {
        if (config('feed.publishing_mode', 'staff_only') === 'staff_only') {
            return in_array($user->role, ['admin', 'collaborator'], true);
        }

        return $user->role === 'admin'
            || $user->ads()->where('status', 'active')->exists()
            || $user->stores()->where('status', 'active')->exists()
            || $user->cultureWorks()->where('status', 'published')->exists();
    }

    private function expirationFor(string $duration)
    {
        return match ($duration) {
            '24_hours' => now()->addHours(24),
            '48_hours' => now()->addHours(48),
            '10_days' => now()->addDays(10),
            default => null,
        };
    }
}
