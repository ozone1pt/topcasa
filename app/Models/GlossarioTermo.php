<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GlossarioTermo extends Model
{
    /** @use HasFactory<\Database\Factories\GlossarioTermoFactory> */
    use HasFactory;

    protected $fillable = ['categoria_id', 'termo', 'definicao'];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}
