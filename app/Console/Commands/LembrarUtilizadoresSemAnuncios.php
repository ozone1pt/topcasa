<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\LembreteAnuncioNotification;
use Illuminate\Console\Command;

class LembrarUtilizadoresSemAnuncios extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:lembrar-utilizadores-sem-anuncios';

    /**
     * @var string
     */
    protected $description = 'Lembra utilizadores aprovados sem nenhum anúncio publicado (uma vez, via lembrete_anuncio_status).';

    public function handle(): int
    {
        $users = User::where('status', 'aprovado')
            ->where('lembrete_anuncio_status', 0)
            ->where('created_at', '<=', now()->subDays(7))
            ->doesntHave('anuncios')
            ->get();

        foreach ($users as $user) {
            $user->notify(new LembreteAnuncioNotification);
            $user->update(['lembrete_anuncio_status' => 1]);
        }

        $this->info("Lembretes enviados: {$users->count()}");

        return self::SUCCESS;
    }
}
