<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation sent to the professional when their anúncio goes live.
 * Text supplied by the client. Not queued: this host has no queue worker.
 */
class AnuncioPublicadoNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('O seu anúncio foi publicado!')
            ->greeting('O seu anúncio foi publicado!')
            ->line('Votos de bons trabalhos.')
            ->salutation('Equipa TopCasa.');
    }
}
