<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $posts = BlogPost::publicados()->orderByDesc('publicado_em')->paginate(9);

        return BlogPostResource::collection($posts);
    }

    public function show(string $post): BlogPostResource
    {
        $model = BlogPost::query()->where('slug', $post)->orWhere('id', ctype_digit($post) ? $post : 0)->first();

        abort_if($model === null || $model->status !== 'publicado', 404);

        $model->increment('views');

        return new BlogPostResource($model);
    }
}
