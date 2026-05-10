<?php

namespace Step2dev\LazySeoTools\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Step2dev\LazySeoTools\Http\Resources\SeoResource;
use Step2dev\LazySeoTools\Models\Seo;

class SeoApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->integer('per_page', 20)));

        return SeoResource::collection(
            Seo::query()->latest()->paginate($perPage)
        );
    }

    public function show(Seo $seo): SeoResource
    {
        return new SeoResource($seo);
    }

    public function store(Request $request): JsonResponse
    {
        $seo = Seo::query()->create($this->validated($request));

        return (new SeoResource($seo->refresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Seo $seo): SeoResource
    {
        $seo->update($this->validated($request, partial: true));

        return new SeoResource($seo->refresh());
    }

    public function destroy(Seo $seo): JsonResponse
    {
        $seo->delete();

        return response()->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }

    /** @return array<string, mixed> */
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
            $allowedSeoableTypes = array_values(array_filter(
                (array) config('lazy-seo.routes.api_allowed_seoable_types', []),
                static fn (mixed $type): bool => is_string($type) && $type !== ''
            ));

            $rules['seoable_type'] = [$required, 'string', 'max:255', Rule::in($allowedSeoableTypes)];
            $rules['seoable_id'] = [$required, 'integer'];
        }

        return $request->validate($rules);
    }
}
