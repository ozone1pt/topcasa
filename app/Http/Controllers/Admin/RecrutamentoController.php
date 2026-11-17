<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PedidoRecrutamento;
use App\Services\PedidoAprovadoNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecrutamentoController extends Controller
{
    public function __construct(private readonly PedidoAprovadoNotifier $aprovadoNotifier)
    {
    }

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

    /**
     * The estado can only be changed once the vaga has been approved
     * (ativo / pausado / cancelado): approval itself only happens through
     * aprovar(), so the approval emails can never be skipped.
     */
    public function update(Request $request, PedidoRecrutamento $pedido): RedirectResponse
    {
        if (! $pedido->isAprovado()) {
            $pedido->update($request->validate([
                'notas_admin' => ['nullable', 'string', 'max:2000'],
            ]));

            return back()->with('status', 'Notas guardadas. Para publicar a vaga usa "Aprovar".');
        }

        $data = $request->validate([
            'status' => ['required', 'in:ativo,pausado,cancelado'],
            'notas_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        $pedido->update($data);

        return back()->with('status', 'Vaga atualizada.');
    }

    /**
     * Same flow as AnuncioModerationController::aprovar(): publishes the
     * vaga and, the first time only, sends the "publicada" email to whoever
     * submitted it and the heads-up to the professionals.
     */
    public function aprovar(PedidoRecrutamento $pedido): RedirectResponse
    {
        // Atomic transition so a double click can't approve (and email) twice.
        $aprovado = PedidoRecrutamento::whereKey($pedido->id)
            ->whereIn('status', ['pendente', 'rejeitado'])
            ->update(['status' => 'ativo', 'motivo_rejeicao' => null, 'updated_at' => now()]);

        if (! $aprovado) {
            return back()->with('status', 'Esta vaga já estava aprovada.');
        }

        $this->aprovadoNotifier->notificar('recrutamento', $pedido->refresh());

        return back()->with('status', 'Vaga aprovada e publicada.');
    }

    public function rejeitar(Request $request, PedidoRecrutamento $pedido): RedirectResponse
    {
        $request->validate(['motivo_rejeicao' => ['nullable', 'string', 'max:500']]);

        $pedido->update([
            'status' => 'rejeitado',
            'motivo_rejeicao' => $request->string('motivo_rejeicao')->trim()->toString() ?: null,
        ]);

        return back()->with('status', 'Vaga rejeitada.');
    }

    public function destroy(PedidoRecrutamento $pedido): RedirectResponse
    {
        $pedido->delete();

        return back()->with('status', 'Vaga removida.');
    }
}
