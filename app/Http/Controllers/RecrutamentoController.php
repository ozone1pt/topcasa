<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecrutamentoRequest;
use App\Jobs\NotificarNovoRecrutamento;
use App\Models\Distrito;
use App\Models\PedidoRecrutamento;
use App\Services\ImageUploadService;
use App\Services\PedidoPublicadoNotifier;
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
            'status' => 'ativo',
            'foto' => $request->hasFile('foto') ? $this->images->store($request->file('foto'), 'recrutamento') : null,
        ]);

        // Emails go out right after the response is sent (nothing is queued -
        // this host has no queue worker, so a queued job would sit unprocessed
        // forever): first the confirmation to whoever published the vaga, then
        // the heads-up to the professionals.
        app()->terminating(fn () => app(PedidoPublicadoNotifier::class)->notificar('recrutamento', $pedido->id, $pedido->email, $pedido->titulo));
        NotificarNovoRecrutamento::dispatchAfterResponse($pedido);

        return redirect()->route('recrutamentos.index')->with('status', 'Vaga publicada.');
    }

    public function pausar(PedidoRecrutamento $pedido): RedirectResponse
    {
        $this->authorizeOwner($pedido);

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
