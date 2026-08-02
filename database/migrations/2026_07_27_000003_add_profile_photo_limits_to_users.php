<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_plan', 20)->default('free')->after('role');
            $table->unsignedTinyInteger('avatar_change_count')->default(0)->after('avatar');
            $table->timestamp('avatar_change_window_started_at')->nullable()->after('avatar_change_count');
            $table->timestamp('avatar_change_locked_until')->nullable()->after('avatar_change_window_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'avatar_change_count',
                'avatar_change_window_started_at',
                'avatar_change_locked_until',
            ]);
        });
    }
};
