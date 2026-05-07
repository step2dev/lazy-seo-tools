<?php

use Illuminate\Support\Facades\Schema;
use Step2dev\LazySeoTools\Models\Seo;
use Step2dev\LazySeoTools\Models\SeoRedirect;
use Step2dev\LazySeoTools\Models\SeoTemplate;

it('uses table names from package config', function () {
    expect((new Seo())->getTable())->toBe(config('lazy-seo.tables.seo'))
        ->and((new SeoRedirect())->getTable())->toBe(config('lazy-seo.tables.seo_redirects'))
        ->and((new SeoTemplate())->getTable())->toBe(config('lazy-seo.tables.seo_templates'));
});

it('creates configured package tables', function () {
    expect(Schema::hasTable(config('lazy-seo.tables.seo')))->toBeTrue()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_redirects')))->toBeTrue()
        ->and(Schema::hasTable(config('lazy-seo.tables.seo_templates')))->toBeTrue();
});
