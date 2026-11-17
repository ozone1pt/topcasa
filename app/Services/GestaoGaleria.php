<?php

namespace App\Services;

use App\Models\GaleriaAnuncio;
use Illuminate\Http\Request;

class GestaoGaleria
{
    public function __construct(private readonly ImageUploadService $images) {}

    public function remover(GaleriaAnuncio $imagem): void
    {
        $this->images->delete($imagem->imagem);
        $imagem->delete();
    }

    /**
     * @param  list<int>  $ids
     */
    public function ordenar(array $ids): void
    {
        $imagens = GaleriaAnuncio::with('anuncio')->whereIn('id', $ids)->get()->keyBy('id');

        abort_unless($imagens->count() === count($ids), 422);
        abort_unless($imagens->pluck('anuncio_id')->unique()->count() === 1, 422);

        foreach (array_values($ids) as $posicao => $id) {
            $imagens[$id]->update(['ordem' => $posicao + 1]);
        }
    }

    public function rodar(GaleriaAnuncio $imagem, bool $sentidoHorario): void
    {
        $this->images->rotate($imagem->imagem, $sentidoHorario);
    }

    /**
     * Same rules the website validates before reordering.
     *
     * @return list<int>
     */
    public function idsValidados(Request $request): array
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:'.ImageUploadService::MAX_GALERIA],
            'ids.*' => ['integer'],
        ]);

        return array_map('intval', $data['ids']);
    }
}
