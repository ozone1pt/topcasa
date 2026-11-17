<?php

namespace App\Services;

use App\Models\ContactoEvento;
use App\Models\DesbloqueioOrcamento;
use App\Models\PedidoOrcamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicacaoOrcamento
{
    public function __construct(private readonly ImageUploadService $images) {}

    /**
     * Same rules as the public form: one pedido per phone per day, starts
     * pending, and does not email anyone. Approval mail stays in the CRM.
     */
    public function criar(Request $request, ?int $userId): PedidoOrcamento
    {
        $existeRecente = PedidoOrcamento::where('telefone', $request->string('telefone'))
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($existeRecente) {
            throw ValidationException::withMessages([
                'telefone' => 'Já existe um pedido recente com este telefone. Tenta novamente mais tarde.',
            ]);
        }

        $data = $request->validated();

        return PedidoOrcamento::create([
            ...collect($data)->except(['foto', 'termo_aceite'])->all(),
            'user_id' => $userId,
            'termo_aceite' => true,
            'status' => 'pendente',
            'foto' => $request->hasFile('foto') ? $this->images->store($request->file('foto'), 'orcamentos') : null,
        ]);
    }

    /**
     * @return array{nome: ?string, telefone: string, email: ?string}
     */
    public function desbloquear(PedidoOrcamento $pedido, ?int $userId): array
    {
        abort_unless($pedido->isAprovado(), 404);

        $pedido->increment('contacto_clicks');
        ContactoEvento::registar('telefone', pedido: $pedido);

        if ($userId) {
            DesbloqueioOrcamento::firstOrCreate([
                'pedido_id' => $pedido->id,
                'user_id' => $userId,
            ], ['created_at' => now()]);
        }

        return [
            'nome' => $pedido->nome,
            'telefone' => $pedido->telefone,
            'email' => $pedido->email,
        ];
    }

    public function concluir(PedidoOrcamento $pedido, User $user): void
    {
        abort_unless($user->id === $pedido->user_id || $user->isAdmin(), 403);
        abort_unless($pedido->status === 'validado', 404);

        $pedido->update(['status' => 'concluido']);
    }
}
