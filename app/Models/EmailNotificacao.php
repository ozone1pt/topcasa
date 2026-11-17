<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotificacao extends Model
{
    protected $table = 'email_notificacoes';

    protected $fillable = [
        'tipo', 'pedido_id', 'anuncio_id', 'destinatarios_count', 'sucesso', 'erro', 'enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'sucesso' => 'boolean',
            'enviado_em' => 'datetime',
        ];
    }
}
