<?php

namespace Step2dev\LazySeoTools\Data;

final readonly class ContentIntelligenceResult
{
    public function __construct(
        public int $score,
        public array $warnings = [],
        public array $suggestions = [],
        public array $metrics = [],
        public array $keywords = [],
        public array $headings = [],
        public array $images = [],
        public array $links = [],
    ) {}

    public function passed(): bool
    {
        return $this->score >= 75 && $this->warnings === [];
    }

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'passed' => $this->passed(),
            'warnings' => $this->warnings,
            'suggestions' => $this->suggestions,
            'metrics' => $this->metrics,
            'keywords' => $this->keywords,
            'headings' => $this->headings,
            'images' => $this->images,
            'links' => $this->links,
        ];
    }
}
