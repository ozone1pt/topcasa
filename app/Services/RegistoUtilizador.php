<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegistoUtilizador
{
    public function __construct(private readonly UsernameGenerator $usernames) {}

    /**
     * Public registration: client account, already approved, email treated as
     * verified — identical to the web form.
     */
    public function criar(string $nome, string $email, string $password): User
    {
        $user = User::create([
            'username' => $this->usernames->fromSeed($email),
            'nome' => $nome,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'cliente',
            'status' => 'aprovado',
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        return $user;
    }
}
