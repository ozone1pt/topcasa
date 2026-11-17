<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrcamentoRequest;
use App\Models\Distrito;
use App\Models\PedidoOrcamento;
use App\Services\PublicacaoOrcamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrcamentoController extends Controller
{
    public function __construct(private readonly PublicacaoOrcamento $pedidos) {}

    public function index(): View
    {
        $pedidos = PedidoOrcamento::publicados()
            ->with('distrito')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('orcamentos.index', ['pedidos' => $pedidos]);
    }

    public function create(): View
    {
        return view('orcamentos.create', ['distritos' => Distrito::orderBy('nome')->get()]);
    }

    public function store(StoreOrcamentoRequest $request): RedirectResponse
    {
        $this->pedidos->criar($request, Auth::id());

        return redirect()->route('orcamentos.index')->with('status', 'Pedido de orçamento submetido com sucesso! Fica pendente de aprovação.');
    }

    /**
     * Public, no login required - same "ver contacto" pattern as an
     * anúncio. Logs the click on contacto_clicks (visible in back-office)
     * and hands back the contact fields to reveal inline. Keeps the old
     * per-user DesbloqueioOrcamento row too when logged in, since it's
     * still used to remember an unlock across page loads for that user.
     */
    public function desbloquear(PedidoOrcamento $pedido): JsonResponse
    {
        return response()->json($this->pedidos->desbloquear($pedido, Auth::id()));
    }

    public function concluir(PedidoOrcamento $pedido): RedirectResponse
    {
        $this->pedidos->concluir($pedido, Auth::user());

        return back()->with('status', 'Pedido marcado como concluído.');
    }
}
