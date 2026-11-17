<?php

namespace App\Console\Commands;

use App\Models\Anuncio;
use App\Models\Configuracao;
use App\Notifications\AnuncioExpiracaoProximaNotification;
use Illuminate\Console\Command;

class AvisarExpiracaoAnuncios extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:avisar-expiracao-anuncios';

    /**
     * @var string
     */
    protected $description = 'Avisa donos de anúncios cuja expiração se aproxima (uma vez por anúncio).';

    public function handle(): int
    {
        $diasAviso = (int) Configuracao::get('dias_aviso_expiracao', 5);
        $limite = now()->addDays($diasAviso);

        $anuncios = Anuncio::where('status', 'aprovado')
            ->whereNotNull('data_expiracao')
            ->where('data_expiracao', '<=', $limite)
            ->where('data_expiracao', '>', now())
            ->whereNull('aviso_expiracao_enviado_em')
            ->with('user')
            ->get();

        foreach ($anuncios as $anuncio) {
            $anuncio->user->notify(new AnuncioExpiracaoProximaNotification($anuncio));
            $anuncio->update(['aviso_expiracao_enviado_em' => now()]);
        }

        $this->info("Avisos enviados: {$anuncios->count()}");

        return self::SUCCESS;
    }
}
