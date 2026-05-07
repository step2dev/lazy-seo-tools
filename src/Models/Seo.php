<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string|null $url
 * @property string|null $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $canonical_url
 * @property array|null $robots
 * @property bool $indexable
 */
class Seo extends Model
{
    use HasTranslations;

    protected $table = 'seo';

    public array $translatable = [
        'title',
        'description',
        'keywords',
    ];

    protected $fillable = [
        'url',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'robots',
        'indexable',
        'seoable_type',
        'seoable_id',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'keywords' => 'array',
        'robots' => 'array',
        'indexable' => 'bool',
    ];

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUrl(Builder $builder, string $url): Builder
    {
        $normalized = '/'.ltrim(parse_url($url, PHP_URL_PATH) ?: $url, '/');

        return $builder->whereIn('url', [$url, $normalized, ltrim($normalized, '/')]);
    }

    public function scopeSearch(Builder $builder, ?string $search): Builder
    {
        return $builder->when($search, function (Builder $query, string $search) {
            $query->where(function (Builder $q) use ($search) {
                $q
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('keywords', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%');
            });
        });
    }
}
