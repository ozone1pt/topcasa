<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class NewsletterCampaignNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $assunto, private readonly string $corpo)
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
        $cancelar = URL::signedRoute('newsletter.destroy', ['email' => $notifiable->email]);

        return (new MailMessage)
            ->subject($this->assunto)
            ->line($this->corpo)
            ->line('')
            ->line("Para cancelar a subscrição: {$cancelar}");
    }
}
