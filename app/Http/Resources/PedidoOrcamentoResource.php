<?php

namespace App\Http\Resources;

use App\Models\PedidoOrcamento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PedidoOrcamento */
class PedidoOrcamentoResource extends JsonResource
{
    /**
     * Contact stays hidden until "Ver contacto", same as the public page.
     * The owner (and an admin) also see status and the rejection reason.
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
            'descricao' => $this->descricao,
            'distrito' => $this->distrito ? [
                'id' => $this->distrito->id,
                'nome' => $this->distrito->nome,
            ] : null,
            'distrito_id' => $this->distrito_id,
            'foto_url' => AnuncioResource::urlAbsoluta($this->foto),
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        if ($dono) {
            $dados['nome'] = $this->nome;
            $dados['telefone'] = $this->telefone;
            $dados['email'] = $this->email;
            $dados['status'] = $this->status;
            $dados['motivo_rejeicao'] = $this->motivo_rejeicao;
        }

        return $dados;
    }
}
