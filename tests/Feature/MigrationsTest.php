<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

it('publishes a database schema that can migrate and rollback cleanly', function (): void {
    expect(Schema::hasTable(config('lazy-seo.tables.seo', 'seo')))->toBeTrue()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_redirects', 'seo_redirects')))->toBeTrue()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_scans', 'seo_scans')))->toBeTrue()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues')))->toBeTrue();

    Artisan::call('migrate:rollback', [
        '--database' => 'testing',
        '--realpath' => true,
        '--path' => __DIR__.'/../../database/migrations',
    ]);

    expect(Schema::hasTable(config('lazy-seo.tables.seo', 'seo')))->toBeFalse()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_redirects', 'seo_redirects')))->toBeFalse()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_scans', 'seo_scans')))->toBeFalse()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues')))->toBeFalse();
});
