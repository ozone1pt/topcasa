<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'nome',
        'apelido',
        'telefone',
        'nif',
        'tipo_conta',
        'empresa_nome',
        'password',
        'user_type',
        'status',
        'google_id',
        'lembrete_anuncio_status',
        'notificar_orcamentos',
        'notificar_recrutamento',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notificar_orcamentos' => 'boolean',
            'notificar_recrutamento' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isGestor(): bool
    {
        return $this->user_type === 'gestor';
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function nomeCompleto(): string
    {
        return trim("{$this->nome} {$this->apelido}") ?: $this->username;
    }

    public function anuncios(): HasMany
    {
        return $this->hasMany(Anuncio::class);
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    /**
     * Approved professional/company accounts - the pool the CRM draws
     * "novo pedido de orçamento/recrutamento" notification recipients from.
     */
    public function scopeProfissionaisAtivos($query)
    {
        return $query->where('status', 'aprovado')->whereIn('tipo_conta', ['profissional', 'empresa']);
    }
}
