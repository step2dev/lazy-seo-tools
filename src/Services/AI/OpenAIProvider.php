<?php

namespace Step2dev\LazySeoTools\Services\AI;

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Contracts\AIProvider;

class OpenAIProvider implements AIProvider
{
    public function chatJson(array $messages): ?array
    {
        $token = $this->token();

        if ($token === null) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->timeout((int) config('lazy-seo.ai.timeout', 15))
                ->retry((int) config('lazy-seo.ai.retry_times', 1), (int) config('lazy-seo.ai.retry_sleep', 250))
                ->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => (string) config('lazy-seo.ai.model', 'gpt-4o-mini'),
                    'response_format' => ['type' => 'json_object'],
                    'messages' => $messages,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $raw = (string) data_get($response->json(), 'choices.0.message.content', '');
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function token(): ?string
    {
        $token = config('lazy-seo.ai.token') ?: config('lazy-seo.ai_token');

        return is_string($token) && trim($token) !== '' ? trim($token) : null;
    }
}
