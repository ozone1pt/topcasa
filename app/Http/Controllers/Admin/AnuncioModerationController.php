<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAnuncioRequest;
use App\Models\Anuncio;
use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\GaleriaAnuncio;
use App\Services\AnuncioPublicadoNotifier;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The CRM's "Anúncios" area - reads/writes the exact same `anuncios` table
 * and fields the public site uses (see App\Models\Anuncio and the public
 * anuncios._form partial, which this controller's edit view also reuses).
 * Nothing here is a parallel structure: aprovar()/status updates here are
 * the same ones the public AnuncioController and Anuncio model expose.
 */
class AnuncioModerationController extends Controller
{
    public function __construct(
        private readonly ImageUploadService $images,
        private readonly AnuncioPublicadoNotifier $publicadoNotifier,
    ) {
    }

    public function index(Request $request): View
    {
        $anuncios = Anuncio::query()
            ->with(['user', 'categoria', 'distrito', 'galeria'])
            ->when($request->filled('busca'), function ($q) use ($request) {
                $termo = '%'.$request->string('busca').'%';
                $q->where(fn ($q) => $q->where('titulo', 'like', $termo)
                    ->orWhere('minha_empresa', 'like', $termo)
                    ->orWhere('email', 'like', $termo)
                    ->orWhere('contacto', 'like', $termo));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('categoria_id'), fn ($q) => $q->where('categoria_id', $request->integer('categoria_id')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.anuncios.index', [
            'anuncios' => $anuncios,
            'categorias' => Categoria::orderBy('nome')->get(),
        ]);
    }

    public function edit(Anuncio $anuncio): View
    {
        return view('admin.anuncios.edit', [
            'anuncio' => $anuncio->load('galeria'),
            'categorias' => Categoria::orderBy('nome')->get(),
            'distritos' => Distrito::orderBy('nome')->get(),
        ]);
    }

    public function update(UpdateAnuncioRequest $request, Anuncio $anuncio): RedirectResponse
    {
        $data = $request->validated();

        $anuncio->update([
            ...collect($data)->only([
                'titulo', 'minha_empresa', 'categoria_id', 'distrito_id',
                'descricao', 'contacto', 'email', 'youtube_link', 'instagram_link',
            ])->all(),
            'destacar' => $request->boolean('destacar'),
            'certificacao_profissional' => $request->boolean('certificacao_profissional'),
            'seguro_responsabilidade_civil' => $request->boolean('seguro_responsabilidade_civil'),
        ]);

        $this->images->storeGaleria($anuncio, $request);

        return redirect()->route('crm.anuncios.index')->with('status', 'Anúncio atualizado.');
    }

    /**
     * Copies the anúncio's fields (and gallery) into a brand new row that
     * starts as "rascunho" - it is never published automatically, matching
     * "o novo anúncio deve começar como rascunho".
     */
    public function duplicar(Anuncio $anuncio): RedirectResponse
    {
        $anuncio->loadMissing('galeria');

        $copia = Anuncio::create([
            'user_id' => $anuncio->user_id,
            'titulo' => $anuncio->titulo.' (cópia)',
            'minha_empresa' => $anuncio->minha_empresa,
            'certificacao_profissional' => $anuncio->certificacao_profissional,
            'seguro_responsabilidade_civil' => $anuncio->seguro_responsabilidade_civil,
            'descricao' => $anuncio->descricao,
            'email' => $anuncio->email,
            'contacto' => $anuncio->contacto,
            'categoria_id' => $anuncio->categoria_id,
            'distrito_id' => $anuncio->distrito_id,
            'concelho_id' => $anuncio->concelho_id,
            'youtube_link' => $anuncio->youtube_link,
            'instagram_link' => $anuncio->instagram_link,
            'status' => 'rascunho',
        ]);

        foreach ($anuncio->galeria as $ordem => $imagem) {
            GaleriaAnuncio::create([
                'anuncio_id' => $copia->id,
                'imagem' => $imagem->imagem,
                'ordem' => $ordem,
            ]);
        }

        return redirect()->route('crm.anuncios.edit', $copia)->with('status', 'Anúncio duplicado como rascunho. Revê e publica quando estiver pronto.');
    }

    /**
     * Ativar/desativar: an aprovado anúncio gets pulled from the public site
     * (status "rejeitado", same field the public site already checks via
     * Anuncio::scopePublicados()); a non-aprovado one gets approved through
     * the same aprovar() the moderation flow uses.
     */
    public function toggle(Anuncio $anuncio): RedirectResponse
    {
        if ($anuncio->isAprovado()) {
            $anuncio->update(['status' => 'rejeitado', 'motivo_rejeicao' => 'Desativado manualmente no CRM.']);

            return back()->with('status', 'Anúncio desativado.');
        }

        $anuncio->aprovar();

        return back()->with('status', 'Anúncio ativado.');
    }

    public function destroy(Anuncio $anuncio): RedirectResponse
    {
        foreach ($anuncio->galeria as $imagem) {
            $this->images->delete($imagem->imagem);
        }

        $anuncio->delete();

        return redirect()->route('crm.anuncios.index')->with('status', 'Anúncio eliminado.');
    }

    public function aprovar(Anuncio $anuncio): RedirectResponse
    {
        $jaPublicado = $anuncio->isAprovado();

        $anuncio->aprovar();

        // Confirmation email to the professional (only the first time it goes live).
        if (! $jaPublicado) {
            $this->publicadoNotifier->notificar($anuncio);
        }

        return back()->with('status', 'Anúncio aprovado.');
    }

    public function rejeitar(Request $request, Anuncio $anuncio): RedirectResponse
    {
        $request->validate(['motivo_rejeicao' => ['nullable', 'string', 'max:500']]);

        $anuncio->update([
            'status' => 'rejeitado',
            'motivo_rejeicao' => $request->string('motivo_rejeicao')->toString() ?: null,
        ]);

        return back()->with('status', 'Anúncio rejeitado.');
    }
}
