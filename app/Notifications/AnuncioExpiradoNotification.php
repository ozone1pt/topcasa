<?php

namespace App\Notifications;

use App\Models\Anuncio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnuncioExpiradoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Anuncio $anuncio)
    {
    }

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
            ->subject('O teu anúncio expirou')
            ->line("O anúncio \"{$this->anuncio->titulo}\" expirou e já não está visível ao público.")
            ->action('Reativar anúncio', route('anuncios.meus'));
    }
}
