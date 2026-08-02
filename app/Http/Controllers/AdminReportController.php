<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Report;
use App\Models\ReportNotification;
use App\Models\Store;
use App\Services\AdTrustService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AdminReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['ad', 'store', 'reporter'])
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'misleading' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(20);

        $reportCounts = Report::query()
            ->get(['subject_type', 'ad_id', 'store_id'])
            ->countBy(fn (Report $item) => $item->subject_key);

        $openCount = Report::whereIn('status', ['open', 'reviewing'])->count();
        $criticalCount = Report::whereIn('status', ['open', 'reviewing'])
            ->where('severity', 'critical')
            ->count();

        return view('admin.reports.index', compact('reports', 'reportCounts', 'openCount', 'criticalCount'));
    }

    public function show(Report $report)
    {
        $report->load([
            'ad.user',
            'ad.images',
            'ad.category',
            'store.user',
            'store.media',
            'advertiser',
            'reporter',
            'reviewer',
        ]);

        $relatedReports = Report::query()
            ->when(
                $report->subject_type === 'store',
                fn ($query) => $query->where('store_id', $report->store_id),
                fn ($query) => $query->where('ad_id', $report->ad_id)
            )
            ->whereKeyNot($report->id)
            ->latest()
            ->get();

        $categories = $report->subject_type === 'store'
            ? collect()
            : Category::where('active', true)->orderBy('name')->get();

        $automaticSignals = $report->ad
            ? app(AdTrustService::class)->calculate($report->ad)['signals']
            : [];

        return view('admin.reports.show', compact('report', 'relatedReports', 'categories', 'automaticSignals'));
    }

    public function action(Request $request, Report $report)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['archive', 'correct_category', 'request_change', 'hide', 'block', 'suspend', 'delete'])],
            'category_id' => ['nullable', 'exists:categories,id'],
            'resolution_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $validated['action'];
        $isStoreReport = $report->subject_type === 'store';
        $content = $isStoreReport ? $report->store : $report->ad;

        if (! $content && $action !== 'archive') {
            return back()->with('error', 'O conteúdo relacionado já não existe.');
        }

        if ($isStoreReport && $action === 'correct_category') {
            return back()->with('error', 'A categoria da loja deve ser ajustada no gerenciamento específico de lojas.');
        }

        if ($action === 'correct_category') {
            $request->validate(['category_id' => ['required', 'exists:categories,id']]);
            $category = Category::findOrFail($validated['category_id']);
            $content->update([
                'category_id' => $category->id,
                'advertiser_type' => $category->name,
            ]);
        } elseif ($action === 'request_change') {
            if (! $report->advertiser_id) {
                return back()->with('error', 'A conta responsável pelo conteúdo já não existe.');
            }

            ReportNotification::sendTo($report->advertiser_id, [
                'report_id' => $report->id,
                'kind' => 'change_request',
                'message' => 'A equipe solicitou uma alteração em "'.$report->ad_title_snapshot.'". Verifique o conteúdo no seu painel.',
                'action_url' => $isStoreReport
                    ? route('store.manage', $content, false)
                    : route('ad.edit', $content->id, false),
            ]);
        } elseif ($action === 'hide') {
            $isStoreReport
                ? $content->update(['active' => false, 'featured' => false, 'featured_until' => null])
                : $content->update(['status' => 'inactive']);
        } elseif ($action === 'block') {
            $isStoreReport
                ? $this->suspendStore($content, $request, $validated['resolution_note'] ?? null)
                : $content->update(['status' => 'banned']);
        } elseif ($action === 'suspend') {
            if ($report->advertiser_id === $request->user()->id) {
                return back()->with('error', 'Você não pode suspender a própria conta administrativa.');
            }
            $report->advertiser?->update(['suspended_at' => now()]);
        } elseif ($action === 'delete') {
            $isStoreReport
                ? $this->deleteStoreFiles($content)
                : $this->deleteAdFiles($content);

            if (! $isStoreReport) {
                $content->delete();
            }
        }

        $report->update([
            'status' => $action === 'archive' ? 'archived' : ($action === 'request_change' ? 'reviewing' : 'resolved'),
            'admin_action' => $action,
            'resolution_note' => $validated['resolution_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($report->wants_notification && $report->reporter_user_id && $action !== 'request_change') {
            ReportNotification::sendTo($report->reporter_user_id, [
                'report_id' => $report->id,
                'kind' => 'report_result',
                'message' => $this->resultMessage($action),
                'action_url' => route('reports.thank_you', $report->public_id, false),
            ]);
        }

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Ação aplicada e denúncia atualizada.');
    }

    private function resultMessage(string $action): string
    {
        return match ($action) {
            'hide', 'block', 'delete' => 'Sua denúncia foi analisada. O conteúdo denunciado foi removido ou ocultado. Obrigado pela colaboração.',
            'correct_category' => 'Sua denúncia foi analisada e a categoria foi corrigida. Obrigado pela colaboração.',
            default => 'Sua denúncia foi analisada. Obrigado pela colaboração.',
        };
    }

    private function suspendStore(Store $store, Request $request, ?string $note): void
    {
        $store->update([
            'moderation_status' => 'suspended',
            'moderation_note' => $note ?: 'Loja bloqueada após análise de denúncia.',
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
            'active' => false,
            'featured' => false,
            'featured_until' => null,
        ]);
    }

    private function deleteStoreFiles(Store $store): void
    {
        $store->loadMissing('media');
        $paths = $store->media->pluck('path')
            ->push($store->logo)
            ->push($store->banner)
            ->filter()
            ->unique();

        DB::transaction(function () use ($store) {
            $store->ads()->update(['store_id' => null]);
            $store->delete();
        });

        $this->deleteUploads($paths);
    }

    private function deleteAdFiles($ad): void
    {
        $paths = $ad->images->pluck('image_path')
            ->push($ad->logo)
            ->push($ad->banner)
            ->filter()
            ->unique();

        $this->deleteUploads($paths);
    }

    private function deleteUploads(iterable $paths): void
    {
        foreach ($paths as $path) {
            $relativePath = ltrim((string) $path, '/\\');
            if (str_starts_with($relativePath, 'uploads/')) {
                File::delete(public_path($relativePath));
            }
        }
    }
}
