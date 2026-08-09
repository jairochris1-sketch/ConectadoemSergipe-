<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body')->nullable();
            $table->string('city', 120)->nullable()->index();
            $table->string('content_hash', 64)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->string('moderation_reason', 500)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('feed_post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('file_hash', 64)->index();
            $table->unsignedTinyInteger('position')->default(0);
            $table->string('moderation_status', 20)->default('manual_review');
            $table->json('moderation_labels')->nullable();
            $table->timestamps();
        });

        Schema::create('feed_post_likes', function (Blueprint $table) {
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['feed_post_id', 'user_id']);
        });

        Schema::create('feed_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('body', 500);
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });

        Schema::create('feed_post_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['feed_post_id', 'reporter_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_post_reports');
        Schema::dropIfExists('feed_comments');
        Schema::dropIfExists('feed_post_likes');
        Schema::dropIfExists('feed_post_images');
        Schema::dropIfExists('feed_posts');
    }
};
