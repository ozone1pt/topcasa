<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mensagem extends Model
{
    protected $table = 'mensagens';

    protected $fillable = [
        'anuncio_id', 'remetente_id', 'remetente_nome', 'remetente_email',
        'destinatario_id', 'mensagem', 'lida', 'parent_id',
        'remetente_deleted_at', 'destinatario_deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'lida' => 'boolean',
            'remetente_deleted_at' => 'datetime',
            'destinatario_deleted_at' => 'datetime',
        ];
    }

    public function anuncio(): BelongsTo
    {
        return $this->belongsTo(Anuncio::class);
    }

    public function remetente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remetente_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Mensagem::class, 'parent_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(Mensagem::class, 'parent_id');
    }

    public function remetenteNome(): string
    {
        return $this->remetente?->nomeCompleto() ?? $this->remetente_nome ?? 'Anónimo';
    }
}
