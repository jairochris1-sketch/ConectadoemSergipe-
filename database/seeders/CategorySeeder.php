<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Limpar banco de categorias antes de repopular para alinhamento total
        Category::truncate();

        // 1. SERVIÇOS & PRESTADORES
        $serviceCategories = [
            'Açougue', 'Advogado', 'Aluga Cadeiras e Mesas', 'Artes Personalizadas', 'Ateliê',
            'Cabeleireira', 'Cantores e Bandas', 'Carro de Mudança', 'Carroceiro', 'Cartomante',
            'Cartório', 'Chaveiro', 'Confeitaria', 'Conserto de Portão', 'Consertos de Eletrodomésticos',
            'Consertos de TV e Som', 'Costureira', 'Dentista', 'Designer de Sobrancelhas', 'Diarista',
            'Editor de Fotos', 'Eletricista', 'Empréstimos e Serviços', 'Encanador', 'Enfermeira(o)',
            'Entregador', 'Entregador de Gás', 'Faxineira', 'Faz Tudo - Serviços Residenciais', 'Fisioterapeuta',
            'Forro de PVC', 'Fotógrafo', 'Frete e Mudanças', 'Gesseiro', 'Gráficas', 'Guincho',
            'Instalação de Portas e Janelas', 'Instalador de Antenas', 'Instalador de Ar-condicionado',
            'Instrutor Autônomo de Carro e Moto', 'Jardineiro', 'Lava Jato', 'Locação de Kits para Festas',
            'Lojas', 'Manicure e Pedicure', 'Marceneiro', 'Maquiadora', 'Material de Construção',
            'Mecânico', 'Montador de Móveis', 'Moto Táxi', 'Móveis Planejados', 'Pedreiro',
            'Pedreiro de Acabamento', 'Pintor', 'Pizzaria', 'Plotagem', 'Portões e Manutenção',
            'Pousada e Hotel', 'Programador', 'Prótese Dentária', 'Provedor de Internet',
            'Reformas e Fabricação de Estofados', 'Restaurante', 'Soldador', 'Tatuador', 'Taxista',
            'Técnica de Enfermagem', 'Técnico de Informática', 'Técnico em Eletrônica', 'Uber'
        ];

        $sortIndex = 1;
        foreach ($serviceCategories as $name) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'module' => 'services',
                'icon' => $this->getServiceIcon($name),
                'color' => '#0d6efd',
                'sort_order' => $sortIndex++,
                'active' => true,
            ]);
        }

        // 2. PRODUTOS & ELETRÔNICOS (Com Subcategorias Hierárquicas)
        $productCategoriesTree = [
            [
                'name' => 'Eletrodomésticos & Eletrônicos',
                'icon' => 'fa-plug',
                'color' => '#06b6d4',
                'children' => [
                    [
                        'name' => 'Fornos & Fogões',
                        'children' => [
                            [
                                'name' => 'Peças, Conexões & Acessórios',
                                'children' => [
                                    ['name' => 'Kits de Instalação de Gás & Mangueiras'],
                                    ['name' => 'Válvulas, Regulares & Queimadores'],
                                ]
                            ],
                            ['name' => 'Fornos de Embutir & Elétricos'],
                            ['name' => 'Cooktops & Fogões Industriais'],
                        ]
                    ],
                    [
                        'name' => 'Geladeiras & Refrigeração',
                        'children' => [
                            ['name' => 'Peças, Motores & Filtros'],
                            ['name' => 'Freezers & Frigobares'],
                        ]
                    ],
                    [
                        'name' => 'Lavadoras, Secadoras & Tanquinhos',
                        'children' => [
                            ['name' => 'Peças & Placas de Máquinas de Lavar'],
                        ]
                    ],
                    ['name' => 'Ar-Condicionado & Ventilação'],
                ]
            ],
            [
                'name' => 'Celulares & Telefonia',
                'icon' => 'fa-mobile-screen',
                'color' => '#8b5cf6',
                'children' => [
                    ['name' => 'Smartphones & Celulares'],
                    [
                        'name' => 'Capas, Películas & Proteção',
                        'children' => [
                            ['name' => 'Capinhas Anti-impacto'],
                            ['name' => 'Películas de Vidro & 3D'],
                        ]
                    ],
                    ['name' => 'Carregadores, Cabos & Baterias'],
                ]
            ],
            [
                'name' => 'Computadores & Informática',
                'icon' => 'fa-laptop',
                'color' => '#6366f1',
                'children' => [
                    ['name' => 'Notebooks & Laptops'],
                    [
                        'name' => 'Periféricos & Acessórios',
                        'children' => [
                            ['name' => 'Teclados, Mouses & Headsets'],
                            ['name' => 'Cabos, Adaptadores & Hubs'],
                        ]
                    ],
                    ['name' => 'Peças & Placas de Vídeo'],
                ]
            ],
            ['name' => 'TV, Vídeo & Áudio', 'icon' => 'fa-tv', 'color' => '#0284c7'],
            ['name' => 'Móveis, Casa & Decoração', 'icon' => 'fa-couch', 'color' => '#d97706'],
            ['name' => 'Utilidades Domésticas', 'icon' => 'fa-kitchen-set', 'color' => '#b45309'],
            ['name' => 'Moda, Roupas & Calçados', 'icon' => 'fa-shirt', 'color' => '#ec4899'],
            ['name' => 'Beleza, Cosméticos & Perfumaria', 'icon' => 'fa-sparkles', 'color' => '#f43f5e'],
            ['name' => 'Relógios & Joias', 'icon' => 'fa-gem', 'color' => '#eab308'],
            ['name' => 'Bebês, Brinquedos & Infantil', 'icon' => 'fa-baby-carriage', 'color' => '#f472b6'],
            ['name' => 'Games & Consoles', 'icon' => 'fa-gamepad', 'color' => '#7c3aed'],
            ['name' => 'Esportes, Fitness & Ciclismo', 'icon' => 'fa-bicycle', 'color' => '#10b981'],
            ['name' => 'Instrumentos Musicais', 'icon' => 'fa-guitar', 'color' => '#3b82f6'],
            ['name' => 'Ferramentas, Jardim & Indústria', 'icon' => 'fa-screwdriver', 'color' => '#475569'],
            ['name' => 'Livros, Papelaria & Escritório', 'icon' => 'fa-book-open', 'color' => '#2563eb'],
            ['name' => 'Alimentos, Bebidas & Supermercado', 'icon' => 'fa-basket-shopping', 'color' => '#16a34a'],
            ['name' => 'Automotivo & Acessórios de Veículos', 'icon' => 'fa-car-battery', 'color' => '#64748b'],
            ['name' => 'Artesanato, Antiguidades & Colecionáveis', 'icon' => 'fa-box-archive', 'color' => '#92400e'],
            [
                'name' => 'Produtos Agrícolas & Agropecuária',
                'icon' => 'fa-tractor',
                'color' => '#16a34a',
                'children' => [
                    ['name' => 'Sementes, Mudas & Adubos'],
                    ['name' => 'Rações, Selaria & Insumos'],
                    ['name' => 'Produtos da Roça & Hortifrúti'],
                    ['name' => 'Gado, Cavalos & Pecuária'],
                    ['name' => 'Aves, Suínos & Animais Rurais'],
                ]
            ],
        ];

        foreach ($productCategoriesTree as $data) {
            $this->createCategoryBranch($data, 'products', null, $sortIndex);
        }

        // 3. IMÓVEIS
        $realEstateCategories = [
            ['name' => 'Casas para Venda', 'icon' => 'fa-house', 'color' => '#0d6efd'],
            ['name' => 'Casas para Aluguel', 'icon' => 'fa-house-user', 'color' => '#2563eb'],
            ['name' => 'Apartamentos para Venda', 'icon' => 'fa-building', 'color' => '#1d4ed8'],
            ['name' => 'Apartamentos para Aluguel', 'icon' => 'fa-building-user', 'color' => '#3b82f6'],
            ['name' => 'Terrenos, Lotes & Chácaras', 'icon' => 'fa-vector-square', 'color' => '#059669'],
            ['name' => 'Sítios, Fazendas & Agronegócio', 'icon' => 'fa-tree', 'color' => '#16a34a'],
            ['name' => 'Salas Comerciais, Lojas & Galpões', 'icon' => 'fa-store', 'color' => '#d97706'],
            ['name' => 'Aluguel por Temporada & Pousadas', 'icon' => 'fa-umbrella-beach', 'color' => '#0284c7'],
            ['name' => 'Garagens & Vagas de Garagem', 'icon' => 'fa-square-parking', 'color' => '#475569'],
        ];

        foreach ($realEstateCategories as $data) {
            Category::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'module' => 'real_estate',
                'icon' => $data['icon'],
                'color' => $data['color'],
                'sort_order' => $sortIndex++,
                'active' => true,
            ]);
        }

        // 4. VEÍCULOS
        $vehicleCategories = [
            ['name' => 'Carros & Utilitários', 'icon' => 'fa-car', 'color' => '#0284c7'],
            ['name' => 'Motos & Ciclomotores', 'icon' => 'fa-motorcycle', 'color' => '#ea580c'],
            ['name' => 'Caminhões, Ônibus & Vans', 'icon' => 'fa-truck', 'color' => '#475569'],
            ['name' => 'Náutica, Barcos & Lanchas', 'icon' => 'fa-ship', 'color' => '#0891b2'],
            ['name' => 'Quadriciclos & Buggies', 'icon' => 'fa-truck-monster', 'color' => '#d97706'],
            ['name' => 'Peças, Pneus & Acessórios', 'icon' => 'fa-gears', 'color' => '#64748b'],
            ['name' => 'Som, Vídeo & Alarmes Automotivos', 'icon' => 'fa-volume-high', 'color' => '#8b5cf6'],
        ];

        foreach ($vehicleCategories as $data) {
            Category::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'module' => 'vehicles',
                'icon' => $data['icon'],
                'color' => $data['color'],
                'sort_order' => $sortIndex++,
                'active' => true,
            ]);
        }

        // 5. EMPREGOS
        $jobCategories = [
            ['name' => 'Vagas Operacionais & Serviços Gerais', 'icon' => 'fa-briefcase', 'color' => '#2563eb'],
            ['name' => 'Vagas Comerciais & Vendas', 'icon' => 'fa-user-tie', 'color' => '#0d6efd'],
            ['name' => 'Vagas Administrativas & Financeiras', 'icon' => 'fa-file-invoice-dollar', 'color' => '#059669'],
            ['name' => 'Vagas em Tecnologia & TI', 'icon' => 'fa-laptop-code', 'color' => '#6366f1'],
            ['name' => 'Vagas em Saúde & Educação', 'icon' => 'fa-user-doctor', 'color' => '#ef4444'],
            ['name' => 'Estágios & Jovem Aprendiz', 'icon' => 'fa-user-graduate', 'color' => '#10b981'],
            ['name' => 'Freelancers, Bicos & Autônomos', 'icon' => 'fa-user-gear', 'color' => '#d97706'],
        ];

        foreach ($jobCategories as $data) {
            Category::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'module' => 'jobs',
                'icon' => $data['icon'],
                'color' => $data['color'],
                'sort_order' => $sortIndex++,
                'active' => true,
            ]);
        }

        // 6. ARTE & CULTURA
        $cultureCategories = [
            ['name' => 'Cordel, Poesia & Literatura Sergipana', 'icon' => 'fa-scroll', 'color' => '#9c2720'],
            ['name' => 'Artesanato, Xilogravuras & Esculturas', 'icon' => 'fa-scissors', 'color' => '#d97706'],
            ['name' => 'Quadros, Pinturas & Artes Visuais', 'icon' => 'fa-palette', 'color' => '#8b5cf6'],
            ['name' => 'Instrumentos, Discos & Produção Musical', 'icon' => 'fa-music', 'color' => '#ec4899'],
            ['name' => 'Livros, Revistas & Obras Raras', 'icon' => 'fa-book', 'color' => '#2563eb'],
            ['name' => 'Teatro, Dança & Performances', 'icon' => 'fa-masks-theater', 'color' => '#7c3aed'],
        ];

        foreach ($cultureCategories as $data) {
            Category::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'module' => 'culture',
                'icon' => $data['icon'],
                'color' => $data['color'],
                'sort_order' => $sortIndex++,
                'active' => true,
            ]);
        }

        // 8. LOJAS & NEGÓCIOS
        $storeCategories = [
            ['name' => 'Comércio & Varejo', 'icon' => 'fa-shop', 'color' => '#0d6efd'],
            ['name' => 'Gastronomia, Lanchonetes & Restaurantes', 'icon' => 'fa-utensils', 'color' => '#ea580c'],
            ['name' => 'Serviços de Beleza & Estética', 'icon' => 'fa-scissors', 'color' => '#ec4899'],
            ['name' => 'Saúde, Clínicas & Farmácias', 'icon' => 'fa-notes-medical', 'color' => '#ef4444'],
            ['name' => 'Auto Peças & Serviços Automotivos', 'icon' => 'fa-wrench', 'color' => '#475569'],
            ['name' => 'Construção, Ferragens & Ferramentas', 'icon' => 'fa-hammer', 'color' => '#d97706'],
            ['name' => 'Educação, Cursos & Escolas', 'icon' => 'fa-graduation-cap', 'color' => '#8b5cf6'],
        ];

        foreach ($storeCategories as $data) {
            Category::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'module' => 'stores',
                'icon' => $data['icon'],
                'color' => $data['color'],
                'sort_order' => $sortIndex++,
                'active' => true,
            ]);
        }
    }

    private function createCategoryBranch(array $data, string $module, ?int $parentId, int &$sortIndex): Category
    {
        $category = Category::create([
            'parent_id'  => $parentId,
            'name'       => $data['name'],
            'slug'       => Str::slug($data['name']),
            'module'     => $module,
            'icon'       => $data['icon'] ?? 'fa-tag',
            'color'      => $data['color'] ?? '#0d6efd',
            'sort_order' => $sortIndex++,
            'active'     => true,
        ]);

        if (!empty($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->createCategoryBranch($childData, $module, $category->id, $sortIndex);
            }
        }

        return $category;
    }

    private function getServiceIcon(string $name): string
    {
        return match ($name) {
            'Eletricista' => 'fa-bolt',
            'Encanador' => 'fa-faucet-drip',
            'Pintor' => 'fa-paint-roller',
            'Mecânico' => 'fa-screwdriver-wrench',
            'Advogado' => 'fa-scale-balanced',
            'Faxineira', 'Diarista' => 'fa-broom',
            'Marceneiro' => 'fa-hammer',
            'Técnico de Informática', 'Programador' => 'fa-laptop-code',
            'Frete e Mudanças', 'Carro de Mudança' => 'fa-truck-moving',
            'Restaurante', 'Pizzaria' => 'fa-utensils',
            'Pedreiro' => 'fa-trowel-bricks',
            'Cabeleireira', 'Maquiadora', 'Manicure e Pedicure' => 'fa-scissors',
            'Chaveiro' => 'fa-key',
            'Dentista' => 'fa-tooth',
            'Fotógrafo', 'Editor de Fotos' => 'fa-camera',
            'Jardineiro' => 'fa-plant-wilt',
            'Guincho' => 'fa-truck-pickup',
            'Costureira' => 'fa-shirt',
            'Uber', 'Taxista', 'Moto Táxi' => 'fa-car-side',
            default => 'fa-screwdriver-wrench',
        };
    }
}
