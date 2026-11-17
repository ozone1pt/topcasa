<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogModerationController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::orderByDesc('created_at')->paginate(20);

        return view('admin.blog.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        return view('admin.blog.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'conteudo' => ['required', 'string'],
            'imagem_destaque' => ['nullable', 'image', 'max:4096'],
        ]);

        $post = BlogPost::create([
            'titulo' => $data['titulo'],
            'conteudo' => $data['conteudo'],
            'status' => 'publicado',
            'publicado_em' => now(),
        ]);

        if ($request->hasFile('imagem_destaque')) {
            $post->update(['imagem_destaque' => $request->file('imagem_destaque')->store('blog', 'public')]);
        }

        return redirect()->route('crm.blog.index')->with('status', 'Artigo criado.');
    }

    public function publicar(BlogPost $post): RedirectResponse
    {
        $post->publicar();

        return back()->with('status', 'Artigo publicado.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Artigo removido.');
    }
}
