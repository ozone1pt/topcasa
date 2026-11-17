<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaApoiadora extends Model
{
    /** @use HasFactory<\Database\Factories\EmpresaApoiadoraFactory> */
    use HasFactory;

    protected $table = 'empresas_apoiadoras';

    protected $fillable = ['nome', 'logo', 'url', 'descricao', 'ativo', 'ordem'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true)->orderBy('ordem');
    }
}
