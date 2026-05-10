<?php

namespace Step2dev\LazySeoTools\Services;

class AISeoWriterService
{
    public function __construct(
        protected AISeoService $seo,
    ) {}

    /** @return array{title: string, description: string, keywords: string} */
    public function generateMeta(string $content): array
    {
        return $this->seo->generateMeta($content);
    }
}
