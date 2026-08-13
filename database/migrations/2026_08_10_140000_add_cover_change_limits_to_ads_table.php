<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            if (! Schema::hasColumn('ads', 'cover_change_count')) {
                $table->unsignedTinyInteger('cover_change_count')->default(0)->after('cover_position_y');
            }
            if (! Schema::hasColumn('ads', 'cover_change_window_started_at')) {
                $table->timestamp('cover_change_window_started_at')->nullable()->after('cover_change_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            if (Schema::hasColumn('ads', 'cover_change_count')) {
                $table->dropColumn(['cover_change_count', 'cover_change_window_started_at']);
            }
        });
    }
};
