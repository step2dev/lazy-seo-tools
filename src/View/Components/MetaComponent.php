<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Step2dev\LazySeoTools\Models\Seo;

class MetaComponent extends Component
{
    public function __construct(
        public ?Seo $seo = null,
        public array $overrides = [],
    ) {}

    public function render(): View
    {
        return app(ViewFactory::class)->make('lazy-seo::components.meta');
    }
}
