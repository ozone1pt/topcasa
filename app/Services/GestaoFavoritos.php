<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\Favorito;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GestaoFavoritos
{
    /**
     * @return bool true when the anúncio was added, false when it was removed
     */
    public function alternar(User $user, Anuncio $anuncio): bool
    {
        $favorito = Favorito::query()
            ->where('user_id', $user->id)
            ->where('anuncio_id', $anuncio->id)
            ->first();

        if ($favorito) {
            $favorito->delete();

            return false;
        }

        Favorito::create([
            'user_id' => $user->id,
            'anuncio_id' => $anuncio->id,
            'created_at' => now(),
        ]);

        return true;
    }

    public function lista(User $user): LengthAwarePaginator
    {
        return Anuncio::query()
            ->whereHas('favoritos', fn ($q) => $q->where('user_id', $user->id))
            ->with(['categoria', 'distrito', 'concelho', 'galeria', 'user'])
            ->paginate(12);
    }
}
