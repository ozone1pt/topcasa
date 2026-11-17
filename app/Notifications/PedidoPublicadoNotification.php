<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation sent to whoever published a pedido de orçamento or a vaga de
 * recrutamento. Not queued: this host has no queue worker.
 */
class PedidoPublicadoNotification extends Notification
{
    use Queueable;

    /** @param 'orcamento'|'recrutamento' $tipo */
    public function __construct(private readonly string $tipo, private readonly ?string $titulo = null)
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
        $orcamento = $this->tipo === 'orcamento';
        $titulo = trim((string) $this->titulo);

        $mail = (new MailMessage)
            ->subject($orcamento ? 'O seu pedido de orçamento foi publicado!' : 'A sua vaga foi publicada!')
            ->greeting($orcamento ? 'O seu pedido de orçamento foi publicado!' : 'A sua vaga foi publicada!');

        if ($titulo !== '') {
            $mail->line('"'.$titulo.'"');
        }

        return $mail
            ->line($orcamento
                ? 'Os profissionais da TopCasa já o podem ver e vão contactá-lo diretamente.'
                : 'Os profissionais da TopCasa já a podem ver e vão contactá-lo diretamente.')
            ->line('Votos de bons trabalhos.')
            ->salutation('Equipa TopCasa.');
    }
}
