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

    public static function eligible(Ad $ad): bool
    {
        if ($ad->module !== 'services') {
            return false;
        }

        $category = Str::lower(Str::ascii($ad->display_category));

        return collect(self::ELIGIBLE_CATEGORIES)->contains(
            fn (string $eligible) => $category === Str::lower(Str::ascii($eligible))
        );
    }
}
