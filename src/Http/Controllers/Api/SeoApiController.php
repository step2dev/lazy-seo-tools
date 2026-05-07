<?php

namespace Step2dev\LazySeoTools\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Step2dev\LazySeoTools\Models\Seo;

class SeoApiController extends Controller
{
    public function index()
    {
        return Seo::query()->latest()->paginate((int) request('per_page', 20));
    }

    public function show(Seo $seo): Seo
    {
        return $seo;
    }

    public function store(Request $request): JsonResponse
    {
        $seo = Seo::query()->create($this->validated($request));

        return response()->json($seo, 201);
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
        $required = $partial ? 'sometimes' : 'required';

        $rules = [
            'url' => [$nullable, 'string', 'max:2048'],
            'title' => [$nullable, 'array'],
            'title.*' => ['nullable', 'string', 'max:255'],
            'description' => [$nullable, 'array'],
            'description.*' => ['nullable', 'string', 'max:500'],
            'keywords' => [$nullable, 'array'],
            'keywords.*' => ['nullable', 'string', 'max:500'],
            'canonical_url' => [$nullable, 'string', 'max:2048'],
            'robots' => [$nullable, 'array'],
            'robots.*' => ['string', Rule::in(['index', 'noindex', 'follow', 'nofollow', 'noarchive', 'nosnippet', 'noimageindex'])],
            'indexable' => [$nullable, 'boolean'],
        ];

        if ((bool) config('lazy-seo.routes.api_allow_morph_binding', false)) {
            $rules['seoable_type'] = [$required, 'string', 'max:255'];
            $rules['seoable_id'] = [$required, 'integer'];
        }

        return $request->validate($rules);
    }
}
