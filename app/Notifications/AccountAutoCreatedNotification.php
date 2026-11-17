<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountAutoCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $plainPassword)
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
            ->subject('A tua conta TopCasa')
            ->greeting('Bem-vindo(a) à TopCasa!')
            ->line('Criámos uma conta para ti para poderes gerir o teu anúncio.')
            ->line('Utilizador: '.$notifiable->username)
            ->line('Password temporária: '.$this->plainPassword)
            ->line('Recomendamos que alteres esta password assim que entrares.')
            ->action('Entrar na minha conta', route('login'));
    }
}
