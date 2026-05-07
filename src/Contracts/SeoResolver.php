<?php

namespace Step2dev\LazySeoTools\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Step2dev\LazySeoTools\Data\SeoData;
use Step2dev\LazySeoTools\Models\Seo;

interface SeoResolver
{
    public function forUrl(string $url): Seo;

    public function forModel(Model $model): Seo;

    public function current(): Seo;

    public function resolve(?Model $model = null, ?string $url = null, array $overrides = []): SeoData;

    public function data(?Seo $seo = null, array $overrides = []): SeoData;

    public function renderMetaTags(?Seo $seo = null, array $overrides = []): HtmlString;
}
