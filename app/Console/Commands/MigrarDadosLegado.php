<?php

namespace App\Console\Commands;

use App\Models\Configuracao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-time import from the legacy site's database (restored into the
 * 'legado' connection, read-only) into the new schema. Run once against a
 * freshly migrated database, before go-live (see Fase 12).
 *
 * Design notes (see plan/Fase 11 for the full audit that produced these):
 *  - distritos/concelhos/categorias are NOT imported here: they were seeded
 *    in Fase 0 directly from the legacy dump's own reference data, using
 *    the SAME ids, so every legacy distrito_id/concelho_id/categoria_id
 *    foreign key already resolves correctly without any id translation.
 *  - Every other table preserves its legacy primary key on import, so
 *    foreign keys between imported tables don't need translation either.
 *  - Tables deliberately not ported (see Fase 2/4/5/7 commit notes):
 *    the old per-event views/views_orcamento/views_recrutamento/
 *    anuncio_clicks logs (we use denormalized counters instead - those
 *    counter columns ARE copied), glossario_* (Fase 5 rebuilt this
 *    smaller/simpler on purpose), magic_links (dead feature), admin_logs,
 *    logs_expiracao, newsletter_attachments, analytics_*.
 *  - Data-quality issues found in the real dump are handled explicitly
 *    (not silently): orphaned foreign keys are skipped or nulled per
 *    column nullability, missing/duplicate slugs are regenerated.
 */
class MigrarDadosLegado extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:migrar-dados-legado
        {--source= : Caminho para a pasta _public_html extraída (uploads/assets), para copiar ficheiros}
        {--skip-files : Não copiar ficheiros de imagem, só dados}';

    /**
     * @var string
     */
    protected $description = 'Importa dados da base de dados legada (ligação "legado") para o novo esquema.';

    private ?string $sourcePath = null;

    public function handle(): int
    {
        if (! DB::connection('legado')->getPdo()) {
            $this->error('Não foi possível ligar à base de dados legada.');

            return self::FAILURE;
        }

        $this->sourcePath = $this->option('source');
        $copyFiles = ! $this->option('skip-files') && $this->sourcePath && is_dir($this->sourcePath);

        if (! $this->option('skip-files') && ! $copyFiles) {
            $this->warn('--source não indicado ou inválido: os ficheiros de imagem NÃO serão copiados (só dados).');
        }

        $this->limparTabelasDestino();

        $this->migrarUsers();
        $this->migrarAnuncios();
        $this->migrarGaleriaAnuncios($copyFiles);
        $this->migrarFavoritos();
        $this->migrarMensagens();
        $this->migrarReviews();
        $this->migrarPedidosOrcamento($copyFiles);
        $this->migrarDesbloqueiosOrcamento();
        $this->migrarPedidosRecrutamento($copyFiles);
        $this->migrarNewsletter();
        $this->migrarBlogPosts($copyFiles);
        $this->migrarBanners($copyFiles);
        $this->migrarEmpresasApoiadoras($copyFiles);
        $this->migrarAlertas();
        $this->migrarConfiguracoes();

        $this->info('Migração de dados concluída.');

        return self::SUCCESS;
    }

    /**
     * Makes the command safely re-runnable against a freshly-migrated (and
     * possibly seeded, e.g. with a placeholder admin from Fase 0/1) database:
     * truncate everything this command is about to (re)populate, in an order
     * that respects foreign keys, rather than requiring the operator to run
     * `migrate:fresh` by hand between attempts.
     */
    private function limparTabelasDestino(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'alertas', 'newsletter_subscribers', 'empresas_apoiadoras', 'banners', 'blog_posts',
            'desbloqueios_orcamento', 'pedidos_recrutamento', 'pedidos_orcamento',
            'reviews', 'mensagens', 'favoritos', 'galeria_anuncios', 'anuncios', 'users',
        ] as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }

    private function migrarUsers(): void
    {
        $rows = DB::connection('legado')->table('users')->orderBy('id')->get();

        $data = $rows->map(fn ($u) => [
            'id' => $u->id,
            'username' => $u->username,
            'email' => $u->email ?: null,
            'nome' => $u->nome,
            'apelido' => $u->apelido,
            'telefone' => $u->telefone,
            'nif' => $u->nif ? substr(trim($u->nif), 0, 9) : null,
            'tipo_conta' => $u->tipo_conta,
            'empresa_nome' => $u->empresa_nome,
            'password' => $u->password,
            'user_type' => $u->user_type,
            'status' => $u->status,
            'lembrete_anuncio_status' => (int) ($u->lembrete_anuncio_status ?? 0),
            'email_verified_at' => $u->email_verified_at,
            'created_at' => $u->created_at,
            'updated_at' => $u->updated_at,
        ])->all();

        DB::table('users')->insert($data);
        $this->info('users: '.count($data));
    }

    private function mapAnuncioStatus(int $status): string
    {
        return match ($status) {
            1 => 'aprovado',
            2 => 'rejeitado',
            default => 'pendente',
        };
    }

    private function migrarAnuncios(): void
    {
        $userIds = DB::table('users')->pluck('id')->flip();
        $usedSlugs = [];
        $skipped = 0;

        DB::connection('legado')->table('anuncios')->orderBy('id')->chunk(300, function ($rows) use ($userIds, &$usedSlugs, &$skipped) {
            $batch = [];

            foreach ($rows as $a) {
                if (! isset($userIds[$a->user_id])) {
                    $skipped++;

                    continue;
                }

                $slug = $a->slug ?: Str::slug($a->titulo);
                $slug = $slug ?: 'anuncio';
                if (isset($usedSlugs[$slug])) {
                    $slug = $slug.'-'.$a->id;
                }
                $usedSlugs[$slug] = true;

                $batch[] = [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'titulo' => $a->titulo,
                    'slug' => $slug,
                    'minha_empresa' => $a->minha_empresa,
                    'certificacao_profissional' => (bool) $a->certificacao_profissional,
                    'seguro_responsabilidade_civil' => (bool) $a->seguro_responsabilidade_civil,
                    'descricao' => $a->descricao,
                    'email' => $a->email,
                    'contacto' => $a->contacto,
                    'categoria_id' => $a->categoria_id,
                    'distrito_id' => $a->distrito_id,
                    'concelho_id' => $a->concelho_id,
                    'destacar' => (bool) $a->destacar,
                    'youtube_link' => $a->youtube_link ?: null,
                    'status' => $this->mapAnuncioStatus((int) $a->status),
                    'data_expiracao' => $a->data_expiracao,
                    'views' => (int) ($a->views ?? 0),
                    'whatsapp_clicks' => (int) ($a->whatsapp_clicks ?? 0),
                    'phone_clicks' => (int) ($a->phone_clicks ?? 0),
                    'email_clicks' => (int) ($a->email_clicks ?? 0),
                    'message_count' => (int) ($a->message_count ?? 0),
                    'created_at' => $a->created_at,
                    'updated_at' => $a->updated_at,
                ];
            }

            if ($batch) {
                DB::table('anuncios')->insert($batch);
            }
        });

        $this->info("anuncios: importados (utilizador inexistente ignorado: {$skipped})");
    }

    private function copyFile(?string $legacyRelativePath, string $targetDir): ?string
    {
        if (! $this->sourcePath || ! $legacyRelativePath) {
            return null;
        }

        $legacyRelativePath = ltrim($legacyRelativePath, '/');
        $source = $this->sourcePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $legacyRelativePath);

        if (! is_file($source)) {
            return null;
        }

        $filename = Str::random(20).'.'.pathinfo($source, PATHINFO_EXTENSION);
        $target = "{$targetDir}/{$filename}";

        Storage::disk('public')->put($target, File::get($source));

        return $target;
    }

    private function migrarGaleriaAnuncios(bool $copyFiles): void
    {
        $anuncioIds = DB::table('anuncios')->pluck('id')->flip();
        $total = 0;

        DB::connection('legado')->table('galeria_anuncios')->orderBy('id')->chunk(300, function ($rows) use ($anuncioIds, $copyFiles, &$total) {
            $batch = [];

            foreach ($rows as $g) {
                if (! isset($anuncioIds[$g->anuncio_id])) {
                    continue;
                }

                $imagem = $copyFiles ? $this->copyFile($g->imagem, 'anuncios') : null;

                if (! $imagem) {
                    continue;
                }

                $batch[] = [
                    'id' => $g->id,
                    'anuncio_id' => $g->anuncio_id,
                    'imagem' => $imagem,
                    'descricao' => $g->descricao,
                    'ordem' => (int) ($g->ordem ?? 0),
                    'created_at' => $g->created_at,
                    'updated_at' => $g->created_at,
                ];
            }

            if ($batch) {
                DB::table('galeria_anuncios')->insert($batch);
                $total += count($batch);
            }
        });

        $this->info("galeria_anuncios: {$total}".($copyFiles ? '' : ' (0 - ficheiros não copiados)'));
    }

    private function migrarFavoritos(): void
    {
        $userIds = DB::table('users')->pluck('id')->flip();
        $anuncioIds = DB::table('anuncios')->pluck('id')->flip();

        $rows = DB::connection('legado')->table('favoritos')->orderBy('id')->get()
            ->filter(fn ($f) => isset($userIds[$f->user_id]) && isset($anuncioIds[$f->anuncio_id]))
            ->map(fn ($f) => [
                'id' => $f->id,
                'user_id' => $f->user_id,
                'anuncio_id' => $f->anuncio_id,
                'created_at' => $f->created_at,
            ])->all();

        if ($rows) {
            DB::table('favoritos')->insert($rows);
        }

        $this->info('favoritos: '.count($rows));
    }

    private function migrarMensagens(): void
    {
        $userIds = DB::table('users')->pluck('id')->flip();
        $anuncioIds = DB::table('anuncios')->pluck('id')->flip();
        $total = 0;

        DB::connection('legado')->table('mensagens')->orderBy('id')->chunk(300, function ($rows) use ($userIds, $anuncioIds, &$total) {
            $batch = [];

            foreach ($rows as $m) {
                if (! isset($anuncioIds[$m->anuncio_id]) || ! isset($userIds[$m->destinatario_id])) {
                    continue;
                }

                $batch[] = [
                    'id' => $m->id,
                    'anuncio_id' => $m->anuncio_id,
                    'remetente_id' => isset($userIds[$m->remetente_id]) ? $m->remetente_id : null,
                    'remetente_nome' => $m->remetente_nome,
                    'remetente_email' => $m->remetente_email,
                    'destinatario_id' => $m->destinatario_id,
                    'mensagem' => $m->mensagem,
                    'lida' => (bool) $m->lida,
                    'parent_id' => $m->parent_id,
                    'remetente_deleted_at' => $m->remetente_deleted_at,
                    'destinatario_deleted_at' => $m->destinatario_deleted_at,
                    'created_at' => $m->data_mensagem,
                    'updated_at' => $m->data_mensagem,
                ];
            }

            if ($batch) {
                DB::table('mensagens')->insertOrIgnore($batch);
                $total += count($batch);
            }
        });

        $this->info("mensagens: {$total}");
    }

    private function mapReviewStatus(string $status): string
    {
        return match ($status) {
            'approved' => 'aprovado',
            'rejected' => 'rejeitado',
            default => 'pendente',
        };
    }

    private function migrarReviews(): void
    {
        $anuncioIds = DB::table('anuncios')->pluck('id')->flip();
        $userIds = DB::table('users')->pluck('id')->flip();

        $rows = DB::connection('legado')->table('reviews')->orderBy('id')->get()
            ->filter(fn ($r) => isset($anuncioIds[$r->anuncio_id]) && isset($userIds[$r->user_id]) && isset($userIds[$r->anunciante_id]))
            ->map(fn ($r) => [
                'id' => $r->id,
                'anuncio_id' => $r->anuncio_id,
                'user_id' => $r->user_id,
                'anunciante_id' => $r->anunciante_id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'status' => $this->mapReviewStatus($r->status),
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ])->all();

        if ($rows) {
            DB::table('reviews')->insert($rows);
        }

        $this->info('reviews: '.count($rows));
    }

    private function migrarPedidosOrcamento(bool $copyFiles): void
    {
        $userIds = DB::table('users')->pluck('id')->flip();

        $rows = DB::connection('legado')->table('pedidos_orcamento')->orderBy('id')->get()->map(function ($p) use ($userIds, $copyFiles) {
            return [
                'id' => $p->id,
                'user_id' => isset($userIds[$p->user_id]) ? $p->user_id : null,
                'titulo' => $p->titulo,
                'nome' => $p->nome,
                'telefone' => $p->telefone,
                'email' => $p->email,
                'distrito_id' => $p->distrito_id,
                'descricao' => $p->descricao,
                'foto' => $copyFiles ? $this->copyFile($p->foto ? "assets/img/orcamentos/{$p->foto}" : null, 'orcamentos') : null,
                'termo_aceite' => (bool) $p->termo_aceite,
                'status' => $p->status === 'concluido' ? 'concluido' : 'validado',
                'views' => (int) ($p->views ?? 0),
                'created_at' => $p->data_criacao,
                'updated_at' => $p->data_criacao,
            ];
        })->all();

        if ($rows) {
            DB::table('pedidos_orcamento')->insert($rows);
        }

        $this->info('pedidos_orcamento: '.count($rows));
    }

    private function migrarDesbloqueiosOrcamento(): void
    {
        $pedidoIds = DB::table('pedidos_orcamento')->pluck('id')->flip();
        $userIds = DB::table('users')->pluck('id')->flip();

        $rows = DB::connection('legado')->table('compras_orcamento')->orderBy('id')->get()
            ->filter(fn ($c) => isset($pedidoIds[$c->pedido_id]) && isset($userIds[$c->profissional_id]))
            ->unique(fn ($c) => $c->pedido_id.'-'.$c->profissional_id)
            ->map(fn ($c) => [
                'id' => $c->id,
                'pedido_id' => $c->pedido_id,
                'user_id' => $c->profissional_id,
                'created_at' => $c->data_compra,
            ])->all();

        if ($rows) {
            DB::table('desbloqueios_orcamento')->insert($rows);
        }

        $this->info('desbloqueios_orcamento: '.count($rows));
    }

    private function migrarPedidosRecrutamento(bool $copyFiles): void
    {
        $userIds = DB::table('users')->pluck('id')->flip();

        $rows = DB::connection('legado')->table('pedidos_recrutamento')->orderBy('id')->get()->map(function ($p) use ($userIds, $copyFiles) {
            return [
                'id' => $p->id,
                'user_id' => isset($userIds[$p->user_id]) ? $p->user_id : null,
                'titulo' => $p->titulo,
                'empresa_nome' => $p->empresa_nome,
                'email' => $p->email,
                'telefone' => $p->telefone,
                'distrito_id' => $p->distrito_id,
                'descricao' => $p->descricao,
                'foto' => $copyFiles ? $this->copyFile($p->foto ? "assets/img/recrutamentos/{$p->foto}" : null, 'recrutamento') : null,
                'termo_aceite' => (bool) $p->termo_aceite,
                'status' => $p->status,
                'views' => (int) ($p->views ?? 0),
                'phone_clicks' => (int) ($p->phone_clicks ?? 0),
                'created_at' => $p->data_criacao,
                'updated_at' => $p->data_atualizacao,
            ];
        })->all();

        if ($rows) {
            DB::table('pedidos_recrutamento')->insert($rows);
        }

        $this->info('pedidos_recrutamento: '.count($rows));
    }

    private function migrarNewsletter(): void
    {
        // The live subscribe form wrote to `newsletter`, not the admin panel's
        // disconnected `newsletter_subscribers` table (a real bug in the
        // legacy site - see Fase 5 commit notes). We import from the one
        // that actually reflects real subscribers.
        $rows = DB::connection('legado')->table('newsletter')->orderBy('id')->get()
            ->unique('email')
            ->map(fn ($n) => [
                'id' => $n->id,
                'email' => $n->email,
                'nome' => $n->nome,
                'ativo' => (bool) $n->ativo,
                'created_at' => $n->created_at,
                'updated_at' => $n->updated_at,
            ])->all();

        if ($rows) {
            DB::table('newsletter_subscribers')->insert($rows);
        }

        $this->info('newsletter_subscribers: '.count($rows));
    }

    private function migrarBlogPosts(bool $copyFiles): void
    {
        $usedSlugs = [];

        $rows = DB::connection('legado')->table('blog_posts')->orderBy('id')->get()->map(function ($p) use (&$usedSlugs, $copyFiles) {
            $slug = $p->slug ?: Str::slug($p->titulo);
            if (isset($usedSlugs[$slug])) {
                $slug = $slug.'-'.$p->id;
            }
            $usedSlugs[$slug] = true;

            return [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'slug' => $slug,
                'conteudo' => $p->conteudo_seo,
                'imagem_destaque' => $copyFiles ? $this->copyFile($p->imagem_destaque, 'blog') : null,
                'status' => $p->status,
                'publicado_em' => $p->publicado_em,
                'views' => (int) ($p->views ?? 0),
                'topico_fonte' => $p->topico_fonte,
                'created_at' => $p->criado_em,
                'updated_at' => $p->criado_em,
            ];
        })->all();

        if ($rows) {
            DB::table('blog_posts')->insert($rows);
        }

        $this->info('blog_posts: '.count($rows));
    }

    private function migrarBanners(bool $copyFiles): void
    {
        $rows = DB::connection('legado')->table('banners')->orderBy('id')->get()->map(fn ($b) => [
            'id' => $b->id,
            'titulo' => $b->titulo,
            'descricao' => $b->descricao,
            'imagem_desktop' => $copyFiles ? $this->copyFile($b->imagem_desktop, 'banners') : null,
            'imagem_mobile' => $copyFiles ? $this->copyFile($b->imagem_mobile, 'banners') : null,
            'url_destino' => $b->url_destino,
            'localizacao' => in_array($b->localizacao, ['topo', 'meio', 'rodape']) ? $b->localizacao : 'topo',
            'ordem' => (int) ($b->ordem ?? 0),
            'ativo' => (bool) $b->ativo,
            'data_inicio' => $b->data_inicio,
            'data_fim' => $b->data_fim,
            'views' => (int) ($b->views ?? 0),
            'clicks' => (int) ($b->clicks ?? 0),
            'created_at' => $b->created_at,
            'updated_at' => $b->updated_at,
        ])->all();

        if ($rows) {
            DB::table('banners')->insert($rows);
        }

        $this->info('banners: '.count($rows));
    }

    private function migrarEmpresasApoiadoras(bool $copyFiles): void
    {
        $rows = DB::connection('legado')->table('empresas_apoiadoras')->orderBy('id')->get()->map(fn ($e) => [
            'id' => $e->id,
            'nome' => $e->nome,
            'logo' => ($copyFiles ? $this->copyFile($e->logo, 'empresas') : null) ?? '',
            'url' => $e->url,
            'descricao' => $e->descricao,
            'ativo' => (bool) $e->status,
            'ordem' => (int) ($e->ordem ?? 0),
            'created_at' => $e->created_at,
            'updated_at' => $e->updated_at,
        ])->filter(fn ($e) => $e['logo'] !== '')->all();

        if ($rows) {
            DB::table('empresas_apoiadoras')->insert($rows);
        }

        $this->info('empresas_apoiadoras: '.count($rows));
    }

    private function mapAlertaTipo(string $tipo): string
    {
        return match ($tipo) {
            'warning' => 'aviso',
            'success' => 'sucesso',
            'error' => 'erro',
            default => 'info',
        };
    }

    private function migrarAlertas(): void
    {
        $userIds = DB::table('users')->pluck('id')->flip();
        $total = 0;

        DB::connection('legado')->table('alerts')->orderBy('id')->chunk(500, function ($rows) use ($userIds, &$total) {
            $batch = [];

            foreach ($rows as $a) {
                if (! isset($userIds[$a->user_id])) {
                    continue;
                }

                $batch[] = [
                    'id' => $a->id,
                    'user_id' => $a->user_id,
                    'titulo' => $a->title,
                    'mensagem' => $a->message,
                    'tipo' => $this->mapAlertaTipo($a->type),
                    'lida' => $a->status === 'read',
                    'lida_em' => $a->read_at,
                    'imagem_url' => $a->image_url,
                    'created_at' => $a->created_at,
                    'updated_at' => $a->updated_at,
                ];
            }

            if ($batch) {
                DB::table('alertas')->insert($batch);
                $total += count($batch);
            }
        });

        $this->info("alertas: {$total}");
    }

    private function migrarConfiguracoes(): void
    {
        $mapa = [
            'dias_expiracao_padrao' => ['configuracoes_sistema', 'chave', 'valor', 'dias_expiracao_padrao'],
            'dias_aviso_expiracao' => ['configuracoes_sistema', 'chave', 'valor', 'dias_aviso_expiracao'],
            'google_analytics_id' => ['google_settings', 'setting_key', 'setting_value', 'google_analytics_id'],
            'google_ads_id' => ['google_settings', 'setting_key', 'setting_value', 'google_ads_id'],
        ];

        foreach ($mapa as $chaveDestino => [$tabela, $colChave, $colValor, $chaveOrigem]) {
            if (! Schema::connection('legado')->hasTable($tabela)) {
                continue;
            }

            $valor = DB::connection('legado')->table($tabela)->where($colChave, $chaveOrigem)->value($colValor);

            if ($valor !== null) {
                Configuracao::set($chaveDestino, $valor);
            }
        }

        $this->info('configuracoes: sincronizadas.');
    }
}
