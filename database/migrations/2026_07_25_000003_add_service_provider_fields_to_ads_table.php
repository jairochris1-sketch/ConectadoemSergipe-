<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('advertiser_type')->default('Prestador de Serviço');
            $table->string('cnpj')->nullable();
            $table->string('state')->default('Sergipe');
            $table->string('region')->nullable();
            $table->text('business_hours')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id', 'advertiser_type', 'cnpj', 'state', 'region',
                'business_hours', 'instagram', 'facebook', 'logo', 'banner'
            ]);
        });
    }
};
