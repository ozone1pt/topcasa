<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\AtualizacaoPerfil;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(ProfileUpdateRequest $request, AtualizacaoPerfil $perfil): UserResource
    {
        return new UserResource($perfil->aplicar($request->user(), $request->validated()));
    }
}
