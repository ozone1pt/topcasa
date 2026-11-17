<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\EmailNotificacao;
use App\Models\PedidoOrcamento;
use App\Models\PedidoRecrutamento;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'utilizadores_pendentes' => User::where('status', 'pendente')->count(),
                'anuncios_pendentes' => Anuncio::where('status', 'pendente')->count(),
                'anuncios_aprovados' => Anuncio::where('status', 'aprovado')->count(),
                'avaliacoes_pendentes' => Review::where('status', 'pendente')->count(),
                'orcamentos_total' => PedidoOrcamento::count(),
                'recrutamentos_ativos' => PedidoRecrutamento::where('status', 'ativo')->count(),
                'utilizadores_total' => User::count(),
                'anuncios_total' => Anuncio::count(),
                'recrutamentos_total' => PedidoRecrutamento::count(),
                // Every email the site has sent (see email_notificacoes).
                'emails_enviados' => (int) EmailNotificacao::where('sucesso', true)->sum('destinatarios_count'),
                // "Total de contactos gerados": every phone/WhatsApp reveal on
                // an anúncio, every pedido de orçamento contact reveal, and
                // every contact-form message - the same counters already
                // surfaced individually elsewhere in the app (Analytics,
                // homepage stats).
                'contactos_total' => (int) Anuncio::sum('phone_clicks')
                    + (int) Anuncio::sum('whatsapp_clicks')
                    + (int) Anuncio::sum('email_clicks')
                    + (int) Anuncio::sum('message_count')
                    + (int) PedidoOrcamento::sum('contacto_clicks'),
            ],
        ]);
    }
}
