<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrcamentoRequest;
use App\Models\DesbloqueioOrcamento;
use App\Models\Distrito;
use App\Models\PedidoOrcamento;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrcamentoController extends Controller
{
    public function __construct(private readonly ImageUploadService $images)
    {
    }

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
        $existeRecente = PedidoOrcamento::where('telefone', $request->string('telefone'))
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($existeRecente) {
            return back()->withErrors(['telefone' => 'Já existe um pedido recente com este telefone. Tenta novamente mais tarde.'])->withInput();
        }

        $data = $request->validated();

        $pedido = PedidoOrcamento::create([
            ...collect($data)->except(['foto', 'termo_aceite'])->all(),
            'user_id' => Auth::id(),
            'termo_aceite' => true,
            // Fica pendente até ser aprovado no CRM (igual aos anúncios).
            'status' => 'pendente',
            'foto' => $request->hasFile('foto') ? $this->images->store($request->file('foto'), 'orcamentos') : null,
        ]);

        // No emails here: the "publicado" confirmation and the professionals'
        // heads-up only go out once the pedido is approved in the CRM
        // (Admin\OrcamentoController::aprovar -> PedidoAprovadoNotifier).

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
        // Pending/rejected pedidos are not public - never hand out their contacts.
        abort_unless($pedido->isAprovado(), 404);

        $pedido->increment('contacto_clicks');
        \App\Models\ContactoEvento::registar('telefone', pedido: $pedido);

        if (Auth::check()) {
            DesbloqueioOrcamento::firstOrCreate([
                'pedido_id' => $pedido->id,
                'user_id' => Auth::id(),
            ], ['created_at' => now()]);
        }

        return response()->json([
            'nome' => $pedido->nome,
            'telefone' => $pedido->telefone,
            'email' => $pedido->email,
        ]);
    }

    public function concluir(PedidoOrcamento $pedido): RedirectResponse
    {
        abort_unless(Auth::id() === $pedido->user_id || Auth::user()?->isAdmin(), 403);
        // Only an approved, still-open pedido can be closed.
        abort_unless($pedido->status === 'validado', 404);

        $pedido->update(['status' => 'concluido']);

        return back()->with('status', 'Pedido marcado como concluído.');
    }
}
