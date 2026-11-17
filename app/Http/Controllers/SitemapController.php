<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\BlogPost;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = collect([
                ['loc' => route('home'), 'priority' => '1.0'],
                ['loc' => route('anuncios.index'), 'priority' => '0.9'],
                ['loc' => route('orcamentos.index'), 'priority' => '0.6'],
                ['loc' => route('recrutamentos.index'), 'priority' => '0.6'],
                ['loc' => route('blog.index'), 'priority' => '0.6'],
            ]);

            Anuncio::publicados()->select(['slug', 'updated_at'])->get()->each(function (Anuncio $anuncio) use ($urls) {
                $urls->push(['loc' => route('anuncios.show', $anuncio), 'lastmod' => $anuncio->updated_at->toAtomString(), 'priority' => '0.7']);
            });

            BlogPost::publicados()->select(['slug', 'updated_at'])->get()->each(function (BlogPost $post) use ($urls) {
                $urls->push(['loc' => route('blog.show', $post), 'lastmod' => $post->updated_at->toAtomString(), 'priority' => '0.5']);
            });

            return view('sitemap', ['urls' => $urls])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
