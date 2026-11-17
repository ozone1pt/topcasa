<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ResolvedorAnuncio;
use App\Services\RevelacaoContacto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    public function __construct(
        private readonly RevelacaoContacto $revelacao,
        private readonly ResolvedorAnuncio $anuncios,
    ) {}

    public function telefone(Request $request, string $anuncio): JsonResponse
    {
        $model = $this->anuncios->porChave($anuncio);
        $this->authorize('view', $model);

        return response()->json([
            'contacto' => $this->revelacao->telefone($model, (string) $request->ip()),
        ]);
    }

    public function whatsapp(string $anuncio): JsonResponse
    {
        $model = $this->anuncios->porChave($anuncio);
        $this->authorize('view', $model);

        return response()->json([
            'url' => $this->revelacao->whatsapp($model),
        ]);
    }
}
