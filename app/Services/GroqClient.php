<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqClient
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    private const MODEL = 'llama-3.3-70b-versatile';

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, float $temperature = 0.7): string
    {
        $key = config('services.groq.key');

        if (! $key) {
            throw new RuntimeException('GROQ_API_KEY não está configurada.');
        }

        $response = Http::withToken($key)
            ->timeout(30)
            ->post(self::API_URL, [
                'model' => self::MODEL,
                'messages' => $messages,
                'temperature' => $temperature,
            ])
            ->throw();

        return $response->json('choices.0.message.content') ?? '';
    }
}
