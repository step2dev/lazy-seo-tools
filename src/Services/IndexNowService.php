<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Models\SeoIndexingLog;

class IndexNowService
{
    /** @return array<string, mixed> */
    public function submit(string|array $urls, array $options = []): array
    {
        $urls = $this->normalizeUrls($urls);

        if ($urls === []) {
            return ['successful' => false, 'status' => null, 'message' => 'No URLs to submit.', 'urls' => []];
        }

        $key = (string) ($options['key'] ?? config('lazy-seo.indexnow.key', ''));

        if ($key === '') {
            return ['successful' => false, 'status' => null, 'message' => 'IndexNow key is missing.', 'urls' => $urls];
        }

        $chunks = array_chunk($urls, (int) ($options['chunk_size'] ?? config('lazy-seo.indexnow.chunk_size', 1000)));
        $results = [];

        foreach ($chunks as $chunk) {
            $results[] = $this->submitChunk($chunk, array_replace($options, ['key' => $key]));
        }

        return [
            'successful' => collect($results)->every(fn (array $result): bool => (bool) $result['successful']),
            'status' => $results[0]['status'] ?? null,
            'message' => collect($results)->pluck('message')->filter()->implode(' | '),
            'chunks' => $results,
            'urls' => $urls,
        ];
    }

    /** @param array<int, string> $urls @return array<string, mixed> */
    protected function submitChunk(array $urls, array $options): array
    {
        $host = (string) ($options['host'] ?? config('lazy-seo.indexnow.host') ?: parse_url($urls[0], PHP_URL_HOST));
        $endpoint = (string) ($options['endpoint'] ?? config('lazy-seo.indexnow.endpoint', 'https://api.indexnow.org/indexnow'));
        $payload = [
            'host' => $host,
            'key' => (string) $options['key'],
            'keyLocation' => $options['key_location'] ?? config('lazy-seo.indexnow.key_location') ?: url('/'.((string) $options['key']).'.txt'),
            'urlList' => array_values($urls),
        ];

        try {
            $response = Http::timeout((int) ($options['timeout'] ?? config('lazy-seo.indexnow.timeout', 10)))
                ->retry((int) ($options['retry_times'] ?? config('lazy-seo.indexnow.retry_times', 2)), (int) ($options['retry_sleep'] ?? config('lazy-seo.indexnow.retry_sleep', 250)))
                ->acceptJson()
                ->asJson()
                ->post($endpoint, $payload);

            return $this->formatResponse($response, $payload, $options);
        } catch (\Throwable $e) {
            $result = [
                'successful' => false,
                'status' => null,
                'message' => $e->getMessage(),
                'payload' => $payload,
                'urls' => $urls,
            ];

            $this->log($result, $options);

            return $result;
        }
    }

    /** @return array<string, mixed> */
    protected function formatResponse(Response $response, array $payload, array $options): array
    {
        $result = [
            'successful' => $response->successful(),
            'status' => $response->status(),
            'message' => $response->successful() ? 'Submitted to IndexNow.' : 'IndexNow request failed.',
            'body' => $response->body(),
            'payload' => $payload,
            'urls' => Arr::wrap($payload['urlList'] ?? []),
        ];

        $this->log($result, $options);

        return $result;
    }

    protected function log(array $result, array $options): void
    {
        if (! (bool) ($options['log'] ?? config('lazy-seo.indexnow.log', true))) {
            return;
        }

        SeoIndexingLog::query()->create([
            'engine' => 'indexnow',
            'host' => parse_url((string) (($result['urls'][0] ?? null) ?: config('app.url')), PHP_URL_HOST),
            'urls' => $result['urls'] ?? [],
            'status' => $result['status'] ?? null,
            'successful' => (bool) ($result['successful'] ?? false),
            'response_body' => $result['body'] ?? $result['message'] ?? null,
            'payload' => $result['payload'] ?? null,
            'meta' => ['message' => $result['message'] ?? null],
            'submitted_at' => now(),
        ]);
    }

    /** @return array<int, string> */
    protected function normalizeUrls(string|array $urls): array
    {
        return collect(Arr::wrap($urls))
            ->map(fn (mixed $url): string => trim((string) $url))
            ->filter(fn (string $url): bool => $url !== '' && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')))
            ->unique()
            ->values()
            ->all();
    }
}
