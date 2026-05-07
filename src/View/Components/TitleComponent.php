<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Step2dev\LazySeoTools\Models\Seo;

class TitleComponent extends Component
{
    public function __construct(
        public ?Seo $seo = null,
        public array $overrides = [],
        public ?string $title = null,
    ) {
        if ($title !== null) {
            $this->overrides['title'] = $title;
        }
    }

    public function render(): View
    {
        return view('lazy-seo::components.title');
    }
}
