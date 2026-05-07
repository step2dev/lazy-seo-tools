<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Step2dev\LazySeoTools\Models\SeoRedirect;

class RedirectTable extends Component
{
    use WithPagination;

    public function render()
    {
        return view('lazy-seo::livewire.redirect-table', [
            'redirects' => SeoRedirect::query()->latest()->paginate(10),
        ]);
    }
}
