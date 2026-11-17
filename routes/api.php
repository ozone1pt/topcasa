<?php

use App\Http\Controllers\Api\V1\AlertaController;
use App\Http\Controllers\Api\V1\AnuncioController;
use App\Http\Controllers\Api\V1\AnuncioGestaoController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\CatalogoController;
use App\Http\Controllers\Api\V1\ContaController;
use App\Http\Controllers\Api\V1\ContactoController;
use App\Http\Controllers\Api\V1\FavoritoController;
use App\Http\Controllers\Api\V1\GaleriaController;
use App\Http\Controllers\Api\V1\MensagemController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\OrcamentoController;
use App\Http\Controllers\Api\V1\PerfilController;
use App\Http\Controllers\Api\V1\RecrutamentoController;
use App\Http\Controllers\Api\V1\ResumoController;
use App\Http\Controllers\Api\V1\ReviewController;
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

Route::get('blog', [BlogController::class, 'index'])->name('api.v1.blog.index');
Route::get('blog/{post}', [BlogController::class, 'show'])->name('api.v1.blog.show');

Route::post('newsletter', [NewsletterController::class, 'store'])->name('api.v1.newsletter.store');

Route::get('anuncios', [AnuncioController::class, 'index'])->name('api.v1.anuncios.index');

Route::middleware('auth.optional')->group(function () {
    Route::get('orcamentos', [OrcamentoController::class, 'index'])->name('api.v1.orcamentos.index');
    Route::post('orcamentos/{orcamento}/desbloquear', [OrcamentoController::class, 'desbloquear'])->name('api.v1.orcamentos.desbloquear');
    Route::get('recrutamento', [RecrutamentoController::class, 'index'])->name('api.v1.recrutamento.index');

    Route::get('anuncios/{anuncio}', [AnuncioController::class, 'show'])->name('api.v1.anuncios.show');
    Route::post('anuncios/{anuncio}/revelar-telefone', [ContactoController::class, 'telefone'])->name('api.v1.anuncios.telefone');
    Route::post('anuncios/{anuncio}/revelar-whatsapp', [ContactoController::class, 'whatsapp'])->name('api.v1.anuncios.whatsapp');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::get('me', [PerfilController::class, 'show'])->name('api.v1.me');
    Route::put('me', [PerfilController::class, 'update'])->name('api.v1.me.update');

    Route::get('me/anuncios', [AnuncioGestaoController::class, 'meus'])->name('api.v1.anuncios.meus');
    Route::post('anuncios', [AnuncioGestaoController::class, 'store'])->name('api.v1.anuncios.store');
    // PHP does not parse multipart bodies on PUT. The app sends JSON for a
    // text-only edit, and POST multipart (with or without _method=PUT) when
    // photos are attached. Both verbs hit the same action.
    Route::match(['put', 'post'], 'anuncios/{anuncio}', [AnuncioGestaoController::class, 'update'])->name('api.v1.anuncios.update');
    Route::delete('anuncios/{anuncio}', [AnuncioGestaoController::class, 'destroy'])->name('api.v1.anuncios.destroy');
    Route::post('anuncios/{anuncio}/reativar', [AnuncioGestaoController::class, 'reativar'])->name('api.v1.anuncios.reativar');

    Route::delete('galeria/{imagem}', [GaleriaController::class, 'destroy'])->name('api.v1.galeria.destroy');
    Route::post('galeria/ordenar', [GaleriaController::class, 'ordenar'])->name('api.v1.galeria.ordenar');
    Route::post('galeria/{imagem}/rodar', [GaleriaController::class, 'rodar'])->name('api.v1.galeria.rodar');

    Route::get('favoritos', [FavoritoController::class, 'index'])->name('api.v1.favoritos.index');
    Route::post('anuncios/{anuncio}/favorito', [FavoritoController::class, 'toggle'])->name('api.v1.favoritos.toggle');

    Route::get('mensagens', [MensagemController::class, 'index'])->name('api.v1.mensagens.index');
    Route::post('anuncios/{anuncio}/mensagens', [MensagemController::class, 'store'])->name('api.v1.mensagens.store');
    Route::get('mensagens/{mensagem}', [MensagemController::class, 'show'])->name('api.v1.mensagens.show');
    Route::post('mensagens/{mensagem}/responder', [MensagemController::class, 'reply'])->name('api.v1.mensagens.reply');
    Route::delete('mensagens/{mensagem}', [MensagemController::class, 'destroy'])->name('api.v1.mensagens.destroy');

    Route::post('anuncios/{anuncio}/reviews', [ReviewController::class, 'store'])->name('api.v1.reviews.store');
    Route::post('reviews/{review}/aprovar', [ReviewController::class, 'aprovar'])->name('api.v1.reviews.aprovar');
    Route::post('reviews/{review}/rejeitar', [ReviewController::class, 'rejeitar'])->name('api.v1.reviews.rejeitar');

    Route::get('alertas', [AlertaController::class, 'index'])->name('api.v1.alertas.index');
    Route::post('alertas/lidos', [AlertaController::class, 'marcarTodos'])->name('api.v1.alertas.lidos');
    Route::post('alertas/{alerta}/lido', [AlertaController::class, 'marcarLido'])->name('api.v1.alertas.lido');

    Route::get('me/resumo', [ResumoController::class, 'show'])->name('api.v1.me.resumo');
    Route::put('me/password', [ContaController::class, 'password'])->name('api.v1.me.password');
    Route::delete('me', [ContaController::class, 'destroy'])->name('api.v1.me.destroy');

    Route::get('me/orcamentos', [OrcamentoController::class, 'meus'])->name('api.v1.orcamentos.meus');
    Route::post('orcamentos', [OrcamentoController::class, 'store'])->name('api.v1.orcamentos.store');
    Route::post('orcamentos/{orcamento}/concluir', [OrcamentoController::class, 'concluir'])->name('api.v1.orcamentos.concluir');

    Route::get('me/recrutamento', [RecrutamentoController::class, 'meus'])->name('api.v1.recrutamento.meus');
    Route::post('recrutamento', [RecrutamentoController::class, 'store'])->name('api.v1.recrutamento.store');
    Route::post('recrutamento/{recrutamento}/pausar', [RecrutamentoController::class, 'pausar'])->name('api.v1.recrutamento.pausar');
    Route::delete('recrutamento/{recrutamento}', [RecrutamentoController::class, 'destroy'])->name('api.v1.recrutamento.destroy');

    Route::get('me/reviews', [ReviewController::class, 'recebidas'])->name('api.v1.reviews.recebidas');
});
