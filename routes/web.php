<?php

use App\Http\Controllers\Admin\AlertaController as AdminAlertaController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AnuncioModerationController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogModerationController;
use App\Http\Controllers\Admin\CategoriaController as AdminCategoriaController;
use App\Http\Controllers\Admin\ConfiguracaoController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmpresaApoiadoraController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\OrcamentoController as AdminOrcamentoController;
use App\Http\Controllers\Admin\PesquisaController;
use App\Http\Controllers\Admin\RecrutamentoController as AdminRecrutamentoController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\BannerTrackingController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MensagemController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecrutamentoController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::view('/dicas-de-seguranca', 'dicas-seguranca')->name('dicas-seguranca');
Route::view('/termos-e-condicoes', 'termos-condicoes')->name('termos');
Route::view('/politica-de-privacidade', 'politica-privacidade')->name('politica-privacidade');
Route::view('/politica-de-cookies', 'politica-cookies')->name('politica-cookies');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/api/distritos/{distrito}/concelhos', [LocationController::class, 'concelhos'])->name('api.concelhos');

Route::get('/anuncios', [AnuncioController::class, 'index'])->name('anuncios.index');
Route::get('/anuncios/criar', [AnuncioController::class, 'create'])->name('anuncios.create');
Route::post('/anuncios', [AnuncioController::class, 'store'])->name('anuncios.store');
Route::get('/anuncios/{anuncio}', [AnuncioController::class, 'show'])->name('anuncios.show');
Route::post('/anuncios/{anuncio}/mensagens', [MensagemController::class, 'store'])->name('mensagens.store');
Route::post('/anuncios/{anuncio}/revelar-telefone', [AnuncioController::class, 'revelarTelefone'])->name('anuncios.revelar-telefone');
Route::post('/anuncios/{anuncio}/revelar-whatsapp', [AnuncioController::class, 'revelarWhatsapp'])->name('anuncios.revelar-whatsapp');

Route::middleware('auth')->group(function () {
    Route::get('/meus-anuncios', [AnuncioController::class, 'meus'])->name('anuncios.meus');
    Route::get('/anuncios/{anuncio}/editar', [AnuncioController::class, 'edit'])->name('anuncios.edit');
    Route::put('/anuncios/{anuncio}', [AnuncioController::class, 'update'])->name('anuncios.update');
    Route::delete('/anuncios/{anuncio}', [AnuncioController::class, 'destroy'])->name('anuncios.destroy');
    Route::post('/anuncios/{anuncio}/reativar', [AnuncioController::class, 'reativar'])->name('anuncios.reativar');
    Route::delete('/galeria/{imagem}', [AnuncioController::class, 'destroyImagem'])->name('anuncios.imagem.destroy');
    Route::post('/galeria/ordenar', [AnuncioController::class, 'ordenarImagens'])->name('anuncios.imagens.ordenar');
    Route::post('/galeria/{imagem}/rodar', [AnuncioController::class, 'rodarImagem'])->name('anuncios.imagem.rodar');

    Route::post('/anuncios/{anuncio}/favorito', [FavoritoController::class, 'toggle'])->name('favoritos.toggle');
    Route::get('/favoritos', [FavoritoController::class, 'index'])->name('favoritos.index');

    Route::post('/anuncios/{anuncio}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/aprovar', [ReviewController::class, 'aprovar'])->name('reviews.aprovar');
    Route::post('/reviews/{review}/rejeitar', [ReviewController::class, 'rejeitar'])->name('reviews.rejeitar');

    Route::get('/mensagens', [MensagemController::class, 'index'])->name('mensagens.index');
    Route::get('/mensagens/{mensagem}', [MensagemController::class, 'show'])->name('mensagens.show');
    Route::post('/mensagens/{mensagem}/responder', [MensagemController::class, 'reply'])->name('mensagens.reply');
    Route::delete('/mensagens/{mensagem}', [MensagemController::class, 'destroy'])->name('mensagens.destroy');

    Route::post('/orcamentos/{pedido}/concluir', [OrcamentoController::class, 'concluir'])->name('orcamentos.concluir');

    Route::post('/recrutamentos/{pedido}/pausar', [RecrutamentoController::class, 'pausar'])->name('recrutamentos.pausar');
    Route::delete('/recrutamentos/{pedido}', [RecrutamentoController::class, 'destroy'])->name('recrutamentos.destroy');

    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::post('/alertas/{alerta}/lido', [AlertaController::class, 'marcarLido'])->name('alertas.lido');
});

// Guests can publish orçamento leads and recrutamento posts without an account, same as the legacy site.
Route::get('/orcamentos', [OrcamentoController::class, 'index'])->name('orcamentos.index');
Route::get('/orcamentos/criar', [OrcamentoController::class, 'create'])->name('orcamentos.create');
Route::post('/orcamentos', [OrcamentoController::class, 'store'])->name('orcamentos.store');
// Contact reveal is public too, same as an anúncio's "Ver contacto" - it just logs a click.
Route::post('/orcamentos/{pedido}/desbloquear', [OrcamentoController::class, 'desbloquear'])->name('orcamentos.desbloquear');

Route::get('/recrutamentos', [RecrutamentoController::class, 'index'])->name('recrutamentos.index');
Route::get('/recrutamentos/criar', [RecrutamentoController::class, 'create'])->name('recrutamentos.create');
Route::post('/recrutamentos', [RecrutamentoController::class, 'store'])->name('recrutamentos.store');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
Route::get('/newsletter/cancelar', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');

Route::post('/chatbot/mensagem', [ChatbotController::class, 'mensagem'])->middleware('throttle:15,1')->name('chatbot.mensagem');

Route::get('/banners/{banner}/ver', [BannerTrackingController::class, 'view'])->name('banners.view');
Route::get('/banners/{banner}/clique', [BannerTrackingController::class, 'click'])->name('banners.click');

Route::middleware(['auth', 'can:admin'])->prefix('crm')->name('crm.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pesquisa', [PesquisaController::class, 'index'])->name('pesquisa');

    Route::get('/anuncios', [AnuncioModerationController::class, 'index'])->name('anuncios.index');
    Route::get('/anuncios/{anuncio}/editar', [AnuncioModerationController::class, 'edit'])->name('anuncios.edit');
    Route::put('/anuncios/{anuncio}', [AnuncioModerationController::class, 'update'])->name('anuncios.update');
    Route::post('/anuncios/{anuncio}/duplicar', [AnuncioModerationController::class, 'duplicar'])->name('anuncios.duplicar');
    Route::post('/anuncios/{anuncio}/toggle', [AnuncioModerationController::class, 'toggle'])->name('anuncios.toggle');
    Route::delete('/anuncios/{anuncio}', [AnuncioModerationController::class, 'destroy'])->name('anuncios.destroy');
    Route::post('/anuncios/{anuncio}/aprovar', [AnuncioModerationController::class, 'aprovar'])->name('anuncios.aprovar');
    Route::post('/anuncios/{anuncio}/rejeitar', [AnuncioModerationController::class, 'rejeitar'])->name('anuncios.rejeitar');

    Route::get('/blog', [BlogModerationController::class, 'index'])->name('blog.index');
    Route::get('/blog/criar', [BlogModerationController::class, 'create'])->name('blog.create');
    Route::post('/blog', [BlogModerationController::class, 'store'])->name('blog.store');
    Route::post('/blog/{post}/publicar', [BlogModerationController::class, 'publicar'])->name('blog.publicar');
    Route::delete('/blog/{post}', [BlogModerationController::class, 'destroy'])->name('blog.destroy');

    Route::resource('banners', BannerController::class)->except(['show']);
    Route::resource('empresas', EmpresaApoiadoraController::class)->except(['show', 'edit', 'update']);

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/aprovar', [AdminUserController::class, 'aprovar'])->name('users.aprovar');
    Route::post('/users/{user}/rejeitar', [AdminUserController::class, 'rejeitar'])->name('users.rejeitar');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/categorias', [AdminCategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [AdminCategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{categoria}', [AdminCategoriaController::class, 'update'])->name('categorias.update');
    Route::post('/categorias/{categoria}/toggle', [AdminCategoriaController::class, 'toggle'])->name('categorias.toggle');
    Route::delete('/categorias/{categoria}', [AdminCategoriaController::class, 'destroy'])->name('categorias.destroy');

    Route::get('/orcamentos', [AdminOrcamentoController::class, 'index'])->name('orcamentos.index');
    Route::put('/orcamentos/{pedido}', [AdminOrcamentoController::class, 'update'])->name('orcamentos.update');
    Route::post('/orcamentos/{pedido}/aprovar', [AdminOrcamentoController::class, 'aprovar'])->name('orcamentos.aprovar');
    Route::post('/orcamentos/{pedido}/rejeitar', [AdminOrcamentoController::class, 'rejeitar'])->name('orcamentos.rejeitar');
    Route::delete('/orcamentos/{pedido}', [AdminOrcamentoController::class, 'destroy'])->name('orcamentos.destroy');

    Route::get('/recrutamentos', [AdminRecrutamentoController::class, 'index'])->name('recrutamentos.index');
    Route::put('/recrutamentos/{pedido}', [AdminRecrutamentoController::class, 'update'])->name('recrutamentos.update');
    Route::post('/recrutamentos/{pedido}/aprovar', [AdminRecrutamentoController::class, 'aprovar'])->name('recrutamentos.aprovar');
    Route::post('/recrutamentos/{pedido}/rejeitar', [AdminRecrutamentoController::class, 'rejeitar'])->name('recrutamentos.rejeitar');
    Route::delete('/recrutamentos/{pedido}', [AdminRecrutamentoController::class, 'destroy'])->name('recrutamentos.destroy');

    Route::get('/newsletter', [AdminNewsletterController::class, 'index'])->name('newsletter.index');
    Route::post('/newsletter/enviar', [AdminNewsletterController::class, 'enviar'])->name('newsletter.enviar');

    Route::get('/alertas', [AdminAlertaController::class, 'index'])->name('alertas.index');
    Route::get('/alertas/criar', [AdminAlertaController::class, 'create'])->name('alertas.create');
    Route::post('/alertas', [AdminAlertaController::class, 'store'])->name('alertas.store');

    Route::get('/configuracoes', [ConfiguracaoController::class, 'edit'])->name('configuracoes.edit');
    Route::put('/configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/exportar', [AnalyticsController::class, 'exportar'])->name('analytics.exportar');
    Route::get('/contactos', [AnalyticsController::class, 'contactos'])->name('contactos.index');
});

// The CRM used to live at /admin - keep old links working.
Route::redirect('/admin', '/crm');

require __DIR__.'/auth.php';
