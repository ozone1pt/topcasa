<?php

namespace App\Policies;

use App\Models\Anuncio;
use App\Models\User;

class AnuncioPolicy
{
    public function view(?User $user, Anuncio $anuncio): bool
    {
        if ($anuncio->isAprovado()) {
            return true;
        }

        return $user && ($user->id === $anuncio->user_id || $user->isAdmin());
    }

    public function update(User $user, Anuncio $anuncio): bool
    {
        return $user->id === $anuncio->user_id || $user->isAdmin();
    }

    public function delete(User $user, Anuncio $anuncio): bool
    {
        return $user->id === $anuncio->user_id || $user->isAdmin();
    }
}
