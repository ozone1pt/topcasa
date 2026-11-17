<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoRecrutamento;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RecrutamentoController extends Controller
{
    public function index(Request $request): View
    {
        $pedidos = PedidoRecrutamento::with(['user', 'distrito'])
            ->when($request->filled('busca'), function ($q) use ($request) {
                $termo = '%'.$request->string('busca').'%';
                $q->where(fn ($q) => $q->where('titulo', 'like', $termo)->orWhere('empresa_nome', 'like', $termo));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.recrutamentos.index', ['pedidos' => $pedidos]);
    }

    public function update(Request $request, PedidoRecrutamento $pedido): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:ativo,pausado,cancelado'],
            'notas_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $pedido->update($data);

        return back()->with('status', 'Vaga atualizada.');
    }

    public function destroy(PedidoRecrutamento $pedido): RedirectResponse
    {
        $pedido->delete();

        return back()->with('status', 'Vaga removida.');
    }
}
