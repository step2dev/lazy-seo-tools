<?php

namespace Step2dev\LazySeoTools\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Step2dev\LazySeoTools\Models\Seo;

class SeoApiController extends Controller
{
    public function index()
    {
        return Seo::query()->latest()->paginate(20);
    }

    public function show(Seo $seo): Seo
    {
        return $seo;
    }

    public function store(Request $request): Seo
    {
        return Seo::create($this->validated($request));
    }

    public function update(Request $request, Seo $seo): Seo
    {
        $seo->update($this->validated($request, partial: true));

        return $seo->refresh();
    }

    public function destroy(Seo $seo): JsonResponse
    {
        $seo->delete();

        return response()->json(['deleted' => true]);
    }

    protected function validated(Request $request, bool $partial = false): array
    {
        $nullable = $partial ? 'sometimes' : 'nullable';

        return $request->validate([
            'url' => [$nullable, 'string', 'max:2048'],
            'title' => [$nullable, 'array'],
            'description' => [$nullable, 'array'],
            'keywords' => [$nullable, 'array'],
            'canonical_url' => [$nullable, 'string', 'max:2048'],
            'robots' => [$nullable, 'array'],
            'indexable' => [$nullable, 'boolean'],
            'seoable_type' => [$nullable, 'string', 'max:255'],
            'seoable_id' => [$nullable, 'integer'],
        ]);
    }
}
