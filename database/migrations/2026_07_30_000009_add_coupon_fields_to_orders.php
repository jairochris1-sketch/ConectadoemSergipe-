<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_promotion_id')
                ->nullable()
                ->after('store_name')
                ->constrained('store_promotions')
                ->nullOnDelete();
            $table->string('coupon_code', 40)->nullable()->after('store_promotion_id');
            $table->string('discount_type', 20)->nullable()->after('coupon_code');
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            $table->decimal('discount_total', 12, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_promotion_id');
            $table->dropColumn([
                'coupon_code',
                'discount_type',
                'discount_value',
                'discount_total',
            ]);
        });
    }
};
