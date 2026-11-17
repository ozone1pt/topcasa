<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnuncioResource;
use App\Models\Anuncio;
use App\Services\AnuncioListagem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnuncioController extends Controller
{
    public function __construct(private readonly AnuncioListagem $listagem) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'categoria' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'distrito' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'concelho_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'q' => ['sometimes', 'nullable', 'string', 'max:200'],
            'seed' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999999999'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        // The client must reuse this seed on the next pages. Without one we
        // mint it, the same way the website stores a seed in the session.
        $seed = ! empty($data['seed']) ? (int) $data['seed'] : random_int(1, 999999);

        $anuncios = $this->listagem->publicados(
            categoriaId: isset($data['categoria']) ? (int) $data['categoria'] : null,
            distritoId: isset($data['distrito']) ? (int) $data['distrito'] : null,
            concelhoId: isset($data['concelho_id']) ? (int) $data['concelho_id'] : null,
            termo: isset($data['q']) ? (string) $data['q'] : null,
            seed: $seed,
        )->paginate(AnuncioListagem::POR_PAGINA)->withQueryString();

        return AnuncioResource::collection($anuncios)->additional(['seed' => $seed]);
    }

    /**
     * Lookup by numeric id or by slug. Visibility matches the website policy:
     * approved anúncios are public; anything else is only for the owner or an admin.
     */
    public function show(string $anuncio): AnuncioResource
    {
        $model = ctype_digit($anuncio)
            ? Anuncio::query()->find($anuncio)
            : Anuncio::query()->where('slug', $anuncio)->first();

        abort_if($model === null, 404);

        $this->authorize('view', $model);

        $model->load(['categoria', 'distrito', 'concelho', 'galeria', 'user']);
        $model->increment('views');

        return new AnuncioResource($model);
    }
}
