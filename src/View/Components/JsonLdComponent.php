<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\View\Component;
use Step2dev\LazySeoTools\Services\JsonLdService;

class JsonLdComponent extends Component
{
    public function __construct(public array $data = []) {}

    public function render()
    {
        return view('lazy-seo::components.jsonld', [
            'jsonLd' => app(JsonLdService::class)->generateForPage($this->data),
        ]);
    }
}
