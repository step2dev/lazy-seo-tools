<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Livewire\Component;
use Step2dev\LazySeoTools\Models\Seo;

class SeoForm extends Component
{
    public ?string $url = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $keywords = null;

    public function save(): void
    {
        $locale = app()->getLocale();

        Seo::create([
            'url' => $this->url,
            'title' => [$locale => $this->title],
            'description' => [$locale => $this->description],
            'keywords' => [$locale => $this->keywords],
            'indexable' => true,
        ]);

        $this->reset(['url', 'title', 'description', 'keywords']);
    }

    public function render()
    {
        return app(ViewFactory::class)->make('lazy-seo::livewire.seo-form');
    }
}
