<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Str;
use Step2dev\LazySeoTools\Data\CrawledPage;
use Step2dev\LazySeoTools\Data\CrawlResult;

class SeoAuditService
{
    /** @return array<int, array<string, mixed>> */
    public function issues(CrawlResult $result): array
    {
        $issues = [];

        foreach ($result->brokenLinks as $url => $sources) {
            $issues[] = $this->issue($url, 'broken_link', 'error', 'Broken internal link detected.', ['sources' => array_values((array) $sources)]);
        }

        foreach ($result->externalBrokenLinks as $url => $payload) {
            $issues[] = $this->issue($url, 'broken_external_link', 'warning', 'Broken external link detected.', [
                'status' => $payload['status'] ?? null,
                'error' => $payload['error'] ?? null,
                'sources' => array_values((array) ($payload['sources'] ?? [])),
            ]);
        }

        foreach ($result->redirectChains as $url => $chain) {
            $issues[] = $this->issue($url, 'redirect_chain', 'warning', 'Redirect chain detected.', ['chain' => array_values((array) $chain)]);
        }

        foreach ($result->duplicateTitles as $title => $urls) {
            $issues[] = $this->issue(null, 'duplicate_title', 'warning', 'Duplicate title detected.', [
                'title' => $title,
                'urls' => array_values((array) $urls),
            ]);
        }

        foreach ($result->duplicateDescriptions as $description => $urls) {
            $issues[] = $this->issue(null, 'duplicate_description', 'warning', 'Duplicate meta description detected.', [
                'description' => $description,
                'urls' => array_values((array) $urls),
            ]);
        }

        foreach ($result->canonicalConflicts as $url => $canonical) {
            $issues[] = $this->issue($url, 'canonical_conflict', 'error', 'Canonical URL points to a different page.', ['canonical' => $canonical]);
        }

        foreach ($result->orphanPages as $url) {
            $issues[] = $this->issue($url, 'orphan_page', 'notice', 'Crawled page has no incoming internal links.');
        }

        foreach ($result->pages as $page) {
            array_push($issues, ...$this->pageIssues($page));
        }

        return array_values(array_filter(
            $issues,
            fn (array $issue): bool => $this->checkEnabled((string) $issue['type'])
        ));
    }

    /** @return array<int, array<string, mixed>> */
    public function pageIssues(CrawledPage $page): array
    {
        $issues = [];
        $metrics = $page->analysis?->metrics ?? [];

        if ($page->status >= 400 || $page->status === 0) {
            $issues[] = $this->issue($page->url, 'http_error', 'error', 'Page returned HTTP status '.$page->status.'.', [
                'status' => $page->status,
                'error' => $page->error,
            ]);
        }

        if (! $page->title) {
            $issues[] = $this->issue($page->url, 'missing_title', 'error', 'Missing page title.');
        } else {
            $titleLength = mb_strlen($page->title);

            if ($titleLength < 30) {
                $issues[] = $this->issue($page->url, 'title_too_short', 'warning', 'Title is too short.', ['length' => $titleLength]);
            }

            if ($titleLength > 65) {
                $issues[] = $this->issue($page->url, 'title_too_long', 'warning', 'Title is too long.', ['length' => $titleLength]);
            }
        }

        if (! $page->description) {
            $issues[] = $this->issue($page->url, 'missing_description', 'warning', 'Missing meta description.');
        } else {
            $descriptionLength = mb_strlen($page->description);

            if ($descriptionLength < 70) {
                $issues[] = $this->issue($page->url, 'description_too_short', 'warning', 'Meta description is too short.', ['length' => $descriptionLength]);
            }

            if ($descriptionLength > 170) {
                $issues[] = $this->issue($page->url, 'description_too_long', 'warning', 'Meta description is too long.', ['length' => $descriptionLength]);
            }
        }

        $h1Count = (int) ($metrics['h1_count'] ?? collect($page->headings)->where('level', 1)->count());

        if ($h1Count === 0) {
            $issues[] = $this->issue($page->url, 'missing_h1', 'warning', 'Missing H1 heading.');
        }

        if ($h1Count > 1) {
            $issues[] = $this->issue($page->url, 'multiple_h1', 'warning', 'Multiple H1 headings found.', ['count' => $h1Count]);
        }

        if (! $page->canonical) {
            $issues[] = $this->issue($page->url, 'missing_canonical', 'notice', 'Missing canonical link.');
        }

        if (in_array('noindex', $page->robots, true)) {
            $issues[] = $this->issue($page->url, 'noindex', 'warning', 'Page is marked as noindex.', ['robots' => $page->robots]);
        }

        foreach ($page->images as $image) {
            if (($image['missing_alt'] ?? false) === true) {
                $issues[] = $this->issue($page->url, 'missing_image_alt', 'notice', 'Image is missing alt text.', ['image' => $image['src'] ?? null]);
            }
        }

        return array_values(array_filter(
            $issues,
            fn (array $issue): bool => $this->checkEnabled((string) $issue['type'])
        ));
    }

    /** @param array<int, array<string, mixed>> $issues */
    public function score(array $issues): int
    {
        $weights = (array) config('lazy-seo.audit.severity_weights', []);
        $penalty = 0;

        foreach ($issues as $issue) {
            $penalty += (int) ($weights[$issue['severity'] ?? 'warning'] ?? 1);
        }

        return max(0, 100 - min((int) config('lazy-seo.audit.max_score_penalty', 100), $penalty));
    }

    /** @param array<string, mixed> $context */
    protected function issue(?string $url, string $type, string $severity, string $message, array $context = []): array
    {
        return [
            'url' => $url,
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'fingerprint' => $this->fingerprint($url, $type, $context),
        ];
    }

    /** @param array<string, mixed> $context */
    protected function fingerprint(?string $url, string $type, array $context = []): string
    {
        $stableContext = match ($type) {
            'duplicate_title' => ['title' => $context['title'] ?? null],
            'duplicate_description' => ['description' => $context['description'] ?? null],
            'missing_image_alt' => ['image' => $context['image'] ?? null],
            default => [],
        };

        return sha1(Str::lower((string) $url).'|'.$type.'|'.json_encode($stableContext));
    }

    protected function checkEnabled(string $type): bool
    {
        $checks = (array) config('lazy-seo.audit.checks', []);

        return (bool) ($checks[$type] ?? $checks[$this->checkGroup($type)] ?? true);
    }

    protected function checkGroup(string $type): string
    {
        return match ($type) {
            'title_too_short', 'title_too_long' => 'title_length',
            'description_too_short', 'description_too_long' => 'description_length',
            default => $type,
        };
    }
}
