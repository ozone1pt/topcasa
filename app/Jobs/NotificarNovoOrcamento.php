<?php

namespace App\Jobs;

use App\Models\EmailNotificacao;
use App\Models\PedidoOrcamento;
use App\Models\User;
use App\Notifications\NewOrcamentoNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class NotificarNovoOrcamento implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly PedidoOrcamento $pedido)
    {
    }

    public function handle(): void
    {
        $log = EmailNotificacao::create([
            'tipo' => 'orcamento',
            'pedido_id' => $this->pedido->id,
        ]);

        // Sending to every professional takes a while and runs after the response.
        set_time_limit(300);

        $count = 0;
        $falhas = 0;
        $ultimoErro = null;

        try {
            User::profissionaisAtivos()
                ->where('notificar_orcamentos', true)
                ->chunkById(200, function ($users) use (&$count, &$falhas, &$ultimoErro) {
                    foreach ($users as $user) {
                        // One bad address must not stop the others.
                        try {
                            $user->notifyNow(new NewOrcamentoNotification($this->pedido));
                            $count++;
                        } catch (Throwable $e) {
                            $falhas++;
                            $ultimoErro = $e->getMessage();
                        }
                    }
                });

            $log->update([
                'sucesso' => $count > 0 || $falhas === 0,
                'destinatarios_count' => $count,
                'erro' => $falhas ? mb_substr("{$falhas} falharam: {$ultimoErro}", 0, 500) : null,
                'enviado_em' => now(),
            ]);
        } catch (Throwable $e) {
            $log->update(['sucesso' => false, 'erro' => mb_substr($e->getMessage(), 0, 500), 'enviado_em' => now()]);
            report($e);
        }
    }
}
