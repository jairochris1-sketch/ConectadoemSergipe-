<?php

namespace App\Core;

/**
 * Municípios de Sergipe e coordenadas aproximadas de suas sedes.
 *
 * As coordenadas são mantidas localmente para que a identificação da cidade
 * mais próxima não dependa de serviços externos durante o uso do site.
 */
class SergipeCities
{
    private static array $coordinates = [
        'Amparo de São Francisco' => ['latitude' => -10.1348, 'longitude' => -36.9350],
        'Aquidabã' => ['latitude' => -10.2780, 'longitude' => -37.0148],
        'Aracaju' => ['latitude' => -10.9091, 'longitude' => -37.0677],
        'Arauá' => ['latitude' => -11.2614, 'longitude' => -37.6201],
        'Areia Branca' => ['latitude' => -10.7580, 'longitude' => -37.3251],
        'Barra dos Coqueiros' => ['latitude' => -10.8996, 'longitude' => -37.0323],
        'Boquim' => ['latitude' => -11.1397, 'longitude' => -37.6195],
        'Brejo Grande' => ['latitude' => -10.4297, 'longitude' => -36.4611],
        'Campo do Brito' => ['latitude' => -10.7392, 'longitude' => -37.4954],
        'Canhoba' => ['latitude' => -10.1365, 'longitude' => -36.9806],
        'Canindé de São Francisco' => ['latitude' => -9.64882, 'longitude' => -37.7923],
        'Capela' => ['latitude' => -10.5069, 'longitude' => -37.0628],
        'Carira' => ['latitude' => -10.3524, 'longitude' => -37.7002],
        'Carmópolis' => ['latitude' => -10.6449, 'longitude' => -36.9887],
        'Cedro de São João' => ['latitude' => -10.2534, 'longitude' => -36.8856],
        'Cristinápolis' => ['latitude' => -11.4668, 'longitude' => -37.7585],
        'Cumbe' => ['latitude' => -10.3520, 'longitude' => -37.1846],
        'Divina Pastora' => ['latitude' => -10.6782, 'longitude' => -37.1506],
        'Estância' => ['latitude' => -11.2659, 'longitude' => -37.4484],
        'Feira Nova' => ['latitude' => -10.2616, 'longitude' => -37.3147],
        'Frei Paulo' => ['latitude' => -10.5513, 'longitude' => -37.5279],
        'Gararu' => ['latitude' => -9.9722, 'longitude' => -37.0869],
        'General Maynard' => ['latitude' => -10.6835, 'longitude' => -36.9838],
        'Gracho Cardoso' => ['latitude' => -10.2252, 'longitude' => -37.2006],
        'Ilha das Flores' => ['latitude' => -10.4425, 'longitude' => -36.5479],
        'Indiaroba' => ['latitude' => -11.5157, 'longitude' => -37.5150],
        'Itabaiana' => ['latitude' => -10.6826, 'longitude' => -37.4273],
        'Itabaianinha' => ['latitude' => -11.2693, 'longitude' => -37.7875],
        'Itabi' => ['latitude' => -10.1248, 'longitude' => -37.1056],
        'Itaporanga d\'Ajuda' => ['latitude' => -10.9900, 'longitude' => -37.3078],
        'Japaratuba' => ['latitude' => -10.5849, 'longitude' => -36.9418],
        'Japoatã' => ['latitude' => -10.3477, 'longitude' => -36.8045],
        'Lagarto' => ['latitude' => -10.9136, 'longitude' => -37.6689],
        'Laranjeiras' => ['latitude' => -10.7981, 'longitude' => -37.1731],
        'Macambira' => ['latitude' => -10.6619, 'longitude' => -37.5413],
        'Malhada dos Bois' => ['latitude' => -10.3418, 'longitude' => -36.9252],
        'Malhador' => ['latitude' => -10.6649, 'longitude' => -37.3004],
        'Maruim' => ['latitude' => -10.7308, 'longitude' => -37.0856],
        'Moita Bonita' => ['latitude' => -10.5769, 'longitude' => -37.3512],
        'Monte Alegre de Sergipe' => ['latitude' => -10.0256, 'longitude' => -37.5616],
        'Muribeca' => ['latitude' => -10.4271, 'longitude' => -36.9588],
        'Neópolis' => ['latitude' => -10.3215, 'longitude' => -36.5850],
        'Nossa Senhora Aparecida' => ['latitude' => -10.3944, 'longitude' => -37.4517],
        'Nossa Senhora da Glória' => ['latitude' => -10.2158, 'longitude' => -37.4211],
        'Nossa Senhora das Dores' => ['latitude' => -10.4854, 'longitude' => -37.1963],
        'Nossa Senhora de Lourdes' => ['latitude' => -10.0772, 'longitude' => -37.0615],
        'Nossa Senhora do Socorro' => ['latitude' => -10.8468, 'longitude' => -37.1231],
        'Pacatuba' => ['latitude' => -10.4538, 'longitude' => -36.6531],
        'Pedra Mole' => ['latitude' => -10.6134, 'longitude' => -37.6922],
        'Pedrinhas' => ['latitude' => -11.1902, 'longitude' => -37.6775],
        'Pinhão' => ['latitude' => -10.5677, 'longitude' => -37.7242],
        'Pirambu' => ['latitude' => -10.7215, 'longitude' => -36.8544],
        'Poço Redondo' => ['latitude' => -9.80616, 'longitude' => -37.6833],
        'Poço Verde' => ['latitude' => -10.7151, 'longitude' => -38.1813],
        'Porto da Folha' => ['latitude' => -9.91626, 'longitude' => -37.2842],
        'Propriá' => ['latitude' => -10.2138, 'longitude' => -36.8442],
        'Riachão do Dantas' => ['latitude' => -11.0729, 'longitude' => -37.7310],
        'Riachuelo' => ['latitude' => -10.7350, 'longitude' => -37.1966],
        'Ribeirópolis' => ['latitude' => -10.5357, 'longitude' => -37.4380],
        'Rosário do Catete' => ['latitude' => -10.6904, 'longitude' => -37.0357],
        'Salgado' => ['latitude' => -11.0288, 'longitude' => -37.4804],
        'Santa Luzia do Itanhy' => ['latitude' => -11.3536, 'longitude' => -37.4586],
        'Santa Rosa de Lima' => ['latitude' => -10.6434, 'longitude' => -37.1931],
        'Santana do São Francisco' => ['latitude' => -10.2922, 'longitude' => -36.6105],
        'Santo Amaro das Brotas' => ['latitude' => -10.7892, 'longitude' => -37.0564],
        'São Cristóvão' => ['latitude' => -11.0084, 'longitude' => -37.2044],
        'São Domingos' => ['latitude' => -10.7916, 'longitude' => -37.5685],
        'São Francisco' => ['latitude' => -10.3442, 'longitude' => -36.8869],
        'São Miguel do Aleixo' => ['latitude' => -10.3847, 'longitude' => -37.3836],
        'Simão Dias' => ['latitude' => -10.7387, 'longitude' => -37.8097],
        'Siriri' => ['latitude' => -10.5965, 'longitude' => -37.1131],
        'Telha' => ['latitude' => -10.2064, 'longitude' => -36.8818],
        'Tobias Barreto' => ['latitude' => -11.1798, 'longitude' => -37.9995],
        'Tomar do Geru' => ['latitude' => -11.3694, 'longitude' => -37.8433],
        'Umbaúba' => ['latitude' => -11.3809, 'longitude' => -37.6623],
    ];

    public static function getAll(): array
    {
        return array_keys(self::$coordinates);
    }

    public static function getCoordinates(): array
    {
        return self::$coordinates;
    }
}
