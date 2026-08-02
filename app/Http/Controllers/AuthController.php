<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        $users = $this->findUsersByIdentifier($request->login);

        $user = $users->count() === 1 ? $users->first() : null;

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'login' => 'Não foi possível entrar. Verifique o identificador e a senha ou tente outra forma de acesso.',
            ])->onlyInput('login');
        }

        if ($user->suspended_at) {
            return back()->withErrors([
                'login' => 'Esta conta está suspensa. Entre em contato com o suporte para solicitar uma revisão.',
            ])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('ad.create'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'username' => $this->normalizeUsername($request->username),
            'phone' => $this->normalizePhone($request->phone),
        ]);

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9._]+$/', 'unique:users,username'],
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'phone' => 'required|digits_between:10,11|unique:users,phone',
                'city' => 'nullable|string|max:100',
            ],
            [
                'username.required' => 'Escolha um @usuário para sua conta.',
                'username.min' => 'O @usuário deve ter pelo menos 3 caracteres.',
                'username.max' => 'O @usuário pode ter no máximo 30 caracteres.',
                'username.regex' => 'O @usuário pode conter apenas letras, números, ponto ou sublinhado.',
                'username.unique' => 'O @usuário informado já está em uso. Escolha outro nome de usuário.',
            ]
        );

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'whatsapp' => $request->phone,
            'city' => $request->city ?? 'Aracaju',
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('ad.create')->with('success', 'Conta criada com sucesso! Você já pode publicar o seu anúncio.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
        ]);

        $users = $this->findUsersByIdentifier($request->login);
        $user = $users->count() === 1 ? $users->first() : null;

        $request->session()->forget('password_recovery_user_id');
        if ($user) {
            $request->session()->put('password_recovery_user_id', $user->id);
        }

        return back()->with(
            'status',
            'Se o identificador corresponder a uma conta, as opções disponíveis de recuperação serão enviadas com segurança.'
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function findUsersByIdentifier(string $identifier): Collection
    {
        $identifier = trim($identifier);
        $normalizedText = mb_strtolower($identifier);
        $normalizedPhone = $this->normalizePhone($identifier);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = strlen($normalizedPhone) >= 10;
        $normalizedUsername = ! $isEmail && ! $isPhone
            ? $this->normalizeUsername($identifier)
            : null;

        return User::query()
            ->get()
            ->filter(function (User $user) use ($normalizedText, $normalizedPhone, $normalizedUsername) {
                $matchesEmail = mb_strtolower(trim($user->email)) === $normalizedText;
                $matchesUsername = $normalizedUsername
                    && mb_strtolower((string) $user->username) === $normalizedUsername;
                $matchesPhone = strlen($normalizedPhone) >= 10
                    && in_array($normalizedPhone, [
                        $this->normalizePhone($user->phone),
                        $this->normalizePhone($user->whatsapp),
                    ], true);

                return $matchesEmail || $matchesUsername || $matchesPhone;
            })
            ->values();
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone ?? '');

        if (str_starts_with($phone, '55') && in_array(strlen($phone), [12, 13], true)) {
            return substr($phone, 2);
        }

        return $phone;
    }

    private function normalizeUsername(?string $username): string
    {
        return mb_strtolower(ltrim(trim($username ?? ''), '@'));
    }
}
