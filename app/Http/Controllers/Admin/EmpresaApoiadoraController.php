<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmpresaApoiadora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmpresaApoiadoraController extends Controller
{
    public function index(): View
    {
        return view('admin.empresas.index', ['empresas' => EmpresaApoiadora::orderBy('ordem')->get()]);
    }

    public function create(): View
    {
        return view('admin.empresas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:500'],
            'descricao' => ['nullable', 'string'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        EmpresaApoiadora::create([
            ...$data,
            'logo' => $request->file('logo')->store('empresas', 'public'),
            'ativo' => true,
        ]);

        return redirect()->route('crm.empresas.index')->with('status', 'Empresa apoiadora adicionada.');
    }

    public function destroy(EmpresaApoiadora $empresa): RedirectResponse
    {
        $empresa->delete();

        return back()->with('status', 'Empresa apoiadora removida.');
    }
}
