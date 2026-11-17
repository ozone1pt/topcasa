<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AutenticacaoUtilizador
{
    /**
     * Username OR email, same lockout as the web login form. Does not start
     * a session — the caller decides between Auth::login and a token.
     *
     * @throws ValidationException
     */
    public function autenticar(Request $request): User
    {
        $this->garantirQueNaoEstaLimitado($request);

        $login = (string) $request->string('login');
        $user = User::query()->where('username', $login)->orWhere('email', $login)->first();

        if (! $user || ! Auth::validate(['id' => $user->id, 'password' => (string) $request->string('password')])) {
            RateLimiter::hit($this->chave($request));

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->chave($request));

        return $user;
    }

    /**
     * @throws ValidationException
     */
    private function garantirQueNaoEstaLimitado(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->chave($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->chave($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function chave(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->string('login')).'|'.$request->ip());
    }
}
