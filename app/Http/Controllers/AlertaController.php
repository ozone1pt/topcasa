<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Services\GestaoAlertas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function __construct(private readonly GestaoAlertas $alertas) {}

    public function index(): View
    {
        return view('alertas.index', [
            'alertas' => $this->alertas->lista(Auth::user()),
        ]);
    }

    public function marcarLido(Alerta $alerta): RedirectResponse
    {
        $this->alertas->marcarLido(Auth::user(), $alerta);

        return back();
    }
}
