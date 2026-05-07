<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
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

    public array $translatable = [
        'title',
        'description',
        'keywords',
    ];

    protected $fillable = [
        'url',
        'url_hash',
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
        'url_hash' => 'string',
        'indexable' => 'bool',
    ];


    protected static function booted(): void
    {
        static::saving(function (Seo $seo): void {
            $seo->url_hash = $seo->url ? sha1(static::normalizeUrlForLookup($seo->url)) : null;
        });
    }

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo', 'seo');
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUrl(Builder $builder, string $url): Builder
    {
        $candidates = array_values(array_unique([
            $url,
            static::normalizeUrlForLookup($url),
            ltrim(static::normalizeUrlForLookup($url), '/'),
        ]));

        $hashes = array_map(static fn (string $candidate): string => sha1(static::normalizeUrlForLookup($candidate)), $candidates);

        return $builder->where(function (Builder $query) use ($candidates, $hashes): void {
            $query->whereIn('url_hash', array_values(array_unique($hashes)))
                ->orWhereIn('url', $candidates);
        });
    }


    public static function normalizeUrlForLookup(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '/';
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $query = parse_url($url, PHP_URL_QUERY);
        $normalized = '/'.trim($path, '/');
        $normalized = $normalized === '/' ? '/' : rtrim($normalized, '/');

        return Str::lower($query ? $normalized.'?'.$query : $normalized);
    }

    public function scopeSearch(Builder $builder, ?string $search): Builder
    {
        return $builder->when($search, function (Builder $query, string $search): void {
            $query->where(function (Builder $q) use ($search): void {
                $q
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('keywords', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%');
            });
        });
    }
}
