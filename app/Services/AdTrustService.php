<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\User;

class AdTrustService
{
    public function calculate(Ad $ad): array
    {
        $score = 55;
        $checks = [];
        $user = $ad->user;

        if ($user?->email_verified_at) {
            $score += 15;
            $checks[] = 'E-mail confirmado';
        }

        if ($user?->phone || $user?->whatsapp) {
            $score += 15;
            $checks[] = 'Telefone informado';
        }

        if ($user?->cpf_cnpj || $ad->cnpj) {
            $score += 5;
            $checks[] = 'Documento informado';
        }

        $profileYears = $user?->created_at?->diffInYears(now()) ?? 0;
        if ($profileYears >= 1) {
            $score += min(10, $profileYears * 3);
            $checks[] = "Perfil há {$profileYears} ".($profileYears === 1 ? 'ano' : 'anos');
        }

        $confirmedReports = $ad->reports()
            ->where('status', 'resolved')
            ->whereIn('admin_action', ['block', 'delete', 'suspend'])
            ->count();

        if ($confirmedReports === 0) {
            $score += 10;
            $checks[] = 'Nenhuma denúncia confirmada';
        } else {
            $score -= min(45, $confirmedReports * 15);
        }

        return [
            'score' => max(10, min(100, $score)),
            'checks' => $checks,
            'label' => $score >= 85 ? 'Alta confiança' : ($score >= 65 ? 'Confiança moderada' : 'Atenção recomendada'),
            'signals' => $this->automaticSignals($ad),
        ];
    }

    private function automaticSignals(Ad $ad): array
    {
        $signals = [];

        if (Ad::whereKeyNot($ad->id)->where('title', $ad->title)->exists()) {
            $signals[] = 'Título duplicado em outro anúncio';
        }

        if (mb_strlen($ad->description) >= 40 && Ad::whereKeyNot($ad->id)->where('description', $ad->description)->exists()) {
            $signals[] = 'Texto repetido em outro anúncio';
        }

        $imagePaths = $ad->images->pluck('image_path')->filter();
        if ($imagePaths->isNotEmpty() && AdImage::where('ad_id', '!=', $ad->id)->whereIn('image_path', $imagePaths)->exists()) {
            $signals[] = 'Imagem repetida em outro anúncio';
        }

        $phone = $ad->user?->phone ?: $ad->user?->whatsapp;
        if ($phone && User::whereKeyNot($ad->user_id)->where(function ($query) use ($phone) {
            $query->where('phone', $phone)->orWhere('whatsapp', $phone);
        })->exists()) {
            $signals[] = 'Telefone usado em mais de uma conta';
        }

        $averagePrice = Ad::where('module', $ad->module)->where('status', 'active')->where('price', '>', 0)->avg('price');
        if ($ad->price > 0 && $averagePrice > 0 && $ad->price < ($averagePrice * 0.35)) {
            $signals[] = 'Preço muito abaixo da média do módulo';
        }

        if ($ad->reports()->where('reason', 'scam')->whereIn('status', ['open', 'reviewing', 'resolved'])->exists()) {
            $signals[] = 'Há denúncia de possível golpe';
        }

        return $signals;
    }
}
