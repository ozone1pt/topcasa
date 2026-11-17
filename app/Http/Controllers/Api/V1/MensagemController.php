<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMensagemRequest;
use App\Http\Resources\MensagemResource;
use App\Models\Mensagem;
use App\Services\CaixaMensagens;
use App\Services\ResolvedorAnuncio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MensagemController extends Controller
{
    public function __construct(
        private readonly CaixaMensagens $caixa,
        private readonly ResolvedorAnuncio $anuncios,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return MensagemResource::collection($this->caixa->caixa($request->user()))
            ->additional(['nao_lidas' => $this->caixa->naoLidas($request->user())]);
    }

    public function store(StoreMensagemRequest $request, string $anuncio): JsonResponse
    {
        $model = $this->anuncios->porChave($anuncio);
        $this->authorize('view', $model);

        $mensagem = $this->caixa->enviar($model, $request->user(), $request->validated());

        return (new MensagemResource($mensagem))
            ->additional(['message' => 'Mensagem enviada.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Mensagem $mensagem): MensagemResource
    {
        return new MensagemResource($this->caixa->ver($mensagem, $request->user()));
    }

    public function reply(Request $request, Mensagem $mensagem): JsonResponse
    {
        $data = $request->validate(['mensagem' => ['required', 'string', 'max:2000']]);

        $resposta = $this->caixa->responder($mensagem, $request->user(), $data['mensagem']);

        return (new MensagemResource($resposta))
            ->additional(['message' => 'Resposta enviada.'])
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, Mensagem $mensagem): JsonResponse
    {
        $this->caixa->apagar($mensagem, $request->user());

        return response()->json(['message' => 'Conversa removida.']);
    }
}
