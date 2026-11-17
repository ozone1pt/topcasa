<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\ConcelhoResource;
use App\Http\Resources\DistritoResource;
use App\Models\Categoria;
use App\Models\Concelho;
use App\Models\Distrito;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogoController extends Controller
{
    public function distritos(): AnonymousResourceCollection
    {
        return DistritoResource::collection(
            Distrito::query()->orderBy('nome')->get()
        );
    }

    public function concelhos(int $distrito): AnonymousResourceCollection
    {
        return ConcelhoResource::collection(
            Concelho::query()->where('distrito_id', $distrito)->orderBy('nome')->get()
        );
    }

    public function categorias(): AnonymousResourceCollection
    {
        return CategoriaResource::collection(
            Categoria::query()->where('status', true)->orderBy('nome')->get()
        );
    }
}
