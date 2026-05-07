<?php

namespace Step2dev\LazySeoTools\Data;

final readonly class CrawledPage
{
    public function __construct(
        public string $url,
        public int $status,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonical = null,
        public array $robots = [],
        public array $headings = [],
        public array $links = [],
        public array $images = [],
        public array $redirects = [],
        public ?SeoAnalysisResult $analysis = null,
        public ?string $error = null,
    ) {}

    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300 && $this->error === null;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical,
            'robots' => $this->robots,
            'headings' => $this->headings,
            'links' => $this->links,
            'images' => $this->images,
            'redirects' => $this->redirects,
            'analysis' => $this->analysis?->toArray(),
            'error' => $this->error,
        ];
    }
}
