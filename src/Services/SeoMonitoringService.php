<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Arr;
use Step2dev\LazySeoTools\Data\CrawledPage;
use Step2dev\LazySeoTools\Data\CrawlResult;
use Step2dev\LazySeoTools\Models\SeoScan;

class SeoMonitoringService
{
    public function __construct(protected SiteCrawlerService $crawler) {}

    public function scan(string $url, array $options = []): SeoScan
    {
        $result = $this->crawler->crawl($url, $options);

        return $this->store($result, $options);
    }

    public function store(CrawlResult $result, array $options = []): SeoScan
    {
        $issues = $this->issuesFromResult($result);

        $scan = SeoScan::query()->create([
            'start_url' => $result->startUrl,
            'score' => $result->score(),
            'pages_count' => count($result->pages),
            'issues_count' => count($issues),
            'broken_links_count' => count($result->brokenLinks),
            'redirect_chains_count' => count($result->redirectChains),
            'duplicate_titles_count' => count($result->duplicateTitles),
            'duplicate_descriptions_count' => count($result->duplicateDescriptions),
            'canonical_conflicts_count' => count($result->canonicalConflicts),
            'summary' => Arr::except($result->toArray(), ['pages']),
            'options' => $options,
            'finished_at' => now(),
        ]);

        foreach ($issues as $issue) {
            $scan->issues()->create($issue);
        }

        $scan->forceFill(['issues_count' => count($issues)])->save();

        return $scan->load('issues');
    }

    /** @return array<int, array<string, mixed>> */
    public function issuesFromResult(CrawlResult $result): array
    {
        $issues = [];

        foreach ($result->brokenLinks as $url => $sources) {
            $issues[] = [
                'url' => $url,
                'type' => 'broken_link',
                'severity' => 'error',
                'message' => 'Broken internal link detected.',
                'context' => ['sources' => $sources],
            ];
        }

        foreach ($result->redirectChains as $url => $chain) {
            $issues[] = [
                'url' => $url,
                'type' => 'redirect_chain',
                'severity' => 'warning',
                'message' => 'Redirect chain detected.',
                'context' => ['chain' => $chain],
            ];
        }

        foreach ($result->duplicateTitles as $title => $urls) {
            $issues[] = [
                'url' => null,
                'type' => 'duplicate_title',
                'severity' => 'warning',
                'message' => 'Duplicate title detected: '.$title,
                'context' => ['title' => $title, 'urls' => $urls],
            ];
        }

        foreach ($result->duplicateDescriptions as $description => $urls) {
            $issues[] = [
                'url' => null,
                'type' => 'duplicate_description',
                'severity' => 'warning',
                'message' => 'Duplicate meta description detected.',
                'context' => ['description' => $description, 'urls' => $urls],
            ];
        }

        foreach ($result->canonicalConflicts as $url => $canonical) {
            $issues[] = [
                'url' => $url,
                'type' => 'canonical_conflict',
                'severity' => 'error',
                'message' => 'Canonical URL points to a different page.',
                'context' => ['canonical' => $canonical],
            ];
        }

        foreach ($result->orphanPages as $url) {
            $issues[] = [
                'url' => $url,
                'type' => 'orphan_page',
                'severity' => 'notice',
                'message' => 'Crawled page has no incoming internal links.',
                'context' => [],
            ];
        }

        foreach ($result->pages as $page) {
            array_push($issues, ...$this->issuesFromPage($page));
        }

        return $issues;
    }

    /** @return array<int, array<string, mixed>> */
    protected function issuesFromPage(CrawledPage $page): array
    {
        $issues = [];

        if ($page->status >= 400 || $page->status === 0) {
            $issues[] = [
                'url' => $page->url,
                'type' => 'http_error',
                'severity' => 'error',
                'message' => 'Page returned HTTP status '.$page->status.'.',
                'context' => ['status' => $page->status, 'error' => $page->error],
            ];
        }

        if (! $page->title) {
            $issues[] = [
                'url' => $page->url,
                'type' => 'missing_title',
                'severity' => 'error',
                'message' => 'Missing page title.',
                'context' => [],
            ];
        }

        if (! $page->description) {
            $issues[] = [
                'url' => $page->url,
                'type' => 'missing_description',
                'severity' => 'warning',
                'message' => 'Missing meta description.',
                'context' => [],
            ];
        }

        if (! $page->canonical) {
            $issues[] = [
                'url' => $page->url,
                'type' => 'missing_canonical',
                'severity' => 'notice',
                'message' => 'Missing canonical link.',
                'context' => [],
            ];
        }

        if (in_array('noindex', $page->robots, true)) {
            $issues[] = [
                'url' => $page->url,
                'type' => 'noindex',
                'severity' => 'warning',
                'message' => 'Page is marked as noindex.',
                'context' => ['robots' => $page->robots],
            ];
        }

        foreach ($page->images as $image) {
            if (($image['missing_alt'] ?? false) === true) {
                $issues[] = [
                    'url' => $page->url,
                    'type' => 'missing_image_alt',
                    'severity' => 'notice',
                    'message' => 'Image is missing alt text.',
                    'context' => ['image' => $image['src'] ?? null],
                ];
            }
        }

        if ($page->analysis) {
            foreach ($page->analysis->errors as $message) {
                $issues[] = [
                    'url' => $page->url,
                    'type' => 'analyzer_error',
                    'severity' => 'error',
                    'message' => $message,
                    'context' => [],
                ];
            }

            foreach ($page->analysis->warnings as $message) {
                $issues[] = [
                    'url' => $page->url,
                    'type' => 'analyzer_warning',
                    'severity' => 'warning',
                    'message' => $message,
                    'context' => [],
                ];
            }
        }

        return $issues;
    }
}
