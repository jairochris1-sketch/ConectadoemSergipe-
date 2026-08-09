<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\ProviderClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProviderClaimController extends Controller
{
    public function create(Request $request, Ad $ad)
    {
        $this->ensureClaimableProvider($ad);

        $pendingClaim = $ad->providerClaims()
            ->where('claimant_user_id', $request->user()->id)
            ->where('status', ProviderClaim::STATUS_PENDING)
            ->latest()
            ->first();

        return view('provider-claims.create', compact('ad', 'pendingClaim'));
    }

    public function store(Request $request, Ad $ad)
    {
        $this->ensureClaimableProvider($ad);

        $validated = $request->validate([
            'relationship' => ['required', Rule::in(['owner', 'professional', 'employee', 'representative'])],
            'verification_phone' => ['nullable', 'required_without:verification_email', 'string', 'max:30'],
            'verification_email' => ['nullable', 'required_without:verification_phone', 'email', 'max:255'],
            'explanation' => ['nullable', 'string', 'max:1000'],
        ], [
            'verification_phone.required_without' => 'Informe um telefone ou um e-mail para confirmarmos a solicitação.',
            'verification_email.required_without' => 'Informe um telefone ou um e-mail para confirmarmos a solicitação.',
        ]);

        $phone = $this->normalizePhone($validated['verification_phone'] ?? null);
        if ($phone !== null && ! preg_match('/^\d{10,13}$/', $phone)) {
            return back()
                ->withErrors(['verification_phone' => 'Informe um telefone válido com DDD.'])
                ->withInput();
        }

        $alreadyPending = DB::transaction(function () use ($ad, $request, $validated, $phone) {
            $lockedAd = Ad::whereKey($ad->id)->lockForUpdate()->firstOrFail();
            $this->ensureClaimableProvider($lockedAd);

            $alreadyPending = $lockedAd->providerClaims()
                ->where('claimant_user_id', $request->user()->id)
                ->where('status', ProviderClaim::STATUS_PENDING)
                ->exists();

            if ($alreadyPending) {
                return true;
            }

            $lockedAd->providerClaims()->create([
                'claimant_user_id' => $request->user()->id,
                'relationship' => $validated['relationship'],
                'verification_phone' => $phone,
                'verification_email' => isset($validated['verification_email'])
                    ? mb_strtolower(trim($validated['verification_email']))
                    : null,
                'explanation' => isset($validated['explanation'])
                    ? trim($validated['explanation'])
                    : null,
                'status' => ProviderClaim::STATUS_PENDING,
            ]);

            return false;
        });

        if ($alreadyPending) {
            return redirect()
                ->route('provider.claim.create', $ad)
                ->with('info', 'Sua solicitação já está em análise.');
        }

        return redirect()
            ->route('provider.show', $ad->slug)
            ->with('claim_success', 'Solicitação enviada. Vamos verificar os dados antes de liberar o perfil.');
    }

    private function ensureClaimableProvider(Ad $ad): void
    {
        abort_unless($ad->module === 'services' && $ad->status === 'active', 404);
        abort_unless($ad->claiming_enabled, 404);
        abort_if($ad->is_claimed, 409, 'Este perfil já foi reivindicado.');
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone ?? '');

        if ($phone === '') {
            return null;
        }

        return str_starts_with($phone, '55') && in_array(strlen($phone), [12, 13], true)
            ? substr($phone, 2)
            : $phone;
    }
}
