<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'nome' => $this->nome,
            'apelido' => $this->apelido,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'nif' => $this->nif,
            'tipo_conta' => $this->tipo_conta,
            'empresa_nome' => $this->empresa_nome,
            'user_type' => $this->user_type,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
        ];
    }
}
