<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('sku', 100)->nullable()->unique()->after('display_mode');
            $table->decimal('sale_price', 12, 2)->nullable()->after('price');
            $table->unsignedInteger('stock_quantity')->default(0)->after('sale_price');
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('stock_quantity');
            $table->boolean('track_stock')->default(false)->after('low_stock_threshold');
            $table->boolean('allow_backorders')->default(false)->after('track_stock');
            $table->unsignedSmallInteger('minimum_quantity')->default(1)->after('allow_backorders');
            $table->json('technical_specs')->nullable()->after('minimum_quantity');
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->string('name');
            $table->json('attributes')->nullable();
            $table->string('sku', 100)->nullable()->unique();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_adjustment', 12, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['ad_id', 'active']);
        });

        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['ad_id', 'active']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('pickup_available')->default(true)->after('product_display_mode');
            $table->boolean('delivery_available')->default(true)->after('pickup_available');
            $table->json('delivery_cities')->nullable()->after('delivery_available');
            $table->json('delivery_neighborhoods')->nullable()->after('delivery_cities');
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('delivery_neighborhoods');
            $table->json('delivery_region_fees')->nullable()->after('delivery_fee');
            $table->decimal('free_delivery_threshold', 12, 2)->nullable()->after('delivery_region_fees');
            $table->unsignedSmallInteger('delivery_min_minutes')->nullable()->after('free_delivery_threshold');
            $table->unsignedSmallInteger('delivery_max_minutes')->nullable()->after('delivery_min_minutes');
            $table->decimal('minimum_order', 12, 2)->default(0)->after('delivery_max_minutes');
            $table->string('pickup_address')->nullable()->after('minimum_order');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('discount_total');
            $table->timestamp('stock_deducted_at')->nullable()->after('placed_at');
            $table->timestamp('stock_restored_at')->nullable()->after('stock_deducted_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variation_id')
                ->nullable()
                ->after('ad_id')
                ->constrained('product_variations')
                ->nullOnDelete();
            $table->string('variation_name')->nullable()->after('product_title');
            $table->string('sku', 100)->nullable()->after('variation_name');
            $table->json('addons')->nullable()->after('sku');
            $table->text('customer_note')->nullable()->after('addons');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variation_id');
            $table->dropColumn(['variation_name', 'sku', 'addons', 'customer_note']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'stock_deducted_at', 'stock_restored_at']);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_available',
                'delivery_available',
                'delivery_cities',
                'delivery_neighborhoods',
                'delivery_fee',
                'delivery_region_fees',
                'free_delivery_threshold',
                'delivery_min_minutes',
                'delivery_max_minutes',
                'minimum_order',
                'pickup_address',
            ]);
        });

        Schema::dropIfExists('product_addons');
        Schema::dropIfExists('product_variations');

        Schema::table('ads', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn([
                'sku',
                'sale_price',
                'stock_quantity',
                'low_stock_threshold',
                'track_stock',
                'allow_backorders',
                'minimum_quantity',
                'technical_specs',
            ]);
        });
    }
};
