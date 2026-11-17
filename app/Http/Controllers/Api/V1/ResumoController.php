<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\Favorito;
use App\Models\Review;
use App\Services\CaixaMensagens;
use App\Services\GestaoAlertas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumoController extends Controller
{
    public function __construct(
        private readonly CaixaMensagens $mensagens,
        private readonly GestaoAlertas $alertas,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $porEstado = Anuncio::query()
            ->where('user_id', $user->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'data' => [
                'anuncios' => [
                    'rascunho' => (int) ($porEstado['rascunho'] ?? 0),
                    'pendente' => (int) ($porEstado['pendente'] ?? 0),
                    'aprovado' => (int) ($porEstado['aprovado'] ?? 0),
                    'rejeitado' => (int) ($porEstado['rejeitado'] ?? 0),
                    'expirado' => (int) ($porEstado['expirado'] ?? 0),
                    'total' => (int) $porEstado->sum(),
                ],
                'mensagens_nao_lidas' => $this->mensagens->naoLidas($user),
                'alertas_nao_lidos' => $this->alertas->naoLidos($user),
                'favoritos' => Favorito::query()->where('user_id', $user->id)->count(),
                'reviews_por_moderar' => Review::query()
                    ->where('anunciante_id', $user->id)
                    ->where('status', 'pendente')
                    ->count(),
            ],
        ]);
    }
}
