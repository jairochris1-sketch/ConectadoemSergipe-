<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_posts', function (Blueprint $table) {
            $table->string('topic', 30)->default('updates')->index()->after('notice_level');
            $table->boolean('is_pinned')->default(false)->index()->after('topic');
            $table->timestamp('pinned_at')->nullable()->index()->after('is_pinned');
        });

        DB::table('feed_posts')->where('type', 'notice')->where('notice_level', 'urgent')->update(['topic' => 'urgent']);
        DB::table('feed_posts')->where('type', 'notice')->where('notice_level', 'important')->update(['topic' => 'important']);
        DB::table('feed_posts')->where('type', 'notice')->where('notice_level', 'information')->update(['topic' => 'informative']);
    }

    public function down(): void
    {
        Schema::table('feed_posts', function (Blueprint $table) {
            $table->dropColumn(['topic', 'is_pinned', 'pinned_at']);
        });
    }
};
