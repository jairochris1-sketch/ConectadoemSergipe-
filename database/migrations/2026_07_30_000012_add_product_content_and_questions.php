<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('technical_specs');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_neighborhood', 120)->nullable()->after('delivery_city');
        });

        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['ad_id', 'active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_questions');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_neighborhood');
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
