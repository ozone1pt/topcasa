<?php

namespace App\Http\Resources;

use App\Models\Concelho;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Concelho */
class ConcelhoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distrito_id' => $this->distrito_id,
            'nome' => $this->nome,
        ];
    }
}
