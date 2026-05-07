<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Step2dev\LazySeoTools\Models\Seo;

class TwitterComponent extends Component
{
    public function __construct(
        public ?Seo $seo = null,
        public array $overrides = [],
        public array $data = [],
    ) {
        $this->overrides = array_replace($this->data, $this->overrides);
    }

    public function render(): View
    {
        return view('lazy-seo::components.twitter');
    }
}
