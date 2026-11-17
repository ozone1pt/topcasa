<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Anuncio;
use App\Models\Review;
use App\Services\GestaoAvaliacoes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(private readonly GestaoAvaliacoes $avaliacoes) {}

    public function store(StoreReviewRequest $request, Anuncio $anuncio): RedirectResponse
    {
        $this->avaliacoes->criar(Auth::user(), $anuncio, $request->validated());

        return back()->with('status', 'Avaliação submetida, fica pendente de aprovação.');
    }

    public function aprovar(Review $review): RedirectResponse
    {
        $this->authorize('moderate', $review);

        $this->avaliacoes->aprovar($review);

        return back()->with('status', 'Avaliação aprovada.');
    }

    public function rejeitar(Review $review): RedirectResponse
    {
        $this->authorize('moderate', $review);

        $this->avaliacoes->rejeitar($review);

        return back()->with('status', 'Avaliação rejeitada.');
    }
}
