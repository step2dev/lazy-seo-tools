<?php

namespace Step2dev\LazySeoTools\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Step2dev\LazySeoTools\Data\SeoData;
use Step2dev\LazySeoTools\Models\Seo;

trait HasSeo
{
    /** @return MorphOne<Seo, $this> */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function resolvedSeo(array $overrides = []): SeoData
    {
        return app('lazy-seo')->resolve(model: $this, overrides: $overrides);
    }

    public function seoData(array $overrides = []): array
    {
        return $this->resolvedSeo($overrides)->toArray();
    }

    public function updateSeo(array $attributes): Seo
    {
        return $this->seo()->updateOrCreate([], $attributes);
    }
}
