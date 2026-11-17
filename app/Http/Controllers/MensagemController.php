<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMensagemRequest;
use App\Models\Anuncio;
use App\Models\Mensagem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MensagemController extends Controller
{
    /**
     * Inbox: root messages (no parent) where the user is sender or recipient,
     * excluding threads they've soft-deleted on their side.
     */
    public function index(): View
    {
        $userId = Auth::id();

        $mensagens = Mensagem::whereNull('parent_id')
            ->where(function ($q) use ($userId) {
                $q->where(fn ($q) => $q->where('destinatario_id', $userId)->whereNull('destinatario_deleted_at'))
                    ->orWhere(fn ($q) => $q->where('remetente_id', $userId)->whereNull('remetente_deleted_at'));
            })
            ->with(['anuncio', 'remetente', 'destinatario', 'respostas'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('mensagens.index', ['mensagens' => $mensagens]);
    }

    public function store(StoreMensagemRequest $request, Anuncio $anuncio): RedirectResponse
    {
        Mensagem::create([
            ...$request->validated(),
            'anuncio_id' => $anuncio->id,
            'remetente_id' => Auth::id(),
            'destinatario_id' => $anuncio->user_id,
        ]);

        $anuncio->increment('message_count');
        \App\Models\ContactoEvento::registar('site', $anuncio);

        return back()->with('status', 'Mensagem enviada.');
    }

    public function show(Mensagem $mensagem): View
    {
        $this->authorizeParticipant($mensagem);

        if ($mensagem->destinatario_id === Auth::id()) {
            $mensagem->update(['lida' => true]);
        }

        $mensagem->load(['anuncio', 'remetente', 'respostas.remetente']);

        return view('mensagens.show', ['mensagem' => $mensagem]);
    }

    public function reply(Request $request, Mensagem $mensagem): RedirectResponse
    {
        $this->authorizeParticipant($mensagem);

        $request->validate(['mensagem' => ['required', 'string', 'max:2000']]);

        $destinatarioId = Auth::id() === $mensagem->destinatario_id
            ? ($mensagem->remetente_id ?? $mensagem->destinatario_id)
            : $mensagem->destinatario_id;

        Mensagem::create([
            'anuncio_id' => $mensagem->anuncio_id,
            'remetente_id' => Auth::id(),
            'destinatario_id' => $destinatarioId,
            'mensagem' => $request->string('mensagem'),
            'parent_id' => $mensagem->parent_id ?? $mensagem->id,
        ]);

        return redirect()->route('mensagens.show', $mensagem->parent_id ?? $mensagem)->with('status', 'Resposta enviada.');
    }

    public function destroy(Mensagem $mensagem): RedirectResponse
    {
        $this->authorizeParticipant($mensagem);

        if (Auth::id() === $mensagem->destinatario_id) {
            $mensagem->update(['destinatario_deleted_at' => now()]);
        } else {
            $mensagem->update(['remetente_deleted_at' => now()]);
        }

        return redirect()->route('mensagens.index')->with('status', 'Conversa removida.');
    }

    private function authorizeParticipant(Mensagem $mensagem): void
    {
        abort_unless(in_array(Auth::id(), [$mensagem->remetente_id, $mensagem->destinatario_id], true), 403);
    }
}
