<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Services\GestaoAvaliacoes;
use App\Services\ResolvedorAnuncio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly GestaoAvaliacoes $avaliacoes,
        private readonly ResolvedorAnuncio $anuncios,
    ) {}

    public function store(StoreReviewRequest $request, string $anuncio): JsonResponse
    {
        $model = $this->anuncios->porChave($anuncio);
        $review = $this->avaliacoes->criar($request->user(), $model, $request->validated());
        $review->load('user');

        return (new ReviewResource($review))
            ->additional(['message' => 'Avaliação submetida, fica pendente de aprovação.'])
            ->response()
            ->setStatusCode(201);
    }

    public function aprovar(Request $request, Review $review): ReviewResource
    {
        $this->authorize('moderate', $review);
        $this->avaliacoes->aprovar($review);

        return new ReviewResource($review->load('user'));
    }

    public function rejeitar(Request $request, Review $review): ReviewResource
    {
        $this->authorize('moderate', $review);
        $this->avaliacoes->rejeitar($review);

        return new ReviewResource($review->load('user'));
    }
}
