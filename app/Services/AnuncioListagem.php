<?php

namespace App\Services;

use App\Models\Anuncio;
use Illuminate\Database\Eloquent\Builder;

class AnuncioListagem
{
    public const POR_PAGINA = 12;

    /**
     * Approved anúncios only, with the same filters and seeded random order
     * the public website uses. The seed is a session value on the web and a
     * query parameter on the API, so paging never repeats or skips rows.
     *
     * @return Builder<Anuncio>
     */
    public function publicados(?int $categoriaId, ?int $distritoId, ?int $concelhoId, ?string $termo, int $seed): Builder
    {
        $query = Anuncio::query()
            ->publicados()
            ->with(['categoria', 'distrito', 'concelho', 'galeria', 'user'])
            ->when($categoriaId !== null, fn (Builder $q) => $q->where('categoria_id', $categoriaId))
            ->when($distritoId !== null, fn (Builder $q) => $q->where('distrito_id', $distritoId))
            ->when($concelhoId !== null, fn (Builder $q) => $q->where('concelho_id', $concelhoId))
            ->when($termo !== null && $termo !== '', function (Builder $q) use ($termo) {
                $like = '%'.$termo.'%';
                $q->where(fn (Builder $inner) => $inner
                    ->where('titulo', 'like', $like)
                    ->orWhere('descricao', 'like', $like));
            });

        $this->ordenarPorSeed($query, $seed);

        return $query;
    }

    /**
     * @param  Builder<Anuncio>  $query
     */
    public function ordenarPorSeed(Builder $query, int $seed): void
    {
        // MySQL RAND(seed) is what the website already orders by. SQLite
        // (the test database) has no seeded RAND, so a deterministic modulo
        // keeps pagination stable there without touching production SQL.
        if ($query->getConnection()->getDriverName() === 'sqlite') {
            $query->orderByRaw('(abs(id * ?) % 1000003)', [$seed]);
        } else {
            $query->orderByRaw('RAND(?)', [$seed]);
        }

        $query->orderByDesc('id');
    }
}
