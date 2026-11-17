<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class BlogPost extends Model
{
    /** @use HasFactory<\Database\Factories\BlogPostFactory> */
    use HasFactory, HasSlug;

    protected $fillable = ['titulo', 'conteudo', 'imagem_destaque', 'status', 'publicado_em', 'topico_fonte'];

    protected function casts(): array
    {
        return ['publicado_em' => 'datetime'];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('titulo')->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublicados($query)
    {
        return $query->where('status', 'publicado');
    }

    public function publicar(): void
    {
        $this->update(['status' => 'publicado', 'publicado_em' => now()]);
    }
}
