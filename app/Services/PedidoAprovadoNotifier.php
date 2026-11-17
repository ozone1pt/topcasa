<?php

namespace App\Services;

use App\Jobs\NotificarNovoOrcamento;
use App\Jobs\NotificarNovoRecrutamento;
use App\Models\EmailNotificacao;
use App\Models\PedidoOrcamento;
use App\Models\PedidoRecrutamento;

/**
 * Emails sent when a pedido de orçamento / vaga de recrutamento is approved
 * in the CRM (the same two emails that used to go out on submission):
 * the "publicado" confirmation to whoever submitted it, then the heads-up to
 * the professionals. Both run right after the response (this host has no
 * queue worker), and never twice for the same pedido.
 */
class PedidoAprovadoNotifier
{
    public function __construct(private readonly PedidoPublicadoNotifier $confirmacao)
    {
    }

    /**
     * @param  'orcamento'|'recrutamento'  $tipo
     * @return bool whether the emails were scheduled (false = already sent before)
     */
    public function notificar(string $tipo, PedidoOrcamento|PedidoRecrutamento $pedido): bool
    {
        $jaEnviado = EmailNotificacao::where('pedido_id', $pedido->id)
            ->whereIn('tipo', [$tipo, $tipo.'_confirmacao'])
            ->exists();

        if ($jaEnviado) {
            return false;
        }

        [$id, $email, $titulo] = [$pedido->id, $pedido->email, $pedido->titulo];

        app()->terminating(fn () => $this->confirmacao->notificar($tipo, $id, $email, $titulo));

        if ($tipo === 'orcamento') {
            NotificarNovoOrcamento::dispatchAfterResponse($pedido);
        } else {
            NotificarNovoRecrutamento::dispatchAfterResponse($pedido);
        }

        return true;
    }
}
