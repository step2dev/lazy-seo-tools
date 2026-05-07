<?php

namespace Step2dev\LazySeoTools\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Step2dev\LazySeoTools\Services\JsonLdService;

class JsonLdComponent extends Component
{
    public function __construct(
        public string $type = 'webPage',
        public array $data = [],
    ) {}

    public function render(): View
    {
        return view('lazy-seo::components.jsonld', [
            'schema' => app(JsonLdService::class)->make($this->type, $this->data),
        ]);
    }
}
