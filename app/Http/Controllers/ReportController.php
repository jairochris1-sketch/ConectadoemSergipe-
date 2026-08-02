<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Report;
use App\Models\Store;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function store(Request $request, Ad $ad)
    {
        $isService = $ad->module === 'services';
        $validated = $this->validateReport(
            $request,
            array_keys($isService ? Report::SERVICE_REASONS : Report::AD_REASONS)
        );

        if ($this->hasRecentReport($request, 'ad_id', $ad->id)) {
            return $this->duplicateReportResponse();
        }

        $report = $this->createReport($request, $validated, [
            'ad_id' => $ad->id,
            'store_id' => null,
            'advertiser_id' => $ad->user_id,
            'subject_type' => $isService ? 'service' : 'ad',
            'ad_title_snapshot' => $ad->title,
            'ad_module_snapshot' => $ad->module,
        ]);

        return redirect()->route('reports.thank_you', $report->public_id);
    }

    public function storeReport(Request $request, Store $store)
    {
        abort_unless($store->active && $store->isModerationApproved(), 404);

        if ($request->user()?->id === $store->user_id) {
            return back()->withErrors([
                'reason' => 'Você não pode denunciar a própria loja.',
            ]);
        }

        $validated = $this->validateReport($request, array_keys(Report::STORE_REASONS));

        if ($this->hasRecentReport($request, 'store_id', $store->id)) {
            return $this->duplicateReportResponse();
        }

        $report = $this->createReport($request, $validated, [
            'ad_id' => null,
            'store_id' => $store->id,
            'advertiser_id' => $store->user_id,
            'subject_type' => 'store',
            'ad_title_snapshot' => $store->name,
            'ad_module_snapshot' => 'stores',
        ]);

        return redirect()->route('reports.thank_you', $report->public_id);
    }

    public function thankYou(string $publicId)
    {
        $report = Report::with(['ad', 'store'])->where('public_id', $publicId)->firstOrFail();

        return view('reports.thank-you', compact('report'));
    }

    private function validateReport(Request $request, array $allowedReasons): array
    {
        return $request->validate([
            'reason' => ['required', Rule::in($allowedReasons)],
            'severity' => ['required', Rule::in(['error', 'misleading', 'critical'])],
            'details' => ['nullable', 'string', 'max:1000'],
            'evidence' => ['nullable', 'array', 'max:3'],
            'evidence.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'wants_notification' => ['nullable', 'boolean'],
            'truth_confirmation' => ['accepted'],
        ], [
            'truth_confirmation.accepted' => 'Confirme que a denúncia é verdadeira antes de enviar.',
            'evidence.max' => 'Você pode anexar no máximo 3 imagens.',
            'evidence.*.max' => 'Cada imagem pode ter no máximo 5 MB.',
        ]);
    }

    private function createReport(Request $request, array $validated, array $subject): Report
    {
        $evidencePaths = [];

        foreach ($request->file('evidence', []) as $file) {
            $path = ImageOptimizer::convertToWebp($file, 'report_evidence');
            if ($path) {
                $evidencePaths[] = $path;
            }
        }

        return Report::create(array_merge($subject, [
            'public_id' => (string) Str::uuid(),
            'reporter_user_id' => $request->user()?->id,
            'reason' => $validated['reason'],
            'severity' => $validated['severity'],
            'details' => $validated['details'] ?? null,
            'evidence_paths' => $evidencePaths,
            'wants_notification' => $request->boolean('wants_notification') && $request->user() !== null,
            'status' => 'open',
        ]));
    }

    private function hasRecentReport(Request $request, string $foreignKey, int $subjectId): bool
    {
        if (! $request->user()) {
            return false;
        }

        return Report::where($foreignKey, $subjectId)
            ->where('reporter_user_id', $request->user()->id)
            ->whereIn('status', ['open', 'reviewing'])
            ->where('created_at', '>=', now()->subDay())
            ->exists();
    }

    private function duplicateReportResponse()
    {
        return back()->withErrors([
            'reason' => 'Você já enviou uma denúncia para este conteúdo nas últimas 24 horas.',
        ]);
    }
}
