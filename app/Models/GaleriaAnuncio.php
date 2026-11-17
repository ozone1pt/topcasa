<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriaAnuncio extends Model
{
    protected $table = 'galeria_anuncios';

    protected $fillable = ['anuncio_id', 'imagem', 'descricao', 'ordem'];

    public function anuncio(): BelongsTo
    {
        return $this->belongsTo(Anuncio::class);
    }

    public function url(): string
    {
        return \Illuminate\Support\Facades\Storage::url($this->imagem);
    }
}
