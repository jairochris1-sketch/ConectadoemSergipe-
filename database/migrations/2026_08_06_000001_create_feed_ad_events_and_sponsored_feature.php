<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_ad_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->char('visitor_key', 64)->nullable();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->string('event_type', 24);
            $table->boolean('is_sponsored')->default(false);
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event_type', 'created_at'], 'feed_events_user_type_created');
            $table->index(['visitor_key', 'event_type', 'created_at'], 'feed_events_visitor_type_created');
            $table->index(['ad_id', 'event_type', 'created_at'], 'feed_events_ad_type_created');
        });

        DB::table('plan_features')->updateOrInsert(
            ['key' => 'feed_sponsored'],
            [
                'name' => 'Anúncios patrocinados no feed',
                'description' => 'Permite a rotação equilibrada de anúncios ativos no feed da Comunidade.',
                'type' => 'boolean',
                'sort_order' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $featureId = DB::table('plan_features')->where('key', 'feed_sponsored')->value('id');

        DB::table('plans')->orderBy('id')->get()->each(function ($plan) use ($featureId) {
            DB::table('plan_feature_values')->updateOrInsert(
                ['plan_id' => $plan->id, 'plan_feature_id' => $featureId],
                [
                    'value' => (float) $plan->price > 0 ? '1' : '0',
                    'show_on_page' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });
    }

    public function down(): void
    {
        DB::table('plan_features')->where('key', 'feed_sponsored')->delete();
        Schema::dropIfExists('feed_ad_events');
    }
};
