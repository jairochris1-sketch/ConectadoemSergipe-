@php
    // Busca serviços e lojas ativos do banco de dados
    $dbServices = \App\Models\Ad::with(['user', 'mainImage', 'category'])
        ->where('status', 'active')
        ->where('module', 'services')
        ->get();

    $dbStores = \App\Models\Store::where('active', true)->get();

    // Mapeamento EXATO e correto das cidades de Sergipe correspondente a cada foto de fundo
    $cityItemsMap = [
        'Tobias Barreto' => [
            ['title' => 'IDM Soluções Elétricas', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=100&auto=format&fit=crop'],
            ['title' => 'Ônibus em Sergipe', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=100&auto=format&fit=crop'],
            ['title' => 'Wesley Montador de Móveis', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
            ['title' => 'Vendedora de Roupas', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=100&auto=format&fit=crop'],
        ],
        'Nossa Senhora das Dores' => [
            ['title' => 'Carvalho Ótica e Relojoaria', 'city' => 'N. Sra. das Dores, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?w=100&auto=format&fit=crop'],
            ['title' => 'Material De Construção', 'city' => 'N. Sra. das Dores, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
            ['title' => 'AFJ Mundo das Novidades', 'city' => 'N. Sra. das Dores, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=100&auto=format&fit=crop'],
            ['title' => 'PrimeBeef Carnes Nobres', 'city' => 'N. Sra. das Dores, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=100&auto=format&fit=crop'],
        ],
        'Aracaju' => [
            ['title' => 'Apartamento 3 Qts', 'city' => 'Aracaju, SE', 'price' => 'R$ 350.000', 'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=100&auto=format&fit=crop'],
            ['title' => 'Eletricista Residencial', 'city' => 'Aracaju, SE', 'price' => 'R$ 120,00', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=100&auto=format&fit=crop'],
            ['title' => 'Loja de Informática', 'city' => 'Aracaju, SE', 'price' => 'R$ 2.499', 'image' => 'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?w=100&auto=format&fit=crop'],
            ['title' => 'Manutenção de Ar-Cond.', 'city' => 'Aracaju, SE', 'price' => 'R$ 150,00', 'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?w=100&auto=format&fit=crop'],
        ],
        'Lagarto' => [
            ['title' => 'Fazenda 50 Hectares', 'city' => 'Lagarto, SE', 'price' => 'R$ 2.800.000', 'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=100&auto=format&fit=crop'],
            ['title' => 'Depósito Lagarto', 'city' => 'Lagarto, SE', 'price' => 'R$ 450,00', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
            ['title' => 'Pintura Comercial', 'city' => 'Lagarto, SE', 'price' => 'R$ 160,00', 'image' => 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=100&auto=format&fit=crop'],
            ['title' => 'Câmeras & Segurança', 'city' => 'Lagarto, SE', 'price' => 'R$ 250,00', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop'],
        ],
        'Estância' => [
            ['title' => 'Toyota Corolla 2022', 'city' => 'Estância, SE', 'price' => 'R$ 118.900', 'image' => 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=100&auto=format&fit=crop'],
            ['title' => 'Serviços de Funilaria', 'city' => 'Estância, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=100&auto=format&fit=crop'],
            ['title' => 'Loja de Pneus & Alinhamento', 'city' => 'Estância, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?w=100&auto=format&fit=crop'],
            ['title' => 'Eletricista Industrial', 'city' => 'Estância, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=100&auto=format&fit=crop'],
        ],
        'Nossa Senhora do Socorro' => [
            ['title' => 'Vaga p/ Mecânico', 'city' => 'N. Sra. do Socorro, SE', 'price' => 'Salário R$ 2.500', 'image' => 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=100&auto=format&fit=crop'],
            ['title' => 'Encanador 24h', 'city' => 'N. Sra. do Socorro, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=100&auto=format&fit=crop'],
            ['title' => 'Supermercado Socorro', 'city' => 'N. Sra. do Socorro, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=100&auto=format&fit=crop'],
            ['title' => 'Marcenaria Sob Medida', 'city' => 'N. Sra. do Socorro, SE', 'price' => 'R$ 890,00', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
        ],
        'Sergipe' => [
            ['title' => 'Dom Marcos Móveis', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
            ['title' => 'Emerson Lima Multisserviços', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=100&auto=format&fit=crop'],
            ['title' => 'Pousada Oliveira', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=100&auto=format&fit=crop'],
            ['title' => 'Lucas Araujo Mídias', 'city' => 'Tobias Barreto, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop'],
        ],
        'Canindé de São Francisco' => [
            ['title' => 'Passeio Cânions do Xingó', 'city' => 'Canindé de São Fco., SE', 'price' => 'R$ 130,00/pess.', 'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=100&auto=format&fit=crop'],
            ['title' => 'Pousada do Velho Chico', 'city' => 'Canindé de São Fco., SE', 'price' => 'Diária R$ 180', 'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=100&auto=format&fit=crop'],
            ['title' => 'Guias de Turismo', 'city' => 'Canindé de São Fco., SE', 'price' => 'R$ 150,00/dia', 'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop'],
            ['title' => 'Loja de Artesanato', 'city' => 'Canindé de São Fco., SE', 'price' => 'R$ 45,00', 'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=100&auto=format&fit=crop'],
        ],
        'Nossa Senhora da Glória' => [
            ['title' => 'Hianne Confeitaria Artesanal', 'city' => 'N. Sra. da Glória, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=100&auto=format&fit=crop'],
            ['title' => 'Erivaldo Construção & Reformas', 'city' => 'N. Sra. da Glória, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=100&auto=format&fit=crop'],
            ['title' => 'Jorge Eletricista Residencial', 'city' => 'N. Sra. da Glória, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=100&auto=format&fit=crop'],
            ['title' => 'Teone do Gesso Sertão', 'city' => 'N. Sra. da Glória, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
        ],
        'Itabaiana' => [
            ['title' => 'Comércio de Pescados & Frutos', 'city' => 'Itabaiana, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=100&auto=format&fit=crop'],
            ['title' => 'Marcenaria Ribanceira', 'city' => 'Itabaiana, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
            ['title' => 'Eletricista de Embarcações', 'city' => 'Itabaiana, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=100&auto=format&fit=crop'],
            ['title' => 'Oficina Mecânica Agreste', 'city' => 'Itabaiana, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=100&auto=format&fit=crop'],
        ],
        'Propriá' => [
            ['title' => 'Comércio de Pescados', 'city' => 'Propriá, SE', 'price' => 'Loja & Empresa', 'image' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=100&auto=format&fit=crop'],
            ['title' => 'Marcenaria Ribanceira', 'city' => 'Propriá, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?w=100&auto=format&fit=crop'],
            ['title' => 'Eletricista de Embarcações', 'city' => 'Propriá, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=100&auto=format&fit=crop'],
            ['title' => 'Oficina Mecânica', 'city' => 'Propriá, SE', 'price' => 'Serviço', 'image' => 'https://images.unsplash.com/photo-1617814076367-b759c7d7e738?w=100&auto=format&fit=crop'],
        ],
    ];

    // Injeta os registros reais cadastrados do banco de dados na cidade correspondente
    foreach ($dbServices as $service) {
        $cityKey = $service->city;
        if ($cityKey && isset($cityItemsMap[$cityKey])) {
            array_unshift($cityItemsMap[$cityKey], [
                'title' => $service->title,
                'city' => $cityKey . ', SE',
                'price' => ($service->price && floatval($service->price) > 0) ? 'R$ ' . number_format($service->price, 2, ',', '.') : 'Serviço',
                'image' => $service->card_image ? asset($service->card_image) : ($service->user?->avatar ? asset($service->user->avatar) : 'https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=100&auto=format&fit=crop'),
            ]);
        }
    }

    foreach ($dbStores as $store) {
        $cityKey = $store->city;
        if ($cityKey && isset($cityItemsMap[$cityKey])) {
            array_unshift($cityItemsMap[$cityKey], [
                'title' => $store->name,
                'city' => $cityKey . ', SE',
                'price' => 'Loja & Empresa',
                'image' => $store->logo ? asset($store->logo) : 'https://images.unsplash.com/photo-1519003722824-194d4455a60c?w=100&auto=format&fit=crop',
            ]);
        }
    }

    $initialCity = $firstCity ?? 'Tobias Barreto';
    
    // Função para garantir que uma cidade tenha cards (usa fallback se não tiver)
    $getCityCards = function($cityName) use ($cityItemsMap) {
        if (isset($cityItemsMap[$cityName]) && count($cityItemsMap[$cityName]) >= 4) {
            return $cityItemsMap[$cityName];
        }
        
        // Fallback: pega os genéricos de Sergipe e muda o nome da cidade para parecer local
        $fallback = $cityItemsMap['Sergipe'] ?? [];
        return array_map(function($item) use ($cityName) {
            $item['city'] = $cityName . ', SE';
            return $item;
        }, $fallback);
    };

    $initialItems = $getCityCards($initialCity);

    $balloonEnabled = \App\Models\Setting::get('auth_balloon_enabled', '1') == '1';
    $balloonMessages = array_values(array_filter([
        \App\Models\Setting::get('auth_balloon_msg1', 'Conecte-se a serviços, produtos, imóveis, veículos e oportunidades em um único lugar.'),
        \App\Models\Setting::get('auth_balloon_msg2'),
        \App\Models\Setting::get('auth_balloon_msg3'),
    ]));
@endphp

<!-- Container dos Cards Flutuantes com Tamanho Clássico e Sincronizados com a Cidade de Fundo -->
<div id="service-cards-container">
    <div class="position-absolute" style="top: 12%; left: 14%; z-index: 5;">
        <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border auth-float-card" style="width: 220px; animation: float 6s ease-in-out infinite; transition: opacity 0.5s ease;" id="provider-card-0">
            <img src="{{ $initialItems[0]['image'] }}" class="rounded-3 object-fit-cover card-img" style="width: 46px; height: 46px; min-width: 46px;">
            <div class="overflow-hidden">
                <h6 class="fw-bold mb-0 text-dark small text-truncate card-title" style="max-width: 135px;">{{ $initialItems[0]['title'] }}</h6>
                <small class="text-muted d-block card-city" style="font-size: 0.72rem;">{{ $initialItems[0]['city'] }}</small>
                <span class="fw-bold text-primary small card-price">{{ $initialItems[0]['price'] }}</span>
            </div>
        </div>
    </div>

    <div class="position-absolute" style="top: 22%; right: 10%; z-index: 5;">
        <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border auth-float-card" style="width: 210px; animation: float 7s ease-in-out infinite 1s; transition: opacity 0.5s ease;" id="provider-card-1">
            <img src="{{ $initialItems[1]['image'] }}" class="rounded-3 object-fit-cover card-img" style="width: 46px; height: 46px; min-width: 46px;">
            <div class="overflow-hidden">
                <h6 class="fw-bold mb-0 text-dark small text-truncate card-title" style="max-width: 130px;">{{ $initialItems[1]['title'] }}</h6>
                <small class="text-muted d-block card-city" style="font-size: 0.72rem;">{{ $initialItems[1]['city'] }}</small>
                <span class="fw-bold text-primary small card-price">{{ $initialItems[1]['price'] }}</span>
            </div>
        </div>
    </div>

    <div class="position-absolute" style="top: 45%; left: 8%; z-index: 5;">
        <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border auth-float-card" style="width: 210px; animation: float 5s ease-in-out infinite 0.5s; transition: opacity 0.5s ease;" id="provider-card-2">
            <img src="{{ $initialItems[2]['image'] }}" class="rounded-3 object-fit-cover card-img" style="width: 46px; height: 46px; min-width: 46px;">
            <div class="overflow-hidden">
                <h6 class="fw-bold mb-0 text-dark small text-truncate card-title" style="max-width: 130px;">{{ $initialItems[2]['title'] }}</h6>
                <small class="text-muted d-block card-city" style="font-size: 0.72rem;">{{ $initialItems[2]['city'] }}</small>
                <span class="fw-bold text-primary small card-price">{{ $initialItems[2]['price'] }}</span>
            </div>
        </div>
    </div>

    <div class="position-absolute" style="bottom: 14%; left: 15%; z-index: 5;">
        <div class="bg-white p-2.5 rounded-4 shadow-lg d-flex align-items-center gap-3 border auth-float-card" style="width: 210px; animation: float 6.5s ease-in-out infinite 1.5s; transition: opacity 0.5s ease;" id="provider-card-3">
            <img src="{{ $initialItems[3]['image'] }}" class="rounded-3 object-fit-cover card-img" style="width: 46px; height: 46px; min-width: 46px;">
            <div class="overflow-hidden">
                <h6 class="fw-bold mb-0 text-dark small text-truncate card-title" style="max-width: 130px;">{{ $initialItems[3]['title'] }}</h6>
                <small class="text-muted d-block card-city" style="font-size: 0.72rem;">{{ $initialItems[3]['city'] }}</small>
                <span class="fw-bold text-primary small card-price">{{ $initialItems[3]['price'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Balão Flutuante de Vidro -->
@if($balloonEnabled && count($balloonMessages) > 0)
    <div id="auth-floating-balloon" class="position-absolute top-50 start-50 translate-middle p-4 text-white rounded-4 border border-white border-opacity-20 shadow-2xl" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(16px); width: 80%; max-width: 400px; z-index: 10;">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" aria-label="Close" onclick="document.getElementById('auth-floating-balloon').style.display='none'"></button>
        <h4 id="auth-balloon-text" class="fw-bold lh-base text-white mb-0 fs-5 mt-2 pe-3" style="transition: opacity 0.5s ease;">
            {{ $balloonMessages[0] }}
        </h4>
    </div>
@endif

@php
    // Ensure all cities from the slideshow have an entry in the map for Javascript
    if (isset($citySlideshowImages)) {
        foreach ($citySlideshowImages as $slide) {
            $cName = $slide['city'];
            if (!isset($cityItemsMap[$cName])) {
                $cityItemsMap[$cName] = $getCityCards($cName);
            }
        }
    }
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cityMap = @json($cityItemsMap);

        // Função Global de Atualização dos Cards por Cidade
        window.updateFloatingCardsForCity = function(cityName) {
            if (!cityName || !cityMap[cityName]) {
                return;
            }

            const items = cityMap[cityName];

            for (let i = 0; i < 4; i++) {
                const card = document.getElementById('provider-card-' + i);
                if (!card) continue;
                
                const item = items[i % items.length];
                
                card.style.opacity = '0';
                setTimeout(function() {
                    card.querySelector('.card-img').src = item.image;
                    card.querySelector('.card-title').textContent = item.title;
                    card.querySelector('.card-city').textContent = item.city;
                    card.querySelector('.card-price').textContent = item.price;
                    card.style.opacity = '1';
                }, 350);
            }
        };

        // Rotação de Mensagens do Balão
        const balloonMessages = @json($balloonMessages);
        if (balloonMessages.length > 1) {
            let balloonIdx = 0;
            const balloonTextEl = document.getElementById('auth-balloon-text');
            setInterval(function() {
                if (!balloonTextEl) return;
                balloonIdx = (balloonIdx + 1) % balloonMessages.length;
                balloonTextEl.style.opacity = '0';
                setTimeout(function() {
                    balloonTextEl.textContent = balloonMessages[balloonIdx];
                    balloonTextEl.style.opacity = '1';
                }, 400);
            }, 6000);
        }
    });
</script>
