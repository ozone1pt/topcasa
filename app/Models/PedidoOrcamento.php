<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoOrcamento extends Model
{
    /** @use HasFactory<\Database\Factories\PedidoOrcamentoFactory> */
    use HasFactory;

    protected $table = 'pedidos_orcamento';

    protected $fillable = [
        'user_id', 'titulo', 'nome', 'telefone', 'email', 'distrito_id',
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

    public function desbloqueios(): HasMany
    {
        return $this->hasMany(DesbloqueioOrcamento::class, 'pedido_id');
    }

    public function desbloqueadoPor(int $userId): bool
    {
        return $this->desbloqueios()->where('user_id', $userId)->exists();
    }
}
