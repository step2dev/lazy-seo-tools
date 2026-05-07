<?php

namespace Step2dev\LazySeoTools\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Step2dev\LazySeoTools\Models\Seo;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function seoData(array $overrides = []): array
    {
        $seo = app('lazy-seo')->forModel($this);

        return app('lazy-seo')->toArray($seo, $overrides);
    }

    public function updateSeo(array $attributes): Seo
    {
        return $this->seo()->updateOrCreate([], $attributes);
    }
}
