<?php

namespace App\Services;

use App\Models\EmailNotificacao;
use App\Notifications\PedidoPublicadoNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Emails the person who published a pedido de orçamento / vaga de
 * recrutamento a confirmation, and logs the attempt in email_notificacoes
 * (so the CRM counts it).
 */
class PedidoPublicadoNotifier
{
    /** @param 'orcamento'|'recrutamento' $tipo */
    public function notificar(string $tipo, int $pedidoId, ?string $email, ?string $titulo): void
    {
        $destino = trim((string) $email);

        if (! filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $log = EmailNotificacao::create([
            'tipo' => $tipo.'_confirmacao',
            'pedido_id' => $pedidoId,
            'destinatarios_count' => 1,
        ]);

        try {
            Notification::route('mail', $destino)->notify(new PedidoPublicadoNotification($tipo, $titulo));

            $log->update(['sucesso' => true, 'enviado_em' => now()]);
        } catch (\Throwable $e) {
            report($e);

            $log->update(['sucesso' => false, 'erro' => Str::limit($e->getMessage(), 500), 'enviado_em' => now()]);
        }
    }
}
