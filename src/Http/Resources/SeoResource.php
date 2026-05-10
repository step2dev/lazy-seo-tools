<?php

namespace Step2dev\LazySeoTools\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Step2dev\LazySeoTools\Models\Seo;

/** @mixin \Step2dev\LazySeoTools\Models\Seo */
class SeoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Seo $seo */
        $seo = $this->resource;

        return [
            'id' => $seo->id,
            'url' => $seo->url,
            'title' => $seo->title,
            'description' => $seo->description,
            'keywords' => $seo->keywords,
            'canonical_url' => $seo->canonical_url,
            'robots' => $seo->robots,
            'indexable' => (bool) $seo->indexable,
            'seoable_type' => $this->when((bool) config('lazy-seo.routes.api_allow_morph_binding', false), $seo->seoable_type),
            'seoable_id' => $this->when((bool) config('lazy-seo.routes.api_allow_morph_binding', false), $seo->seoable_id),
            'created_at' => $seo->created_at?->toISOString(),
            'updated_at' => $seo->updated_at?->toISOString(),
        ];
    }
}
