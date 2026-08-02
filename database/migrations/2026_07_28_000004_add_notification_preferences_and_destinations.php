<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notifications_enabled')->default(true);
        });

        Schema::table('report_notifications', function (Blueprint $table) {
            $table->string('action_url', 500)->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('report_notifications', function (Blueprint $table) {
            $table->dropColumn('action_url');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notifications_enabled');
        });
    }
};
