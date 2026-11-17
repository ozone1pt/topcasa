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
        'descricao', 'foto', 'termo_aceite', 'status', 'notas_admin', 'motivo_rejeicao',
    ];

    /** Novos pedidos ficam pendentes até serem aprovados no CRM (como os anúncios). */
    public const ESTADOS_APROVADOS = ['validado', 'concluido'];

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

    /** Visíveis no site público: só os aprovados e ainda em aberto. */
    public function scopePublicados($query)
    {
        return $query->where('status', 'validado');
    }

    /** Já passou pela aprovação no CRM (validado ou, depois, concluído). */
    public function isAprovado(): bool
    {
        return in_array($this->status, self::ESTADOS_APROVADOS, true);
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? \Illuminate\Support\Facades\Storage::url($this->foto) : null;
    }

    public function desbloqueadoPor(int $userId): bool
    {
        return $this->desbloqueios()->where('user_id', $userId)->exists();
    }
}
