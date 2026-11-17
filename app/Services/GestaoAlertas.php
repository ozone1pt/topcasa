<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GestaoAlertas
{
    public function lista(User $user): LengthAwarePaginator
    {
        return $user->alertas()->orderByDesc('created_at')->paginate(15);
    }

    public function naoLidos(User $user): int
    {
        return $user->alertas()->where('lida', false)->count();
    }

    public function marcarLido(User $user, Alerta $alerta): Alerta
    {
        abort_unless($alerta->user_id === $user->id, 403);

        $alerta->update(['lida' => true, 'lida_em' => now()]);

        return $alerta;
    }
}
