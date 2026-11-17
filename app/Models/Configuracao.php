<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Single key/value settings store replacing the legacy site's three
 * overlapping ones (settings, google_settings, configuracoes_sistema).
 */
class Configuracao extends Model
{
    protected $table = 'configuracoes';

    protected $fillable = ['chave', 'valor'];

    public static function get(string $chave, mixed $default = null): mixed
    {
        return Cache::rememberForever("config.{$chave}", function () use ($chave, $default) {
            return static::where('chave', $chave)->value('valor') ?? $default;
        });
    }

    public static function set(string $chave, mixed $valor): void
    {
        static::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
        Cache::forget("config.{$chave}");
    }
}
