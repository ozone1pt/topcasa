<?php

namespace App\Jobs;

use App\Models\NewsletterSubscriber;
use App\Notifications\NewsletterCampaignNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class EnviarCampanhaNewsletter implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $assunto, private readonly string $corpo)
    {
    }

    public function handle(): void
    {
        NewsletterSubscriber::where('ativo', true)
            ->chunkById(200, function ($subscribers) {
                Notification::send($subscribers, new NewsletterCampaignNotification($this->assunto, $this->corpo));
            });
    }
}
