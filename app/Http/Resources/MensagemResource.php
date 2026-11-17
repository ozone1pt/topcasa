<?php

namespace App\Http\Resources;

use App\Models\Mensagem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Mensagem */
class MensagemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'anuncio_id' => $this->anuncio_id,
            'anuncio_titulo' => $this->relationLoaded('anuncio') ? $this->anuncio?->titulo : null,
            'remetente_id' => $this->remetente_id,
            'destinatario_id' => $this->destinatario_id,
            'remetente_nome' => $this->remetenteNome(),
            'mensagem' => $this->mensagem,
            'lida' => $this->lida,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'respostas' => $this->whenLoaded(
                'respostas',
                fn () => MensagemResource::collection($this->respostas)->resolve(),
            ),
        ];
    }
}
