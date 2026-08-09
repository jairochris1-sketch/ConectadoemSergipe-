<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_posts', function (Blueprint $table) {
            $table->string('type', 20)->default('post')->index();
            $table->string('title', 180)->nullable();
            $table->string('notice_level', 20)->nullable();
            $table->timestamp('poll_ends_at')->nullable();
        });

        Schema::create('feed_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->string('label', 180);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('feed_poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feed_poll_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['feed_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_poll_votes');
        Schema::dropIfExists('feed_poll_options');
        Schema::table('feed_posts', function (Blueprint $table) {
            $table->dropColumn(['type', 'title', 'notice_level', 'poll_ends_at']);
        });
    }
};
