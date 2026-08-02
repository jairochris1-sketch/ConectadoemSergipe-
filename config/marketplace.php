<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Limites de perfis profissionais por plano
    |--------------------------------------------------------------------------
    |
    | Use null para planos sem limite. Os planos pagos exibidos atualmente no
    | site anunciam perfis/anúncios ilimitados, por isso permanecem sem limite.
    |
    */
    'professional_profile_limits' => [
        'free'         => 1,
        'start'        => 1,
        'pro'          => null,
        'professional' => null,
        'profissional' => null,
        'enterprise'   => null,
        'premium'      => null,
        'gold'         => null,
        'ouro'         => null,
    ],

    /*
    | Limites de lojas simultâneas por plano.
    | null = ilimitado | 0 = sem loja | inteiro = limite
    */
    'store_limits' => [
        'free'         => 0,
        'start'        => 1,
        'pro'          => 1,
        'professional' => 1,
        'profissional' => 1,
        'enterprise'   => 3,
        'premium'      => 3,
        'gold'         => 3,
        'ouro'         => 3,
    ],

    'store_product_limits' => [
        'free'         => 0,
        'start'        => 30,
        'pro'          => null,
        'professional' => null,
        'profissional' => null,
        'enterprise'   => null,
        'premium'      => null,
        'gold'         => null,
        'ouro'         => null,
    ],

    'plan_labels' => [
        'free'         => 'Gratuito',
        'start'        => 'Start',
        'pro'          => 'PRO',
        'professional' => 'PRO',
        'profissional' => 'PRO',
        'enterprise'   => 'Premium',
        'premium'      => 'Premium',
        'gold'         => 'Premium',
        'ouro'         => 'Premium',
    ],

    'store_featured_enabled' => [
        'free'         => false,
        'start'        => false,
        'pro'          => true,
        'professional' => false,
        'profissional' => false,
        'enterprise'   => true,
        'premium'      => true,
        'gold'         => true,
        'ouro'         => true,
    ],

    'store_featured_default_days' => 30,

    'store_media_limits' => [
        'free'         => ['banners' => 0, 'gallery' => 0],
        'start'        => ['banners' => 1, 'gallery' => 5],
        'pro'          => ['banners' => 3, 'gallery' => 12],
        'professional' => ['banners' => 3, 'gallery' => 12],
        'profissional' => ['banners' => 3, 'gallery' => 12],
        'enterprise'   => ['banners' => 6, 'gallery' => 20],
        'premium'      => ['banners' => 6, 'gallery' => 20],
        'gold'         => ['banners' => 6, 'gallery' => 20],
        'ouro'         => ['banners' => 6, 'gallery' => 20],
    ],

    'store_analytics_period_days' => [
        'free'         => 7,
        'start'        => 30,
        'pro'          => 60,
        'professional' => 30,
        'profissional' => 30,
        'enterprise'   => 90,
        'premium'      => 90,
        'gold'         => 90,
        'ouro'         => 90,
    ],

    /*
    | Quantidade máxima de promoções simultaneamente ativas por plano.
    */
    'store_promotion_limits' => [
        'free'         => 0,
        'start'        => 2,
        'pro'          => 5,
        'professional' => 5,
        'profissional' => 5,
        'enterprise'   => 10,
        'premium'      => 10,
        'gold'         => 10,
        'ouro'         => 10,
    ],
];
