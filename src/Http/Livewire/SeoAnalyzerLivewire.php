<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Livewire\Component;
use Step2dev\LazySeoTools\Services\SeoAnalyzerService;

class SeoAnalyzerLivewire extends Component
{
    public string $title = '';

    public string $description = '';

    public string $keywords = '';

    public string $content = '';

    public array $result = [
        'score' => 0,
        'grade' => 'red',
        'warnings' => [],
    ];

    public function updated($field)
    {
        $this->analyze();
    }

    public function analyze(): void
    {
        $this->result = app(SeoAnalyzerService::class)->analyze(
            $this->title,
            $this->description,
            $this->keywords,
            $this->content,
        );
    }

    public function render()
    {
        return app(ViewFactory::class)->make('lazy-seo::livewire.analyzer');
    }
}
