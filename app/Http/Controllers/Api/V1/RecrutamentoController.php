<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecrutamentoRequest;
use App\Http\Resources\PedidoRecrutamentoResource;
use App\Models\PedidoRecrutamento;
use App\Services\PublicacaoRecrutamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecrutamentoController extends Controller
{
    public function __construct(private readonly PublicacaoRecrutamento $pedidos) {}

    public function index(): AnonymousResourceCollection
    {
        $pedidos = PedidoRecrutamento::ativos()
            ->with(['distrito', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return PedidoRecrutamentoResource::collection($pedidos);
    }

    public function meus(Request $request): AnonymousResourceCollection
    {
        $pedidos = PedidoRecrutamento::query()
            ->where('user_id', $request->user()->id)
            ->with('distrito')
            ->orderByDesc('created_at')
            ->paginate(15);

        return PedidoRecrutamentoResource::collection($pedidos);
    }

    public function store(StoreRecrutamentoRequest $request): JsonResponse
    {
        $pedido = $this->pedidos->criar($request, $request->user()->id);
        $pedido->load('distrito');

        return (new PedidoRecrutamentoResource($pedido))
            ->additional(['message' => 'Recrutamento submetido com sucesso! Fica pendente de aprovação.'])
            ->response()
            ->setStatusCode(201);
    }

    public function pausar(Request $request, PedidoRecrutamento $recrutamento): PedidoRecrutamentoResource
    {
        $pedido = $this->pedidos->alternarPausa($recrutamento, $request->user());
        $pedido->load('distrito');

        return new PedidoRecrutamentoResource($pedido);
    }

    public function destroy(Request $request, PedidoRecrutamento $recrutamento): JsonResponse
    {
        $this->pedidos->apagar($recrutamento, $request->user());

        return response()->json(['message' => 'Recrutamento removido.']);
    }
}
