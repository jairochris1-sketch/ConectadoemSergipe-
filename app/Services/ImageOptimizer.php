<?php

namespace App\Services;

class ImageOptimizer
{
    /**
     * Converte qualquer imagem enviada (JPG, PNG, WEBP) para WEBP comprimido (qualidade 80%)
     * e descarta a imagem original não otimizada.
     */
    public static function convertToWebp($file, $prefix = 'img')
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        // Executar validação de conteúdo explícito / SafeSearch antes de otimizar e salvar
        ImageModerationService::check($file);

        $uploadPath = public_path('uploads');
        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = $prefix.'_'.time().'_'.rand(1000, 9999).'.webp';
        $destinationPath = $uploadPath.'/'.$filename;

        $realPath = $file->getRealPath();
        $mime = $file->getMimeType();
        $sourceImage = null;

        if (function_exists('imagecreatefromstring')) {
            $contents = file_get_contents($realPath);
            $sourceImage = @imagecreatefromstring($contents);
        }

        if (! $sourceImage) {
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    if (function_exists('imagecreatefromjpeg')) {
                        $sourceImage = @imagecreatefromjpeg($realPath);
                    }
                    break;
                case 'image/png':
                    if (function_exists('imagecreatefrompng')) {
                        $sourceImage = @imagecreatefrompng($realPath);
                    }
                    if ($sourceImage) {
                        imagepalettetotruecolor($sourceImage);
                        imagealphablending($sourceImage, true);
                        imagesavealpha($sourceImage, true);
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $sourceImage = @imagecreatefromwebp($realPath);
                    }
                    break;
            }
        }

        if ($sourceImage && function_exists('imagewebp')) {
            $origWidth = imagesx($sourceImage);
            $origHeight = imagesy($sourceImage);
            $maxDim = 1200; // Redimensiona inteligentemente preservando proporção perfeita (1080x1080, etc.)

            if ($origWidth > $maxDim || $origHeight > $maxDim) {
                if ($origWidth >= $origHeight) {
                    $newWidth = $maxDim;
                    $newHeight = max(1, (int) round(($origHeight / $origWidth) * $maxDim));
                } else {
                    $newHeight = $maxDim;
                    $newWidth = max(1, (int) round(($origWidth / $origHeight) * $maxDim));
                }

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                imagedestroy($sourceImage);
                $sourceImage = $resizedImage;
            }

            // Salva no formato WebP comprimido (Qualidade 82%)
            imagewebp($sourceImage, $destinationPath, 82);
            imagedestroy($sourceImage);

            // O arquivo temporário/original é automaticamente descartado pelo PHP ao fim do request
            return 'uploads/'.$filename;
        }

        // Fallback de segurança se GD não estiver disponível
        $fallbackFilename = $prefix.'_'.time().'_'.rand(1000, 9999).'.'.$file->getClientOriginalExtension();
        $file->move($uploadPath, $fallbackFilename);

        return 'uploads/'.$fallbackFilename;
    }

    public static function createSocialJpeg(?string $relativePath, string $prefix = 'home_social'): ?string
    {
        if (! $relativePath || str_starts_with($relativePath, 'http')) {
            return null;
        }

        $sourcePath = public_path($relativePath);
        if (! is_file($sourcePath) || ! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if (! $source) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetWidth = 1200;
        $targetHeight = 630;
        $targetRatio = $targetWidth / $targetHeight;
        $sourceRatio = $sourceWidth / max(1, $sourceHeight);

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = max(1, (int) round($sourceHeight * $targetRatio));
            $sourceX = max(0, (int) floor(($sourceWidth - $cropWidth) / 2));
            $sourceY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = max(1, (int) round($sourceWidth / $targetRatio));
            $sourceX = 0;
            $sourceY = max(0, (int) floor(($sourceHeight - $cropHeight) / 2));
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        $background = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $background);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetWidth,
            $targetHeight,
            $cropWidth,
            $cropHeight
        );
        imageinterlace($target, true);

        $filename = $prefix.'_'.time().'_'.rand(1000, 9999).'.jpg';
        $relativeDestination = 'uploads/'.$filename;
        $saved = imagejpeg($target, public_path($relativeDestination), 86);

        imagedestroy($source);
        imagedestroy($target);

        return $saved ? $relativeDestination : null;
    }
}
