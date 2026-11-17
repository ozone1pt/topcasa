<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use App\Models\GlossarioTermo;
use App\Services\GroqClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Simplified re-implementation of the legacy "glossário" SEO subsystem: for
 * each category missing glossary terms, ask Groq for a handful of related
 * terms + short definitions, shown on the category's listing page for SEO.
 * The legacy version was a much larger (30+ file), undocumented subsystem
 * auto-triggered on every ad submission; this covers the same end value
 * (SEO glossary content per category) as a simple, deliberately-run command
 * instead.
 */
class GerarGlossarioCategoria extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:gerar-glossario-categoria {categoria_id? : ID da categoria (todas se omitido)}';

    /**
     * @var string
     */
    protected $description = 'Gera termos de glossário SEO para categorias via IA (Groq).';

    public function handle(GroqClient $groq): int
    {
        $categorias = $this->argument('categoria_id')
            ? Categoria::where('id', $this->argument('categoria_id'))->get()
            : Categoria::whereDoesntHave('glossarioTermos')->get();

        foreach ($categorias as $categoria) {
            $this->info("A gerar glossário para: {$categoria->nome}");

            $resposta = $groq->chat([
                ['role' => 'system', 'content' => 'Respondes apenas em JSON válido, sem markdown.'],
                ['role' => 'user', 'content' => "Dá-me 5 termos relacionados com a categoria de serviço \"{$categoria->nome}\" (marketplace português de serviços domésticos), cada um com uma definição curta (1-2 frases, português de Portugal). Responde só com um array JSON de objetos {termo, definicao}."],
            ]);

            $termos = json_decode(Str::between($resposta, '[', ']') ? '['.Str::between($resposta, '[', ']').']' : $resposta, true);

            foreach ($termos ?? [] as $item) {
                if (empty($item['termo']) || empty($item['definicao'])) {
                    continue;
                }

                GlossarioTermo::updateOrCreate(
                    ['categoria_id' => $categoria->id, 'termo' => $item['termo']],
                    ['definicao' => $item['definicao']]
                );
            }
        }

        return self::SUCCESS;
    }
}
