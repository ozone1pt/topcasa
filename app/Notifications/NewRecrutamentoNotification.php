<?php

namespace App\Notifications;

use App\Models\PedidoRecrutamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRecrutamentoNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PedidoRecrutamento $pedido)
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
            ->subject('Nova vaga de recrutamento: '.$this->pedido->titulo)
            ->line($this->pedido->empresa_nome.' publicou uma nova vaga: '.$this->pedido->titulo)
            ->line('**Data:** '.($this->pedido->created_at ?? now())->timezone('Europe/Lisbon')->format('d/m/Y H:i'))
            ->action('Ver vaga', route('recrutamentos.index'));
    }
}
