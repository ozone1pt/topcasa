<?php

namespace App\Console\Commands;

use App\Models\Anuncio;
use App\Notifications\AnuncioExpiradoNotification;
use Illuminate\Console\Command;

class ExpirarAnuncios extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:expirar-anuncios';

    /**
     * @var string
     */
    protected $description = 'Marca como expirados os anúncios aprovados cuja data de expiração já passou.';

    public function handle(): int
    {
        $anuncios = Anuncio::where('status', 'aprovado')
            ->whereNotNull('data_expiracao')
            ->where('data_expiracao', '<=', now())
            ->with('user')
            ->get();

        foreach ($anuncios as $anuncio) {
            $anuncio->update(['status' => 'expirado']);
            $anuncio->user->notify(new AnuncioExpiradoNotification($anuncio));
        }

        $this->info("Anúncios expirados: {$anuncios->count()}");

        return self::SUCCESS;
    }
}
