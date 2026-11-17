<?php

namespace App\Notifications;

use App\Models\Anuncio;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an anúncio's owner whenever a visitor clicks "Ver contacto".
 * Deliberately not queued: this host has no queue worker, so it's sent
 * straight away (after the response, see ContactoReveladoNotifier).
 */
class ContactoReveladoNotification extends Notification
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
            ->subject('Alguém guardou o teu contacto na TopCasa')
            ->greeting('Olá'.($this->anuncio->minha_empresa ? ' '.$this->anuncio->minha_empresa : '').',')
            ->line('Alguém guardou o teu contacto através do teu anúncio "'.$this->anuncio->titulo.'" e pode vir a fazer-te um pedido de orçamento.')
            ->line('Mantém o telemóvel por perto e o anúncio atualizado para não perderes esta oportunidade.')
            ->action('Ver o meu anúncio', route('anuncios.show', $this->anuncio))
            ->salutation('Cumprimentos, TopCasa');
    }
}
