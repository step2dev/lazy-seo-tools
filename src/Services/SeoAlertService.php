<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Step2dev\LazySeoTools\Models\SeoScan;
use Throwable;

class SeoAlertService
{
    public function __construct(
        protected SeoScanReportService $reports,
    ) {}

    /** @return array<int, string> */
    public function reasonsFor(SeoScan $scan): array
    {
        if (! (bool) config('lazy-seo.alerts.enabled', false)) {
            return [];
        }

        $reasons = [];

        if ($scan->status === 'failed' && (bool) config('lazy-seo.alerts.failed_scans', true)) {
            $reasons[] = 'scan_failed';
        }

        if ($scan->status === 'completed') {
            $scoreThreshold = (int) config('lazy-seo.alerts.score_threshold', 75);
            $criticalThreshold = (int) config('lazy-seo.alerts.critical_issues_threshold', 1);
            $newIssuesThreshold = (int) config('lazy-seo.alerts.new_issues_threshold', 1);
            $criticalIssues = $scan->issues()->where('status', 'open')->where('severity', 'error')->count();

            if ($scoreThreshold > 0 && $scan->score < $scoreThreshold) {
                $reasons[] = 'score_below_threshold';
            }

            if ($criticalThreshold > 0 && $criticalIssues >= $criticalThreshold) {
                $reasons[] = 'critical_issues_threshold';
            }

            if ($newIssuesThreshold > 0 && $scan->new_issues_count >= $newIssuesThreshold) {
                $reasons[] = 'new_issues_threshold';
            }
        }

        return array_values(array_unique($reasons));
    }

    public function notifyIfNeeded(SeoScan $scan): bool
    {
        $reasons = $this->reasonsFor($scan);

        if ($reasons === [] || $this->isCoolingDown($scan, $reasons)) {
            return false;
        }

        $payload = [
            'event' => 'lazy_seo.scan_alert',
            'reasons' => $reasons,
            'report' => $this->reports->make($scan),
        ];

        $sent = $this->sendWebhook($payload);

        if ($sent) {
            $this->startCooldown($scan, $reasons);
        }

        return $sent;
    }

    /** @param array<string, mixed> $payload */
    protected function sendWebhook(array $payload): bool
    {
        $url = config('lazy-seo.alerts.webhook_url') ?: config('lazy-seo.webhooks.seo.alert');

        if (! is_string($url) || trim($url) === '') {
            return false;
        }

        try {
            Http::timeout(10)->post($url, $payload);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Lazy SEO alert webhook failed.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /** @param array<int, string> $reasons */
    protected function isCoolingDown(SeoScan $scan, array $reasons): bool
    {
        return Cache::store($this->cacheStore())->has($this->cooldownKey($scan, $reasons));
    }

    /** @param array<int, string> $reasons */
    protected function startCooldown(SeoScan $scan, array $reasons): void
    {
        $minutes = (int) config('lazy-seo.alerts.cooldown_minutes', 60);

        if ($minutes <= 0) {
            return;
        }

        Cache::store($this->cacheStore())->put($this->cooldownKey($scan, $reasons), true, now()->addMinutes($minutes));
    }

    /** @param array<int, string> $reasons */
    protected function cooldownKey(SeoScan $scan, array $reasons): string
    {
        sort($reasons);

        return 'lazy-seo:alert:'.sha1($scan->start_url.'|'.implode(',', $reasons));
    }

    protected function cacheStore(): ?string
    {
        $store = config('lazy-seo.alerts.cache_store');

        return is_string($store) && $store !== '' ? $store : null;
    }
}
