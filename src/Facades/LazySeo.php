<?php

namespace Step2dev\LazySeoTools\Facades;

use Illuminate\Support\Facades\Facade;
use Step2dev\LazySeoTools\Services\SeoManager;

/**
 * @method static \Step2dev\LazySeoTools\Models\Seo forUrl(string $url)
 * @method static \Step2dev\LazySeoTools\Models\Seo forModel(\Illuminate\Database\Eloquent\Model $model)
 * @method static \Illuminate\Support\HtmlString renderMetaTags(?\Step2dev\LazySeoTools\Models\Seo $seo = null, array $overrides = [])
 *
 * @see SeoManager
 */
class LazySeo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SeoManager::class;
    }
}
