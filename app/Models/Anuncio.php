<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Anuncio extends Model
{
    /** @use HasFactory<\Database\Factories\AnuncioFactory> */
    use HasFactory, HasSlug;

    protected $fillable = [
        'user_id',
        'titulo',
        'minha_empresa',
        'certificacao_profissional',
        'seguro_responsabilidade_civil',
        'descricao',
        'email',
        'contacto',
        'categoria_id',
        'distrito_id',
        'concelho_id',
        'destacar',
        'youtube_link',
        'instagram_link',
        'status',
        'motivo_rejeicao',
        'data_expiracao',
        'aviso_expiracao_enviado_em',
    ];

    protected static function booted(): void
    {
        // The legacy AI-moderation log table references anuncios with an FK whose
        // ON DELETE CASCADE does not take effect on this database (deleting an
        // anúncio that has a log row fails with a 500), so clear it explicitly.
        static::deleting(function (self $anuncio) {
            if (Schema::hasTable('anuncios_verificacao_log')) {
                DB::table('anuncios_verificacao_log')->where('anuncio_id', $anuncio->id)->delete();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'certificacao_profissional' => 'boolean',
            'seguro_responsabilidade_civil' => 'boolean',
            'destacar' => 'boolean',
            'data_expiracao' => 'datetime',
            'aviso_expiracao_enviado_em' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('titulo')
            ->saveSlugsTo('slug')
            ->usingSeparator('-')
            // The slug is the anúncio's URL: it must stay stable. Regenerating it on
            // every save (even Eloquent's increment('views') fires that) swapped the
            // slug of legacy anúncios for a title-based one in memory, so every URL
            // built afterwards on the page (Ver contacto, WhatsApp, message form...)
            // pointed to a page that didn't exist.
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }

    public function concelho(): BelongsTo
    {
        return $this->belongsTo(Concelho::class);
    }

    public function galeria(): HasMany
    {
        return $this->hasMany(GaleriaAnuncio::class)->orderBy('ordem');
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function mensagens(): HasMany
    {
        return $this->hasMany(Mensagem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * The name shown to visitors: the name typed in the anúncio, falling back to
     * the owner's company / account name (a lot of imported anúncios have it blank).
     */
    public function nomeProfissional(): string
    {
        $nome = trim((string) $this->minha_empresa);

        if ($nome !== '') {
            return $nome;
        }

        $user = $this->user;

        return trim((string) ($user?->empresa_nome ?: $user?->nomeCompleto()));
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function aprovar(): void
    {
        $dias = (int) Configuracao::get('dias_expiracao_padrao', 30);

        $this->update([
            'status' => 'aprovado',
            'motivo_rejeicao' => null,
            'data_expiracao' => now()->addDays($dias),
            'aviso_expiracao_enviado_em' => null,
        ]);
    }

    public function imagemCapa(): ?GaleriaAnuncio
    {
        return $this->relationLoaded('galeria')
            ? $this->galeria->first()
            : $this->galeria()->first();
    }

    public function youtubeEmbedUrl(): ?string
    {
        if (! $this->youtube_link) {
            return null;
        }

        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]+)/', $this->youtube_link, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return null;
    }

    public function instagramUrl(): ?string
    {
        $link = trim((string) $this->instagram_link);

        if ($link === '') {
            return null;
        }

        if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
            return $link;
        }

        return 'https://instagram.com/'.ltrim($link, '@/');
    }

    public function whatsappUrl(): ?string
    {
        $raw = $this->contacto ?: $this->user?->telefone;

        if (! $raw) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw);

        // Strip a leading 351/00351 so we don't double it up, then require a plausible PT mobile number.
        $digits = preg_replace('/^(00351|351)/', '', $digits);

        if (! preg_match('/^9\d{8}$/', $digits)) {
            return null;
        }

        $message = "Olá, encontrei o seu serviço \"{$this->titulo}\" na TopCasa e gostaria de obter mais informações.";

        return 'https://wa.me/351'.$digits.'?text='.rawurlencode($message);
    }

    public function scopePublicados($query)
    {
        return $query->where('status', 'aprovado');
    }
}
