<?php

namespace App\Notifications;

use App\Models\PedidoOrcamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrcamentoNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PedidoOrcamento $pedido)
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
        $pedido = $this->pedido;

        $mail = (new MailMessage)
            ->subject('Novo pedido de orçamento: '.($pedido->titulo ?: 'Pedido de orçamento'))
            ->line('Foi publicado um novo pedido de orçamento.');

        // Each detail on its own line (the contact details stay hidden until
        // the professional opens the pedido and clicks "Ver contacto").
        if (trim((string) $pedido->titulo) !== '') {
            $mail->line('**Serviço:** '.trim($pedido->titulo));
        }
        if (trim((string) $pedido->nome) !== '') {
            $mail->line('**Cliente:** '.trim($pedido->nome));
        }
        if ($pedido->distrito) {
            $mail->line('**Distrito:** '.$pedido->distrito->nome);
        }

        $mail->line('**Data:** '.($pedido->created_at ?? now())->timezone('Europe/Lisbon')->format('d/m/Y H:i'));

        return $mail
            ->line(\Illuminate\Support\Str::limit($pedido->descricao, 200))
            ->action('Ver pedido', route('orcamentos.index'));
    }
}
