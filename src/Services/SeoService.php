<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Database\Eloquent\Model;
use Step2dev\LazySeoTools\Models\Seo;

class SeoService
{
    public function createOrUpdateSeo(Model $model, array $seoData): Seo
    {
        /** @var Seo $seo */
        $seo = $model->seo()->updateOrCreate([], $seoData);

        return $seo;
    }

    public function getSeo(Model $model): Seo
    {
        return $model->seo ?: new Seo;
    }
}
