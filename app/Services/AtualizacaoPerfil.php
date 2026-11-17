<?php

namespace App\Services;

use App\Models\User;

class AtualizacaoPerfil
{
    /**
     * Applies ProfileUpdateRequest data. Changing the email clears
     * verification, same as the web profile form.
     *
     * @param  array<string, mixed>  $dados
     */
    public function aplicar(User $user, array $dados): User
    {
        $user->fill($dados);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
