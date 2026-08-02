<?php

namespace App\Http\Controllers;

use App\Models\ReportNotification;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function panel()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with(
                'info',
                'Entre na sua conta para visualizar seus anúncios.'
            );
        }

        $user = Auth::user();
        $ads = $user->ads()->with(['mainImage', 'store'])->latest()->get();
        $stores = $user->stores()
            ->withCount([
                'ads as products_count' => fn ($query) => $query->where('module', 'products'),
                'ads as active_ads_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
                'reviews as approved_reviews_count' => fn ($query) => $query->where('status', 'approved'),
                'orders as orders_count',
                'orders as pending_orders_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->withAvg([
                'reviews as approved_reviews_average' => fn ($query) => $query->where('status', 'approved'),
            ], 'rating')
            ->latest()
            ->get();
        $store = $stores->first();
        $storeLimit = $user->storeLimit();
        $storeProductLimit = $user->storeProductLimit();
        $canCreateStore = $user->canCreateAnotherStore();
        $favorites = $user->favorites()->with('mainImage')->get();
        $followedStores = $user->followedStores()
            ->publiclyVisible()
            ->with(['user'])
            ->withCount([
                'ads as active_ads_count' => fn ($query) => $query
                    ->where('module', 'products')
                    ->where('status', 'active'),
                'followers',
            ])
            ->orderByPivot('created_at', 'desc')
            ->get();
        $unreadMessagesCount = $user->receivedMessages()->where('is_read', false)->count();
        $unreadNotificationsCount = $user->reportNotifications()->whereNull('read_at')->count();
        $reportNotifications = $user->reportNotifications()->latest()->take(20)->get();

        return view('user.panel', compact(
            'user',
            'ads',
            'store',
            'stores',
            'storeLimit',
            'storeProductLimit',
            'canCreateStore',
            'favorites',
            'followedStores',
            'reportNotifications',
            'unreadMessagesCount',
            'unreadNotificationsCount'
        ));
    }

    public function openNotification(ReportNotification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        $destination = $notification->action_url;
        if (! $destination || ! str_starts_with($destination, '/') || str_starts_with($destination, '//')) {
            $destination = route('user.panel').'#notificacoes';
        }

        return redirect($destination);
    }

    public function updateNotificationPreference(Request $request)
    {
        $validated = $request->validate([
            'notifications_enabled' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'notifications_enabled' => (bool) $validated['notifications_enabled'],
        ]);

        $message = $request->user()->notifications_enabled
            ? 'Notificações ativadas com sucesso.'
            : 'Notificações desativadas. Você pode ativá-las novamente quando quiser.';

        return back()->with('notification_preference_success', $message);
    }

    public function updateAvailability(Request $request)
    {
        $validated = $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);

        $request->user()->update([
            'is_available' => (bool) $validated['is_available'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_available' => $request->user()->is_available,
                'message' => $request->user()->is_available
                    ? 'Status alterado para "Disponível para atendimentos".'
                    : 'Status alterado para "Indisponível no momento".',
            ]);
        }

        return back()->with('availability_success', $request->user()->is_available
            ? 'Seu status agora é "Disponível para atendimentos".'
            : 'Seu status agora é "Indisponível no momento".');
    }


    public function settings()
    {
        return view('user.settings', ['user' => Auth::user()]);
    }

    public function updateSettings(Request $request)
    {
        $request->merge([
            'whatsapp' => $this->normalizePhone($request->whatsapp),
            'instagram' => trim((string) $request->instagram),
            'facebook' => trim((string) $request->facebook),
            'website' => trim((string) $request->website),
        ]);

        $validated = $request->validate([
            'header_layout' => ['required', Rule::in(['horizontal', 'vertical'])],
            'theme_preference' => ['required', Rule::in(['light', 'dark', 'system'])],
            'notifications_enabled' => ['required', 'boolean'],
            'notification_messages_enabled' => ['required', 'boolean'],
            'notification_reviews_enabled' => ['required', 'boolean'],
            'notification_reports_enabled' => ['required', 'boolean'],
            'smart_search_enabled' => ['required', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
            'whatsapp' => ['nullable', 'digits_between:10,11'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $request->user()->update([
            'header_layout' => $validated['header_layout'],
            'theme_preference' => $validated['theme_preference'],
            'notifications_enabled' => (bool) $validated['notifications_enabled'],
            'notification_messages_enabled' => (bool) $validated['notification_messages_enabled'],
            'notification_reviews_enabled' => (bool) $validated['notification_reviews_enabled'],
            'notification_reports_enabled' => (bool) $validated['notification_reports_enabled'],
            'smart_search_enabled' => (bool) $validated['smart_search_enabled'],
            'is_available' => isset($validated['is_available']) ? (bool) $validated['is_available'] : ($request->user()->is_available ?? true),
            'whatsapp' => $validated['whatsapp'] ?: null,
            'instagram' => $validated['instagram'] ?: null,
            'facebook' => $validated['facebook'] ?: null,
            'website' => $validated['website'] ?: null,
        ]);

        return back()
            ->with('settings_success', 'Suas configurações foram salvas.')
            ->with('saved_theme_preference', $validated['theme_preference']);
    }

    public function profile()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with(
                'info',
                'Entre na sua conta para editar seu perfil.'
            );
        }

        $user = Auth::user();
        $avatarPolicy = $this->avatarPolicy($user);

        return view('user.profile', compact('user', 'avatarPolicy'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $request->merge([
            'username' => mb_strtolower(ltrim(trim($request->username ?? ''), '@')),
            'phone' => $this->normalizePhone($request->phone),
            'whatsapp' => $this->normalizePhone($request->whatsapp),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'phone' => 'nullable|digits_between:10,11',
            'whatsapp' => 'required|digits_between:10,11',
            'city' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $oldAvatar = null;

        DB::transaction(function () use ($request, &$oldAvatar) {
            $user = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $updates = [
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'city' => $request->city,
            ];

            if ($request->hasFile('avatar')) {
                $now = now();

                if ($user->isFreePlan()) {
                    if ($user->avatar_change_locked_until?->isFuture()) {
                        throw ValidationException::withMessages([
                            'avatar' => 'Você poderá trocar a foto novamente em '.$user->avatar_change_locked_until->format('d/m/Y').'.',
                        ]);
                    }

                    $windowExpired = ! $user->avatar_change_window_started_at
                        || $user->avatar_change_window_started_at->lte($now->copy()->subDays(7))
                        || ($user->avatar_change_locked_until && $user->avatar_change_locked_until->isPast());

                    $changeCount = $windowExpired ? 0 : $user->avatar_change_count;
                    $windowStartedAt = $windowExpired ? $now : $user->avatar_change_window_started_at;

                    if ($changeCount >= 2) {
                        throw ValidationException::withMessages([
                            'avatar' => 'O limite de duas trocas foi atingido. Aguarde 30 dias para alterar novamente.',
                        ]);
                    }

                    $changeCount++;
                    $updates['avatar_change_count'] = $changeCount;
                    $updates['avatar_change_window_started_at'] = $windowStartedAt;
                    $updates['avatar_change_locked_until'] = $changeCount >= 2 ? $now->copy()->addDays(30) : null;
                }

                $newAvatar = ImageOptimizer::convertToWebp($request->file('avatar'), 'avatar');
                if (! $newAvatar) {
                    throw ValidationException::withMessages(['avatar' => 'Não foi possível processar a foto enviada.']);
                }

                $oldAvatar = $user->avatar;
                $updates['avatar'] = $newAvatar;
            }

            $user->update($updates);
        });

        if ($oldAvatar && str_starts_with(ltrim($oldAvatar, '/\\'), 'uploads/')) {
            File::delete(public_path(ltrim($oldAvatar, '/\\')));
        }

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $oldAvatar = null;

        DB::transaction(function () use ($request, &$oldAvatar) {
            $user = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $now = now();
            $updates = [];

            if ($user->isFreePlan()) {
                if ($user->avatar_change_locked_until?->isFuture()) {
                    throw ValidationException::withMessages([
                        'avatar' => 'Você poderá trocar a foto novamente em '.$user->avatar_change_locked_until->format('d/m/Y').'.',
                    ]);
                }

                $windowExpired = ! $user->avatar_change_window_started_at
                    || $user->avatar_change_window_started_at->lte($now->copy()->subDays(7))
                    || ($user->avatar_change_locked_until && $user->avatar_change_locked_until->isPast());

                $changeCount = $windowExpired ? 0 : $user->avatar_change_count;
                $windowStartedAt = $windowExpired ? $now : $user->avatar_change_window_started_at;

                if ($changeCount >= 2) {
                    throw ValidationException::withMessages([
                        'avatar' => 'O limite de duas trocas foi atingido. Aguarde 30 dias para alterar novamente.',
                    ]);
                }

                $changeCount++;
                $updates['avatar_change_count'] = $changeCount;
                $updates['avatar_change_window_started_at'] = $windowStartedAt;
                $updates['avatar_change_locked_until'] = $changeCount >= 2 ? $now->copy()->addDays(30) : null;
            }

            $newAvatar = ImageOptimizer::convertToWebp($request->file('avatar'), 'avatar');
            if (! $newAvatar) {
                throw ValidationException::withMessages(['avatar' => 'Não foi possível processar a foto enviada.']);
            }

            $oldAvatar = $user->avatar;
            $updates['avatar'] = $newAvatar;

            $user->update($updates);
        });

        if ($oldAvatar && str_starts_with(ltrim($oldAvatar, '/\\'), 'uploads/')) {
            File::delete(public_path(ltrim($oldAvatar, '/\\')));
        }

        return back()->with('success', 'Foto de perfil atualizada com sucesso!');
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone ?? '');

        if (str_starts_with($phone, '55') && in_array(strlen($phone), [12, 13], true)) {
            return substr($phone, 2);
        }

        return $phone;
    }

    private function avatarPolicy(User $user): array
    {
        if (! $user->isFreePlan()) {
            return ['allowed' => true, 'remaining' => null, 'locked_until' => null];
        }

        if ($user->avatar_change_locked_until?->isFuture()) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'locked_until' => $user->avatar_change_locked_until,
            ];
        }

        $windowExpired = ! $user->avatar_change_window_started_at
            || $user->avatar_change_window_started_at->lte(now()->subDays(7))
            || ($user->avatar_change_locked_until && $user->avatar_change_locked_until->isPast());
        $count = $windowExpired ? 0 : $user->avatar_change_count;

        return [
            'allowed' => $count < 2,
            'remaining' => max(0, 2 - $count),
            'locked_until' => null,
        ];
    }
}
