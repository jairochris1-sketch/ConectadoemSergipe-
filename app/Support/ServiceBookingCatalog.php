<?php

namespace App\Support;

use App\Models\Ad;
use Illuminate\Support\Str;

class ServiceBookingCatalog
{
    public const ELIGIBLE_CATEGORIES = [
        'Cabeleireira',
        'Manicure e Pedicure',
        'Maquiadora',
        'Designer de Sobrancelhas',
        'Tatuador',
    ];

    public const CONSULTATION_CATEGORY_TERMS = [
        'médico',
        'médica',
        'cardiologista',
        'dentista',
        'psicólogo',
        'psicóloga',
        'fisioterapeuta',
        'nutricionista',
        'fonoaudiólogo',
        'fonoaudióloga',
        'terapeuta ocupacional',
        'pediatra',
        'ginecologista',
        'dermatologista',
        'psiquiatra',
    ];

    public const CRM_CATEGORY_TERMS = [
        'médico',
        'médica',
        'alergista',
        'anestesiologista',
        'angiologista',
        'cardiologista',
        'cirurgião',
        'clínico geral',
        'coloproctologista',
        'dermatologista',
        'endocrinologista',
        'gastroenterologista',
        'geriatra',
        'pediatra',
        'ginecologista',
        'hematologista',
        'infectologista',
        'mastologista',
        'nefrologista',
        'neurocirurgião',
        'neurologista',
        'oftalmologista',
        'oncologista',
        'ortopedista',
        'otorrinolaringologista',
        'patologista',
        'pneumologista',
        'psiquiatra',
        'radiologista',
        'reumatologista',
        'urologista',
    ];

    private const NON_BOOKABLE_PROFILE_KINDS = [
        'service_company',
        'store_commerce',
        'agro_producer',
        'real_estate_agency',
        'hiring_company',
    ];

    public static function eligible(Ad $ad): bool
    {
        if ($ad->module !== 'services') {
            return false;
        }

        if (in_array($ad->profile_kind, self::NON_BOOKABLE_PROFILE_KINDS, true)) {
            return false;
        }

        return true;
    }

    public static function type(Ad $ad): ?string
    {
        if (! self::eligible($ad)) {
            return null;
        }

        return $ad->profile_kind === 'liberal_professional'
            && self::isConsultationCategory($ad->display_category)
                ? 'consultation'
                : 'professional_service';
    }

    public static function isConsultation(Ad $ad): bool
    {
        return self::type($ad) === 'consultation';
    }

    public static function isConsultationCategory(?string $category): bool
    {
        $normalizedCategory = Str::lower(Str::ascii((string) $category));

        return collect([...self::CONSULTATION_CATEGORY_TERMS, ...self::CRM_CATEGORY_TERMS])->contains(
            fn (string $term) => str_contains($normalizedCategory, Str::lower(Str::ascii($term)))
        );
    }

    public static function usesCrmCategory(?string $category): bool
    {
        $normalizedCategory = Str::lower(Str::ascii((string) $category));

        return collect(self::CRM_CATEGORY_TERMS)->contains(
            fn (string $term) => str_contains($normalizedCategory, Str::lower(Str::ascii($term)))
        );
    }

    public static function allowedAttendanceModes(Ad $ad): array
    {
        $configured = collect($ad->service_modes ?? [])
            ->filter(fn ($mode) => in_array($mode, ['presencial', 'online'], true))
            ->values()
            ->all();

        if ($configured !== []) {
            return $configured;
        }

        return self::isConsultation($ad) ? ['presencial', 'online'] : ['presencial'];
    }

    public static function actionLabel(Ad $ad): string
    {
        return self::isConsultation($ad) ? 'Agendar consulta' : 'Agendar serviço';
    }
}
