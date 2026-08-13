<?php

namespace App\Http\Controllers;

use App\Models\CultureWork;
use App\Models\ReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCultureController extends Controller
{
    private const STATUSES = ['published', 'draft', 'hidden'];

    private const CATEGORIES = [
        'cordel' => 'Cordel',
        'literatura' => 'Literatura',
        'musica' => 'Música / Áudio',
        'arte_visual' => 'Artes Visuais',
    ];

    public function index(Request $request)
    {
        $status = in_array((string) $request->query('status'), self::STATUSES, true)
            ? (string) $request->query('status')
            : '';
        $category = array_key_exists((string) $request->query('category'), self::CATEGORIES)
            ? (string) $request->query('category')
            : '';
        $search = Str::limit(trim((string) $request->query('q')), 100, '');

        $works = CultureWork::query()
            ->with(['user:id,name,email,username', 'ad:id,title,slug'])
            ->withCount('likes')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('theme', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'total' => CultureWork::count(),
            'published' => CultureWork::where('status', 'published')->count(),
            'draft' => CultureWork::where('status', 'draft')->count(),
            'hidden' => CultureWork::where('status', 'hidden')->count(),
        ];

        return view('admin.culture.index', [
            'works' => $works,
            'metrics' => $metrics,
            'categories' => self::CATEGORIES,
            'status' => $status,
            'category' => $category,
            'search' => $search,
        ]);
    }

    public function action(Request $request, CultureWork $work)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'hide'])],
        ]);

        $status = $validated['action'] === 'publish' ? 'published' : 'hidden';
        $work->update(['status' => $status]);

        if ($work->user_id !== $request->user()->id) {
            ReportNotification::sendTo($work->user_id, [
                'kind' => 'culture_moderation',
                'message' => $status === 'published'
                    ? "Sua obra \"{$work->title}\" foi liberada no Espaço Cultural."
                    : "Sua obra \"{$work->title}\" foi retirada da exibição pela administração.",
                'action_url' => route('culture.show', $work->slug, false),
            ]);
        }

        return back()->with('success', $status === 'published'
            ? 'Obra publicada no Espaço Cultural.'
            : 'Obra ocultada da exibição pública.');
    }
}
