<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\View\Component;

class OgComponent extends Component
{
    public function __construct(public array $data = []) {}

    public function render()
    {
        return view('lazy-seo::components.og', [
            'data' => app('lazy-seo-manager')->toArray(app('lazy-seo-manager')->current(), $this->data),
        ]);
    }
}
