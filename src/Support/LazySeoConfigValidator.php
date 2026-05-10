<?php

namespace Step2dev\LazySeoTools\Support;

use InvalidArgumentException;

class LazySeoConfigValidator
{
    public function validate(): void
    {
        if (! (bool) config('lazy-seo.validation.enabled', true)) {
            return;
        }

        $this->validateRoutes();
        $this->validateLivewire();
        $this->validateCrawler();
        $this->validateAi();
    }

    protected function validateRoutes(): void
    {
        if ((bool) config('lazy-seo.routes.web', false) && (bool) config('lazy-seo.features.admin', true)) {
            $adminMiddleware = $this->middlewareList(config('lazy-seo.routes.admin_middleware', []));

            if (! $this->containsMiddleware($adminMiddleware, ['auth', 'auth:', 'auth.'])) {
                $this->fail('Admin routes are enabled, but lazy-seo.routes.admin_middleware does not contain auth middleware.');
            }

            if ((bool) config('lazy-seo.routes.admin_gate_enabled', true) && ! $this->containsMiddleware($adminMiddleware, ['can:'])) {
                $this->fail('Admin routes are enabled, but lazy-seo.routes.admin_middleware does not contain a can:* gate middleware.');
            }
        }

        if ((bool) config('lazy-seo.routes.api', false) && (bool) config('lazy-seo.features.api', true)) {
            $readMiddleware = $this->middlewareList(config('lazy-seo.routes.api_read_middleware', []));
            $writeMiddleware = $this->middlewareList(config('lazy-seo.routes.api_write_middleware', []));

            if (! $this->containsMiddleware($readMiddleware, ['auth', 'auth:', 'auth.', 'sanctum', 'passport'])) {
                $this->fail('API read routes are enabled, but lazy-seo.routes.api_read_middleware does not contain auth middleware.');
            }

            if (! $this->containsMiddleware($writeMiddleware, ['auth', 'auth:', 'auth.', 'sanctum', 'passport'])) {
                $this->fail('API write routes are enabled, but lazy-seo.routes.api_write_middleware does not contain auth middleware.');
            }

            if ((bool) config('lazy-seo.routes.api_allow_morph_binding', false) && $this->middlewareList(config('lazy-seo.routes.api_allowed_seoable_types', [])) === []) {
                $this->fail('API morph binding is enabled, but lazy-seo.routes.api_allowed_seoable_types is empty.');
            }
        }
    }

    protected function validateLivewire(): void
    {
        $livewireEnabled = (bool) config('lazy-seo.features.livewire', false);
        $adminEnabled = (bool) config('lazy-seo.features.admin', false) || (bool) config('lazy-seo.routes.web', false);

        if (! $livewireEnabled && $adminEnabled) {
            $this->fail('Lazy SEO admin UI requires lazy-seo.features.livewire to be enabled.');
        }

        if ($livewireEnabled && ! class_exists('Livewire\Livewire')) {
            $this->fail('Lazy SEO Livewire feature is enabled, but livewire/livewire is not installed. Run composer require livewire/livewire or disable lazy-seo.features.livewire.');
        }
    }

    protected function validateCrawler(): void
    {
        if (! (bool) config('lazy-seo.features.crawler', true) || ! (bool) config('lazy-seo.crawler.enabled', true)) {
            return;
        }

        $maxPages = (int) config('lazy-seo.crawler.max_pages', 50);
        $maxDepth = (int) config('lazy-seo.crawler.max_depth', 5);
        $timeout = (int) config('lazy-seo.crawler.timeout', 10);
        $maxRedirects = (int) config('lazy-seo.crawler.max_redirects', 5);
        $maxBodyKb = (int) config('lazy-seo.crawler.max_body_kb', 1024);
        $retryTimes = (int) config('lazy-seo.crawler.retry_times', 1);
        $retrySleep = (int) config('lazy-seo.crawler.retry_sleep', 250);
        $rateLimitMs = (int) config('lazy-seo.crawler.rate_limit_ms', 250);

        if ($maxPages < 1 || $maxPages > 10000) {
            $this->fail('lazy-seo.crawler.max_pages must be between 1 and 10000.');
        }

        if ($maxDepth < 0 || $maxDepth > 100) {
            $this->fail('lazy-seo.crawler.max_depth must be between 0 and 100.');
        }

        if ($timeout < 1 || $timeout > 60) {
            $this->fail('lazy-seo.crawler.timeout must be between 1 and 60 seconds.');
        }

        if ($maxRedirects < 0 || $maxRedirects > 10) {
            $this->fail('lazy-seo.crawler.max_redirects must be between 0 and 10.');
        }

        if ($maxBodyKb < 1 || $maxBodyKb > 10240) {
            $this->fail('lazy-seo.crawler.max_body_kb must be between 1 and 10240.');
        }

        if ($retryTimes < 0 || $retryTimes > 5) {
            $this->fail('lazy-seo.crawler.retry_times must be between 0 and 5.');
        }

        if ($retrySleep < 0 || $retrySleep > 5000) {
            $this->fail('lazy-seo.crawler.retry_sleep must be between 0 and 5000 milliseconds.');
        }

        if ($rateLimitMs < 0 || $rateLimitMs > 60000) {
            $this->fail('lazy-seo.crawler.rate_limit_ms must be between 0 and 60000 milliseconds.');
        }
    }

    protected function validateAi(): void
    {
        if (! (bool) config('lazy-seo.ai.enabled', false)) {
            return;
        }

        $provider = (string) config('lazy-seo.ai.provider', 'openai');

        if (! in_array($provider, ['openai'], true)) {
            $this->fail("Unsupported lazy-seo.ai.provider [{$provider}].");
        }

        if (blank(config('lazy-seo.ai.token'))) {
            $this->fail('AI is enabled, but lazy-seo.ai.token is empty.');
        }
    }

    /** @return array<int, string> */
    protected function middlewareList(mixed $middleware): array
    {
        if (is_string($middleware)) {
            return [$middleware];
        }

        if (! is_array($middleware)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn (mixed $item): string => (string) $item, $middleware)));
    }

    /** @param array<int, string> $needles */
    protected function containsMiddleware(array $middleware, array $needles): bool
    {
        foreach ($middleware as $item) {
            foreach ($needles as $needle) {
                if ($item === rtrim($needle, ':') || str_starts_with($item, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function fail(string $message): void
    {
        throw new InvalidArgumentException($message.' Disable lazy-seo.validation.enabled only if you intentionally accept this risk.');
    }
}
