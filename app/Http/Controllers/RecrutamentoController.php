<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecrutamentoRequest;
use App\Models\Distrito;
use App\Models\PedidoRecrutamento;
use App\Services\PublicacaoRecrutamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecrutamentoController extends Controller
{
    public function __construct(private readonly PublicacaoRecrutamento $pedidos) {}

    public function index(): View
    {
        $pedidos = PedidoRecrutamento::ativos()
            ->with(['distrito', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('recrutamentos.index', ['pedidos' => $pedidos]);
    }

    public function create(): View
    {
        return view('recrutamentos.create', ['distritos' => Distrito::orderBy('nome')->get()]);
    }

    public function store(StoreRecrutamentoRequest $request): RedirectResponse
    {
        $this->pedidos->criar($request, Auth::id());

        return redirect()->route('recrutamentos.index')->with('status', 'Vaga submetida com sucesso! Fica pendente de aprovação.');
    }

    public function pausar(PedidoRecrutamento $pedido): RedirectResponse
    {
        $this->pedidos->alternarPausa($pedido, Auth::user());

        return back()->with('status', 'Estado atualizado.');
    }

    public function destroy(PedidoRecrutamento $pedido): RedirectResponse
    {
        $this->pedidos->apagar($pedido, Auth::user());

        return redirect()->route('recrutamentos.index')->with('status', 'Vaga removida.');
    }
}
