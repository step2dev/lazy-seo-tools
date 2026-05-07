<?php

namespace Step2dev\LazySeoTools\Data;

final readonly class SeoAnalysisResult
{
    public function __construct(
        public int $score,
        public array $errors = [],
        public array $warnings = [],
        public array $notices = [],
        public array $metrics = [],
    ) {}

    public function grade(): string
    {
        return match (true) {
            $this->score >= 90 => 'excellent',
            $this->score >= 75 => 'good',
            $this->score >= 50 => 'needs-work',
            default => 'poor',
        };
    }

    public function passed(): bool
    {
        return $this->score >= 75 && $this->errors === [];
    }

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'grade' => $this->grade(),
            'passed' => $this->passed(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'notices' => $this->notices,
            'metrics' => $this->metrics,
        ];
    }
}
