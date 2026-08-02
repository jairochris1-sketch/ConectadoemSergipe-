<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_id')->nullable()->change();
            $table->foreignId('store_id')
                ->nullable()
                ->after('ad_id')
                ->constrained('stores')
                ->cascadeOnDelete();
            $table->unique(['store_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['store_id', 'user_id']);
            $table->dropConstrainedForeignId('store_id');
            $table->unsignedBigInteger('ad_id')->nullable(false)->change();
        });
    }
};
