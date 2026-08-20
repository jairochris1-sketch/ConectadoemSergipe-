<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\User;
use Illuminate\Support\Str;

class DemoAdSeeder
{
    public static function seedIfNeeded(bool $force = false): void
    {
        if (app()->runningUnitTests() && ! $force) {
            return;
        }

        if (app()->environment('local')) {
            self::seedFeaturedServiceProfilesIfNeeded();
        }

        self::seedStoresIfNeeded();

        if (! $force && Ad::where('status', 'active')->count() >= 8) {
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
            [
                'user_id' => $user->id,
                'module' => 'real_estate',
                'title' => 'Casa com Piscina no Mosqueiro',
                'slug' => 'casa-com-piscina-no-mosqueiro',
                'description' => 'Casa ampla no Mosqueiro com 3 quartos, área gourmet, piscina e garagem para 2 carros.',
                'price' => 680000.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=80'
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
            [
                'user_id' => $user->id,
                'module' => 'vehicles',
                'title' => 'Jeep Renegade Longitude 2021',
                'slug' => 'jeep-renegade-longitude-2021',
                'description' => 'Jeep Renegade Longitude automático, completo, revisado e com 38.000 km rodados.',
                'price' => 92900.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'vehicles',
                'title' => 'Fiat Strada Freedom 2022',
                'slug' => 'fiat-strada-freedom-2022',
                'description' => 'Fiat Strada Freedom cabine dupla, flex, completa e pronta para trabalho ou lazer.',
                'price' => 89900.00,
                'city' => 'Itabaiana',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1494905998402-395d579af36f?auto=format&fit=crop&w=800&q=80'
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
            [
                'user_id' => $user->id,
                'module' => 'products',
                'title' => 'Cadeira de Escritório Ergonômica',
                'slug' => 'cadeira-de-escritorio-ergonomica',
                'description' => 'Cadeira ergonômica com apoio lombar, regulagem de altura e braços ajustáveis.',
                'price' => 890.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'products',
                'title' => 'Smartwatch com GPS e Bluetooth',
                'slug' => 'smartwatch-com-gps-e-bluetooth',
                'description' => 'Relógio inteligente com GPS, monitor cardíaco, notificações e bateria de longa duração.',
                'price' => 649.00,
                'city' => 'Nossa Senhora do Socorro',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80'
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
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Encanador e Desentupidora 24h',
                'slug' => 'encanador-e-desentupidora-24h',
                'advertiser_type' => 'Encanador',
                'description' => 'Caça vazamentos, reparos hidráulicos, instalação de torneiras, chuveiros e desentupimento em geral.',
                'price' => 0.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Pintor Profissional & Acabamentos',
                'slug' => 'pintor-profissional-e-acabamentos',
                'advertiser_type' => 'Pintor',
                'description' => 'Pintura residencial e predial, textura, verniz, massa corrida e impermeabilização com garantia.',
                'price' => 0.00,
                'city' => 'Itabaiana',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Técnico em Refrigeração & Ar Condicionado',
                'slug' => 'tecnico-em-refrigeracao-e-ar-condicionado',
                'advertiser_type' => 'Técnico de Informática',
                'description' => 'Instalação, higienização e manutenção preventiva de ar-condicionado split e comercial.',
                'price' => 0.00,
                'city' => 'Estância',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Fretes & Mudanças Sergipe',
                'slug' => 'fretes-e-mudancas-sergipe',
                'advertiser_type' => 'Frete e Mudanças',
                'description' => 'Transporte seguro e pontual de cargas, móveis e mudanças residenciais para todas as cidades de Sergipe.',
                'price' => 0.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Mecânica Automotiva & Injeção Eletrônica',
                'slug' => 'mecanica-automotiva-e-injecao-eletronica',
                'advertiser_type' => 'Mecânico',
                'description' => 'Revisão mecânica, suspensão, freios, troca de óleo e diagnóstico computadorizado de motores.',
                'price' => 0.00,
                'city' => 'Lagarto',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Diarista Profissional & Passadeira',
                'slug' => 'diarista-profissional-e-passadeira',
                'advertiser_type' => 'Diarista',
                'description' => 'Serviço de limpeza diária, organização de armários e passagem de roupas com referências.',
                'price' => 0.00,
                'city' => 'Nossa Senhora da Glória',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'services',
                'title' => 'Suporte de TI & Manutenção de Computadores',
                'slug' => 'suporte-de-ti-e-manutencao-de-computadores',
                'advertiser_type' => 'TI / Informática',
                'description' => 'Formatação, remoção de vírus, troca de telas, montagem de computadores gamer e redes de internet.',
                'price' => 0.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=800&q=80'
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
            [
                'user_id' => $user->id,
                'module' => 'jobs',
                'title' => 'Assistente Administrativo',
                'slug' => 'vaga-assistente-administrativo',
                'description' => 'Empresa em Aracaju contrata assistente administrativo com ensino médio completo e informática básica.',
                'price' => 1900.00,
                'city' => 'Aracaju',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=800&q=80'
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
            ],
            [
                'user_id' => $user->id,
                'module' => 'agro',
                'title' => 'Novilhas Nelore para Cria',
                'slug' => 'novilhas-nelore-para-cria',
                'description' => 'Lote de novilhas Nelore saudáveis, vacinadas e prontas para criação em propriedade rural.',
                'price' => 4200.00,
                'city' => 'Lagarto',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'user_id' => $user->id,
                'module' => 'agro',
                'title' => 'Cestas de Hortifruti da Roça',
                'slug' => 'cestas-de-hortifruti-da-roca',
                'description' => 'Cestas semanais com frutas, verduras e legumes frescos produzidos por agricultores locais.',
                'price' => 75.00,
                'city' => 'Itabaiana',
                'state' => 'SE',
                'status' => 'active',
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=80'
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

    public static function seedFeaturedServiceProfilesIfNeeded(): void
    {
        $profiles = [
            [
                'name' => 'Manoel Santos',
                'email' => 'demo.destaque.manoel@example.invalid',
                'title' => 'Manoel Consertos de TV e Eletrônicos',
                'slug' => 'demo-manoel-consertos-eletronicos',
                'category' => 'Consertos de TV e Som',
                'city' => 'Porto da Folha',
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Adriano Rezende',
                'email' => 'demo.destaque.adriano@example.invalid',
                'title' => 'Moto Táxi Adriano Rezende',
                'slug' => 'demo-moto-taxi-adriano-rezende',
                'category' => 'Moto Táxi',
                'city' => 'Nossa Senhora da Glória',
                'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Thierry Almeida',
                'email' => 'demo.destaque.thierry@example.invalid',
                'title' => 'Thierry Reformas e Construções',
                'slug' => 'demo-thierry-reformas-construcoes',
                'category' => 'Pedreiro',
                'city' => 'Nossa Senhora da Glória',
                'image' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Raquel Andrade',
                'email' => 'demo.destaque.raquel@example.invalid',
                'title' => 'Raquel Maquiagem Social e Penteados',
                'slug' => 'demo-raquel-maquiagem-penteados',
                'category' => 'Maquiadora',
                'city' => 'Nossa Senhora da Glória',
                'image' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Isaac Oliveira',
                'email' => 'demo.destaque.isaac@example.invalid',
                'title' => 'Isaac Instalações Elétricas',
                'slug' => 'demo-isaac-instalacoes-eletricas',
                'category' => 'Eletricista',
                'city' => 'Nossa Senhora da Glória',
                'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Carla Menezes',
                'email' => 'demo.destaque.carla@example.invalid',
                'title' => 'Carla Fotografia e Produção Visual',
                'slug' => 'demo-carla-fotografia-producao',
                'category' => 'Fotógrafa',
                'city' => 'Aracaju',
                'image' => 'https://images.unsplash.com/photo-1554048612-b6a482bc67e5?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Helena Barreto',
                'email' => 'demo.liberal.helena@example.invalid',
                'title' => 'Dra. Helena Barreto Advocacia',
                'slug' => 'demo-helena-barreto-advocacia',
                'category' => 'Advogada',
                'city' => 'Aracaju',
                'kind' => 'liberal_professional',
                'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Marina Oliveira',
                'email' => 'demo.liberal.marina@example.invalid',
                'title' => 'Marina Oliveira Psicologia',
                'slug' => 'demo-marina-oliveira-psicologia',
                'category' => 'Psicóloga',
                'city' => 'Lagarto',
                'kind' => 'liberal_professional',
                'image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Camila Freire',
                'email' => 'demo.liberal.camila@example.invalid',
                'title' => 'Camila Freire Nutrição Clínica',
                'slug' => 'demo-camila-freire-nutricao',
                'category' => 'Nutricionista',
                'city' => 'Itabaiana',
                'kind' => 'liberal_professional',
                'image' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Larissa Fontes',
                'email' => 'demo.liberal.larissa@example.invalid',
                'title' => 'Larissa Fontes Arquitetura',
                'slug' => 'demo-larissa-fontes-arquitetura',
                'category' => 'Arquiteta',
                'city' => 'Estância',
                'kind' => 'liberal_professional',
                'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Patrícia Menezes',
                'email' => 'demo.liberal.patricia@example.invalid',
                'title' => 'Patrícia Menezes Contabilidade',
                'slug' => 'demo-patricia-menezes-contabilidade',
                'category' => 'Contadora',
                'city' => 'Nossa Senhora da Glória',
                'kind' => 'liberal_professional',
                'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=900&q=82',
            ],
            [
                'name' => 'Renata Alves',
                'email' => 'demo.liberal.renata@example.invalid',
                'title' => 'Renata Alves Fisioterapia',
                'slug' => 'demo-renata-alves-fisioterapia',
                'category' => 'Fisioterapeuta',
                'city' => 'Propriá',
                'kind' => 'liberal_professional',
                'image' => 'https://images.unsplash.com/photo-1598257006458-087169a1f08d?auto=format&fit=crop&w=900&q=82',
            ],
        ];

        $liberalDetails = [
            'Advogada' => [
                'headline' => 'Atuação jurídica com atendimento claro e foco em soluções seguras.',
                'credential' => 'OAB/SE DEMO-001',
                'credential_issuer' => 'Registro demonstrativo para apresentação visual do perfil.',
                'education' => 'Bacharelado em Direito',
                'education_institution' => 'Formação superior demonstrativa — Sergipe.',
                'specialties' => [
                    ['title' => 'Direito Civil', 'description' => 'Orientação em contratos, família e responsabilidade civil.'],
                    ['title' => 'Direito Trabalhista', 'description' => 'Consultoria preventiva e acompanhamento profissional.'],
                ],
            ],
            'Psicóloga' => [
                'headline' => 'Acolhimento psicológico humanizado para diferentes fases da vida.',
                'credential' => 'CRP 19/DEMO-002',
                'credential_issuer' => 'Registro demonstrativo para apresentação visual do perfil.',
                'education' => 'Graduação em Psicologia',
                'education_institution' => 'Formação superior demonstrativa — Sergipe.',
                'specialties' => [
                    ['title' => 'Psicoterapia individual', 'description' => 'Escuta profissional e acompanhamento personalizado.'],
                    ['title' => 'Orientação familiar', 'description' => 'Apoio para relações e momentos de mudança.'],
                ],
            ],
            'Nutricionista' => [
                'headline' => 'Planejamento alimentar individualizado para uma rotina mais saudável.',
                'credential' => 'CRN-5 DEMO-003',
                'credential_issuer' => 'Registro demonstrativo para apresentação visual do perfil.',
                'education' => 'Graduação em Nutrição',
                'education_institution' => 'Formação superior demonstrativa — Sergipe.',
                'specialties' => [
                    ['title' => 'Nutrição clínica', 'description' => 'Acompanhamento alimentar conforme objetivos e rotina.'],
                    ['title' => 'Reeducação alimentar', 'description' => 'Estratégias práticas para hábitos sustentáveis.'],
                ],
            ],
            'Arquiteta' => [
                'headline' => 'Projetos funcionais que conectam estética, conforto e identidade.',
                'credential' => 'CAU/SE DEMO-004',
                'credential_issuer' => 'Registro demonstrativo para apresentação visual do perfil.',
                'education' => 'Arquitetura e Urbanismo',
                'education_institution' => 'Formação superior demonstrativa — Sergipe.',
                'specialties' => [
                    ['title' => 'Projeto residencial', 'description' => 'Soluções para casas e apartamentos.'],
                    ['title' => 'Interiores', 'description' => 'Ambientes funcionais e visualmente acolhedores.'],
                ],
            ],
            'Contadora' => [
                'headline' => 'Organização contábil para profissionais, empresas e novos negócios.',
                'credential' => 'CRC/SE DEMO-005',
                'credential_issuer' => 'Registro demonstrativo para apresentação visual do perfil.',
                'education' => 'Ciências Contábeis',
                'education_institution' => 'Formação superior demonstrativa — Sergipe.',
                'specialties' => [
                    ['title' => 'Contabilidade empresarial', 'description' => 'Rotinas fiscais e acompanhamento de empresas.'],
                    ['title' => 'Consultoria para MEI', 'description' => 'Orientação para formalização e organização financeira.'],
                ],
            ],
            'Fisioterapeuta' => [
                'headline' => 'Cuidado fisioterapêutico individualizado para movimento e qualidade de vida.',
                'credential' => 'CREFITO-17 DEMO-006',
                'credential_issuer' => 'Registro demonstrativo para apresentação visual do perfil.',
                'education' => 'Graduação em Fisioterapia',
                'education_institution' => 'Formação superior demonstrativa — Sergipe.',
                'specialties' => [
                    ['title' => 'Fisioterapia ortopédica', 'description' => 'Acompanhamento funcional e prevenção de limitações.'],
                    ['title' => 'Reabilitação', 'description' => 'Plano individual conforme avaliação profissional.'],
                ],
            ],
        ];

        $liberalGallery = [
            'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=82',
            'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=900&q=82',
            'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=900&q=82',
        ];

        foreach ($profiles as $profile) {
            $isLiberal = ($profile['kind'] ?? 'professional') === 'liberal_professional';
            $profileDetails = $liberalDetails[$profile['category']] ?? [];
            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => Str::random(64),
                    'city' => $profile['city'],
                    'role' => 'user',
                    'subscription_plan' => 'start',
                ]
            );
            $user->fill([
                'name' => $profile['name'],
                'city' => $profile['city'],
                'role' => 'user',
                'subscription_plan' => 'start',
            ])->save();

            $adData = [
                    'user_id' => $user->id,
                    'module' => 'services',
                    'profile_kind' => $profile['kind'] ?? 'professional',
                    'advertiser_type' => $profile['category'],
                    'title' => $profile['title'],
                    'description' => ($profile['kind'] ?? 'professional') === 'liberal_professional'
                        ? 'Perfil demonstrativo criado para apresentar a vitrine de profissionais liberais em destaque do Conectado em Sergipe.'
                        : 'Perfil demonstrativo criado para apresentar a vitrine de prestadores em destaque do Conectado em Sergipe.',
                    'price' => 0,
                    'city' => $profile['city'],
                    'state' => 'SE',
                    'status' => 'active',
                    'logo' => $profile['image'],
                    'card_image' => $profile['image'],
                    'views' => 100,
                ];

            if ($isLiberal) {
                $adData = array_merge($adData, [
                    'description' => 'Perfil demonstrativo de profissional liberal criado para apresentar formação, especialidades, registro profissional e formas de atendimento.',
                    'technical_specs' => [
                        'liberal_profile' => array_merge($profileDetails, [
                            'credential_verified' => false,
                            'service_area' => "Atendimento em {$profile['city']} e por videoconferência.",
                        ]),
                    ],
                    'banner' => $liberalGallery[0],
                    'public_address' => "Centro, {$profile['city']}",
                    'business_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '18:00'],
                        'friday' => ['open' => '08:00', 'close' => '18:00'],
                        'saturday' => ['open' => '09:00', 'close' => '12:00'],
                    ],
                    'contact_phone' => '(79) 99999-0000',
                    'contact_whatsapp' => '7999990000',
                    'is_claimed' => true,
                    'claiming_enabled' => false,
                ]);
            }

            $ad = Ad::updateOrCreate(['slug' => $profile['slug']], $adData);

            AdImage::updateOrCreate(
                ['ad_id' => $ad->id, 'is_main' => true],
                ['image_path' => $profile['image']]
            );

            if ($isLiberal) {
                foreach ($liberalGallery as $galleryImage) {
                    AdImage::updateOrCreate(
                        ['ad_id' => $ad->id, 'image_path' => $galleryImage],
                        ['is_main' => false]
                    );
                }
            }
        }
    }

    public static function seedStoresIfNeeded(): void
    {
        if (\App\Models\Store::count() > 0) {
            return;
        }

        $user = User::where('role', 'admin')->first() ?: User::first();
        if (! $user) {
            return;
        }

        $storesData = [
            [
                'user_id' => $user->id,
                'name' => 'Boutique Encanto',
                'slug' => 'boutique-encanto',
                'description' => 'Boutique com as melhores marcas e coleções exclusivas de moda feminina em Aracaju.',
                'category' => 'Moda feminina',
                'city' => 'Aracaju',
                'state' => 'SE',
                'active' => true,
                'moderation_status' => 'approved',
                'featured' => true,
                'banner' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80',
                'logo' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'user_id' => $user->id,
                'name' => 'Mercadinho Bom Preço',
                'slug' => 'mercadinho-bom-preco',
                'description' => 'Mercado completo com hortifruti fresco, carnes selecionadas e preços baixos todo dia.',
                'category' => 'Mercado',
                'city' => 'Nossa Senhora do Socorro',
                'state' => 'SE',
                'active' => true,
                'moderation_status' => 'approved',
                'featured' => true,
                'banner' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80',
                'logo' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'user_id' => $user->id,
                'name' => 'Casa & Cia Decor',
                'slug' => 'casa-cia-decor',
                'description' => 'Móveis, objetos de decoração e utilidades para deixar seu lar ainda mais aconchegante.',
                'category' => 'Decoração e Utilidades',
                'city' => 'Aracaju',
                'state' => 'SE',
                'active' => true,
                'moderation_status' => 'approved',
                'featured' => true,
                'banner' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=600&q=80',
                'logo' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'user_id' => $user->id,
                'name' => 'ConstruLar Materiais',
                'slug' => 'constrular-materiais',
                'description' => 'Do alicerce ao acabamento: materiais de construção de qualidade com entrega rápida em Estância.',
                'category' => 'Materiais de Construção',
                'city' => 'Estância',
                'state' => 'SE',
                'active' => true,
                'moderation_status' => 'approved',
                'featured' => true,
                'banner' => 'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?auto=format&fit=crop&w=600&q=80',
                'logo' => 'https://images.unsplash.com/photo-1581783342308-f792dbdd27c5?auto=format&fit=crop&w=200&q=80',
            ],
            [
                'user_id' => $user->id,
                'name' => 'Agro Forte',
                'slug' => 'agro-forte',
                'description' => 'Produtos agropecuários, rações, ferramentas e sementes selecionadas no coração de Itabaiana.',
                'category' => 'Agropecuária',
                'city' => 'Itabaiana',
                'state' => 'SE',
                'active' => true,
                'moderation_status' => 'approved',
                'featured' => true,
                'banner' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=600&q=80',
                'logo' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=200&q=80',
            ],
        ];

        foreach ($storesData as $sData) {
            \App\Models\Store::updateOrCreate(['slug' => $sData['slug']], $sData);
        }
    }
}
