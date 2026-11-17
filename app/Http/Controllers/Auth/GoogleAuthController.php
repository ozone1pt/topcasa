<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsernameGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(UsernameGenerator $usernames): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed', ['message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors([
                'login' => 'Não foi possível autenticar com o Google. Tenta novamente.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user && $user->email && ! $user->email_verified_at) {
            return redirect()->route('login')->withErrors([
                'login' => 'Esta conta ainda não está ativada. Verifica o teu email.',
            ]);
        }

        if (! $user) {
            $user = User::create([
                'username' => $usernames->fromSeed($googleUser->getEmail() ?? $googleUser->getName() ?? 'user'),
                'email' => $googleUser->getEmail(),
                'nome' => $googleUser->getName(),
                'password' => bin2hex(random_bytes(16)),
                'user_type' => 'cliente',
                'status' => 'aprovado',
                'email_verified_at' => now(),
            ]);
        }

        if (! $user->google_id) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
