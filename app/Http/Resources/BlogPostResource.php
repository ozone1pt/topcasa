<?php

namespace App\Http\Resources;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BlogPost */
class BlogPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'titulo' => $this->titulo,
            'conteudo' => $this->conteudo,
            'imagem_url' => AnuncioResource::urlAbsoluta($this->imagem_destaque),
            'publicado_em' => $this->publicado_em?->toIso8601String(),
            'views' => $this->views,
        ];
    }
}
