<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $categories = config('marketplace.service_categories_by_profile_kind.cultural_artist', []);
        $sortOrder = (int) DB::table('categories')->max('sort_order');

        foreach ($categories as $name) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'module' => 'services',
                    'profile_kind' => 'cultural_artist',
                    'icon' => 'fa-palette',
                    'color' => '#7c3aed',
                    'sort_order' => ++$sortOrder,
                    'active' => true,
                ]
            );
        }

        DB::table('categories')
            ->whereIn('name', ['Personalizados, Artes Sublimadas e Logos', 'Plotagem'])
            ->where('profile_kind', 'cultural_artist')
            ->update(['profile_kind' => 'service_company']);

        DB::table('categories')
            ->where('name', 'Editor de Fotos')
            ->where('profile_kind', 'cultural_artist')
            ->update(['profile_kind' => 'professional']);
    }

    public function down(): void
    {
        DB::table('categories')
            ->whereIn('slug', collect(config('marketplace.service_categories_by_profile_kind.cultural_artist', []))
                ->map(fn (string $name) => Str::slug($name))
                ->all())
            ->where('profile_kind', 'cultural_artist')
            ->update(['profile_kind' => null]);
    }
};
