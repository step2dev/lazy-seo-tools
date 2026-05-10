<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Step2dev\LazySeoTools\Models\Seo;

class SeoForm extends Component
{
    public ?string $url = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $keywords = null;

    /** @return array<string, list<string>> */
    protected function rules(): array
    {
        return [
            'url' => ['nullable', 'string', 'max:2048'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(): void
    {
        $ability = (string) config('lazy-seo.routes.admin_gate', 'manage-lazy-seo');

        if ((bool) config('lazy-seo.routes.admin_gate_enabled', true) && $ability !== '') {
            Gate::authorize($ability);
        }

        /** @var array{url?: string|null, title: string, description?: string|null, keywords?: string|null} $data */
        $data = $this->validate();
        $locale = app()->getLocale();

        Seo::query()->create([
            'url' => $data['url'] ?? null,
            'title' => [$locale => $data['title']],
            'description' => [$locale => $data['description'] ?? null],
            'keywords' => [$locale => $data['keywords'] ?? null],
            'indexable' => true,
        ]);

        $this->reset(['url', 'title', 'description', 'keywords']);
    }

    public function render()
    {
        return app(ViewFactory::class)->make('lazy-seo::livewire.seo-form');
    }
}
