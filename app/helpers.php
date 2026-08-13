<?php

if (! function_exists('format_price')) {
    function format_price($price, string $defaultIfEmpty = 'Sob consulta'): string
    {
        if ($price === null || $price === '' || (float) $price <= 0) {
            return $defaultIfEmpty;
        }

        $val = (float) $price;
        if (floor($val) == $val) {
            return 'R$ ' . number_format($val, 0, ',', '.');
        }

        return 'R$ ' . number_format($val, 2, ',', '.');
    }
}
