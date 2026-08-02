<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\User;

class DemoAdSeeder
{
    public static function seedIfNeeded(): void
    {
        if (Ad::where('status', 'active')->count() >= 8) {
            return;
        }

        $user = User::where('role', 'admin')->first() ?: User::first();

        if (!$user) {
            return;
        }

        $adsData = [
            // IMÓVEIS
            [
                'user_id' => $user->id,
                'module' => 'real_estate',
                'title' => 'Apartamento no Jardins',
                'slug' => 'apartamento-no-jardins',
                'description' => 'Excelente apartamento no bairro Jardins com 3 quartos, 1 suíte e 2 vagas de garagem. Próximo a shoppings e supermercados em Aracaju.',
                'price' => 320000.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'real_estate',
                'title' => 'Casa no Alphaville',
                'slug' => 'casa-no-alphaville',
                'description' => 'Casa de alto padrão com 4 quartos, 3 suítes, área gourmet completa e piscina na Barra dos Coqueiros.',
                'price' => 1200000.00,
                'city' => 'Barra dos Coqueiros',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'real_estate',
                'title' => 'Apartamento no Atalaia',
                'slug' => 'apartamento-no-atalaia',
                'description' => 'Apartamento a 200 metros da Orla de Atalaia com 2 quartos, varanda gourmet e vista para o mar.',
                'price' => 450000.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'real_estate',
                'title' => 'Casa na Aruana',
                'slug' => 'casa-na-aruana',
                'description' => 'Casa ampla com 3 quartos, suíte, quintal e garagem para 3 carros na Aruana.',
                'price' => 750000.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=800&q=80'
            ],

            // VEÍCULOS
            [
                'user_id' => $user->id,
                'module' => 'vehicles',
                'title' => 'Chevrolet Onix 2020',
                'slug' => 'chevrolet-onix-2020',
                'description' => 'Chevrolet Onix LT 1.0 Flex, completo, ano 2020, com 45.000 km. Revisado e pronto para uso em Aracaju.',
                'price' => 58900.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'vehicles',
                'title' => 'Toyota Corolla XEi 2021',
                'slug' => 'toyota-corolla-xei-2021',
                'description' => 'Corolla XEi 2.0 Flex automático, bancos em couro, apenas 32.000 km rodados, revisões na concessionária.',
                'price' => 115000.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'vehicles',
                'title' => 'Honda CG 160 Start 2022',
                'slug' => 'honda-cg-160-start-2022',
                'description' => 'Honda CG 160 Start ano 2022, único dono, muito econômica e com documentação quitada.',
                'price' => 13500.00,
                'city' => 'Itabaiana',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=800&q=80'
            ],

            // PRODUTOS
            [
                'user_id' => $user->id,
                'module' => 'products',
                'title' => 'iPhone 13 128GB',
                'slug' => 'iphone-13-128gb',
                'description' => 'iPhone 13 de 128GB em excelente estado de conservação, bateria excelente, acompanha cabo original.',
                'price' => 2199.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'products',
                'title' => 'Smart TV Samsung 55" 4K',
                'slug' => 'smart-tv-samsung-55-4k',
                'description' => 'Smart TV Samsung Crystal UHD 4K de 55 polegadas, Wi-Fi integrado, comando de voz e tela sem bordas.',
                'price' => 2450.00,
                'city' => 'Lagarto',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1593784991095-a205069470b6?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'products',
                'title' => 'Notebook Dell Inspiron i5',
                'slug' => 'notebook-dell-inspiron-i5',
                'description' => 'Notebook Dell com processador Intel Core i5, 8GB de RAM, SSD 256GB, tela Full HD de 15.6 polegadas.',
                'price' => 3100.00,
                'city' => 'Estância',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80'
            ],

            // SERVIÇOS
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Pedreiro Residencial e Comercial',
                'slug' => 'pedreiro-residencial-e-comercial',
                'advertiser_type' => 'Pedreiro',
                'description' => 'Serviços de construção, reforma, assentamento de pisos, porcelanatos e acabamentos em geral em Aracaju.',
                'price' => 0.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Eletricista em Aracaju',
                'slug' => 'eletricista-em-aracaju',
                'advertiser_type' => 'Eletricista',
                'description' => 'Instalações elétricas residenciais e comerciais, manutenção de quadros, padrão de energia e iluminação LED.',
                'price' => 0.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Montador de Móveis Ronivon',
                'slug' => 'montador-de-moveis-ronivon',
                'advertiser_type' => 'Marceneiro',
                'description' => 'Montagem e desmontagem de guarda-roupas, cozinhas, painéis e móveis planejados em Nossa Sra do Socorro e Aracaju.',
                'price' => 0.00,
                'city' => 'Nossa Senhora do Socorro',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Faxina & Cia',
                'slug' => 'faxina-e-cia',
                'advertiser_type' => 'Limpeza residencial',
                'description' => 'Limpeza residencial e comercial completa em Aracaju. Diárias, faxinas pesadas e pós-obra.',
                'price' => 0.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80'
            ],

            // EMPREGOS
            [
                'user_id' => $user->id,
                'module' => 'jobs',
                'title' => 'Vaga para Vendedor Interno',
                'slug' => 'vaga-para-vendedor-interno',
                'description' => 'Oportunidade para Vendedor Interno em loja de materiais de construção em Aracaju. Ensino médio completo e boa comunicação.',
                'price' => 1800.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=800&q=80'
            ],

            // AGRO
            [
                'user_id' => $user->id,
                'module' => 'agro',
                'title' => 'Trator Massey Ferguson 275',
                'slug' => 'trator-massey-ferguson-275',
                'description' => 'Trator agrícola Massey Ferguson 275 ano 2008, traçado 4x4, revisado e pronto para o trabalho no campo.',
                'price' => 85000.00,
                'city' => 'Lagarto',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1530267981608-bc7e3be93d56?auto=format&fit=crop&w=800&q=80'
            ]
        ];

        foreach ($adsData as $data) {
            $img = $data['image'];
            unset($data['image']);
            $ad = Ad::updateOrCreate(['slug' => $data['slug']], $data);
            AdImage::updateOrCreate(
                ['ad_id' => $ad->id, 'is_main' => true],
                ['image_path' => $img]
            );
        }
    }
}
