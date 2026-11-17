<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnuncioResource;
use App\Services\GestaoFavoritos;
use App\Services\ResolvedorAnuncio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoritoController extends Controller
{
    public function __construct(
        private readonly GestaoFavoritos $favoritos,
        private readonly ResolvedorAnuncio $anuncios,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AnuncioResource::collection($this->favoritos->lista($request->user()));
    }

    public function toggle(Request $request, string $anuncio): JsonResponse
    {
        $model = $this->anuncios->porChave($anuncio);
        $adicionado = $this->favoritos->alternar($request->user(), $model);

        return response()->json([
            'favorito' => $adicionado,
            'message' => $adicionado ? 'Adicionado aos favoritos.' : 'Removido dos favoritos.',
        ]);
    }
}
