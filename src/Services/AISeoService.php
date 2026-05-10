<?php

namespace Step2dev\LazySeoTools\Services;

use Step2dev\LazySeoTools\Contracts\AIProvider;

class AISeoService
{
    public function __construct(
        protected AIProvider $provider,
    ) {}

    /** @return array{title: string, description: string, keywords: string} */
    public function generateMeta(string $content): array
    {
        if (! $this->enabled()) {
            return $this->fallback();
        }

        $decoded = $this->provider->chatJson([
            ['role' => 'system', 'content' => 'Return only valid JSON with string fields: title, description, keywords. Keep title under 60 chars, description under 160 chars.'],
            ['role' => 'user', 'content' => mb_substr($content, 0, 8000)],
        ]);

        if (! is_array($decoded)) {
            return $this->fallback();
        }

        return [
            'title' => $this->clean($decoded['title'] ?? '', 60),
            'description' => $this->clean($decoded['description'] ?? '', 160),
            'keywords' => $this->clean($decoded['keywords'] ?? '', 255),
        ];
    }

    protected function enabled(): bool
    {
        return (bool) config('lazy-seo.ai.enabled', false);
    }

    /** @return array{title: string, description: string, keywords: string} */
    protected function fallback(): array
    {
        return [
            'title' => '',
            'description' => '',
            'keywords' => '',
        ];
    }

    protected function clean(mixed $value, int $limit): string
    {
        return mb_substr(trim(strip_tags((string) $value)), 0, $limit);
    }
}
