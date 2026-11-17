<?php

namespace App\Http\Controllers;

use App\Models\Concelho;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Cascading select data source for the distrito -> concelho pickers.
     */
    public function concelhos(int $distritoId): JsonResponse
    {
        return response()->json(
            Concelho::where('distrito_id', $distritoId)->orderBy('nome')->get(['id', 'nome'])
        );
    }
}
