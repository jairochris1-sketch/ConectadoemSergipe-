<?php

namespace App\Http\Controllers;

use App\Models\ProviderClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminProviderClaimController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                ProviderClaim::STATUS_PENDING,
                ProviderClaim::STATUS_APPROVED,
                ProviderClaim::STATUS_REJECTED,
            ])],
        ]);

        $status = $validated['status'] ?? ProviderClaim::STATUS_PENDING;
        $claims = ProviderClaim::with(['ad', 'claimant', 'reviewer'])
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.provider-claims.index', compact('claims', 'status'));
    }

    public function review(Request $request, ProviderClaim $claim)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($claim->status !== ProviderClaim::STATUS_PENDING) {
            return back()->withErrors(['claim' => 'Esta solicitação já foi analisada.']);
        }

        if ($validated['action'] === 'approve') {
            $this->approve($claim, $request);

            return back()->with('success', 'Perfil transferido para o solicitante com sucesso.');
        }

        $claim->update([
            'status' => ProviderClaim::STATUS_REJECTED,
            'reviewed_by_user_id' => $request->user()->id,
            'admin_note' => trim((string) ($validated['admin_note'] ?? '')) ?: null,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Solicitação rejeitada.');
    }

    private function approve(ProviderClaim $claim, Request $request): void
    {
        DB::transaction(function () use ($claim, $request) {
            $lockedClaim = ProviderClaim::whereKey($claim->id)->lockForUpdate()->firstOrFail();
            $ad = $lockedClaim->ad()->lockForUpdate()->firstOrFail();
            $claimant = $lockedClaim->claimant()->firstOrFail();

            if ($lockedClaim->status !== ProviderClaim::STATUS_PENDING || $ad->is_claimed) {
                throw ValidationException::withMessages([
                    'claim' => 'Este perfil ou esta solicitação já foi analisado.',
                ]);
            }

            if ($ad->user_id !== $claimant->id && ! $claimant->canCreateAnotherProfessionalProfile()) {
                throw ValidationException::withMessages([
                    'claim' => 'O solicitante atingiu o limite de perfis profissionais do plano atual.',
                ]);
            }

            $ad->update([
                'user_id' => $claimant->id,
                'is_claimed' => true,
                'claiming_enabled' => false,
                'claimed_at' => now(),
            ]);

            $lockedClaim->update([
                'status' => ProviderClaim::STATUS_APPROVED,
                'reviewed_by_user_id' => $request->user()->id,
                'admin_note' => trim((string) ($request->input('admin_note'))) ?: null,
                'reviewed_at' => now(),
            ]);

            ProviderClaim::where('ad_id', $ad->id)
                ->whereKeyNot($lockedClaim->id)
                ->where('status', ProviderClaim::STATUS_PENDING)
                ->update([
                    'status' => ProviderClaim::STATUS_REJECTED,
                    'reviewed_by_user_id' => $request->user()->id,
                    'admin_note' => 'Encerrada automaticamente porque outra solicitação foi aprovada.',
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
        });
    }
}
