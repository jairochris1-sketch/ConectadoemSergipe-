<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Report;
use App\Models\ReportNotification;
use App\Models\ReviewReport;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            if (Auth::user()->role === 'admin' && ! Auth::user()->suspended_at) {
                $request->session()->regenerate();

                return redirect()->intended(route('admin.dashboard'));
            } else {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Acesso negado. Esta conta não possui privilégios de Administrador.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'E-mail ou senha incorretos para o Painel Administrativo.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $usersCount = User::count();
        $adsCount = Ad::count();
        $activeAdsCount = Ad::where('status', 'active')->count();
        $categoriesCount = Category::count();
        $storesCount = Store::count();
        $openReportsCount = Report::whereIn('status', ['open', 'reviewing'])->count();
        $criticalReportsCount = Report::whereIn('status', ['open', 'reviewing'])->where('severity', 'critical')->count();
        $pendingReviewReportsCount = ReviewReport::where('status', 'pending')->count();

        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentAds = Ad::with(['user', 'mainImage'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('usersCount', 'adsCount', 'activeAdsCount', 'categoriesCount', 'storesCount', 'recentUsers', 'recentAds', 'openReportsCount', 'criticalReportsCount', 'pendingReviewReportsCount'));
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        $plans = \App\Models\Plan::active()->ordered()->get();

        return view('admin.users', compact('users', 'plans'));
    }

    public function storeUser(Request $request)
    {
        $request->merge([
            'username' => mb_strtolower(ltrim(trim($request->username ?? ''), '@')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9._]+$/', 'unique:users,username'],
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'role' => 'required|in:user,collaborator,admin',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'whatsapp' => $request->phone,
            'city' => $request->city ?? 'Aracaju',
            'role' => $request->role,
        ]);

        return back()->with('success', 'Cliente / Usuário cadastrado com sucesso!');
    }

    public function toggleUserRole($id, Request $request)
    {
        $user = User::findOrFail($id);

        if ($user->is($request->user())) {
            return back()->with('error', 'Você não pode alterar o seu próprio nível de acesso.');
        }

        $newRole = $request->validate([
            'role' => 'nullable|in:user,collaborator,admin',
        ])['role'] ?? ($user->role === 'admin' ? 'user' : 'admin');

        if ($user->role === 'admin' && $newRole !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'O painel precisa manter pelo menos um administrador.');
        }

        $user->update(['role' => $newRole]);

        return back()->with('success', "Função do usuário {$user->name} alterada para {$newRole}.");
    }

    public function ads()
    {
        $ads = Ad::with(['user', 'mainImage'])->orderBy('created_at', 'desc')->paginate(15);
        $users = User::orderBy('name', 'asc')->get();

        return view('admin.ads', compact('ads', 'users'));
    }

    public function storeAd(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'module' => 'required|in:real_estate,vehicles,products,services,jobs,agro',
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'description' => 'required|string',
            'contact_phone' => 'nullable|string|max:30',
            'contact_whatsapp' => 'nullable|string|max:30',
            'profile_is_claimed' => 'nullable|boolean',
        ]);

        $slug = Str::slug($request->title).'-'.time().'-'.rand(1000, 9999);
        $isClaimed = $request->module !== 'services' || $request->boolean('profile_is_claimed');
        $ownerUserId = $request->module === 'services' && ! $isClaimed
            ? $request->user()->id
            : (int) $request->user_id;

        $ad = Ad::create([
            'user_id' => $ownerUserId,
            'module' => $request->module,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'status' => 'active',
            'city' => $request->city,
            'views' => 0,
            'publication_ip' => $request->ip(),
            'is_claimed' => $isClaimed,
            'claiming_enabled' => false,
            'claimed_at' => $isClaimed ? now() : null,
            'contact_phone' => $request->input('contact_phone'),
            'contact_whatsapp' => $request->input('contact_whatsapp'),
        ]);

        return back()->with('success', 'Anúncio / Prestador de Serviço cadastrado com sucesso!');
    }

    public function toggleAdStatus($id, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive,sold,banned,pending',
        ]);

        $ad = Ad::findOrFail($id);
        $status = $validated['status'];
        $ad->update(['status' => $status]);

        return back()->with('success', "Status do anúncio #{$ad->id} alterado para {$status}.");
    }

    public function toggleProviderClaiming(Ad $ad, Request $request)
    {
        abort_unless($ad->module === 'services', 404);
        abort_unless(
            $ad->user_id === $request->user()->id,
            403,
            'Somente perfis cadastrados pela sua conta administrativa podem ser liberados para reivindicação.'
        );

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);
        $enabled = (bool) $validated['enabled'];
        $changes = ['claiming_enabled' => $enabled];

        if ($enabled) {
            $changes = array_merge($changes, [
                'is_claimed' => false,
                'claimed_at' => null,
            ]);
        }

        $ad->update($changes);

        return back()->with(
            'success',
            $enabled
                ? "A opção de reivindicar foi ativada em {$ad->title}."
                : "A opção de reivindicar foi desativada em {$ad->title}."
        );
    }

    public function categories()
    {
        $categories = Category::orderBy('sort_order', 'asc')->get();

        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => ['required', 'string', 'max:100', 'regex:/^fa-[a-z0-9-]+$/'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $slug = Str::slug($validated['name']);

        if ($slug === '' || Category::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Já existe uma categoria com este nome.',
            ]);
        }

        Category::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'sort_order' => Category::count() + 1,
        ]);

        return back()->with('success', 'Categoria criada com sucesso!');
    }

    public function stores(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'moderation' => ['nullable', Rule::in(['approved', 'suspended'])],
            'featured' => ['nullable', Rule::in(['yes', 'no'])],
            'category' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $status = $validated['status'] ?? '';
        $moderation = $validated['moderation'] ?? '';
        $featured = $validated['featured'] ?? '';
        $category = trim((string) ($validated['category'] ?? ''));
        $city = trim((string) ($validated['city'] ?? ''));

        $stores = Store::query()
            ->with(['user', 'moderator'])
            ->withCount([
                'ads as products_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->withAvg([
                'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
            ], 'rating')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($searchQuery) use ($q) {
                    $searchQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery
                                ->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('active', false))
            ->when($moderation, fn ($query) => $query->where('moderation_status', $moderation))
            ->when($featured === 'yes', fn ($query) => $query
                ->where('featured', true)
                ->where(fn ($dateQuery) => $dateQuery
                    ->whereNull('featured_until')
                    ->orWhere('featured_until', '>', now())))
            ->when($featured === 'no', fn ($query) => $query
                ->where(fn ($featuredQuery) => $featuredQuery
                    ->where('featured', false)
                    ->orWhere('featured_until', '<=', now())))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($city, fn ($query) => $query->where('city', $city))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total' => Store::count(),
            'public' => Store::publiclyVisible()->count(),
            'inactive' => Store::where('active', false)->where('moderation_status', 'approved')->count(),
            'suspended' => Store::where('moderation_status', 'suspended')->count(),
            'featured' => Store::where('featured', true)
                ->where(fn ($query) => $query
                    ->whereNull('featured_until')
                    ->orWhere('featured_until', '>', now()))
                ->count(),
        ];
        $categories = Store::whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
        $cities = Store::whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city');

        return view('admin.stores', compact(
            'stores',
            'metrics',
            'categories',
            'cities',
            'q',
            'status',
            'moderation',
            'featured',
            'category',
            'city'
        ));
    }

    public function showStore(Store $store)
    {
        $store->load([
            'user',
            'moderator',
            'media',
            'ads' => fn ($query) => $query
                ->with('mainImage')
                ->where('module', 'products')
                ->latest()
                ->limit(12),
            'reviews' => fn ($query) => $query
                ->with('user')
                ->latest()
                ->limit(10),
        ]);
        $store->loadCount([
            'ads as products_count' => fn ($query) => $query->where('module', 'products'),
            'reviews as reviews_count',
            'media as additional_banners_count' => fn ($query) => $query->where('type', 'banner'),
            'media as gallery_images_count' => fn ($query) => $query->where('type', 'gallery'),
        ]);
        $store->loadAvg([
            'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
        ], 'rating');

        return view('admin.store-show', compact('store'));
    }

    public function storeAction(Request $request, Store $store)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in([
                'approve',
                'suspend',
                'activate',
                'deactivate',
                'feature',
                'unfeature',
            ])],
            'featured_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'moderation_note' => [
                'nullable',
                'string',
                'max:2000',
                Rule::requiredIf(fn () => $request->input('action') === 'suspend'),
            ],
        ]);

        $action = $validated['action'];
        $note = isset($validated['moderation_note'])
            ? trim($validated['moderation_note'])
            : null;

        if ($action === 'activate' && ! $store->isModerationApproved()) {
            return back()->with('error', 'A loja precisa ser aprovada antes de ser ativada.');
        }
        if ($action === 'feature' && (! $store->active || ! $store->isModerationApproved())) {
            return back()->with('error', 'Somente lojas ativas e aprovadas podem receber destaque.');
        }
        if ($action === 'feature' && ! $store->user?->canHaveFeaturedStore()) {
            return back()->with('error', 'O destaque de loja está disponível somente para proprietários do plano Ouro.');
        }

        $changes = [
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
            'moderation_note' => $note,
        ];
        $message = match ($action) {
            'approve' => 'Loja aprovada e publicada novamente.',
            'suspend' => 'Loja suspensa e removida da vitrine pública.',
            'activate' => 'Loja ativada na vitrine pública.',
            'deactivate' => 'Loja desativada temporariamente.',
            'feature' => 'Loja adicionada aos destaques da vitrine.',
            'unfeature' => 'Destaque da loja removido.',
        };

        if ($action === 'approve') {
            $changes['moderation_status'] = 'approved';
            $changes['active'] = true;
        } elseif ($action === 'suspend') {
            $changes['moderation_status'] = 'suspended';
            $changes['active'] = false;
            $changes['featured'] = false;
            $changes['featured_until'] = null;
        } elseif ($action === 'activate') {
            $changes['active'] = true;
        } elseif ($action === 'deactivate') {
            $changes['active'] = false;
            $changes['featured'] = false;
            $changes['featured_until'] = null;
        } elseif ($action === 'feature') {
            $days = (int) ($validated['featured_days']
                ?? config('marketplace.store_featured_default_days', 30));
            $changes['featured'] = true;
            $changes['featured_until'] = now()->addDays($days);
        } elseif ($action === 'unfeature') {
            $changes['featured'] = false;
            $changes['featured_until'] = null;
        }

        $store->update($changes);

        ReportNotification::sendTo($store->user_id, [
            'kind' => 'store_moderation',
            'message' => match ($action) {
                'approve' => "Sua loja \"{$store->name}\" foi aprovada e está visível novamente.",
                'suspend' => "Sua loja \"{$store->name}\" foi suspensa pela moderação. Consulte a observação no gerenciamento da loja.",
                'activate' => "Sua loja \"{$store->name}\" foi ativada pela administração.",
                'deactivate' => "Sua loja \"{$store->name}\" foi desativada temporariamente pela administração.",
                'feature' => "Sua loja \"{$store->name}\" recebeu destaque na vitrine.",
                'unfeature' => "O período de destaque da loja \"{$store->name}\" foi encerrado.",
            },
            'action_url' => $store->user->stores()->count() === 1
                ? route('store.edit', [], false)
                : route('store.manage', $store, false),
        ]);

        return back()->with('success', $message);
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        $rules = [
            'site_name' => 'required|string|max:100',
            'contact_email' => 'required|email|max:255',
            'whatsapp_number' => 'nullable|string|max:30',
            'instagram_url' => 'nullable|url|max:255',
        ];
        $bannerKeys = [];

        foreach (['home_banner', 'services_banner', 'real_estate_banner', 'vehicles_banner'] as $prefix) {
            $maxSlots = in_array($prefix, ['real_estate_banner', 'vehicles_banner']) ? 5 : 6;
            foreach (range(1, $maxSlots) as $slot) {
                $key = "{$prefix}_{$slot}";
                $bannerKeys[] = $key;
                $rules[$key] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120';
            }
        }

        $validated = $request->validate($rules);

        foreach (['site_name', 'contact_email', 'whatsapp_number', 'instagram_url'] as $key) {
            Setting::set($key, $validated[$key] ?? null);
        }

        foreach ($bannerKeys as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $oldPath = Setting::get($key);
            $newPath = ImageOptimizer::convertToWebp($request->file($key), $key);

            if (! $newPath) {
                throw ValidationException::withMessages([
                    $key => 'Não foi possível processar esta imagem.',
                ]);
            }

            Setting::set($key, $newPath);
            $this->deleteManagedBanner($oldPath);

            if ($key === 'home_banner_1') {
                $oldSocialPreview = Setting::get('home_social_preview');
                $newSocialPreview = ImageOptimizer::createSocialJpeg($newPath);
                if ($newSocialPreview) {
                    Setting::set('home_social_preview', $newSocialPreview);
                    $this->deleteManagedBanner($oldSocialPreview);
                }
            }
        }

        return back()->with('success', 'Configurações globais e banners salvos com sucesso!');
    }

    public function updatePublishPageDesign(Request $request)
    {
        $validated = $request->validate([
            'publish_page_design' => 'required|in:design4,design5',
        ]);

        Setting::set('publish_page_design', $validated['publish_page_design']);

        $designName = $validated['publish_page_design'] === 'design5'
            ? 'Modelo 5 — Minimalista'
            : 'Modelo 4 — Bordas arredondadas';

        return back()->with('success', "{$designName} ativado na página de anunciar.");
    }

    private function deleteManagedBanner(?string $path): void
    {
        if (
            ! $path ||
            (! str_starts_with($path, 'uploads/home_banner_') &&
             ! str_starts_with($path, 'uploads/services_banner_') &&
             ! str_starts_with($path, 'uploads/real_estate_banner_') &&
             ! str_starts_with($path, 'uploads/vehicles_banner_') &&
             ! str_starts_with($path, 'uploads/home_social_'))
        ) {
            return;
        }

        $absolutePath = public_path($path);

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
