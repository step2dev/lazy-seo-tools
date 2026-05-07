<?php

use Step2dev\LazySeoTools\Services\SeoManager;

if (! function_exists('seo')) {
    function seo(): SeoManager
    {
        return app('lazy-seo');
    }
}
