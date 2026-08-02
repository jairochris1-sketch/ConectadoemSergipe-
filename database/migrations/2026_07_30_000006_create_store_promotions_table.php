<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('coupon_code', 40)->nullable();
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 10, 2);
            $table->string('description', 500)->nullable();
            $table->string('terms', 500)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'coupon_code']);
            $table->index(['store_id', 'active', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_promotions');
    }
};
