<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::publicados()->orderByDesc('publicado_em')->paginate(9);

        return view('blog.index', ['posts' => $posts]);
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->status === 'publicado', 404);

        $post->increment('views');

        return view('blog.show', ['post' => $post]);
    }
}
