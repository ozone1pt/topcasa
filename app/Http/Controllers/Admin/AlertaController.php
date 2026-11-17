<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertaController extends Controller
{
    public function index(): View
    {
        return view('admin.alertas.index', [
            'alertas' => Alerta::with('user')->orderByDesc('created_at')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.alertas.create', ['users' => User::where('status', 'aprovado')->orderBy('username')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'mensagem' => ['required', 'string'],
            'tipo' => ['required', 'in:info,aviso,sucesso,erro'],
            'destinatarios' => ['required', 'in:todos,selecionados'],
            'user_ids' => ['required_if:destinatarios,selecionados', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $userIds = $data['destinatarios'] === 'todos'
            ? User::where('status', 'aprovado')->pluck('id')
            : collect($data['user_ids']);

        $now = now();
        $userIds->chunk(500)->each(function ($chunk) use ($data, $now) {
            Alerta::insert($chunk->map(fn ($userId) => [
                'user_id' => $userId,
                'titulo' => $data['titulo'],
                'mensagem' => $data['mensagem'],
                'tipo' => $data['tipo'],
                'lida' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        });

        return redirect()->route('crm.alertas.index')->with('status', "Alerta enviado a {$userIds->count()} utilizador(es).");
    }
}
