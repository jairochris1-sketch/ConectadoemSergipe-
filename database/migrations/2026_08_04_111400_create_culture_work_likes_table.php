<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('culture_work_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('culture_work_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'culture_work_id']);
        });

        Schema::table('culture_works', function (Blueprint $table) {
            $table->unsignedBigInteger('likes_count')->default(0)->after('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('culture_works', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });

        Schema::dropIfExists('culture_work_likes');
    }
};
