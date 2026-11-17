<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoOrcamento;
use App\Services\PedidoAprovadoNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrcamentoController extends Controller
{
    public function __construct(private readonly PedidoAprovadoNotifier $aprovadoNotifier)
    {
    }

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
     * The estado can only be changed once the pedido has been approved
     * (validado <-> concluido): approval itself only happens through
     * aprovar(), so the approval emails can never be skipped.
     */
    public function update(Request $request, PedidoOrcamento $pedido): RedirectResponse
    {
        if (! $pedido->isAprovado()) {
            $pedido->update($request->validate([
                'notas_admin' => ['nullable', 'string', 'max:2000'],
            ]));

            return back()->with('status', 'Notas guardadas. Para publicar o pedido usa "Aprovar".');
        }

        $data = $request->validate([
            'status' => ['required', 'in:validado,concluido'],
            'notas_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $pedido->update($data);

        return back()->with('status', 'Pedido atualizado.');
    }

    /**
     * Same flow as AnuncioModerationController::aprovar(): publishes the
     * pedido and, the first time only, sends the "publicado" email to the
     * requester and the heads-up to the professionals.
     */
    public function aprovar(PedidoOrcamento $pedido): RedirectResponse
    {
        // Atomic transition so a double click can't approve (and email) twice.
        $aprovado = PedidoOrcamento::whereKey($pedido->id)
            ->whereIn('status', ['pendente', 'rejeitado'])
            ->update(['status' => 'validado', 'motivo_rejeicao' => null, 'updated_at' => now()]);

        if (! $aprovado) {
            return back()->with('status', 'Este pedido já estava aprovado.');
        }

        $this->aprovadoNotifier->notificar('orcamento', $pedido->refresh());

        return back()->with('status', 'Pedido aprovado e publicado.');
    }

    public function rejeitar(Request $request, PedidoOrcamento $pedido): RedirectResponse
    {
        $request->validate(['motivo_rejeicao' => ['nullable', 'string', 'max:500']]);

        $pedido->update([
            'status' => 'rejeitado',
            'motivo_rejeicao' => $request->string('motivo_rejeicao')->trim()->toString() ?: null,
        ]);

        return back()->with('status', 'Pedido rejeitado.');
    }

    public function destroy(PedidoOrcamento $pedido): RedirectResponse
    {
        $pedido->delete();

        return back()->with('status', 'Pedido removido.');
    }
}
