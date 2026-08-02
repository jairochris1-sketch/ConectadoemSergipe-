<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('header_layout', 20)->default('horizontal');
            $table->string('theme_preference', 20)->default('system');
            $table->boolean('notification_messages_enabled')->default(true);
            $table->boolean('notification_reviews_enabled')->default(true);
            $table->boolean('notification_reports_enabled')->default(true);
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('website')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'header_layout',
                'theme_preference',
                'notification_messages_enabled',
                'notification_reviews_enabled',
                'notification_reports_enabled',
                'instagram',
                'facebook',
                'website',
            ]);
        });
    }
};
