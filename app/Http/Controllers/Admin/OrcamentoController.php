<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoOrcamento;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrcamentoController extends Controller
{
    public function index(Request $request): View
    {
        $pedidos = PedidoOrcamento::with(['user', 'distrito'])
            ->when($request->filled('busca'), function ($q) use ($request) {
                $termo = '%'.$request->string('busca').'%';
                $q->where(fn ($q) => $q->where('nome', 'like', $termo)->orWhere('titulo', 'like', $termo)->orWhere('telefone', 'like', $termo));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orcamentos.index', ['pedidos' => $pedidos]);
    }

    /**
     * Internal-only fields (status, notas_admin) - the data the user
     * originally submitted through the public form is never touched here.
     */
    public function update(Request $request, PedidoOrcamento $pedido): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:validado,concluido'],
            'notas_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $pedido->update($data);

        return back()->with('status', 'Pedido atualizado.');
    }

    public function destroy(PedidoOrcamento $pedido): RedirectResponse
    {
        $pedido->delete();

        return back()->with('status', 'Pedido removido.');
    }
}
