<?php

use Step2dev\LazySeoTools\Support\LazySeoConfigValidator;

it('rejects crawler config outside safe bounds', function (): void {
    config()->set('lazy-seo.crawler.timeout', 120);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'lazy-seo.crawler.timeout');

it('rejects enabled ai without token', function (): void {
    config()->set('lazy-seo.ai.enabled', true);
    config()->set('lazy-seo.ai.token', null);
    config()->set('lazy-seo.ai_token', null);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'AI is enabled');

it('allows disabling config validation explicitly', function (): void {
    config()->set('lazy-seo.validation.enabled', false);
    config()->set('lazy-seo.routes.web', true);
    config()->set('lazy-seo.routes.admin_middleware', ['web']);

    app(LazySeoConfigValidator::class)->validate();

    expect(true)->toBeTrue();
});

it('rejects unsafe crawler depth config', function (): void {
    config()->set('lazy-seo.crawler.max_depth', 101);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'lazy-seo.crawler.max_depth');

it('rejects excessive crawler retry config', function (): void {
    config()->set('lazy-seo.crawler.retry_times', 10);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'lazy-seo.crawler.retry_times');

it('rejects excessive crawler rate limit config', function (): void {
    config()->set('lazy-seo.crawler.rate_limit_ms', 60001);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'lazy-seo.crawler.rate_limit_ms');
