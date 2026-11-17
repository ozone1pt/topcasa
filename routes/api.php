<?php

use App\Http\Controllers\Api\V1\AnuncioController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\PerfilController;
use Illuminate\Support\Facades\Route;

// The "api" middleware group (prefix api/v1, throttle:api) is applied by
// bootstrap/app.php. These auth routes add the stricter "auth" limiter.

Route::middleware('throttle:auth')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.v1.login');
    Route::post('auth/register', [AuthController::class, 'register'])->name('api.v1.register');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.v1.forgot-password');
});

Route::get('distritos', [CatalogoController::class, 'distritos'])->name('api.v1.distritos');
Route::get('distritos/{distrito}/concelhos', [CatalogoController::class, 'concelhos'])->name('api.v1.concelhos');
Route::get('categorias', [CatalogoController::class, 'categorias'])->name('api.v1.categorias');

Route::get('anuncios', [AnuncioController::class, 'index'])->name('api.v1.anuncios.index');
Route::get('anuncios/{anuncio}', [AnuncioController::class, 'show'])
    ->middleware('auth.optional')
    ->name('api.v1.anuncios.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::get('me', [PerfilController::class, 'show'])->name('api.v1.me');
    Route::put('me', [PerfilController::class, 'update'])->name('api.v1.me.update');
});
