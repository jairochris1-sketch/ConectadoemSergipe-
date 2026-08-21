<?php

namespace App\Http\Requests;

use App\Core\SergipeCities;
use App\Models\Ad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuickProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_name' => trim((string) $this->input('account_name')),
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            'profile_kind' => trim((string) $this->input('profile_kind')),
            'category' => trim((string) $this->input('category')),
            'main_city' => trim((string) $this->input('main_city')),
        ]);
    }

    public function rules(): array
    {
        $guest = $this->user() === null;
        $profileKind = (string) $this->input('profile_kind');
        $categories = $this->categoriesFor($profileKind);
        $cities = SergipeCities::getAll();

        return [
            'profile_kind' => ['required', Rule::in(array_keys(Ad::PROFILE_KINDS))],
            'account_name' => [Rule::excludeIf(! $guest), Rule::requiredIf($guest), 'string', 'min:3', 'max:120'],
            'email' => [Rule::excludeIf(! $guest), Rule::requiredIf($guest), 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => [Rule::excludeIf(! $guest), Rule::requiredIf($guest), 'digits_between:10,11', Rule::unique('users', 'phone')],
            'password' => [Rule::excludeIf(! $guest), Rule::requiredIf($guest), 'string', 'min:6', 'confirmed'],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'category' => ['required', 'string', 'max:100', Rule::in($categories)],
            'main_city' => ['required', 'string', Rule::in($cities)],
            'whole_state' => ['nullable', 'boolean'],
            'cities' => ['nullable', 'array', 'max:5'],
            'cities.*' => ['string', 'distinct', Rule::in($cities)],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'services' => ['required', 'array', 'min:1', 'max:5'],
            'services.*' => ['string', 'distinct', Rule::in($categories)],
            'description' => ['required', 'string', 'min:20', 'max:500'],
            'liberal_credential' => [Rule::requiredIf($profileKind === 'liberal_professional'), 'nullable', 'string', 'max:150'],
            'liberal_credential_issuer' => [Rule::requiredIf($profileKind === 'liberal_professional'), 'nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_kind.required' => 'Escolha o tipo do seu perfil profissional.',
            'profile_kind.in' => 'O tipo de perfil escolhido não é válido.',
            'account_name.required' => 'Informe o seu nome para criar a conta.',
            'email.unique' => 'Este e-mail já possui uma conta. Entre no site para cadastrar o perfil.',
            'phone.unique' => 'Este telefone já possui uma conta. Entre no site para cadastrar o perfil.',
            'category.in' => 'Escolha uma categoria disponível para o tipo de perfil selecionado.',
            'services.required' => 'Escolha pelo menos um serviço ou área de atuação.',
            'services.min' => 'Escolha pelo menos um serviço ou área de atuação.',
            'description.min' => 'Conte um pouco mais sobre o seu trabalho usando pelo menos 20 caracteres.',
            'liberal_credential.required' => 'Informe o registro profissional.',
            'liberal_credential_issuer.required' => 'Informe o conselho ou órgão responsável pelo registro.',
            'terms.accepted' => 'Você precisa aceitar os Termos de Uso para publicar o perfil.',
        ];
    }

    private function categoriesFor(string $profileKind): array
    {
        return collect(config("marketplace.service_categories_by_profile_kind.{$profileKind}", []))
            ->whenEmpty(fn ($items) => $items->concat(config('marketplace.service_categories', [])))
            ->filter()
            ->unique(fn (string $name) => mb_strtolower($name))
            ->values()
            ->all();
    }
}
