<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\EmailNotificacao;
use App\Notifications\AnuncioPublicadoNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Emails the professional that their anúncio is now published and logs the
 * attempt in email_notificacoes (so the CRM counts it).
 */
class AnuncioPublicadoNotifier
{
    public function notificar(Anuncio $anuncio): void
    {
        $destino = trim((string) ($anuncio->email ?: $anuncio->user?->email));

        if (! filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $log = EmailNotificacao::create([
            'tipo' => 'anuncio_publicado',
            'anuncio_id' => $anuncio->id,
            'destinatarios_count' => 1,
        ]);

        try {
            Notification::route('mail', $destino)->notify(new AnuncioPublicadoNotification);

            $log->update(['sucesso' => true, 'enviado_em' => now()]);
        } catch (\Throwable $e) {
            report($e);

            $log->update(['sucesso' => false, 'erro' => Str::limit($e->getMessage(), 500), 'enviado_em' => now()]);
        }
    }
}
