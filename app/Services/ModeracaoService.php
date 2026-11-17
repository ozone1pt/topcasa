<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Keyword-blocklist auto-moderation, used when the admin enables
 * "aprovação automática" (Configuracao::get('auto_approval_enabled')).
 * Equivalent to the legacy site's hardcoded accent-insensitive blocklist
 * check, centralized here instead of duplicated inline.
 */
class ModeracaoService
{
    private const PALAVRAS_BLOQUEADAS = [
        'droga', 'cocaina', 'heroina', 'arma de fogo', 'pistola',
        'documento falso', 'cartao falso', 'passaporte falso',
        'burla', 'esquema piramidal', 'ganha dinheiro facil',
        'conteudo adulto', 'acompanhante', 'escort',
    ];

    public function verificar(string $titulo, string $descricao): bool
    {
        $texto = Str::ascii(Str::lower("{$titulo} {$descricao}"));

        foreach (self::PALAVRAS_BLOQUEADAS as $palavra) {
            if (Str::contains($texto, Str::ascii($palavra))) {
                return false;
            }
        }

        return true;
    }
}
