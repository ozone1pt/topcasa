<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrcamentoRequest;
use App\Http\Resources\PedidoOrcamentoResource;
use App\Models\PedidoOrcamento;
use App\Services\PublicacaoOrcamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrcamentoController extends Controller
{
    public function __construct(private readonly PublicacaoOrcamento $pedidos) {}

    public function index(): AnonymousResourceCollection
    {
        $pedidos = PedidoOrcamento::publicados()
            ->with('distrito')
            ->orderByDesc('created_at')
            ->paginate(15);

        return PedidoOrcamentoResource::collection($pedidos);
    }

    public function meus(Request $request): AnonymousResourceCollection
    {
        $pedidos = PedidoOrcamento::query()
            ->where('user_id', $request->user()->id)
            ->with('distrito')
            ->orderByDesc('created_at')
            ->paginate(15);

        return PedidoOrcamentoResource::collection($pedidos);
    }

    public function store(StoreOrcamentoRequest $request): JsonResponse
    {
        $pedido = $this->pedidos->criar($request, $request->user()->id);
        $pedido->load('distrito');

        return (new PedidoOrcamentoResource($pedido))
            ->additional(['message' => 'Pedido de orçamento submetido com sucesso! Fica pendente de aprovação.'])
            ->response()
            ->setStatusCode(201);
    }

    public function desbloquear(Request $request, PedidoOrcamento $orcamento): JsonResponse
    {
        return response()->json($this->pedidos->desbloquear($orcamento, $request->user()?->id));
    }

    public function concluir(Request $request, PedidoOrcamento $orcamento): PedidoOrcamentoResource
    {
        $this->pedidos->concluir($orcamento, $request->user());
        $orcamento->load('distrito');

        return new PedidoOrcamentoResource($orcamento);
    }
}
