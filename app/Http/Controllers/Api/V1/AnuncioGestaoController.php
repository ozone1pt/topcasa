<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnuncioRequest;
use App\Http\Requests\UpdateAnuncioRequest;
use App\Http\Resources\AnuncioResource;
use App\Services\PublicacaoAnuncio;
use App\Services\ResolvedorAnuncio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnuncioGestaoController extends Controller
{
    public function __construct(
        private readonly PublicacaoAnuncio $publicacao,
        private readonly ResolvedorAnuncio $anuncios,
    ) {}

    public function meus(Request $request): AnonymousResourceCollection
    {
        $anuncios = $request->user()->anuncios()
            ->with(['categoria', 'distrito', 'concelho', 'galeria', 'user'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return AnuncioResource::collection($anuncios);
    }

    public function store(StoreAnuncioRequest $request): JsonResponse
    {
        $anuncio = $this->publicacao->criar($request->user(), $request->validated(), $request);

        $mensagens = [
            'pendente' => 'Anúncio submetido com sucesso! Fica pendente de aprovação.',
            'aprovado' => 'Anúncio submetido e aprovado automaticamente.',
            'rejeitado' => 'O anúncio foi rejeitado automaticamente por conter conteúdo não permitido.',
        ];

        return (new AnuncioResource($anuncio))
            ->additional(['message' => $mensagens[$anuncio->status] ?? $mensagens['pendente']])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAnuncioRequest $request, string $anuncio): AnuncioResource
    {
        $model = $this->anuncios->porChave($anuncio);
        $this->authorize('update', $model);

        return new AnuncioResource($this->publicacao->atualizar($model, $request->validated(), $request));
    }

    public function destroy(Request $request, string $anuncio): JsonResponse
    {
        $model = $this->anuncios->porChave($anuncio);
        $this->authorize('delete', $model);
        $this->publicacao->apagar($model);

        return response()->json(['message' => 'Anúncio removido.']);
    }

    public function reativar(Request $request, string $anuncio): AnuncioResource
    {
        $model = $this->anuncios->porChave($anuncio);
        $this->authorize('update', $model);
        $this->publicacao->reativar($model);

        return new AnuncioResource($model->fresh(['categoria', 'distrito', 'concelho', 'galeria', 'user']));
    }
}
