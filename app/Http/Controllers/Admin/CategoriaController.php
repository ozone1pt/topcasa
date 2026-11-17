<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        return view('admin.categorias.index', ['categorias' => Categoria::orderBy('nome')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'icone' => ['nullable', 'string', 'max:50'],
        ]);

        Categoria::create([...$data, 'status' => true]);

        return back()->with('status', 'Categoria criada.');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'icone' => ['nullable', 'string', 'max:50'],
        ]);

        $categoria->update($data);

        return back()->with('status', 'Categoria atualizada.');
    }

    public function toggle(Categoria $categoria): RedirectResponse
    {
        $categoria->update(['status' => ! $categoria->status]);

        return back()->with('status', 'Estado atualizado.');
    }

    /**
     * Orphaned ads get categoria_id = NULL (nullOnDelete FK), same as the
     * legacy behaviour, instead of blocking or cascading the delete.
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        $categoria->delete();

        return back()->with('status', 'Categoria removida.');
    }
}
