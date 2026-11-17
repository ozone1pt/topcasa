<?php

namespace App\Services;

use App\Models\Anuncio;

class ResolvedorAnuncio
{
    public function porChave(string $chave): Anuncio
    {
        $anuncio = ctype_digit($chave)
            ? Anuncio::query()->find($chave)
            : Anuncio::query()->where('slug', $chave)->first();

        abort_if($anuncio === null, 404);

        return $anuncio;
    }
}
