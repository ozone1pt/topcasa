<?php

namespace App\Services;

use App\Models\Anuncio;
use App\Models\Configuracao;
use App\Models\User;
use Illuminate\Http\Request;

class PublicacaoAnuncio
{
    public function __construct(
        private readonly ImageUploadService $images,
        private readonly ModeracaoService $moderacao,
        private readonly AnuncioPublicadoNotifier $publicadoNotifier,
    ) {}

    /**
     * Creates the anúncio exactly as the website does once the account is
     * known: pending, unless automatic moderation is on.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(User $user, array $dados, Request $request): Anuncio
    {
        $anuncio = Anuncio::create([
            ...collect($dados)->only([
                'titulo', 'minha_empresa', 'categoria_id', 'distrito_id',
                'descricao', 'contacto', 'email', 'youtube_link', 'instagram_link',
            ])->all(),
            'user_id' => $user->id,
            'destacar' => $request->boolean('destacar'),
            'certificacao_profissional' => $request->boolean('certificacao_profissional'),
            'seguro_responsabilidade_civil' => $request->boolean('seguro_responsabilidade_civil'),
            'status' => 'pendente',
        ]);

        if (Configuracao::get('auto_approval_enabled') === '1') {
            if ($this->moderacao->verificar($dados['titulo'], $dados['descricao'])) {
                $anuncio->aprovar();
                $this->publicadoNotifier->notificar($anuncio);
            } else {
                $anuncio->update(['status' => 'rejeitado']);
            }
        }

        $user->update(['lembrete_anuncio_status' => 0]);

        $this->images->storeGaleria($anuncio, $request);

        return $anuncio->fresh(['categoria', 'distrito', 'concelho', 'galeria', 'user']) ?? $anuncio;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Anuncio $anuncio, array $dados, Request $request): Anuncio
    {
        $anuncio->update([
            ...collect($dados)->only([
                'titulo', 'minha_empresa', 'categoria_id', 'distrito_id',
                'descricao', 'contacto', 'email', 'youtube_link', 'instagram_link',
            ])->all(),
            'destacar' => $request->boolean('destacar'),
            'certificacao_profissional' => $request->boolean('certificacao_profissional'),
            'seguro_responsabilidade_civil' => $request->boolean('seguro_responsabilidade_civil'),
        ]);

        $this->images->storeGaleria($anuncio, $request);

        return $anuncio->fresh(['categoria', 'distrito', 'concelho', 'galeria', 'user']) ?? $anuncio;
    }

    public function reativar(Anuncio $anuncio): void
    {
        abort_unless($anuncio->status === 'expirado', 400);

        $anuncio->aprovar();
    }

    public function apagar(Anuncio $anuncio): void
    {
        $anuncio->loadMissing('galeria');

        foreach ($anuncio->galeria as $imagem) {
            $this->images->delete($imagem->imagem);
        }

        $anuncio->delete();
    }
}
