<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('profile_kind', 30)->nullable()->after('module');
        });

        DB::table('ads')
            ->where('module', 'services')
            ->whereNull('profile_kind')
            ->update(['profile_kind' => 'professional']);
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('profile_kind');
        });
    }
};
