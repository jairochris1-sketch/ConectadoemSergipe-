<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feed_posts', function (Blueprint $table) {
            $table->string('video_path')->nullable()->after('body');
            $table->string('video_url', 2048)->nullable()->after('video_path');
            $table->unsignedSmallInteger('video_duration_seconds')->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('feed_posts', function (Blueprint $table) {
            $table->dropColumn(['video_path', 'video_url', 'video_duration_seconds']);
        });
    }
};
