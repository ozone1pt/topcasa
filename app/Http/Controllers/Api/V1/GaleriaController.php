<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnuncioResource;
use App\Models\GaleriaAnuncio;
use App\Services\GestaoGaleria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GaleriaController extends Controller
{
    public function __construct(private readonly GestaoGaleria $galeria) {}

    public function destroy(GaleriaAnuncio $imagem): JsonResponse
    {
        $this->authorize('update', $imagem->anuncio);
        $this->galeria->remover($imagem);

        return response()->json(['ok' => true]);
    }

    public function ordenar(Request $request): JsonResponse
    {
        $ids = $this->galeria->idsValidados($request);
        $imagens = GaleriaAnuncio::with('anuncio')->whereIn('id', $ids)->get();

        abort_unless($imagens->count() === count($ids), 422);
        abort_unless($imagens->pluck('anuncio_id')->unique()->count() === 1, 422);
        $this->authorize('update', $imagens->first()->anuncio);

        $this->galeria->ordenar($ids);

        return response()->json(['ok' => true]);
    }

    public function rodar(Request $request, GaleriaAnuncio $imagem): JsonResponse
    {
        $this->authorize('update', $imagem->anuncio);

        $request->validate(['sentido' => ['required', 'in:esquerda,direita']]);

        $this->galeria->rodar($imagem, $request->input('sentido') === 'direita');

        return response()->json([
            'url' => AnuncioResource::urlAbsoluta($imagem->imagem).'?v='.time(),
        ]);
    }
}
