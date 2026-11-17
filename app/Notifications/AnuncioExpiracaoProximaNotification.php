<?php

namespace App\Notifications;

use App\Models\Anuncio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnuncioExpiracaoProximaNotification extends Notification implements ShouldQueue
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
            ->subject('O teu anúncio vai expirar em breve')
            ->line("O anúncio \"{$this->anuncio->titulo}\" expira em {$this->anuncio->data_expiracao->format('d/m/Y')}.")
            ->action('Ver anúncio', route('anuncios.show', $this->anuncio));
    }
}
