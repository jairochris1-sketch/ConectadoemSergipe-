<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ─── 1. FEATURES (serviços disponíveis na plataforma) ───────────────────
        $features = [
            ['key' => 'store_limit',         'name' => 'Lojas simultâneas',             'type' => 'integer',   'sort_order' => 1],
            ['key' => 'product_limit',        'name' => 'Produtos por loja',             'type' => 'integer',   'sort_order' => 2],
            ['key' => 'ad_limit',             'name' => 'Anúncios ativos',               'type' => 'integer',   'sort_order' => 3],
            ['key' => 'store_banners',        'name' => 'Banners por loja',              'type' => 'integer',   'sort_order' => 4],
            ['key' => 'store_gallery',        'name' => 'Fotos na galeria da loja',      'type' => 'integer',   'sort_order' => 5],
            ['key' => 'analytics_days',       'name' => 'Histórico de estatísticas',     'type' => 'integer',   'sort_order' => 6],
            ['key' => 'promotions_limit',     'name' => 'Promoções simultâneas',         'type' => 'integer',   'sort_order' => 7],
            ['key' => 'store_featured',       'name' => 'Loja em destaque na vitrine',   'type' => 'boolean',   'sort_order' => 8],
            ['key' => 'verified_badge',       'name' => 'Selo de verificado',            'type' => 'boolean',   'sort_order' => 9],
            ['key' => 'priority_support',     'name' => 'Suporte prioritário',           'type' => 'boolean',   'sort_order' => 10],
            ['key' => 'professional_profiles','name' => 'Perfis profissionais',          'type' => 'integer',   'sort_order' => 11],
            ['key' => 'provider_featured',    'name' => 'Prestador em destaque',          'type' => 'boolean',   'sort_order' => 12],
            ['key' => 'feed_sponsored',       'name' => 'Anúncios patrocinados no feed',  'type' => 'boolean',   'sort_order' => 13],
        ];

        foreach ($features as $feature) {
            DB::table('plan_features')->updateOrInsert(
                ['key' => $feature['key']],
                array_merge($feature, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $featureIds = DB::table('plan_features')->pluck('id', 'key');

        // ─── 2. PLANOS ──────────────────────────────────────────────────────────
        $plans = [
            // ── GRATUITO ─────────────────────────────────────────────────────────
            [
                'slug'           => 'free',
                'name'           => 'Gratuito',
                'badge_label'    => null,
                'headline'       => 'Comece a divulgar seu negócio sem custo.',
                'description'    => 'Ideal para quem está começando: publique 1 anúncio, crie 1 loja com até 5 produtos e mostre seu perfil de prestador de serviço gratuitamente.',
                'price'          => 0.00,
                'color'          => 'secondary',
                'is_active'      => true,
                'is_highlighted' => false,
                'sort_order'     => 1,
            ],
            // ── START ─────────────────────────────────────────────────────────────
            [
                'slug'           => 'start',
                'name'           => 'Plano Start',
                'badge_label'    => 'POPULAR',
                'headline'       => 'Dê o primeiro passo com sua loja online.',
                'description'    => 'Ideal para pequenos vendedores e prestadores que querem mais profissionalismo.',
                'price'          => 25.00,
                'color'          => 'primary',
                'is_active'      => true,
                'is_highlighted' => false,
                'sort_order'     => 2,
            ],
            // ── PRO ──────────────────────────────────────────────────────────────
            [
                'slug'           => 'pro',
                'name'           => 'Plano PRO',
                'badge_label'    => 'MAIS ESCOLHIDO',
                'headline'       => 'Venda todos os dias e fique à frente.',
                'description'    => 'Para lojas que vendem constantemente e querem mais visibilidade, destaque e mais espaço para crescer.',
                'price'          => 49.90,
                'color'          => 'primary',
                'is_active'      => true,
                'is_highlighted' => true,
                'sort_order'     => 3,
            ],
            // ── PREMIUM ───────────────────────────────────────────────────────────
            [
                'slug'           => 'enterprise',
                'name'           => 'Plano Premium',
                'badge_label'    => 'MÁXIMA VISIBILIDADE',
                'headline'       => 'Domine o mercado em Sergipe!',
                'description'    => 'Para empresas que querem dominar as buscas, ter até 3 lojas e suporte exclusivo.',
                'price'          => 99.90,
                'color'          => 'purple',
                'is_active'      => true,
                'is_highlighted' => false,
                'sort_order'     => 4,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $plan['slug']],
                array_merge($plan, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $planIds = DB::table('plans')->pluck('id', 'slug');

        // ─── 3. VALORES DAS FEATURES POR PLANO ──────────────────────────────────
        // null = ilimitado | '0' = bloqueado | número como string = limite
        $values = [

            // ── FREE ────────────────────────────────────────────────────────────
            ['plan' => 'free', 'feature' => 'store_limit',          'value' => '0',    'show' => true],   // Gratuito: sem loja (upgrade necesssário)
            ['plan' => 'free', 'feature' => 'product_limit',         'value' => '5',    'show' => true],   // Gratuito: 5 produtos
            ['plan' => 'free', 'feature' => 'ad_limit',              'value' => '1',    'show' => true],   // Gratuito: 1 anúncio
            ['plan' => 'free', 'feature' => 'store_banners',         'value' => '0',    'show' => false],
            ['plan' => 'free', 'feature' => 'store_gallery',         'value' => '0',    'show' => false],
            ['plan' => 'free', 'feature' => 'analytics_days',        'value' => '7',    'show' => true],
            ['plan' => 'free', 'feature' => 'promotions_limit',      'value' => '0',    'show' => false],
            ['plan' => 'free', 'feature' => 'store_featured',        'value' => '0',    'show' => true],
            ['plan' => 'free', 'feature' => 'verified_badge',        'value' => '0',    'show' => true],
            ['plan' => 'free', 'feature' => 'priority_support',      'value' => '0',    'show' => true],
            ['plan' => 'free', 'feature' => 'professional_profiles', 'value' => '1',    'show' => false],
            ['plan' => 'free', 'feature' => 'provider_featured',     'value' => '0',    'show' => true],
            ['plan' => 'free', 'feature' => 'feed_sponsored',        'value' => '0',    'show' => true],

            // ── START ────────────────────────────────────────────────────────────
            ['plan' => 'start', 'feature' => 'store_limit',          'value' => '1',    'show' => true],
            ['plan' => 'start', 'feature' => 'product_limit',         'value' => '30',   'show' => true],  // Start: 30 produtos/loja
            ['plan' => 'start', 'feature' => 'ad_limit',              'value' => '5',    'show' => true],  // Start: 5 anúncios
            ['plan' => 'start', 'feature' => 'store_banners',         'value' => '1',    'show' => true],
            ['plan' => 'start', 'feature' => 'store_gallery',         'value' => '5',    'show' => true],
            ['plan' => 'start', 'feature' => 'analytics_days',        'value' => '30',   'show' => true],
            ['plan' => 'start', 'feature' => 'promotions_limit',      'value' => '2',    'show' => false],
            ['plan' => 'start', 'feature' => 'store_featured',        'value' => '0',    'show' => true],
            ['plan' => 'start', 'feature' => 'verified_badge',        'value' => '0',    'show' => true],
            ['plan' => 'start', 'feature' => 'priority_support',      'value' => '0',    'show' => true],
            ['plan' => 'start', 'feature' => 'professional_profiles', 'value' => '1',    'show' => false],
            ['plan' => 'start', 'feature' => 'provider_featured',     'value' => '1',    'show' => true],
            ['plan' => 'start', 'feature' => 'feed_sponsored',        'value' => '1',    'show' => true],

            // ── PRO ──────────────────────────────────────────────────────────────
            ['plan' => 'pro', 'feature' => 'store_limit',            'value' => '1',    'show' => true],
            ['plan' => 'pro', 'feature' => 'product_limit',          'value' => '100',  'show' => true],  // PRO: 100 produtos/loja
            ['plan' => 'pro', 'feature' => 'ad_limit',               'value' => '20',   'show' => true],  // PRO: 20 anúncios
            ['plan' => 'pro', 'feature' => 'store_banners',          'value' => '3',    'show' => true],
            ['plan' => 'pro', 'feature' => 'store_gallery',          'value' => '12',   'show' => true],
            ['plan' => 'pro', 'feature' => 'analytics_days',         'value' => '60',   'show' => true],
            ['plan' => 'pro', 'feature' => 'promotions_limit',       'value' => '5',    'show' => false],
            ['plan' => 'pro', 'feature' => 'store_featured',         'value' => '1',    'show' => true],
            ['plan' => 'pro', 'feature' => 'verified_badge',         'value' => '1',    'show' => true],
            ['plan' => 'pro', 'feature' => 'priority_support',       'value' => '1',    'show' => true],
            ['plan' => 'pro', 'feature' => 'professional_profiles',  'value' => null,   'show' => false],
            ['plan' => 'pro', 'feature' => 'provider_featured',      'value' => '1',    'show' => true],
            ['plan' => 'pro', 'feature' => 'feed_sponsored',         'value' => '1',    'show' => true],

            // ── ENTERPRISE (Premium) ─────────────────────────────────────────────
            ['plan' => 'enterprise', 'feature' => 'store_limit',            'value' => '3',   'show' => true],
            ['plan' => 'enterprise', 'feature' => 'product_limit',          'value' => '300', 'show' => true],  // Premium: 300 produtos/loja
            ['plan' => 'enterprise', 'feature' => 'ad_limit',               'value' => '60',  'show' => true],  // Premium: 60 anúncios
            ['plan' => 'enterprise', 'feature' => 'store_banners',          'value' => '6',   'show' => true],
            ['plan' => 'enterprise', 'feature' => 'store_gallery',          'value' => '20',  'show' => true],
            ['plan' => 'enterprise', 'feature' => 'analytics_days',         'value' => '90',  'show' => true],
            ['plan' => 'enterprise', 'feature' => 'promotions_limit',       'value' => '10',  'show' => false],
            ['plan' => 'enterprise', 'feature' => 'store_featured',         'value' => '1',   'show' => true],
            ['plan' => 'enterprise', 'feature' => 'verified_badge',         'value' => '1',   'show' => true],
            ['plan' => 'enterprise', 'feature' => 'priority_support',       'value' => '1',   'show' => true],
            ['plan' => 'enterprise', 'feature' => 'professional_profiles',  'value' => null,  'show' => false],
            ['plan' => 'enterprise', 'feature' => 'provider_featured',      'value' => '1',   'show' => true],
            ['plan' => 'enterprise', 'feature' => 'feed_sponsored',         'value' => '1',   'show' => true],
        ];

        foreach ($values as $v) {
            DB::table('plan_feature_values')->updateOrInsert(
                [
                    'plan_id'         => $planIds[$v['plan']],
                    'plan_feature_id' => $featureIds[$v['feature']],
                ],
                [
                    'value'        => $v['value'],
                    'show_on_page' => $v['show'],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]
            );
        }
    }
}
