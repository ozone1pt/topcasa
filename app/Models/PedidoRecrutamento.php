<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoRecrutamento extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoRecrutamentoFactory> */
    use HasFactory;

    protected $table = 'pedidos_recrutamento';

    protected $fillable = [
        'user_id', 'titulo', 'empresa_nome', 'email', 'telefone', 'distrito_id',
        'descricao', 'foto', 'termo_aceite', 'status', 'notas_admin',
    ];

    protected function casts(): array
    {
        return ['termo_aceite' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }
}
