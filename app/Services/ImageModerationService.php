<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImageModerationService
{
    /**
     * Inspect an image file and determine if it contains explicit/prohibited content.
     *
     * @param  UploadedFile|string  $file
     * @return array{isSafe: bool, reason: string|null, details: array}
     */
    public function inspect(UploadedFile|string $file): array
    {
        // 1. Verificar se a moderação de imagem está ativa nas configurações
        $enabled = filter_var(
            Setting::get('image_moderation_enabled', env('IMAGE_MODERATION_ENABLED', false)),
            FILTER_VALIDATE_BOOL
        );

        if (! $enabled) {
            return [
                'isSafe' => true,
                'reason' => null,
                'details' => ['status' => 'disabled'],
            ];
        }

        // 2. Obter a chave de API do Google Vision ou Sightengine
        $googleVisionKey = Setting::get('google_vision_api_key', env('GOOGLE_VISION_API_KEY'));

        if (! empty($googleVisionKey)) {
            return $this->inspectWithGoogleVision($file, $googleVisionKey);
        }

        // 3. Fallback para Sightengine se configurado
        $sightengineUser = Setting::get('sightengine_api_user', env('SIGHTENGINE_API_USER'));
        $sightengineSecret = Setting::get('sightengine_api_secret', env('SIGHTENGINE_API_SECRET'));

        if (! empty($sightengineUser) && ! empty($sightengineSecret)) {
            return $this->inspectWithSightengine($file, $sightengineUser, $sightengineSecret);
        }

        // Se ativado mas sem chaves de API válidas, logar aviso e permitir
        Log::warning('[ImageModerationService] Moderação de imagem ativada, mas nenhuma chave de API válida foi informada.');

        return [
            'isSafe' => true,
            'reason' => null,
            'details' => ['status' => 'missing_api_key'],
        ];
    }

    /**
     * Executa a validação diretamente e lança ValidationException se a imagem for imprópria.
     *
     * @throws ValidationException
     */
    public static function check(UploadedFile|string|null $file, string $field = 'image'): void
    {
        if (! $file) {
            return;
        }

        $service = new static();
        $result = $service->inspect($file);

        if (! $result['isSafe']) {
            throw ValidationException::withMessages([
                $field => $result['reason'] ?? 'A imagem enviada foi recusada por conter conteúdo explícito ou impróprio (SafeSearch).',
            ]);
        }
    }

    /**
     * Análise usando Google Cloud Vision API SafeSearch.
     */
    protected function inspectWithGoogleVision(UploadedFile|string $file, string $apiKey): array
    {
        try {
            $imageBytes = $this->getImageBytes($file);
            if (! $imageBytes) {
                return ['isSafe' => true, 'reason' => null, 'details' => ['error' => 'empty_image']];
            }

            $response = Http::timeout(10)->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
                'requests' => [
                    [
                        'image' => [
                            'content' => base64_encode($imageBytes),
                        ],
                        'features' => [
                            [
                                'type' => 'SAFE_SEARCH_DETECTION',
                                'maxResults' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('[GoogleVision] Falha na requisição API HTTP: '.$response->body());
                return ['isSafe' => true, 'reason' => null, 'details' => ['http_error' => $response->status()]];
            }

            $data = $response->json();
            $safeSearch = $data['responses'][0]['safeSearchAnnotation'] ?? null;

            if (! $safeSearch) {
                return ['isSafe' => true, 'reason' => null, 'details' => ['no_annotation' => true]];
            }

            // Níveis do Google Vision: UNKNOWN, VERY_UNLIKELY, UNLIKELY, POSSIBLE, LIKELY, VERY_LIKELY
            $adult = $safeSearch['adult'] ?? 'UNKNOWN';
            $violence = $safeSearch['violence'] ?? 'UNKNOWN';
            $racy = $safeSearch['racy'] ?? 'UNKNOWN';

            $unsafeLevels = ['LIKELY', 'VERY_LIKELY'];

            if (in_array($adult, $unsafeLevels, true)) {
                return [
                    'isSafe' => false,
                    'reason' => 'A imagem enviada foi bloqueada por conter conteúdo adulto ou explícito (Google SafeSearch).',
                    'details' => $safeSearch,
                ];
            }

            if (in_array($violence, $unsafeLevels, true)) {
                return [
                    'isSafe' => false,
                    'reason' => 'A imagem enviada foi bloqueada por conter conteúdo de violência explícita.',
                    'details' => $safeSearch,
                ];
            }

            if ($racy === 'VERY_LIKELY') {
                return [
                    'isSafe' => false,
                    'reason' => 'A imagem enviada foi bloqueada por conter conteúdo altamente sugestivo ou impróprio.',
                    'details' => $safeSearch,
                ];
            }

            return [
                'isSafe' => true,
                'reason' => null,
                'details' => $safeSearch,
            ];
        } catch (\Throwable $e) {
            Log::error('[GoogleVision] Exceção durante moderação de imagem: '.$e->getMessage());
            return ['isSafe' => true, 'reason' => null, 'details' => ['exception' => $e->getMessage()]];
        }
    }

    /**
     * Análise de fallback usando Sightengine API.
     */
    protected function inspectWithSightengine(UploadedFile|string $file, string $user, string $secret): array
    {
        try {
            $imageBytes = $this->getImageBytes($file);
            if (! $imageBytes) {
                return ['isSafe' => true, 'reason' => null, 'details' => ['error' => 'empty_image']];
            }

            $response = Http::timeout(10)
                ->attach('media', $imageBytes, 'image.jpg')
                ->post('https://api.sightengine.com/1.0/check.json', [
                    'models' => 'nudity-2.0,violence',
                    'api_user' => $user,
                    'api_secret' => $secret,
                ]);

            if ($response->failed()) {
                return ['isSafe' => true, 'reason' => null, 'details' => ['http_error' => $response->status()]];
            }

            $data = $response->json();
            $nudity = $data['nudity']['sexual_activity'] ?? 0;
            $erotica = $data['nudity']['erotica'] ?? 0;
            $violence = $data['violence']['prob'] ?? 0;

            if ($nudity > 0.5 || $erotica > 0.75 || $violence > 0.7) {
                return [
                    'isSafe' => false,
                    'reason' => 'A imagem foi recusada pelo filtro de conteúdo impróprio/adulto (Sightengine).',
                    'details' => $data,
                ];
            }

            return ['isSafe' => true, 'reason' => null, 'details' => $data];
        } catch (\Throwable $e) {
            Log::error('[Sightengine] Exceção na moderação: '.$e->getMessage());
            return ['isSafe' => true, 'reason' => null, 'details' => ['exception' => $e->getMessage()]];
        }
    }

    /**
     * Lê o conteúdo em bytes do arquivo.
     */
    protected function getImageBytes(UploadedFile|string $file): ?string
    {
        if ($file instanceof UploadedFile) {
            return file_get_contents($file->getRealPath());
        }

        if (is_string($file) && file_exists($file)) {
            return file_get_contents($file);
        }

        return null;
    }
}
