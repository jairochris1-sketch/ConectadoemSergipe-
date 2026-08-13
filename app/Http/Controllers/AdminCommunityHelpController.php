<?php

namespace App\Http\Controllers;

use App\Core\SergipeCities;
use App\Models\CommunityHelpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCommunityHelpController extends Controller
{
    private const STATUSES = ['pending', 'open', 'in_progress', 'resolved', 'rejected', 'hidden'];

    public function index(Request $request)
    {
        $status = in_array((string) $request->query('status'), self::STATUSES, true)
            ? (string) $request->query('status')
            : '';
        $category = array_key_exists((string) $request->query('category'), CommunityHelpRequest::CATEGORIES)
            ? (string) $request->query('category')
            : '';
        $city = in_array((string) $request->query('city'), SergipeCities::getAll(), true)
            ? (string) $request->query('city')
            : '';
        $search = Str::limit(trim((string) $request->query('q')), 100, '');
        $reportedOnly = $request->boolean('reported');

        $helpRequests = CommunityHelpRequest::query()
            ->with(['user:id,name,email,username', 'reviewer:id,name'])
            ->withCount([
                'responses',
                'responses as pending_reports_count' => fn ($responses) => $responses
                    ->whereHas('reports', fn ($reports) => $reports->where('status', 'pending')),
            ])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($city, fn ($query) => $query->where('city', $city))
            ->when($reportedOnly, fn ($query) => $query->whereHas(
                'responses.reports',
                fn ($reports) => $reports->where('status', 'pending')
            ))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('public_id', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('neighborhood', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $metrics = [
            'total' => CommunityHelpRequest::count(),
            'pending' => CommunityHelpRequest::where('status', 'pending')->count(),
            'active' => CommunityHelpRequest::whereIn('status', ['open', 'in_progress'])->count(),
            'resolved' => CommunityHelpRequest::where('status', 'resolved')->count(),
            'reported' => CommunityHelpRequest::whereHas(
                'responses.reports',
                fn ($reports) => $reports->where('status', 'pending')
            )->count(),
        ];

        return view('admin.community-help.index', [
            'helpRequests' => $helpRequests,
            'metrics' => $metrics,
            'categories' => CommunityHelpRequest::CATEGORIES,
            'cities' => SergipeCities::getAll(),
            'status' => $status,
            'category' => $category,
            'city' => $city,
            'search' => $search,
            'reportedOnly' => $reportedOnly,
        ]);
    }

    public function show(CommunityHelpRequest $helpRequest)
    {
        $helpRequest->load([
            'user',
            'reviewer',
            'responses' => fn ($query) => $query->with([
                'user',
                'reviewer',
                'reports' => fn ($reports) => $reports->with('reporter')->latest(),
            ])->latest(),
        ]);

        return view('admin.community-help.show', [
            'helpRequest' => $helpRequest,
            'categories' => CommunityHelpRequest::CATEGORIES,
            'urgencies' => CommunityHelpRequest::URGENCIES,
        ]);
    }
}
