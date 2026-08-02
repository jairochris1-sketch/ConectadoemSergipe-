<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('description');
            $table->string('city', 100)->nullable()->after('category');
            $table->string('state', 2)->default('SE')->after('city');
            $table->string('phone', 20)->nullable()->after('state');
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->string('instagram', 120)->nullable()->after('whatsapp');
            $table->string('website')->nullable()->after('instagram');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'city',
                'state',
                'phone',
                'whatsapp',
                'instagram',
                'website',
            ]);
        });
    }
};
