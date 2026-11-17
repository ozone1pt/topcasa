<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /** @use HasFactory<\Database\Factories\BannerFactory> */
    use HasFactory;

    protected $fillable = [
        'titulo', 'descricao', 'imagem_desktop', 'imagem_mobile', 'url_destino',
        'localizacao', 'ordem', 'ativo', 'data_inicio', 'data_fim',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'data_inicio' => 'date',
            'data_fim' => 'date',
        ];
    }

    public function scopeAtivosEm($query, string $localizacao)
    {
        $hoje = now()->toDateString();

        return $query->where('localizacao', $localizacao)
            ->where('ativo', true)
            ->where(fn ($q) => $q->whereNull('data_inicio')->orWhere('data_inicio', '<=', $hoje))
            ->where(fn ($q) => $q->whereNull('data_fim')->orWhere('data_fim', '>=', $hoje))
            ->orderBy('ordem');
    }
}
