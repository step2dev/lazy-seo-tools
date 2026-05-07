<?php

namespace Step2dev\LazySeoTools\Data;

final readonly class CrawlResult
{
    /** @param array<int, CrawledPage> $pages */
    public function __construct(
        public string $startUrl,
        public array $pages,
        public array $brokenLinks = [],
        public array $externalBrokenLinks = [],
        public array $redirectChains = [],
        public array $duplicateTitles = [],
        public array $duplicateDescriptions = [],
        public array $canonicalConflicts = [],
        public array $orphanPages = [],
    ) {}

    public function score(): int
    {
        $score = 100;
        $score -= min(25, count($this->brokenLinks) * 5);
        $score -= min(15, count($this->externalBrokenLinks) * 2);
        $score -= min(15, count($this->redirectChains) * 3);
        $score -= min(20, count($this->duplicateTitles) * 4);
        $score -= min(15, count($this->duplicateDescriptions) * 3);
        $score -= min(15, count($this->canonicalConflicts) * 5);

        foreach ($this->pages as $page) {
            if ($page->analysis && ! $page->analysis->passed()) {
                $score -= 1;
            }
        }

        return max(0, $score);
    }

    public function toArray(): array
    {
        return [
            'start_url' => $this->startUrl,
            'score' => $this->score(),
            'pages' => array_map(static fn (CrawledPage $page): array => $page->toArray(), $this->pages),
            'broken_links' => $this->brokenLinks,
            'external_broken_links' => $this->externalBrokenLinks,
            'redirect_chains' => $this->redirectChains,
            'duplicate_titles' => $this->duplicateTitles,
            'duplicate_descriptions' => $this->duplicateDescriptions,
            'canonical_conflicts' => $this->canonicalConflicts,
            'orphan_pages' => $this->orphanPages,
        ];
    }
}
