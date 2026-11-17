<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LembreteAnuncioNotification extends Notification implements ShouldQueue
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
            ->subject('Ainda não publicaste nenhum anúncio')
            ->line('Repara que ainda não publicaste nenhum anúncio na TopCasa. Publica o teu primeiro anúncio e chega a mais clientes.')
            ->action('Publicar anúncio', route('anuncios.create'));
    }
}
