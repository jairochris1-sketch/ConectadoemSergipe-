<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_help_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 40)->index();
            $table->string('title', 120);
            $table->text('description');
            $table->string('city', 120)->index();
            $table->string('neighborhood', 120);
            $table->string('urgency', 20)->default('normal')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedTinyInteger('duration_days')->default(7);
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('moderation_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['city', 'status', 'published_at'], 'community_help_local_feed_index');
        });

        Schema::create('community_help_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_help_request_id')
                ->constrained('community_help_requests')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('message', 700);
            $table->boolean('is_selected')->default(false);
            $table->timestamps();

            $table->unique(['community_help_request_id', 'user_id'], 'community_help_response_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_help_responses');
        Schema::dropIfExists('community_help_requests');
    }
};
