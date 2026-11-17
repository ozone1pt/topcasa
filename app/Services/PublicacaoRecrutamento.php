<?php

namespace App\Services;

use App\Models\PedidoRecrutamento;
use App\Models\User;
use Illuminate\Http\Request;

class PublicacaoRecrutamento
{
    public function __construct(private readonly ImageUploadService $images) {}

    /**
     * New vagas stay pending. No mail is sent here; the CRM approval
     * notifier is the only place those emails go out.
     */
    public function criar(Request $request, ?int $userId): PedidoRecrutamento
    {
        $data = $request->validated();

        return PedidoRecrutamento::create([
            ...collect($data)->except(['foto', 'termo_aceite'])->all(),
            'user_id' => $userId,
            'termo_aceite' => true,
            'status' => 'pendente',
            'foto' => $request->hasFile('foto') ? $this->images->store($request->file('foto'), 'recrutamento') : null,
        ]);
    }

    public function alternarPausa(PedidoRecrutamento $pedido, User $user): PedidoRecrutamento
    {
        $this->dono($pedido, $user);
        abort_unless(in_array($pedido->status, ['ativo', 'pausado'], true), 404);

        $pedido->update(['status' => $pedido->status === 'ativo' ? 'pausado' : 'ativo']);

        return $pedido;
    }

    public function apagar(PedidoRecrutamento $pedido, User $user): void
    {
        $this->dono($pedido, $user);

        if ($pedido->foto) {
            $this->images->delete($pedido->foto);
        }

        $pedido->delete();
    }

    public function dono(PedidoRecrutamento $pedido, User $user): void
    {
        abort_unless($user->id === $pedido->user_id || $user->isAdmin(), 403);
    }
}
