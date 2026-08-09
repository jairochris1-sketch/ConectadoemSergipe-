<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('plan_features')->updateOrInsert(
            ['key' => 'provider_featured'],
            [
                'name' => 'Prestador em destaque',
                'description' => 'Prioriza perfis profissionais no bloco de destaques da página inicial.',
                'type' => 'boolean',
                'sort_order' => 12,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $featureId = DB::table('plan_features')->where('key', 'provider_featured')->value('id');
        DB::table('plans')->orderBy('id')->get()->each(function ($plan) use ($featureId, $now) {
            DB::table('plan_feature_values')->updateOrInsert(
                ['plan_id' => $plan->id, 'plan_feature_id' => $featureId],
                [
                    'value' => (float) $plan->price > 0 ? '1' : '0',
                    'show_on_page' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        });
    }

    public function down(): void
    {
        DB::table('plan_features')->where('key', 'provider_featured')->delete();
    }
};
