<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\BlogPost;
use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\Configuracao;
use App\Models\Mensagem;
use App\Models\PedidoOrcamento;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Raw hit counter for "Total de Visitantes" - bumped on every homepage
        // load. Done as a direct atomic SQL increment (not Configuracao::set,
        // which would overwrite with a stale cached read) then the cache is
        // invalidated so the stats array below picks up the fresh value.
        DB::table('configuracoes')->updateOrInsert(
            ['chave' => 'site_visits'],
            ['valor' => DB::raw('CAST(COALESCE(valor, 0) AS UNSIGNED) + 1')]
        );
        Cache::forget('config.site_visits');

        return view('home', [
            'categorias' => Categoria::where('status', true)->orderBy('nome')->limit(12)->get(),
            'categoriasTodas' => Categoria::where('status', true)->orderBy('nome')->get(),
            'distritos' => Distrito::orderBy('nome')->get(),
            // The "Em destaque" block is switched off for now (client request) - the
            // anúncios keep their destacar flag, set this back to the query below
            // to bring the section back:
            // Anuncio::publicados()->where('destacar', true)->with([...])->latest()->limit(6)->get()
            'destaques' => collect(),
            'recentes' => Anuncio::publicados()->with(['categoria', 'distrito', 'concelho', 'galeria', 'user'])->latest()->limit(8)->get(),
            'posts' => BlogPost::publicados()->latest('publicado_em')->limit(3)->get(),
            'stats' => [
                'anuncios' => Anuncio::publicados()->count(),
                // "contactos_offset" lets the client set a baseline for the counter shown
                // on the homepage (e.g. to reflect contacts made before this site existed);
                // it's added on top of real contact activity: messages sent, and every
                // "Ver contacto" click (phone/WhatsApp on an anúncio, or on a pedido de
                // orçamento).
                'contactos' => (int) Configuracao::get('contactos_offset', 0)
                    + Mensagem::whereNull('parent_id')->count()
                    + (int) Anuncio::sum('phone_clicks')
                    + (int) Anuncio::sum('whatsapp_clicks')
                    + (int) PedidoOrcamento::sum('contacto_clicks'),
                // Site-wide hit counter (every homepage load) plus every individual
                // anúncio page view.
                'visualizacoes' => (int) Configuracao::get('site_visits', 0) + Anuncio::publicados()->sum('views'),
            ],
        ]);
    }
}
