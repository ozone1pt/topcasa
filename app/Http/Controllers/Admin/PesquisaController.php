<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anuncio;
use App\Models\PedidoOrcamento;
use App\Models\PedidoRecrutamento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The CRM's global search box: one query looked up across anúncios,
 * clientes, pedidos de orçamento and pedidos de recrutamento.
 */
class PesquisaController extends Controller
{
    private const LIMITE = 15;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $resultados = [
            'anuncios' => collect(),
            'clientes' => collect(),
            'orcamentos' => collect(),
            'recrutamentos' => collect(),
        ];

        if (mb_strlen($q) >= 2) {
            // Escape LIKE wildcards so searching for "50%" or "a_b" is literal.
            $termo = '%'.addcslashes($q, '%_\\').'%';

            $resultados['anuncios'] = Anuncio::query()
                ->with(['categoria', 'user'])
                ->where(fn ($w) => $w->where('titulo', 'like', $termo)
                    ->orWhere('minha_empresa', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('contacto', 'like', $termo)
                    ->orWhere('descricao', 'like', $termo))
                ->latest()
                ->limit(self::LIMITE)
                ->get();

            $resultados['clientes'] = User::query()
                ->withCount('anuncios')
                ->where(fn ($w) => $w->where('nome', 'like', $termo)
                    ->orWhere('apelido', 'like', $termo)
                    ->orWhere('username', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('telefone', 'like', $termo)
                    ->orWhere('nif', 'like', $termo)
                    ->orWhere('empresa_nome', 'like', $termo))
                ->latest()
                ->limit(self::LIMITE)
                ->get();

            $resultados['orcamentos'] = PedidoOrcamento::query()
                ->where(fn ($w) => $w->where('titulo', 'like', $termo)
                    ->orWhere('nome', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('telefone', 'like', $termo)
                    ->orWhere('descricao', 'like', $termo))
                ->latest()
                ->limit(self::LIMITE)
                ->get();

            $resultados['recrutamentos'] = PedidoRecrutamento::query()
                ->where(fn ($w) => $w->where('titulo', 'like', $termo)
                    ->orWhere('empresa_nome', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('telefone', 'like', $termo)
                    ->orWhere('descricao', 'like', $termo))
                ->latest()
                ->limit(self::LIMITE)
                ->get();
        }

        return view('admin.pesquisa.index', [
            'q' => $q,
            'resultados' => $resultados,
            'total' => collect($resultados)->sum(fn ($r) => $r->count()),
        ]);
    }
}
