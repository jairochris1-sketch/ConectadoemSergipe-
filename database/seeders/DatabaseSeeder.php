<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Ad;
use App\Models\AdImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrador do Sistema
        $admin = User::firstOrCreate(
            ['email' => 'admin@conectadoemsergipe.com.br'],
            [
                'name' => 'Administrador Conectado',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'phone' => '79999999999',
                'whatsapp' => '79999999999',
                'city' => 'Aracaju',
                'role' => 'admin'
            ]
        );

        // 2. Usuário de Teste Padrão
        $user = User::firstOrCreate(
            ['email' => 'usuario@exemplo.com'],
            [
                'name' => 'Anunciante de Sergipe',
                'username' => 'usuario',
                'password' => Hash::make('12345678'),
                'phone' => '79988887777',
                'whatsapp' => '79988887777',
                'city' => 'Aracaju',
                'role' => 'user'
            ]
        );

        // 3. Categorias Principais
        $categories = [
            ['name' => 'Imóveis', 'slug' => 'imoveis', 'icon' => 'fa-building', 'color' => '#0284c7', 'sort_order' => 1],
            ['name' => 'Veículos', 'slug' => 'veiculos', 'icon' => 'fa-car', 'color' => '#e11d48', 'sort_order' => 2],
            ['name' => 'Produtos', 'slug' => 'produtos', 'icon' => 'fa-mobile-screen', 'color' => '#7c3aed', 'sort_order' => 3],
            ['name' => 'Serviços', 'slug' => 'servicos', 'icon' => 'fa-screwdriver-wrench', 'color' => '#059669', 'sort_order' => 4],
            ['name' => 'Empregos', 'slug' => 'empregos', 'icon' => 'fa-briefcase', 'color' => '#d97706', 'sort_order' => 5],
            ['name' => 'Agro', 'slug' => 'agro', 'icon' => 'fa-tractor', 'color' => '#16a34a', 'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 4. Anúncios de Amostra
        $sampleAds = [
            [
                'user_id' => $user->id,
                'module' => 'real_estate',
                'title' => 'Apartamento 3 Quatros na Atalaia com Vista Mar',
                'slug' => 'apartamento-3-quartos-atalaia-' . rand(100, 999),
                'description' => 'Excelente apartamento na Orla da Atalaia em Aracaju. 3 quartos (1 suíte), varanda gourmet e 2 vagas de garagem.',
                'price' => 480000.00,
                'status' => 'active',
                'city' => 'Aracaju',
                'views' => 142
            ],
            [
                'user_id' => $user->id,
                'module' => 'vehicles',
                'title' => 'Toyota Corolla Cross XRE 2.0 Flex 2023 completo',
                'slug' => 'toyota-corolla-cross-2023-' . rand(100, 999),
                'description' => 'Veículo seminovo com apenas 25.000km rodados, revisões feitas na concessionária em Aracaju.',
                'price' => 139900.00,
                'status' => 'active',
                'city' => 'Aracaju',
                'views' => 98
            ],
            [
                'user_id' => $user->id,
                'module' => 'products',
                'title' => 'iPhone 15 Pro Max 256GB Titânio Natural',
                'slug' => 'iphone-15-pro-max-256gb-' . rand(100, 999),
                'description' => 'Aparelho em estado de novo, com caixa, cabo original e garantia Apple ativa.',
                'price' => 6499.00,
                'status' => 'active',
                'city' => 'Itabaiana',
                'views' => 210
            ]
        ];

        foreach ($sampleAds as $adData) {
            Ad::updateOrCreate(['slug' => $adData['slug']], $adData);
        }
    }
}
