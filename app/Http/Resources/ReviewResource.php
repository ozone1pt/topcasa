<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Review */
class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'anuncio_id' => $this->anuncio_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'autor' => $this->relationLoaded('user') ? $this->user?->nomeCompleto() : null,
            'anuncio_titulo' => $this->relationLoaded('anuncio') ? $this->anuncio?->titulo : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
