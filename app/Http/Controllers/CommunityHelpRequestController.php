<?php

namespace App\Http\Controllers;

use App\Core\SergipeCities;
use App\Models\CommunityHelpRequest;
use App\Models\CommunityHelpResponse;
use App\Models\ReportNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommunityHelpRequestController extends Controller
{
    public function index(Request $request)
    {
        $scope = in_array($request->query('scope'), ['all', 'mine', 'moderation', 'reported'], true)
            ? $request->query('scope')
            : 'all';

        if ($scope === 'mine') {
            abort_unless($request->user(), 403);
        }

        if (in_array($scope, ['moderation', 'reported'], true)) {
            abort_unless($request->user()?->role === 'admin', 403);
        }

        $category = array_key_exists((string) $request->query('category'), CommunityHelpRequest::CATEGORIES)
            ? (string) $request->query('category')
            : null;
        $city = in_array((string) $request->query('city'), SergipeCities::getAll(), true)
            ? (string) $request->query('city')
            : null;
        $search = Str::limit(trim((string) $request->query('q')), 100, '');

        $query = CommunityHelpRequest::query()
            ->with('user:id,name,username,avatar,city,role')
            ->withCount([
                'responses' => fn ($builder) => $builder->where('status', 'published'),
                'responses as pending_response_reports_count' => fn ($builder) => $builder
                    ->whereHas('reports', fn ($reports) => $reports->where('status', 'pending')),
            ]);

        if ($scope === 'mine') {
            $query->where('user_id', $request->user()->id);
        } elseif ($scope === 'moderation') {
            $query->where('status', 'pending');
        } elseif ($scope === 'reported') {
            $query->whereHas('responses.reports', fn ($reports) => $reports->where('status', 'pending'));
        } else {
            $query->publiclyVisible();
            if ($request->query('status') === 'resolved') {
                $query->where('status', 'resolved');
            } else {
                $query->whereIn('status', ['open', 'in_progress']);
            }
        }

        $helpRequests = $query
            ->when($category, fn ($builder) => $builder->where('category', $category))
            ->when($city, fn ($builder) => $builder->where('city', $city))
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('neighborhood', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE urgency WHEN 'urgent' THEN 0 WHEN 'today' THEN 1 ELSE 2 END")
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        $activeQuery = CommunityHelpRequest::query()->publiclyVisible()->whereIn('status', ['open', 'in_progress']);
        $stats = [
            'active' => (clone $activeQuery)->count(),
            'cities' => (clone $activeQuery)->distinct()->count('city'),
            'resolved' => CommunityHelpRequest::query()
                ->where('status', 'resolved')
                ->where('resolved_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return view('community-help.index', [
            'helpRequests' => $helpRequests,
            'categories' => CommunityHelpRequest::CATEGORIES,
            'urgencies' => CommunityHelpRequest::URGENCIES,
            'cities' => SergipeCities::getAll(),
            'scope' => $scope,
            'category' => $category,
            'city' => $city,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request)
    {
        return view('community-help.create', [
            'categories' => CommunityHelpRequest::CATEGORIES,
            'urgencies' => CommunityHelpRequest::URGENCIES,
            'cities' => SergipeCities::getAll(),
            'suggestedCity' => $request->user()->city,
            'helpRequest' => null,
        ]);
    }

    public function edit(Request $request, CommunityHelpRequest $helpRequest)
    {
        abort_unless($helpRequest->user_id === $request->user()->id, 403);
        abort_unless(in_array($helpRequest->status, ['pending', 'rejected'], true), 422);

        return view('community-help.create', [
            'categories' => CommunityHelpRequest::CATEGORIES,
            'urgencies' => CommunityHelpRequest::URGENCIES,
            'cities' => SergipeCities::getAll(),
            'suggestedCity' => $helpRequest->city,
            'helpRequest' => $helpRequest,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $isAdmin = $request->user()->role === 'admin';
        $helpRequest = CommunityHelpRequest::create([
            'public_id' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'city' => $validated['city'],
            'neighborhood' => $validated['neighborhood'],
            'urgency' => $validated['urgency'],
            'status' => $isAdmin ? 'open' : 'pending',
            'duration_days' => (int) $validated['duration_days'],
            'expires_at' => $isAdmin ? now()->addDays((int) $validated['duration_days']) : null,
            'published_at' => $isAdmin ? now() : null,
            'reviewed_by' => $isAdmin ? $request->user()->id : null,
            'reviewed_at' => $isAdmin ? now() : null,
        ]);

        if (! $isAdmin) {
            User::query()->where('role', 'admin')->pluck('id')->each(function (int $adminId) use ($helpRequest) {
                ReportNotification::sendTo($adminId, [
                    'kind' => 'community_request_moderation',
                    'message' => 'Novo pedido local aguardando análise em '.$helpRequest->city.'.',
                    'action_url' => route('community-help.show', $helpRequest, false),
                ]);
            });
        }

        return redirect()
            ->route('community-help.show', $helpRequest)
            ->with('success', $isAdmin
                ? 'Pedido publicado. A comunidade já pode ajudar.'
                : 'Pedido enviado com segurança. Ele aparecerá após a análise da equipe.');
    }

    public function update(Request $request, CommunityHelpRequest $helpRequest)
    {
        abort_unless($helpRequest->user_id === $request->user()->id, 403);
        abort_unless(in_array($helpRequest->status, ['pending', 'rejected'], true), 422);

        $validated = $this->validatePayload($request);

        $helpRequest->update([
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'city' => $validated['city'],
            'neighborhood' => $validated['neighborhood'],
            'urgency' => $validated['urgency'],
            'status' => 'pending',
            'duration_days' => (int) $validated['duration_days'],
            'expires_at' => null,
            'published_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'moderation_reason' => null,
        ]);

        User::query()->where('role', 'admin')->pluck('id')->each(function (int $adminId) use ($helpRequest) {
            ReportNotification::sendTo($adminId, [
                'kind' => 'community_request_moderation',
                'message' => 'Pedido local corrigido e reenviado para análise em '.$helpRequest->city.'.',
                'action_url' => route('community-help.show', $helpRequest, false),
            ]);
        });

        return redirect()
            ->route('community-help.show', $helpRequest)
            ->with('success', 'Pedido corrigido e reenviado para análise.');
    }

    public function show(Request $request, CommunityHelpRequest $helpRequest)
    {
        abort_unless($helpRequest->canBeViewedBy($request->user()), 404);

        $isAdmin = $request->user()?->role === 'admin';

        $helpRequest->load([
            'user:id,name,username,avatar,city,role',
            'responses' => fn ($query) => $query
                ->when(! $isAdmin, fn ($responses) => $responses->where('status', 'published'))
                ->with('user:id,name,username,avatar,city,role')
                ->withCount(['reports' => fn ($reports) => $reports->where('status', 'pending')])
                ->oldest(),
        ]);

        return view('community-help.show', [
            'helpRequest' => $helpRequest,
            'categories' => CommunityHelpRequest::CATEGORIES,
            'urgencies' => CommunityHelpRequest::URGENCIES,
        ]);
    }

    public function respond(Request $request, CommunityHelpRequest $helpRequest)
    {
        abort_unless($helpRequest->isPubliclyVisible(), 404);
        abort_unless(in_array($helpRequest->status, ['open', 'in_progress'], true), 422);
        abort_if($helpRequest->user_id === $request->user()->id, 403, 'Você não pode responder ao próprio pedido.');

        $existingResponse = $helpRequest->responses()->where('user_id', $request->user()->id)->first();
        abort_if($existingResponse?->status === 'hidden', 422, 'Esta resposta foi moderada e não pode ser reenviada.');

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:10', 'max:700'],
        ]);

        $response = $helpRequest->responses()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['message' => $validated['message']]
        );

        if ($response->wasRecentlyCreated) {
            ReportNotification::sendTo($helpRequest->user_id, [
                'kind' => 'community_request_response',
                'message' => $request->user()->name.' respondeu ao seu pedido local.',
                'action_url' => route('community-help.show', $helpRequest, false).'#respostas',
            ]);
        }

        return back()->with('success', $response->wasRecentlyCreated
            ? 'Sua resposta foi enviada ao autor.'
            : 'Sua resposta foi atualizada.');
    }

    public function reportResponse(
        Request $request,
        CommunityHelpRequest $helpRequest,
        CommunityHelpResponse $response
    ) {
        abort_unless($helpRequest->isPubliclyVisible(), 404);
        abort_unless($response->community_help_request_id === $helpRequest->id, 404);
        abort_unless($response->status === 'published', 404);
        abort_if($response->user_id === $request->user()->id, 403, 'Você não pode denunciar a própria resposta.');

        $validated = $request->validate([
            'reason' => ['required', Rule::in(['spam', 'scam', 'inappropriate', 'harassment', 'personal_data', 'other'])],
            'details' => ['nullable', 'string', 'max:700'],
        ]);

        $report = $response->reports()->updateOrCreate(
            ['reporter_user_id' => $request->user()->id],
            [
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        if ($report->wasRecentlyCreated) {
            User::query()->where('role', 'admin')->pluck('id')->each(function (int $adminId) use ($helpRequest) {
                ReportNotification::sendTo($adminId, [
                    'kind' => 'community_response_report',
                    'message' => 'Uma resposta de pedido local foi denunciada e aguarda análise.',
                    'action_url' => route('community-help.show', $helpRequest, false).'#respostas',
                ]);
            });
        }

        return back()->with('success', 'Denúncia enviada para análise. Obrigado por ajudar a proteger a comunidade.');
    }

    public function updateStatus(Request $request, CommunityHelpRequest $helpRequest)
    {
        abort_unless($helpRequest->canBeManagedBy($request->user()), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved'])],
        ]);

        abort_if(in_array($helpRequest->status, ['pending', 'rejected'], true), 422, 'O pedido precisa ser aprovado antes de mudar de etapa.');

        DB::transaction(function () use ($helpRequest, $validated) {
            $helpRequest->update([
                'status' => $validated['status'],
                'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
            ]);
        });

        return back()->with('success', match ($validated['status']) {
            'resolved' => 'Ótimo! O pedido foi marcado como resolvido.',
            'in_progress' => 'Pedido marcado como em atendimento.',
            default => 'Pedido reaberto para receber ajuda.',
        });
    }

    public function selectResponse(
        Request $request,
        CommunityHelpRequest $helpRequest,
        CommunityHelpResponse $response
    ) {
        abort_unless($helpRequest->canBeManagedBy($request->user()), 403);
        abort_unless($response->community_help_request_id === $helpRequest->id, 404);
        abort_unless(in_array($helpRequest->status, ['open', 'in_progress', 'resolved'], true), 422);

        DB::transaction(function () use ($helpRequest, $response) {
            CommunityHelpRequest::query()->whereKey($helpRequest->id)->lockForUpdate()->firstOrFail();
            $helpRequest->responses()->update(['is_selected' => false]);
            $response->update(['is_selected' => true]);
            $helpRequest->update(['status' => 'resolved', 'resolved_at' => now()]);
        });

        ReportNotification::sendTo($response->user_id, [
            'kind' => 'community_help_selected',
            'message' => 'Sua resposta ajudou a resolver um pedido da comunidade. Obrigado por colaborar!',
            'action_url' => route('community-help.show', $helpRequest, false).'#respostas',
        ]);

        return back()->with('success', 'Ajuda confirmada e pedido marcado como resolvido.');
    }

    public function moderateResponse(
        Request $request,
        CommunityHelpRequest $helpRequest,
        CommunityHelpResponse $response
    ) {
        abort_unless($request->user()->role === 'admin', 403);
        abort_unless($response->community_help_request_id === $helpRequest->id, 404);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['hide', 'dismiss_reports', 'restore'])],
            'moderation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['action'] === 'dismiss_reports') {
            $response->reports()->where('status', 'pending')->update([
                'status' => 'dismissed',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            return back()->with('success', 'Denúncias arquivadas e resposta mantida.');
        }

        if ($validated['action'] === 'restore') {
            $response->update([
                'status' => 'published',
                'moderation_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            return back()->with('success', 'Resposta restaurada.');
        }

        $wasSelected = $response->is_selected;
        DB::transaction(function () use ($request, $response, $helpRequest, $validated, $wasSelected) {
            $response->update([
                'status' => 'hidden',
                'is_selected' => false,
                'moderation_reason' => $validated['moderation_reason'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
            $response->reports()->where('status', 'pending')->update([
                'status' => 'actioned',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($wasSelected) {
                $helpRequest->update(['status' => 'open', 'resolved_at' => null]);
            }
        });

        ReportNotification::sendTo($response->user_id, [
            'kind' => 'community_response_hidden',
            'message' => 'Uma resposta sua foi ocultada após análise da equipe.',
            'action_url' => route('community-help.show', $helpRequest, false),
        ]);

        if ($wasSelected) {
            ReportNotification::sendTo($helpRequest->user_id, [
                'kind' => 'community_request_reopened',
                'message' => 'Seu pedido foi reaberto porque a resposta confirmada precisou ser removida.',
                'action_url' => route('community-help.show', $helpRequest, false),
            ]);
        }

        return back()->with('success', 'Resposta ocultada e denúncias tratadas.');
    }

    public function moderate(Request $request, CommunityHelpRequest $helpRequest)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'hide', 'restore'])],
            'moderation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $action = $validated['action'];
        $allowed = match ($helpRequest->status) {
            'pending' => ['approve', 'reject'],
            'open', 'in_progress', 'resolved' => ['hide'],
            'rejected', 'hidden' => ['restore'],
            default => [],
        };
        abort_unless(in_array($action, $allowed, true), 422);

        if ($action === 'hide') {
            $helpRequest->update([
                'status' => 'hidden',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'moderation_reason' => $validated['moderation_reason'] ?? null,
            ]);

            ReportNotification::sendTo($helpRequest->user_id, [
                'kind' => 'community_request_hidden',
                'message' => 'Seu pedido local foi retirado da exibição após análise administrativa.',
                'action_url' => route('community-help.show', $helpRequest, false),
            ]);

            return back()->with('success', 'Pedido ocultado da comunidade.');
        }

        if ($action === 'restore') {
            $helpRequest->update([
                'status' => 'open',
                'published_at' => $helpRequest->published_at ?? now(),
                'expires_at' => now()->addDays($helpRequest->duration_days),
                'resolved_at' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'moderation_reason' => null,
            ]);

            ReportNotification::sendTo($helpRequest->user_id, [
                'kind' => 'community_request_restored',
                'message' => 'Seu pedido local foi restaurado e voltou a aparecer para a comunidade.',
                'action_url' => route('community-help.show', $helpRequest, false),
            ]);

            return back()->with('success', 'Pedido restaurado e publicado novamente.');
        }

        $approved = $action === 'approve';
        $helpRequest->update([
            'status' => $approved ? 'open' : 'rejected',
            'published_at' => $approved ? now() : null,
            'expires_at' => $approved ? now()->addDays($helpRequest->duration_days) : null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'moderation_reason' => $validated['moderation_reason'] ?? null,
        ]);

        ReportNotification::sendTo($helpRequest->user_id, [
            'kind' => 'community_request_reviewed',
            'message' => $approved
                ? 'Seu pedido local foi aprovado e já está visível para a comunidade.'
                : 'Seu pedido local precisa de ajustes antes de ser publicado.',
            'action_url' => route('community-help.show', $helpRequest, false),
        ]);

        return back()->with('success', $approved ? 'Pedido aprovado e publicado.' : 'Pedido devolvido ao autor.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(array_keys(CommunityHelpRequest::CATEGORIES))],
            'title' => ['required', 'string', 'min:8', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:1500'],
            'city' => ['required', Rule::in(SergipeCities::getAll())],
            'neighborhood' => ['required', 'string', 'min:2', 'max:120'],
            'urgency' => ['required', Rule::in(array_keys(CommunityHelpRequest::URGENCIES))],
            'duration_days' => ['required', 'integer', Rule::in([2, 7, 14, 30])],
            'safety_acknowledged' => ['accepted'],
        ], [
            'safety_acknowledged.accepted' => 'Confirme que o pedido não contém dados pessoais sensíveis.',
        ]);
    }
}
