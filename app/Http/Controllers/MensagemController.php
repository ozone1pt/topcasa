<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMensagemRequest;
use App\Models\Anuncio;
use App\Models\Mensagem;
use App\Services\CaixaMensagens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MensagemController extends Controller
{
    public function __construct(private readonly CaixaMensagens $caixa) {}

    /**
     * Inbox: root messages (no parent) where the user is sender or recipient,
     * excluding threads they've soft-deleted on their side.
     */
    public function index(): View
    {
        return view('mensagens.index', [
            'mensagens' => $this->caixa->caixa(Auth::user()),
        ]);
    }

    public function store(StoreMensagemRequest $request, Anuncio $anuncio): RedirectResponse
    {
        $this->caixa->enviar($anuncio, Auth::user(), $request->validated());

        return back()->with('status', 'Mensagem enviada.');
    }

    public function show(Mensagem $mensagem): View
    {
        return view('mensagens.show', [
            'mensagem' => $this->caixa->ver($mensagem, Auth::user()),
        ]);
    }

    public function reply(Request $request, Mensagem $mensagem): RedirectResponse
    {
        $request->validate(['mensagem' => ['required', 'string', 'max:2000']]);

        $this->caixa->responder($mensagem, Auth::user(), $request->string('mensagem')->toString());

        return redirect()->route('mensagens.show', $mensagem->parent_id ?? $mensagem)->with('status', 'Resposta enviada.');
    }

    public function destroy(Mensagem $mensagem): RedirectResponse
    {
        $this->caixa->apagar($mensagem, Auth::user());

        return redirect()->route('mensagens.index')->with('status', 'Conversa removida.');
    }
}
