<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\View\Component;

class TitleComponent extends Component
{
    public function __construct(public ?string $title = null) {}

    public function render()
    {
        return view('lazy-seo::components.title', [
            'title' => $this->title ?? config('lazy-seo.defaults.title', config('app.name')),
        ]);
    }
}
