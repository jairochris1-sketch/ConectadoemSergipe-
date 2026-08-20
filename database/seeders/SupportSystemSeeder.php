<?php

namespace Database\Seeders;

use App\Models\SupportCannedResponse;
use App\Models\SupportDepartment;
use Illuminate\Database\Seeder;

class SupportSystemSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Suporte Geral & Dúvidas',
                'description' => 'Atendimento para dúvidas sobre o uso da plataforma e cadastro.',
                'icon' => 'fa-circle-question',
                'sort_order' => 1,
            ],
            [
                'name' => 'Prestadores de Serviços & Anúncios',
                'description' => 'Ajuda na publicação de anúncios, fotos e perfil profissional.',
                'icon' => 'fa-screwdriver-wrench',
                'sort_order' => 2,
            ],
            [
                'name' => 'Lojas, Produtos & Vendas',
                'description' => 'Suporte a lojistas, pedidos e catálogo de produtos.',
                'icon' => 'fa-store',
                'sort_order' => 3,
            ],
            [
                'name' => 'Planos & Financeiro',
                'description' => 'Informações sobre assinaturas, planos de destaque e pagamentos.',
                'icon' => 'fa-gem',
                'sort_order' => 4,
            ],
        ];

        foreach ($departments as $dept) {
            SupportDepartment::firstOrCreate(['name' => $dept['name']], $dept);
        }

        $canned = [
            [
                'shortcut' => '/ola',
                'title' => 'Saudação Inicial',
                'content' => 'Olá! Seja muito bem-vindo ao suporte do Conectado em Sergipe. Como posso te ajudar hoje?',
            ],
            [
                'shortcut' => '/planos',
                'title' => 'Informações sobre Planos',
                'content' => 'Nossos Planos de Destaque aumentam a visibilidade do seu perfil e anúncios no topo de Sergipe! Você pode conhecer todas as vantagens e valores acessando: https://conectadoemsergipe.com/planos',
            ],
            [
                'shortcut' => '/anuncio',
                'title' => 'Como publicar um anúncio',
                'content' => 'Para publicar seu anúncio ou perfil profissional gratuitamente, basta clicar no botão \'Anunciar\' no topo do site ou acessar: https://conectadoemsergipe.com/anunciar/criar',
            ],
            [
                'shortcut' => '/espera',
                'title' => 'Pedir um momento',
                'content' => 'Só um instante enquanto consulto as informações para você, por favor.',
            ],
            [
                'shortcut' => '/obrigado',
                'title' => 'Encerramento e Agradecimento',
                'content' => 'Foi um prazer te atender! Se precisar de qualquer outra ajuda, estamos sempre à disposição. Um abraço e ótimos negócios em Sergipe!',
            ],
        ];

        foreach ($canned as $resp) {
            SupportCannedResponse::firstOrCreate(['shortcut' => $resp['shortcut']], $resp);
        }
    }
}
