<?php

use Illuminate\Support\Facades\Gate;
use Step2dev\LazySeoTools\Support\LazySeoConfigValidator;

it('protects admin and api write routes by default', function (): void {
    expect(config('lazy-seo.routes.admin_middleware'))
        ->toContain('web')
        ->toContain('auth')
        ->toContain('can:manage-lazy-seo')
        ->and(config('lazy-seo.routes.admin_gate'))
        ->toBe('manage-lazy-seo')
        ->and(config('lazy-seo.routes.api_write_middleware'))
        ->toContain('auth:sanctum');
});

it('registers the default admin gate', function (): void {
    expect(Gate::has('manage-lazy-seo'))->toBeTrue();
});

it('rejects enabled admin routes without auth middleware', function (): void {
    config()->set('lazy-seo.routes.web', true);
    config()->set('lazy-seo.routes.admin_middleware', ['web', 'can:manage-lazy-seo']);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'Admin routes are enabled');

it('rejects enabled admin routes without gate middleware', function (): void {
    config()->set('lazy-seo.routes.web', true);
    config()->set('lazy-seo.routes.admin_middleware', ['web', 'auth']);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'can:* gate middleware');

it('rejects enabled write api routes without auth middleware', function (): void {
    config()->set('lazy-seo.routes.api', true);
    config()->set('lazy-seo.routes.api_write_middleware', []);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'API write routes are enabled');

it('protects api read routes by default', function (): void {
    expect(config('lazy-seo.routes.api_read_middleware'))
        ->toContain('auth:sanctum');
});

it('rejects enabled read api routes without auth middleware', function (): void {
    config()->set('lazy-seo.routes.api', true);
    config()->set('lazy-seo.routes.api_read_middleware', []);

    app(LazySeoConfigValidator::class)->validate();
})->throws(InvalidArgumentException::class, 'API read routes are enabled');
