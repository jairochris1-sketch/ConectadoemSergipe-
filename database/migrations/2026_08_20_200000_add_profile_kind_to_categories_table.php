<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'profile_kind')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('profile_kind', 50)->nullable()->after('module')->index();
            });
        }

        DB::table('categories')
            ->whereIn('name', [
                'Corretora',
                'Farmacêutico',
                'Farmacêutica',
            ])
            ->update([
                'module' => 'services',
                'profile_kind' => 'liberal_professional',
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'profile_kind')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('profile_kind');
            });
        }
    }
};
