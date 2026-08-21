<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ads', 'service_modes')) {
            Schema::table('ads', function (Blueprint $table) {
                $table->json('service_modes')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ads', 'service_modes')) {
            Schema::table('ads', function (Blueprint $table) {
                $table->dropColumn('service_modes');
            });
        }
    }
};
