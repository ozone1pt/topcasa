<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\ContactoEvento;
use App\Models\Mensagem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CaixaMensagens
{
    public function caixa(User $user): LengthAwarePaginator
    {
        $userId = $user->id;

        return Mensagem::query()
            ->whereNull('parent_id')
            ->where(function ($q) use ($userId) {
                $q->where(fn ($q) => $q->where('destinatario_id', $userId)->whereNull('destinatario_deleted_at'))
                    ->orWhere(fn ($q) => $q->where('remetente_id', $userId)->whereNull('remetente_deleted_at'));
            })
            ->with(['anuncio', 'remetente', 'destinatario', 'respostas'])
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function naoLidas(User $user): int
    {
        return Mensagem::query()
            ->where('destinatario_id', $user->id)
            ->where('lida', false)
            ->whereNull('destinatario_deleted_at')
            ->count();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function enviar(Anuncio $anuncio, ?User $remetente, array $dados): Mensagem
    {
        $mensagem = Mensagem::create([
            ...$dados,
            'anuncio_id' => $anuncio->id,
            'remetente_id' => $remetente?->id,
            'destinatario_id' => $anuncio->user_id,
        ]);

        $anuncio->increment('message_count');
        ContactoEvento::registar('site', $anuncio);

        return $mensagem;
    }

    public function ver(Mensagem $mensagem, User $user): Mensagem
    {
        $this->participante($mensagem, $user);

        if ($mensagem->destinatario_id === $user->id) {
            $mensagem->update(['lida' => true]);
        }

        return $mensagem->load(['anuncio', 'remetente', 'destinatario', 'respostas.remetente']);
    }

    public function responder(Mensagem $mensagem, User $user, string $texto): Mensagem
    {
        $this->participante($mensagem, $user);

        $destinatarioId = $user->id === $mensagem->destinatario_id
            ? ($mensagem->remetente_id ?? $mensagem->destinatario_id)
            : $mensagem->destinatario_id;

        return Mensagem::create([
            'anuncio_id' => $mensagem->anuncio_id,
            'remetente_id' => $user->id,
            'destinatario_id' => $destinatarioId,
            'mensagem' => $texto,
            'parent_id' => $mensagem->parent_id ?? $mensagem->id,
        ]);
    }

    public function apagar(Mensagem $mensagem, User $user): void
    {
        $this->participante($mensagem, $user);

        // The owner can message their own ad (same as the website). Then they
        // are both sides of the row, and clearing only one timestamp leaves
        // the thread visible through the other side of the inbox query.
        $agora = now();

        if ($user->id === $mensagem->destinatario_id) {
            $mensagem->destinatario_deleted_at = $agora;
        }

        if ($user->id === $mensagem->remetente_id) {
            $mensagem->remetente_deleted_at = $agora;
        }

        $mensagem->save();
    }

    public function participante(Mensagem $mensagem, User $user): void
    {
        abort_unless(in_array($user->id, [$mensagem->remetente_id, $mensagem->destinatario_id], true), 403);
    }
}
