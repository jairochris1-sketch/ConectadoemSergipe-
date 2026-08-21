<?php

namespace App\Http\Controllers;

use App\Core\SergipeCities;
use App\Http\Requests\StoreQuickProfileRequest;
use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Category;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class QuickProfileController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user() && ! $request->user()->canCreateAnotherProfessionalProfile()) {
            return redirect()
                ->route('module.services')
                ->with('professional_profile_limit', 'Seu plano atual atingiu o limite de perfis profissionais.');
        }

        $profileKinds = collect(Ad::PROFILE_KINDS)->map(function (array $profile, string $key) {
            $displayLabels = [
                'professional' => 'Prestador de Serviços',
                'liberal_professional' => 'Profissional Liberal',
                'store_commerce' => 'Lojas',
                'service_company' => 'Empresas de Serviços',
                'cultural_artist' => 'Artistas',
                'hiring_company' => 'Empresa Contratante',
                'real_estate_agency' => 'Imobiliária',
                'agro_producer' => 'Produtor Rural',
            ];

            return [...$profile, 'key' => $key, 'label' => $displayLabels[$key] ?? $profile['label']];
        })->values();

        $categoriesByProfileKind = collect(array_keys(Ad::PROFILE_KINDS))
            ->mapWithKeys(fn (string $kind) => [$kind => $this->categoriesFor($kind)]);

        return view('quick-profile.create', [
            'cities' => SergipeCities::getAll(),
            'profileKinds' => $profileKinds,
            'categoriesByProfileKind' => $categoriesByProfileKind,
        ]);
    }

    public function store(StoreQuickProfileRequest $request): RedirectResponse
    {
        if ($request->user() && ! $request->user()->canCreateAnotherProfessionalProfile()) {
            throw ValidationException::withMessages([
                'profile_kind' => 'Seu plano atual atingiu o limite de perfis profissionais.',
            ]);
        }

        $validated = $request->validated();
        $uploadedPaths = [];
        $createdUser = false;

        try {
            [$user, $ad] = DB::transaction(function () use ($request, $validated, &$uploadedPaths, &$createdUser) {
                $user = $request->user();

                if (! $user) {
                    $user = User::create([
                        'name' => $validated['account_name'],
                        'username' => $this->uniqueUsername($validated['account_name']),
                        'email' => $validated['email'],
                        'password' => Hash::make($validated['password']),
                        'phone' => $validated['phone'],
                        'whatsapp' => $validated['phone'],
                        'city' => $validated['main_city'],
                        'role' => 'user',
                    ]);
                    $createdUser = true;
                }

                $category = Category::query()
                    ->where('active', true)
                    ->where('module', 'services')
                    ->where('name', $validated['category'])
                    ->first();

                $services = collect($validated['services'])->unique()->values()->all();
                $coverage = $request->boolean('whole_state')
                    ? 'Todo o estado de Sergipe'
                    : collect([$validated['main_city'], ...($validated['cities'] ?? [])])->unique()->implode(', ');

                $technicalSpecs = [
                    'quick_profile' => [
                        'services' => $services,
                        'coverage' => $coverage,
                    ],
                ];

                if ($validated['profile_kind'] === 'liberal_professional') {
                    $technicalSpecs['liberal_profile'] = [
                        'credential' => $validated['liberal_credential'],
                        'credential_issuer' => $validated['liberal_credential_issuer'],
                        'credential_verified' => false,
                        'specialties' => collect($services)->map(fn (string $service) => ['title' => $service])->all(),
                    ];
                }

                foreach ($request->file('photos', []) as $photo) {
                    $path = ImageOptimizer::convertToWebp($photo, 'quick_profile');
                    if ($path) {
                        $uploadedPaths[] = $path;
                    }
                }

                $adData = [
                    'user_id' => $user->id,
                    'category_id' => $category?->id,
                    'module' => 'services',
                    'profile_kind' => $validated['profile_kind'],
                    'advertiser_type' => $validated['category'],
                    'title' => $validated['name'],
                    'slug' => $this->uniqueAdSlug($validated['name']),
                    'description' => $validated['description'],
                    'price' => null,
                    'technical_specs' => $technicalSpecs,
                    'city' => $validated['main_city'],
                    'state' => 'Sergipe',
                    'region' => $validated['neighborhood'] ?? null,
                    'logo' => $uploadedPaths[0] ?? null,
                    'status' => 'active',
                    'views' => 0,
                    'publication_ip' => $request->ip(),
                    'is_claimed' => true,
                    'claiming_enabled' => false,
                    'claimed_at' => now(),
                ];

                if (Schema::hasColumn('ads', 'price_type')) {
                    $adData['price_type'] = 'negotiable';
                }
                if (Schema::hasColumn('ads', 'service_modes')) {
                    $adData['service_modes'] = [];
                }

                $ad = Ad::create($adData);

                foreach ($uploadedPaths as $index => $path) {
                    AdImage::create([
                        'ad_id' => $ad->id,
                        'image_path' => $path,
                        'is_main' => $index === 0,
                    ]);
                }

                $user->update([
                    'whatsapp' => $user->whatsapp ?: $user->phone,
                    'city' => $validated['main_city'],
                ]);

                return [$user, $ad];
            });
        } catch (Throwable $exception) {
            collect($uploadedPaths)->each(function (string $path) {
                $absolutePath = public_path($path);
                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            });

            throw $exception;
        }

        if ($createdUser) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()
            ->route('provider.show', $ad->slug)
            ->with('success', 'Conta e perfil profissional criados com sucesso!');
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

    private function uniqueUsername(string $name): string
    {
        $base = Str::of(Str::ascii($name))
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->limit(24, '')
            ->value();
        $base = strlen($base) >= 3 ? $base : 'usuario';
        $candidate = $base;
        $suffix = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = Str::limit($base, 25, '').'.'.$suffix++;
        }

        return $candidate;
    }

    private function uniqueAdSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'perfil-profissional';
        $candidate = $base;
        $suffix = 2;

        while (Ad::where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
