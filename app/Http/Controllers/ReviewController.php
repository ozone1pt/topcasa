<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Anuncio;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Anuncio $anuncio): RedirectResponse
    {
        if (! $anuncio->isAprovado()) {
            abort(404);
        }

        if (Auth::id() === $anuncio->user_id) {
            return back()->withErrors(['rating' => 'Não podes avaliar o teu próprio anúncio.']);
        }

        if ($anuncio->reviews()->where('user_id', Auth::id())->exists()) {
            return back()->withErrors(['rating' => 'Já avaliaste este anúncio.']);
        }

        Review::create([
            ...$request->validated(),
            'anuncio_id' => $anuncio->id,
            'user_id' => Auth::id(),
            'anunciante_id' => $anuncio->user_id,
            'status' => 'pendente',
        ]);

        return back()->with('status', 'Avaliação submetida, fica pendente de aprovação.');
    }

    public function aprovar(Review $review): RedirectResponse
    {
        $this->authorize('moderate', $review);

        $review->update(['status' => 'aprovado']);

        return back()->with('status', 'Avaliação aprovada.');
    }

    public function rejeitar(Review $review): RedirectResponse
    {
        $this->authorize('moderate', $review);

        $review->update(['status' => 'rejeitado']);

        return back()->with('status', 'Avaliação rejeitada.');
    }
}
