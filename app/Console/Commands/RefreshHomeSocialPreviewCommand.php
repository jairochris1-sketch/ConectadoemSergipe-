<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\ImageOptimizer;
use Illuminate\Console\Command;

class RefreshHomeSocialPreviewCommand extends Command
{
    protected $signature = 'app:refresh-home-preview';

    protected $description = 'Gera uma imagem JPEG otimizada para o preview social da página inicial';

    public function handle(): int
    {
        $source = Setting::get('home_banner_1');
        if (! $source || str_starts_with($source, 'http')) {
            $source = 'images/logo-hero.png';
        }

        $newPreview = ImageOptimizer::createSocialJpeg($source);
        if (! $newPreview) {
            $this->components->error('Não foi possível gerar a imagem social em JPEG.');

            return self::FAILURE;
        }

        $oldPreview = Setting::get('home_social_preview');
        Setting::set('home_social_preview', $newPreview);
        if ($oldPreview && str_starts_with($oldPreview, 'uploads/home_social_')) {
            $oldPath = public_path($oldPreview);
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $this->components->success("Preview social gerado: {$newPreview}");

        return self::SUCCESS;
    }
}
