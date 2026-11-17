<?php

namespace App\Http\Resources;

use App\Models\PedidoRecrutamento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PedidoRecrutamento */
class PedidoRecrutamentoResource extends JsonResource
{
    /**
     * The public recrutamento page already shows phone and email.
     * Status and the rejection reason are only for the owner or an admin.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dono = $request->user()
            && ($request->user()->id === $this->user_id || $request->user()->isAdmin());

        $dados = [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'empresa_nome' => $this->empresa_nome,
            'descricao' => $this->descricao,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'distrito' => $this->distrito ? [
                'id' => $this->distrito->id,
                'nome' => $this->distrito->nome,
            ] : null,
            'distrito_id' => $this->distrito_id,
            'foto_url' => AnuncioResource::urlAbsoluta($this->foto),
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        if ($dono) {
            $dados['status'] = $this->status;
            $dados['motivo_rejeicao'] = $this->motivo_rejeicao;
        }

        return $dados;
    }
}
