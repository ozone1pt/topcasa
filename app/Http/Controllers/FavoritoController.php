<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Models\Favorito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoritoController extends Controller
{
    public function toggle(Anuncio $anuncio): RedirectResponse
    {
        $favorito = Favorito::where('user_id', Auth::id())->where('anuncio_id', $anuncio->id)->first();

        if ($favorito) {
            $favorito->delete();
            $status = 'Removido dos favoritos.';
        } else {
            Favorito::create(['user_id' => Auth::id(), 'anuncio_id' => $anuncio->id, 'created_at' => now()]);
            $status = 'Adicionado aos favoritos.';
        }

        return back()->with('status', $status);
    }

    public function index(): View
    {
        $anuncios = Anuncio::query()
            ->whereHas('favoritos', fn ($q) => $q->where('user_id', Auth::id()))
            ->with(['categoria', 'galeria'])
            ->paginate(12);

        return view('anuncios.favoritos', ['anuncios' => $anuncios]);
    }
}
