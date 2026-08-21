<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\CommunityHelpRequest;
use App\Models\CultureWork;
use App\Models\FeedPost;
use App\Models\Order;
use App\Models\ProviderClaim;
use App\Models\Report;
use App\Models\ReportNotification;
use App\Models\ReviewReport;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use App\Services\ImageOptimizer;
use App\Support\HomeCityGroupCatalog;
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
        $ordersCount = Order::count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $cultureWorksCount = CultureWork::count();
        $pendingHelpRequestsCount = CommunityHelpRequest::where('status', 'pending')->count();
        $reportedHelpRequestsCount = CommunityHelpRequest::whereHas(
            'responses.reports',
            fn ($reports) => $reports->where('status', 'pending')
        )->count();
        $pendingAdsCount = Ad::where('status', 'pending')->count();
        $pendingClaimsCount = ProviderClaim::where('status', ProviderClaim::STATUS_PENDING)->count();
        $pendingFeedPostsCount = FeedPost::where('status', 'pending')->count();
        $reportedFeedPostsCount = FeedPost::whereHas(
            'reports',
            fn ($reports) => $reports->where('status', 'pending')
        )->count();
        $suspendedUsersCount = User::whereNotNull('suspended_at')->count();
        $openReportsCount = Report::whereIn('status', ['open', 'reviewing'])->count();
        $criticalReportsCount = Report::whereIn('status', ['open', 'reviewing'])->where('severity', 'critical')->count();
        $pendingReviewReportsCount = ReviewReport::where('status', 'pending')->count();

        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $recentAds = Ad::with(['user', 'mainImage'])->orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('usersCount', 'adsCount', 'activeAdsCount', 'categoriesCount', 'storesCount', 'ordersCount', 'pendingOrdersCount', 'cultureWorksCount', 'pendingHelpRequestsCount', 'reportedHelpRequestsCount', 'pendingAdsCount', 'pendingClaimsCount', 'pendingFeedPostsCount', 'reportedFeedPostsCount', 'suspendedUsersCount', 'recentUsers', 'recentAds', 'openReportsCount', 'criticalReportsCount', 'pendingReviewReportsCount'));
    }

    public function users(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['user', 'collaborator', 'admin'])],
            'account_status' => ['nullable', Rule::in(['active', 'suspended'])],
        ]);
        $search = trim((string) ($validated['q'] ?? ''));
        $role = $validated['role'] ?? '';
        $accountStatus = $validated['account_status'] ?? '';

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($accountStatus === 'active', fn ($query) => $query->whereNull('suspended_at'))
            ->when($accountStatus === 'suspended', fn ($query) => $query->whereNotNull('suspended_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $plans = \App\Models\Plan::active()->ordered()->get();
        $metrics = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->whereNull('suspended_at')->count(),
            'collaborators' => User::where('role', 'collaborator')->whereNull('suspended_at')->count(),
            'suspended' => User::whereNotNull('suspended_at')->count(),
        ];

        return view('admin.users', compact('users', 'plans', 'metrics', 'search', 'role', 'accountStatus'));
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

    public function updateUser($id, Request $request)
    {
        $user = User::findOrFail($id);

        if ($request->has('username')) {
            $request->merge([
                'username' => mb_strtolower(ltrim(trim($request->username ?? ''), '@')),
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9._]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'city' => 'nullable|string|max:100',
            'role' => 'required|in:user,collaborator,admin',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'whatsapp' => $request->phone,
            'city' => $request->city ?? 'Aracaju',
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return back()->with('success', "Dados do usuário #{$user->id} ('{$user->name}') atualizados com sucesso!");
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

        if ($user->role === 'admin'
            && ! $user->suspended_at
            && $newRole !== 'admin'
            && User::where('role', 'admin')->whereNull('suspended_at')->count() <= 1) {
            return back()->with('error', 'O painel precisa manter pelo menos um administrador.');
        }

        $user->update(['role' => $newRole]);

        return back()->with('success', "Função do usuário {$user->name} alterada para {$newRole}.");
    }

    public function updateUserStatus(User $user, Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['suspend', 'restore'])],
        ]);

        if ($user->is($request->user())) {
            return back()->with('error', 'Você não pode suspender a própria conta administrativa.');
        }

        if ($validated['action'] === 'suspend'
            && $user->role === 'admin'
            && User::where('role', 'admin')->whereNull('suspended_at')->count() <= 1) {
            return back()->with('error', 'O painel precisa manter ao menos um administrador ativo.');
        }

        $user->forceFill([
            'suspended_at' => $validated['action'] === 'suspend' ? now() : null,
            'remember_token' => $validated['action'] === 'suspend' ? null : $user->remember_token,
        ])->save();

        return back()->with('success', $validated['action'] === 'suspend'
            ? "A conta de {$user->name} foi suspensa."
            : "A conta de {$user->name} foi reativada.");
    }

    public function destroyUser($id, Request $request)
    {
        $user = User::findOrFail($id);

        if ($user->is($request->user())) {
            return back()->with('error', 'Você não pode excluir a própria conta de administrador.');
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Não é possível excluir o único administrador da plataforma.');
        }

        $userName = $user->name;

        // Exclui em cascata ou desvincula registros associados
        $user->ads()->delete();
        $user->stores()->delete();
        $user->cultureWorks()->delete();
        $user->feedPosts()->delete();
        $user->communityHelpRequests()->delete();

        $user->delete();

        return back()->with('success', "Cliente / Usuário '{$userName}' (#{$id}) foi excluído com sucesso junto com seus registros.");
    }

    public function ads(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', Rule::in(['real_estate', 'vehicles', 'products', 'services', 'jobs', 'agro', 'culture', 'stores'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'sold', 'banned', 'pending'])],
        ]);
        $query = Ad::with(['user', 'mainImage']);

        if ($search = ($validated['q'] ?? null)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($module = ($validated['module'] ?? null)) {
            $query->where('module', $module);
        }
        if ($status = ($validated['status'] ?? null)) {
            $query->where('status', $status);
        }

        $ads = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $users = User::orderBy('name', 'asc')->get();
        $categories = Category::where('active', true)->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        return view('admin.ads', compact('ads', 'users', 'categories', 'status'));
    }

    public function storeAd(Request $request)
    {
        // Higienizar valor monetário (ex: "80,00", "80.000,00" ou "80000" -> 80.00 / 80000.00)
        if ($request->filled('price')) {
            $rawPrice = (string) $request->input('price');
            $rawPrice = preg_replace('/[^\d\,\.]/', '', $rawPrice);
            if (str_contains($rawPrice, ',')) {
                $rawPrice = str_replace('.', '', $rawPrice);
                $rawPrice = str_replace(',', '.', $rawPrice);
            }
            $request->merge(['price' => is_numeric($rawPrice) ? (float) $rawPrice : null]);
        }

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'new_client_name' => 'nullable|string|max:255',
            'new_client_email' => 'nullable|email|max:255',
            'new_client_phone' => 'nullable|string|max:30',
            'module' => 'required|in:real_estate,vehicles,products,services,jobs,agro,culture,stores',
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'contact_phone' => 'nullable|string|max:30',
            'contact_whatsapp' => 'nullable|string|max:30',
            'contact_telegram' => 'nullable|string|max:50',
            'public_address' => 'nullable|string|max:255',
            'card_image' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
            'logo' => 'nullable|image|max:5120',
            'is_plan_featured' => 'nullable|boolean',
            'profile_is_claimed' => 'nullable|boolean',
        ]);

        $slug = Str::slug($request->title).'-'.time().'-'.rand(1000, 9999);
        $isClaimed = $request->module !== 'services' ? true : $request->boolean('profile_is_claimed', false);

        $ownerUserId = null;
        if ($request->filled('new_client_name')) {
            $clientName = trim($request->input('new_client_name'));
            $clientEmail = trim((string) $request->input('new_client_email'));
            $clientPhone = trim((string) $request->input('new_client_phone'));

            if (! $clientEmail) {
                $cleanSlug = Str::slug($clientName) ?: 'cliente';
                $clientEmail = $cleanSlug . '-' . rand(100, 999) . '@cliente.conectadoemsergipe.com.br';
            }

            $user = User::where('email', $clientEmail)->first();
            if (! $user) {
                $user = User::create([
                    'name' => $clientName,
                    'email' => $clientEmail,
                    'phone' => $clientPhone ?: $request->input('contact_whatsapp') ?: $request->input('contact_phone'),
                    'whatsapp' => $clientPhone ?: $request->input('contact_whatsapp'),
                    'city' => $request->input('city') ?? 'Aracaju',
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'role' => 'user',
                ]);
            } else {
                if ($clientPhone && ! $user->phone) {
                    $user->update(['phone' => $clientPhone, 'whatsapp' => $clientPhone]);
                }
            }
            $ownerUserId = $user->id;
        } else {
            $ownerUserId = (int) $request->input('user_id');
        }

        if (!$ownerUserId) {
            $ownerUserId = $request->user()?->id ?: User::first()->id;
        }

        $cardImagePath = null;
        if ($request->hasFile('card_image')) {
            $cardImagePath = 'storage/' . $request->file('card_image')->store('ads', 'public');
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = 'storage/' . $request->file('banner')->store('ads', 'public');
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = 'storage/' . $request->file('logo')->store('ads', 'public');
        }

        $ad = Ad::create([
            'user_id' => $ownerUserId,
            'module' => $request->module,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'status' => 'active',
            'city' => $request->city,
            'public_address' => $request->input('public_address'),
            'views' => 0,
            'publication_ip' => $request->ip(),
            'is_claimed' => $isClaimed,
            'claiming_enabled' => false,
            'claimed_at' => $isClaimed ? now() : null,
            'contact_phone' => $request->input('contact_phone'),
            'contact_whatsapp' => $request->input('contact_whatsapp'),
            'contact_telegram' => $request->input('contact_telegram'),
            'card_image' => $cardImagePath,
            'banner' => $bannerPath,
            'logo' => $logoPath,
            'is_plan_featured' => $request->boolean('is_plan_featured'),
        ]);

        return back()->with('success', 'Anúncio / Prestador de Serviço cadastrado com sucesso!');
    }

    public function updateAd($id, Request $request)
    {
        $ad = Ad::findOrFail($id);

        if ($request->filled('price')) {
            $rawPrice = (string) $request->input('price');
            $rawPrice = preg_replace('/[^\d\,\.]/', '', $rawPrice);
            if (str_contains($rawPrice, ',')) {
                $rawPrice = str_replace('.', '', $rawPrice);
                $rawPrice = str_replace(',', '.', $rawPrice);
            }
            $request->merge(['price' => is_numeric($rawPrice) ? (float) $rawPrice : null]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'city' => 'required|string|max:100',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'contact_phone' => 'nullable|string|max:30',
            'contact_whatsapp' => 'nullable|string|max:30',
            'contact_telegram' => 'nullable|string|max:50',
            'public_address' => 'nullable|string|max:255',
            'card_image' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
            'logo' => 'nullable|image|max:5120',
            'is_plan_featured' => 'nullable|boolean',
            'status' => 'required|in:active,pending,inactive,sold,banned',
        ]);

        $updateData = [
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'city' => $request->city,
            'public_address' => $request->input('public_address'),
            'contact_phone' => $request->input('contact_phone'),
            'contact_whatsapp' => $request->input('contact_whatsapp'),
            'contact_telegram' => $request->input('contact_telegram'),
            'is_plan_featured' => $request->boolean('is_plan_featured'),
            'status' => $request->status,
        ];

        if ($request->hasFile('card_image')) {
            $updateData['card_image'] = 'storage/' . $request->file('card_image')->store('ads', 'public');
        }

        if ($request->hasFile('banner')) {
            $updateData['banner'] = 'storage/' . $request->file('banner')->store('ads', 'public');
        }

        if ($request->hasFile('logo')) {
            $updateData['logo'] = 'storage/' . $request->file('logo')->store('ads', 'public');
        }

        if ($ad->module === 'services') {
            $updateData['is_claimed'] = $request->boolean('profile_is_claimed');
            if ($updateData['is_claimed'] && ! $ad->claimed_at) {
                $updateData['claimed_at'] = now();
            } elseif (! $updateData['is_claimed']) {
                $updateData['claimed_at'] = null;
            }
        }

        $ad->update($updateData);

        return back()->with('success', "Anúncio / Prestador #{$ad->id} ('{$ad->title}') atualizado com sucesso no Painel Admin!");
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

    public function destroyAd($id)
    {
        $ad = Ad::with(['images', 'variations'])->findOrFail($id);
        $title = $ad->title;

        $files = $ad->images->pluck('image_path')
            ->push($ad->logo)
            ->push($ad->banner)
            ->push($ad->card_image)
            ->merge($ad->variations->pluck('image'))
            ->filter()
            ->unique();

        $ad->delete();

        $files->each(function ($path) {
            if ($path && ! str_contains($path, '://')) {
                \Illuminate\Support\Facades\File::delete(public_path(ltrim($path, '/')));
            }
        });

        return back()->with('success', "Anúncio / Prestador '{$title}' excluido com sucesso!");
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

    public function categories(Request $request)
    {
        if (Category::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'CategorySeeder', '--force' => true]);
        }

        $query = Category::query();

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc')->paginate(20)->withQueryString();

        $modules = [
            'real_estate' => 'Imóveis',
            'vehicles' => 'Veículos',
            'products' => 'Produtos',
            'services' => 'Serviços',
            'jobs' => 'Empregos',
            'agro' => 'Agro',
            'culture' => 'Arte & Cultura',
            'stores' => 'Lojas & Negócios',
        ];
        $profileKinds = Ad::PROFILE_KINDS;

        return view('admin.categories', compact('categories', 'modules', 'profileKinds'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'nullable|string|max:50',
            'profile_kind' => [
                Rule::requiredIf(fn () => $request->input('module') === 'services'),
                'nullable',
                Rule::in(array_keys(Ad::PROFILE_KINDS)),
            ],
            'icon' => ['required', 'string', 'max:100', 'regex:/^fa-[a-z0-9-]+$/'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $slug = Str::slug($validated['name']);

        if ($slug === '' || Category::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Já existe uma categoria com este nome ou slug.',
            ]);
        }

        Category::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'module' => $validated['module'] ?? null,
            'profile_kind' => ($validated['module'] ?? null) === 'services'
                ? $validated['profile_kind']
                : null,
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'sort_order' => Category::count() + 1,
            'active' => true,
        ]);

        return back()->with('success', 'Categoria criada com sucesso!');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'nullable|string|max:50',
            'profile_kind' => [
                Rule::requiredIf(fn () => $request->input('module') === 'services'),
                'nullable',
                Rule::in(array_keys(Ad::PROFILE_KINDS)),
            ],
            'icon' => ['required', 'string', 'max:100', 'regex:/^fa-[a-z0-9-]+$/'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => 'required|integer|min:0',
        ]);

        $newSlug = Str::slug($validated['name']);
        if ($newSlug !== $category->slug && Category::where('slug', $newSlug)->where('id', '!=', $category->id)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Já existe outra categoria com este nome.',
            ]);
        }

        $category->update([
            'name' => trim($validated['name']),
            'slug' => $newSlug,
            'module' => $validated['module'] ?? null,
            'profile_kind' => ($validated['module'] ?? null) === 'services'
                ? $validated['profile_kind']
                : null,
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Categoria atualizada com sucesso!');
    }

    public function toggleCategoryStatus(Category $category)
    {
        $category->update([
            'active' => ! $category->active,
        ]);

        $statusText = $category->active ? 'ativada' : 'desativada';

        return back()->with('success', "Categoria '{$category->name}' {$statusText} com sucesso!");
    }

    public function deleteCategory(Category $category)
    {
        $categoryName = $category->name;
        $category->delete();

        return back()->with('success', "Categoria '{$categoryName}' excluída com sucesso!");
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
            'image_moderation_enabled' => 'nullable|boolean',
            'google_vision_api_key' => 'nullable|string|max:255',
            'google_login_enabled' => 'nullable|boolean',
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'auth_balloon_enabled' => 'nullable|boolean',
            'auth_balloon_msg1' => 'nullable|string|max:255',
            'auth_balloon_msg2' => 'nullable|string|max:255',
            'auth_balloon_msg3' => 'nullable|string|max:255',
            'landing_enabled' => 'nullable|boolean',
            'landing_eyebrow' => 'nullable|string|max:100',
            'landing_title' => 'nullable|string|max:100',
            'landing_highlight' => 'nullable|string|max:60',
            'landing_description' => 'nullable|string|max:500',
            'landing_supporting_text' => 'nullable|string|max:300',
            'landing_about_eyebrow' => 'nullable|string|max:100',
            'landing_about_title' => 'nullable|string|max:150',
            'landing_about_description' => 'nullable|string|max:500',
            'landing_video_url' => 'nullable|url|max:255',
            'landing_primary_label' => 'nullable|string|max:40',
            'landing_secondary_label' => 'nullable|string|max:40',
            'home_banner_brightness' => 'nullable|integer|min:0|max:100',
            'home_banner_blur' => 'nullable|integer|min:0|max:20',
        ];
        $homeCityGroupSlots = array_column(HomeCityGroupCatalog::all(), 'slot');
        foreach ($homeCityGroupSlots as $slot) {
            $rules["home_city_group_link_{$slot}"] = 'nullable|url|max:500';
            $rules["home_city_group_enabled_{$slot}"] = 'nullable|boolean';
        }
        $bannerKeys = [];

        foreach (['landing_image', 'home_city_group_cover', 'home_banner', 'services_banner', 'real_estate_banner', 'vehicles_banner', 'culture_banner'] as $prefix) {
            $maxSlots = match($prefix) {
                'landing_image' => 7,
                'home_city_group_cover' => count($homeCityGroupSlots),
                'real_estate_banner', 'vehicles_banner' => 5,
                'culture_banner' => 4,
                default => 6,
            };
            foreach (range(1, $maxSlots) as $slot) {
                $key = "{$prefix}_{$slot}";
                $bannerKeys[] = $key;
                $rules[$key] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120';
            }
        }

        $validated = $request->validate($rules);

        foreach ([
            'site_name',
            'contact_email',
            'whatsapp_number',
            'instagram_url',
            'google_client_id',
            'auth_balloon_msg1',
            'auth_balloon_msg2',
            'auth_balloon_msg3',
            'landing_eyebrow',
            'landing_title',
            'landing_highlight',
            'landing_description',
            'landing_supporting_text',
            'landing_about_eyebrow',
            'landing_about_title',
            'landing_about_description',
            'landing_video_url',
            'landing_primary_label',
            'landing_secondary_label',
            'home_banner_brightness',
            'home_banner_blur',
        ] as $key) {
            if (array_key_exists($key, $validated)) {
                Setting::set($key, $validated[$key] ?? '');
            }
        }

        foreach ($homeCityGroupSlots as $slot) {
            $linkKey = "home_city_group_link_{$slot}";
            if (array_key_exists($linkKey, $validated)) {
                Setting::set($linkKey, $validated[$linkKey] ?? '');
            }
            Setting::set(
                "home_city_group_enabled_{$slot}",
                $request->boolean("home_city_group_enabled_{$slot}") ? '1' : '0'
            );
        }

        foreach (['image_moderation_enabled', 'google_login_enabled', 'auth_balloon_enabled', 'landing_enabled'] as $key) {
            Setting::set($key, $request->boolean($key) ? '1' : '0');
        }

        foreach (['google_vision_api_key', 'google_client_secret'] as $key) {
            if ($request->filled($key)) {
                Setting::set($key, $validated[$key]);
            }
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
            (! str_starts_with($path, 'uploads/landing_image_') &&
             ! str_starts_with($path, 'uploads/home_city_group_cover_') &&
             ! str_starts_with($path, 'uploads/home_banner_') &&
             ! str_starts_with($path, 'uploads/services_banner_') &&
             ! str_starts_with($path, 'uploads/real_estate_banner_') &&
             ! str_starts_with($path, 'uploads/vehicles_banner_') &&
             ! str_starts_with($path, 'uploads/culture_banner_') &&
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
