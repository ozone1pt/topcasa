<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGenerator
{
    /**
     * Same username derived from an email (or any seed) that the web
     * registration, Google sign-in and guest anúncio flow already use.
     */
    public function fromSeed(string $seed): string
    {
        $base = Str::slug(Str::before($seed, '@')) ?: 'user';
        $username = $base;
        $suffix = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }
}
