<?php

namespace App\Http\Controllers;

use App\Services\GroqClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
        És o assistente de apoio ao cliente da TopCasa, um marketplace português de
        serviços domésticos (eletricistas, canalizadores, pintores, etc.). Respondes
        em português de Portugal, de forma breve e simpática, e ajudas os visitantes
        a perceber como publicar ou encontrar anúncios, pedir orçamentos, e usar o
        site. Se não souberes a resposta, sugere contactar o suporte.
        PROMPT;

    public function mensagem(Request $request, GroqClient $groq): JsonResponse
    {
        $request->validate(['mensagem' => ['required', 'string', 'max:500']]);

        $historico = $request->session()->get('chatbot.historico', []);
        $historico[] = ['role' => 'user', 'content' => $request->string('mensagem')->toString()];
        $historico = array_slice($historico, -10);

        $resposta = $groq->chat([
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ...$historico,
        ]);

        $historico[] = ['role' => 'assistant', 'content' => $resposta];
        $request->session()->put('chatbot.historico', $historico);

        return response()->json(['resposta' => $resposta]);
    }
}
