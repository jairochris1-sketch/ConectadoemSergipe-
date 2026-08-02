<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('store_id')
                ->nullable()
                ->after('ad_id')
                ->constrained('stores')
                ->nullOnDelete();
            $table->index(['store_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['store_id', 'status']);
            $table->dropConstrainedForeignId('store_id');
        });
    }
};
