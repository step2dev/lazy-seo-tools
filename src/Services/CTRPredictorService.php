<?php

namespace Step2dev\LazySeoTools\Services;

use Step2dev\LazySeoTools\Contracts\AIProvider;

class CTRPredictorService
{
    public function __construct(
        protected AIProvider $provider,
    ) {}

    /** @return array{raw: string, ctr: string, score: int|null} */
    public function predict(string $title, string $description): array
    {
        if (! (bool) config('lazy-seo.ai.enabled', false)) {
            return $this->fallback();
        }

        $decoded = $this->provider->chatJson([
            ['role' => 'system', 'content' => 'Return only valid JSON with fields ctr_percent integer 0-100 and comment string.'],
            ['role' => 'user', 'content' => "Estimate SERP click potential.\nTitle: {$title}\nDescription: {$description}"],
        ]);

        if (! is_array($decoded)) {
            return $this->fallback();
        }

        $score = isset($decoded['ctr_percent']) ? max(0, min(100, (int) $decoded['ctr_percent'])) : null;

        return [
            'raw' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
            'ctr' => $score === null ? 'N/A' : $score.'%',
            'score' => $score,
        ];
    }

    /** @return array{raw: string, ctr: string, score: int|null} */
    protected function fallback(): array
    {
        return [
            'raw' => '',
            'ctr' => 'N/A',
            'score' => null,
        ];
    }
}
