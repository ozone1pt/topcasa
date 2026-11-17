<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\EmailNotificacao;
use App\Notifications\ContactoReveladoNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Emails the anúncio's owner when somebody clicks "Ver contacto", and logs
 * each attempt in email_notificacoes so the CRM can count the emails sent.
 */
class ContactoReveladoNotifier
{
    public function notificar(Anuncio $anuncio, ?string $ip): void
    {
        $destino = trim((string) ($anuncio->email ?: $anuncio->user?->email));

        if (! filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // The same visitor tapping the button several times in a row (or a
        // bot hammering the endpoint) must not flood the owner's inbox.
        if (! Cache::add('contacto-revelado:'.$anuncio->id.':'.($ip ?: 'x'), 1, now()->addMinutes(10))) {
            return;
        }

        $log = EmailNotificacao::create([
            'tipo' => 'contacto_revelado',
            'anuncio_id' => $anuncio->id,
            'destinatarios_count' => 1,
        ]);

        try {
            Notification::route('mail', $destino)->notify(new ContactoReveladoNotification($anuncio));

            $log->update(['sucesso' => true, 'enviado_em' => now()]);
        } catch (\Throwable $e) {
            report($e);

            $log->update(['sucesso' => false, 'erro' => Str::limit($e->getMessage(), 500), 'enviado_em' => now()]);
        }
    }
}
