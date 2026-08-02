<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CityImage
{
    public static function for(?string $city): string
    {
        $city = trim($city ?: 'Sergipe');
        $slug = Str::slug($city);
        $extensions = ['webp', 'jpg', 'jpeg', 'png'];
        $directories = ['Cidades', 'images/Cidades', 'images/cidades'];

        foreach ($directories as $directory) {
            $matches = self::findMatchingImage($directory, $city, $slug, $extensions);

            if ($matches) {
                return $matches;
            }
        }

        foreach ($directories as $directory) {
            foreach ($extensions as $extension) {
                $defaultPath = "{$directory}/imagempadrao.{$extension}";

                if (File::exists(public_path($defaultPath))) {
                    return $defaultPath;
                }
            }
        }

        return 'images/logo.png';
    }

    private static function findMatchingImage(string $directory, string $city, string $slug, array $extensions): ?string
    {
        foreach ([$city, $slug] as $name) {
            foreach ($extensions as $extension) {
                $path = "{$directory}/{$name}.{$extension}";

                if (File::exists(public_path($path))) {
                    return $path;
                }
            }
        }

        $normalizedCity = self::normalizeName($city);
        $files = File::glob(public_path("{$directory}/*.{webp,jpg,jpeg,png}"), GLOB_BRACE) ?: [];

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);

            if (self::normalizeName($filename) === $normalizedCity) {
                return str_replace('\\', '/', Str::after($file, public_path().DIRECTORY_SEPARATOR));
            }
        }

        return null;
    }

    private static function normalizeName(string $name): string
    {
        return Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}
