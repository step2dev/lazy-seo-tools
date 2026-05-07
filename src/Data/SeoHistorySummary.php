<?php

namespace Step2dev\LazySeoTools\Data;

class SeoHistorySummary
{
    /**
     * @param  array<int, array<string, mixed>>  $scoreTrend
     * @param  array<string, mixed>  $issueTrend
     * @param  array<int, array<string, mixed>>  $regressions
     * @param  array<int, array<string, mixed>>  $resolved
     */
    public function __construct(
        public readonly ?int $currentScore,
        public readonly ?int $previousScore,
        public readonly int $scoreDelta,
        public readonly array $scoreTrend,
        public readonly array $issueTrend,
        public readonly array $regressions,
        public readonly array $resolved,
    ) {}

    public function toArray(): array
    {
        return [
            'current_score' => $this->currentScore,
            'previous_score' => $this->previousScore,
            'score_delta' => $this->scoreDelta,
            'score_trend' => $this->scoreTrend,
            'issue_trend' => $this->issueTrend,
            'regressions' => $this->regressions,
            'resolved' => $this->resolved,
        ];
    }
}
