<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(): View
    {
        $alertas = Auth::user()->alertas()->orderByDesc('created_at')->paginate(15);

        return view('alertas.index', ['alertas' => $alertas]);
    }

    public function marcarLido(Alerta $alerta): RedirectResponse
    {
        abort_unless($alerta->user_id === Auth::id(), 403);

        $alerta->update(['lida' => true, 'lida_em' => now()]);

        return back();
    }
}
