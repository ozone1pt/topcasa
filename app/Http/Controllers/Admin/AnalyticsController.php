<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\ContactoEvento;
use App\Models\EmailNotificacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Aggregates the denormalized view/click counters already kept on anuncios
 * (views, phone_clicks, email_clicks, whatsapp_clicks, message_count) into
 * a CSV-exportable table. The legacy site logged every single view/click as
 * its own row in a separate `views` table for finer-grained analytics; we
 * deliberately did not port that events table (see Fase 2/4 notes) and
 * report on the summary counters instead, which covers the same
 * "cliques por anúncio, exportável" need without the extra subsystem.
 */
class AnalyticsController extends Controller
{
    public function index(): View
    {
        $anuncios = Anuncio::orderByDesc('views')
            ->select(['id', 'titulo', 'slug', 'views', 'phone_clicks', 'email_clicks', 'whatsapp_clicks', 'message_count'])
            ->paginate(30);

        return view('admin.analytics.index', ['anuncios' => $anuncios]);
    }

    /**
     * "Contactos" area: origem (telefone/whatsapp/site), por profissional and
     * por período come from the lightweight contacto_eventos log (see
     * App\Models\ContactoEvento); it's only being written to going forward,
     * so lifetime origem totals instead read the same counters already used
     * on the homepage/Anuncios analytics for consistency.
     */
    public function contactos(Request $request): View
    {
        $dias = (int) $request->integer('dias', 30) ?: 30;
        $desde = now()->subDays($dias)->startOfDay();

        $porOrigemTotal = [
            'telefone' => (int) Anuncio::sum('phone_clicks') + (int) \App\Models\PedidoOrcamento::sum('contacto_clicks'),
            'whatsapp' => (int) Anuncio::sum('whatsapp_clicks'),
            'site' => (int) Anuncio::sum('message_count'),
        ];

        $porPeriodo = ContactoEvento::where('created_at', '>=', $desde)
            ->select(DB::raw('DATE(created_at) as dia'), 'origem', DB::raw('COUNT(*) as total'))
            ->groupBy('dia', 'origem')
            ->orderBy('dia')
            ->get()
            ->groupBy('dia');

        $porProfissional = User::query()
            ->withSum('anuncios as soma_telefone', 'phone_clicks')
            ->withSum('anuncios as soma_whatsapp', 'whatsapp_clicks')
            ->withSum('anuncios as soma_site', 'message_count')
            ->get()
            ->map(fn ($u) => (object) [
                'user' => $u,
                'total' => (int) $u->soma_telefone + (int) $u->soma_whatsapp + (int) $u->soma_site,
            ])
            ->filter(fn ($row) => $row->total > 0)
            ->sortByDesc('total')
            ->take(20);

        // Emails the site has sent (each row of email_notificacoes is one send
        // attempt; destinatarios_count is how many inboxes it went to).
        $enviados = EmailNotificacao::where('sucesso', true);
        $recentes = EmailNotificacao::latest('id')->limit(15)->get();

        $emails = [
            'total' => (int) (clone $enviados)->sum('destinatarios_count'),
            'periodo' => (int) (clone $enviados)->where('enviado_em', '>=', $desde)->sum('destinatarios_count'),
            'falhados' => EmailNotificacao::where('sucesso', false)->whereNotNull('enviado_em')->count(),
            'porTipo' => (clone $enviados)->select('tipo', DB::raw('SUM(destinatarios_count) as total'))->groupBy('tipo')->pluck('total', 'tipo'),
            'recentes' => $recentes,
            'titulos' => Anuncio::whereIn('id', $recentes->pluck('anuncio_id')->filter())->pluck('titulo', 'id'),
            'entregaReal' => ! in_array(config('mail.default'), ['log', 'array'], true),
        ];

        return view('admin.contactos.index', [
            'dias' => $dias,
            'porOrigemTotal' => $porOrigemTotal,
            'porPeriodo' => $porPeriodo,
            'porProfissional' => $porProfissional,
            'emails' => $emails,
        ]);
    }

    public function exportar(): StreamedResponse
    {
        $filename = 'analytics-anuncios-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Título', 'Views', 'Cliques telefone', 'Cliques email', 'Cliques WhatsApp', 'Mensagens']);

            Anuncio::orderByDesc('views')->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $anuncio) {
                    fputcsv($handle, [
                        $anuncio->id, $anuncio->titulo, $anuncio->views,
                        $anuncio->phone_clicks, $anuncio->email_clicks, $anuncio->whatsapp_clicks, $anuncio->message_count,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
