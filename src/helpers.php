<?php

use Step2dev\LazySeoTools\Services\SeoManager;

if (! function_exists('seo')) {
    function seo(): SeoManager
    {
        return app('lazy-seo');
    }
}


if (! function_exists('seo_schema')) {
    function seo_schema(string $type = 'webPage', array $data = []): array
    {
        return app(\Step2dev\LazySeoTools\Services\SchemaService::class)->make($type, $data);
    }
}

if (! function_exists('seo_jsonld')) {
    function seo_jsonld(string $type = 'webPage', array $data = []): string
    {
        return app(\Step2dev\LazySeoTools\Services\JsonLdService::class)->script($type, $data);
    }
}
