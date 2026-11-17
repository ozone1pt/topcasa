<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactoEvento extends Model
{
    public $timestamps = false;

    protected $table = 'contacto_eventos';

    protected $fillable = ['origem', 'anuncio_id', 'user_id', 'pedido_orcamento_id', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function anuncio(): BelongsTo
    {
        return $this->belongsTo(Anuncio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function registar(string $origem, ?Anuncio $anuncio = null, ?PedidoOrcamento $pedido = null): void
    {
        static::create([
            'origem' => $origem,
            'anuncio_id' => $anuncio?->id,
            'user_id' => $anuncio?->user_id,
            'pedido_orcamento_id' => $pedido?->id,
            'created_at' => now(),
        ]);
    }
}
