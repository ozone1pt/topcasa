<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecrutamentoRequest;
use App\Models\Distrito;
use App\Models\PedidoRecrutamento;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RecrutamentoController extends Controller
{
    public function __construct(private readonly ImageUploadService $images)
    {
    }

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
        $data = $request->validated();

        $pedido = PedidoRecrutamento::create([
            ...collect($data)->except(['foto', 'termo_aceite'])->all(),
            'user_id' => Auth::id(),
            'termo_aceite' => true,
            // Fica pendente até ser aprovada no CRM (igual aos anúncios).
            'status' => 'pendente',
            'foto' => $request->hasFile('foto') ? $this->images->store($request->file('foto'), 'recrutamento') : null,
        ]);

        // No emails here: the "publicada" confirmation and the professionals'
        // heads-up only go out once the vaga is approved in the CRM
        // (Admin\RecrutamentoController::aprovar -> PedidoAprovadoNotifier).

        return redirect()->route('recrutamentos.index')->with('status', 'Vaga submetida com sucesso! Fica pendente de aprovação.');
    }

    public function pausar(PedidoRecrutamento $pedido): RedirectResponse
    {
        $this->authorizeOwner($pedido);
        // Pausar/reativar is only for approved vagas - a pending or rejected
        // one must never be switched to "ativo" without going through the CRM.
        abort_unless(in_array($pedido->status, ['ativo', 'pausado'], true), 404);

        $pedido->update(['status' => $pedido->status === 'ativo' ? 'pausado' : 'ativo']);

        return back()->with('status', 'Estado atualizado.');
    }

    public function destroy(PedidoRecrutamento $pedido): RedirectResponse
    {
        $this->authorizeOwner($pedido);

        if ($pedido->foto) {
            $this->images->delete($pedido->foto);
        }

        $pedido->delete();

        return redirect()->route('recrutamentos.index')->with('status', 'Vaga removida.');
    }

    private function authorizeOwner(PedidoRecrutamento $pedido): void
    {
        abort_unless(Auth::id() === $pedido->user_id || Auth::user()?->isAdmin(), 403);
    }
}
