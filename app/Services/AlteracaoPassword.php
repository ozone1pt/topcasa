<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AlteracaoPassword
{
    public function definir(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }
}
