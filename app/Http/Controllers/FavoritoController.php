<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use App\Services\GestaoFavoritos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoritoController extends Controller
{
    public function __construct(private readonly GestaoFavoritos $favoritos) {}

    public function toggle(Anuncio $anuncio): RedirectResponse
    {
        $adicionado = $this->favoritos->alternar(Auth::user(), $anuncio);

        return back()->with('status', $adicionado ? 'Adicionado aos favoritos.' : 'Removido dos favoritos.');
    }

    public function index(): View
    {
        return view('anuncios.favoritos', [
            'anuncios' => $this->favoritos->lista(Auth::user()),
        ]);
    }
}
