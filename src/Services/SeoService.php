<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Database\Eloquent\Model;
use Step2dev\LazySeoTools\Models\Seo;

class SeoService
{
    public function createOrUpdateSeo(Model $model, array $seoData): Seo
    {
        /** @var Seo $seo */
        $seo = $model->morphOne(Seo::class, 'seoable')->updateOrCreate([], $seoData);

        return $seo;
    }

    public function getSeo(Model $model): Seo
    {
        $seo = $model->morphOne(Seo::class, 'seoable')->first();

        return $seo instanceof Seo ? $seo : new Seo;
    }
}
