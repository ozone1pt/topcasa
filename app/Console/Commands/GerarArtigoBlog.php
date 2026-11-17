<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Services\GroqClient;
use Illuminate\Console\Command;

class GerarArtigoBlog extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:gerar-artigo-blog';

    /**
     * @var string
     */
    protected $description = 'Gera um artigo de blog via IA (Groq) a partir de um tópico ainda não usado recentemente.';

    /**
     * Rotating pool of SEO topics for a home-services marketplace. A smaller,
     * representative set compared to the legacy site's 60+ topics - can be
     * extended freely, the anti-duplicate logic doesn't depend on its size.
     */
    public const TOPICOS = [
        'Como escolher um eletricista de confiança',
        'Sinais de que precisas de um canalizador urgentemente',
        'Quanto custa remodelar uma casa de banho em Portugal',
        'Dicas para pintar a tua casa como um profissional',
        'Como poupar energia com um bom isolamento térmico',
        'O que perguntar antes de contratar um construtor',
        'Manutenção de jardins: o guia completo',
        'Como escolher pavimentos para a tua casa',
        'Sinais de infiltrações e como resolvê-las',
        'Guia para climatização eficiente em casa',
        'Como planear uma remodelação de cozinha',
        'Segurança em obras: o que deves exigir',
        'Vantagens de painéis solares em Portugal',
        'Como escolher um serralheiro para portões e grades',
        'Erros comuns ao contratar serviços de limpeza',
        'Certificação energética: o que precisas de saber',
        'Como preparar a casa para o inverno',
        'Dicas para escolher revestimentos de fachada',
        'Quando contratar um arquiteto vs. um construtor',
        'Como avaliar orçamentos de obras corretamente',
    ];

    public function handle(GroqClient $groq): int
    {
        $topico = $this->escolherTopico();

        if (! $topico) {
            $this->info('Sem tópicos disponíveis (todos usados recentemente).');

            return self::SUCCESS;
        }

        $this->info("A gerar artigo para: {$topico}");

        $conteudo = $groq->chat([
            ['role' => 'system', 'content' => 'És um redator SEO para um marketplace português de serviços domésticos (TopCasa). Escreve em português de Portugal, em HTML simples (parágrafos <p>, subtítulos <h2>), sem markdown.'],
            ['role' => 'user', 'content' => "Escreve um artigo de blog de 400-600 palavras sobre: \"{$topico}\". Não repitas o título dentro do conteúdo."],
        ]);

        BlogPost::create([
            'titulo' => $topico,
            'conteudo' => $conteudo,
            'status' => 'pendente',
            'topico_fonte' => $topico,
        ]);

        $this->info('Artigo criado como pendente de revisão.');

        return self::SUCCESS;
    }

    private function escolherTopico(): ?string
    {
        $usadosRecentemente = BlogPost::where('created_at', '>=', now()->subDays(90))
            ->pluck('topico_fonte')
            ->filter()
            ->all();

        $disponiveis = array_values(array_diff(self::TOPICOS, $usadosRecentemente));

        if (empty($disponiveis)) {
            return null;
        }

        return $disponiveis[array_rand($disponiveis)];
    }
}
