<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesbloqueioOrcamento extends Model
{
    public $timestamps = false;

    protected $table = 'desbloqueios_orcamento';

    protected $fillable = ['pedido_id', 'user_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(PedidoOrcamento::class, 'pedido_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
