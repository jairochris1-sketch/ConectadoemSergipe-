<?php

namespace App\Rules;

use App\Services\ImageModerationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeImage implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile && (! is_string($value) || ! file_exists($value))) {
            return;
        }

        $service = new ImageModerationService();
        $result = $service->inspect($value);

        if (! $result['isSafe']) {
            $fail($result['reason'] ?? 'A imagem enviada foi recusada pelo filtro de moderação de conteúdo (SafeSearch).');
        }
    }
}
