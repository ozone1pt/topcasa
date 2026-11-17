<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class GestaoAvaliacoes
{
    /**
     * @param  array{rating: int, comment?: string|null}  $dados
     */
    public function criar(User $user, Anuncio $anuncio, array $dados): Review
    {
        if (! $anuncio->isAprovado()) {
            abort(404);
        }

        if ($user->id === $anuncio->user_id) {
            throw ValidationException::withMessages([
                'rating' => 'Não podes avaliar o teu próprio anúncio.',
            ]);
        }

        if ($anuncio->reviews()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'rating' => 'Já avaliaste este anúncio.',
            ]);
        }

        return Review::create([
            ...$dados,
            'anuncio_id' => $anuncio->id,
            'user_id' => $user->id,
            'anunciante_id' => $anuncio->user_id,
            'status' => 'pendente',
        ]);
    }

    public function aprovar(Review $review): void
    {
        $review->update(['status' => 'aprovado']);
    }

    public function rejeitar(Review $review): void
    {
        $review->update(['status' => 'rejeitado']);
    }
}
