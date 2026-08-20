<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('folder_id')
                ->nullable()
                ->after('ad_id')
                ->constrained('favorite_folders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
        });

        Schema::dropIfExists('favorite_folders');
    }
};
