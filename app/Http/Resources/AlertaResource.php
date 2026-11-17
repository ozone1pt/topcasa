<?php

namespace App\Http\Resources;

use App\Models\Alerta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Alerta */
class AlertaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'mensagem' => $this->mensagem,
            'tipo' => $this->tipo,
            'imagem_url' => $this->imagem_url,
            'lida' => $this->lida,
            'lida_em' => $this->lida_em?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
