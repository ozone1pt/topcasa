<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', ['banners' => Banner::orderBy('localizacao')->orderBy('ordem')->get()]);
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Banner::create([
            ...$data,
            'imagem_desktop' => $request->file('imagem_desktop')?->store('banners', 'public'),
            'imagem_mobile' => $request->file('imagem_mobile')?->store('banners', 'public'),
            'ativo' => $request->boolean('ativo'),
        ]);

        return redirect()->route('crm.banners.index')->with('status', 'Banner criado.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', ['banner' => $banner]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $data = $this->validated($request);

        $banner->update([
            ...$data,
            'imagem_desktop' => $request->file('imagem_desktop')?->store('banners', 'public') ?? $banner->imagem_desktop,
            'imagem_mobile' => $request->file('imagem_mobile')?->store('banners', 'public') ?? $banner->imagem_mobile,
            'ativo' => $request->boolean('ativo'),
        ]);

        return redirect()->route('crm.banners.index')->with('status', 'Banner atualizado.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return back()->with('status', 'Banner removido.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'url_destino' => ['nullable', 'url', 'max:500'],
            'localizacao' => ['required', 'in:topo,meio,rodape'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'imagem_desktop' => ['nullable', 'image', 'max:4096'],
            'imagem_mobile' => ['nullable', 'image', 'max:4096'],
        ]);
    }
}
