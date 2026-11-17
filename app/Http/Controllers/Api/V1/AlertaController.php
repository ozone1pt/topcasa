<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertaResource;
use App\Models\Alerta;
use App\Services\GestaoAlertas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlertaController extends Controller
{
    public function __construct(private readonly GestaoAlertas $alertas) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AlertaResource::collection($this->alertas->lista($request->user()))
            ->additional(['nao_lidas' => $this->alertas->naoLidos($request->user())]);
    }

    public function marcarLido(Request $request, Alerta $alerta): AlertaResource
    {
        return new AlertaResource($this->alertas->marcarLido($request->user(), $alerta));
    }
}
