<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('product_display_mode', 20)
                ->default('individual')
                ->after('category');
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->string('display_mode', 20)
                ->default('default')
                ->after('module');
        });

        DB::table('stores')
            ->select(['id', 'category'])
            ->orderBy('id')
            ->each(function ($store) {
                $category = Str::lower(Str::ascii((string) $store->category));
                $catalog = collect([
                    'alimentacao',
                    'confeitaria',
                    'pizzaria',
                    'hamburgueria',
                    'restaurante',
                    'lanchonete',
                    'marmitaria',
                    'padaria',
                    'acaiteria',
                    'cafeteria',
                ])->contains(fn ($term) => str_contains($category, $term));

                if ($catalog) {
                    DB::table('stores')
                        ->where('id', $store->id)
                        ->update(['product_display_mode' => 'catalog']);
                }
            });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('display_mode');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('product_display_mode');
        });
    }
};
