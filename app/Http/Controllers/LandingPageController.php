<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $enabled = Setting::get('landing_enabled', '1') === '1';
        $preview = $request->boolean('preview') && $request->user()?->role === 'admin';

        if (! $enabled && ! $preview) {
            return redirect()->route('home');
        }

        $cityImages = array_values(array_map(
            fn (string $path): string => 'Cidades/'.basename($path),
            glob(public_path('Cidades/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}'), GLOB_BRACE) ?: []
        ));

        $defaultImages = [
            1 => 'images/landing/prestador.jpg',
            2 => 'images/landing/loja-local.jpg',
            3 => 'images/landing/veiculo.jpg',
            4 => 'images/landing/imovel.jpg',
            5 => 'images/landing/alimentacao.jpg',
            6 => 'images/landing/agro.jpg',
            7 => 'images/landing/profissional.jpg',
        ];

        $images = [];
        foreach (range(1, 7) as $slot) {
            $images[$slot] = Setting::get("landing_image_{$slot}") ?: $defaultImages[$slot];
        }

        $cityBackgrounds = $cityImages;
        if (! $cityBackgrounds) {
            $cityBackgrounds = collect(range(1, 6))
                ->map(fn (int $slot) => Setting::get("home_banner_{$slot}"))
                ->filter()
                ->values()
                ->all();
        }

        $videoUrl = Setting::get('landing_video_url', 'https://youtu.be/LS0ObEgTwZk');
        $videoId = null;
        $videoHost = mb_strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
        if (str_contains($videoHost, 'youtu.be')) {
            $videoId = trim((string) parse_url($videoUrl, PHP_URL_PATH), '/');
        } elseif (str_contains($videoHost, 'youtube.com')) {
            parse_str((string) parse_url($videoUrl, PHP_URL_QUERY), $videoQuery);
            $videoId = $videoQuery['v'] ?? null;
        }
        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            $videoId = 'LS0ObEgTwZk';
        }

        $cityWikipediaUrls = [
            'Aracaju' => 'https://pt.wikipedia.org/wiki/Aracaju',
            'Canindé de São Francisco' => 'https://pt.wikipedia.org/wiki/Canind%C3%A9_de_S%C3%A3o_Francisco',
            'Itabaiana' => 'https://pt.wikipedia.org/wiki/Itabaiana_(Sergipe)',
            'Monte Alegre' => 'https://pt.wikipedia.org/wiki/Monte_Alegre_de_Sergipe',
            'Nossa Senhora da Glória' => 'https://pt.wikipedia.org/wiki/Nossa_Senhora_da_Gl%C3%B3ria',
            'Nossa Senhora das Dores' => 'https://pt.wikipedia.org/wiki/Nossa_Senhora_das_Dores_(Sergipe)',
            'Tobias Barreto' => 'https://pt.wikipedia.org/wiki/Tobias_Barreto_(Sergipe)',
        ];

        return view('landing', compact('images', 'cityBackgrounds', 'cityWikipediaUrls', 'videoId', 'preview'));
    }
}
