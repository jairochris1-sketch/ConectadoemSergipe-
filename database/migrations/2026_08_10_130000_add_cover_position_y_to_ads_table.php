<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ads', 'cover_position_y')) {
            Schema::table('ads', function (Blueprint $table) {
                $table->integer('cover_position_y')->nullable()->default(50)->after('banner');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ads', 'cover_position_y')) {
            Schema::table('ads', function (Blueprint $table) {
                $table->dropColumn('cover_position_y');
            });
        }
    }
};
