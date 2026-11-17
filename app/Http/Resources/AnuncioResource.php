<?php

namespace App\Http\Resources;

use App\Models\Anuncio;
use App\Models\GaleriaAnuncio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Anuncio */
class AnuncioResource extends JsonResource
{
    /**
     * Public shape of an anúncio. Contact details stay hidden — the website
     * only reveals the phone after "Ver contacto", which is a later phase.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imagens = $this->galeria->map(fn (GaleriaAnuncio $imagem) => [
            'id' => $imagem->id,
            'url' => self::urlAbsoluta($imagem->imagem),
            'descricao' => $imagem->descricao,
            'ordem' => $imagem->ordem,
        ])->values();

        $capa = $imagens->first();
        $dono = $request->user()
            && ($request->user()->id === $this->user_id || $request->user()->isAdmin());

        $dados = [
            'id' => $this->id,
            'slug' => $this->slug,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'nome_profissional' => $this->nomeProfissional(),
            'minha_empresa' => $this->minha_empresa,
            'certificacao_profissional' => $this->certificacao_profissional,
            'seguro_responsabilidade_civil' => $this->seguro_responsabilidade_civil,
            'destacar' => $this->destacar,
            'youtube_url' => $this->youtubeEmbedUrl(),
            'instagram_url' => $this->instagramUrl(),
            'views' => $this->views,
            'categoria' => $this->categoria ? [
                'id' => $this->categoria->id,
                'nome' => $this->categoria->nome,
            ] : null,
            'distrito' => $this->distrito ? [
                'id' => $this->distrito->id,
                'nome' => $this->distrito->nome,
            ] : null,
            'concelho' => $this->concelho ? [
                'id' => $this->concelho->id,
                'nome' => $this->concelho->nome,
            ] : null,
            'imagem_capa' => is_array($capa) ? $capa['url'] : null,
            'imagens' => $imagens,
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        if ($this->relationLoaded('favoritos') && $request->user()) {
            $dados['favorito'] = $this->favoritos->contains('user_id', $request->user()->id);
        }

        if ($this->relationLoaded('reviews')) {
            $dados['reviews'] = $this->reviews->map(fn ($review) => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'status' => $review->status,
                'autor' => $review->relationLoaded('user') ? $review->user?->nomeCompleto() : null,
                'created_at' => $review->created_at?->toIso8601String(),
            ])->values();
        }

        if ($dono) {
            $dados['status'] = $this->status;
            $dados['motivo_rejeicao'] = $this->motivo_rejeicao;
            $dados['contacto'] = $this->contacto;
            $dados['email'] = $this->email;
            $dados['youtube_link'] = $this->youtube_link;
            $dados['instagram_link'] = $this->instagram_link;
            $dados['categoria_id'] = $this->categoria_id;
            $dados['distrito_id'] = $this->distrito_id;
            $dados['concelho_id'] = $this->concelho_id;
            $dados['data_expiracao'] = $this->data_expiracao?->toIso8601String();
        }

        return $dados;
    }

    /**
     * Same absolute URL the public disk is configured with (APP_URL/storage/...),
     * without touching the disk — the Hostinger layout has no storage symlink
     * and the disk root is not needed just to build the link.
     */
    public static function urlAbsoluta(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $base = rtrim((string) config('filesystems.disks.public.url'), '/');

        return $base.'/'.ltrim($path, '/');
    }
}
