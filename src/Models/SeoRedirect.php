<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $old_url
 * @property string|null $normalized_old_url
 * @property string|null $normalized_old_url_hash
 * @property string|null $new_url
 * @property int $status_code
 * @property bool $enabled
 * @property bool $is_regex
 * @property int $hits
 */
class SeoRedirect extends Model
{
    protected $fillable = [
        'old_url',
        'normalized_old_url',
        'normalized_old_url_hash',
        'new_url',
        'status_code',
        'enabled',
        'is_regex',
        'hits',
        'last_hit_at',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'is_regex' => 'bool',
        'status_code' => 'int',
        'hits' => 'int',
        'last_hit_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (SeoRedirect $redirect): void {
            $redirect->normalized_old_url = $redirect->shouldNormalizeOldUrl()
                ? static::normalizePath($redirect->old_url)
                : null;
            $redirect->normalized_old_url_hash = $redirect->normalized_old_url
                ? sha1($redirect->normalized_old_url)
                : null;
        });

        static::saved(fn (): bool => Cache::forget('lazy-seo.redirects.patterns'));
        static::deleted(fn (): bool => Cache::forget('lazy-seo.redirects.patterns'));
    }

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_redirects', 'seo_redirects');
    }

    public function scopeEnabled(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }

    public function scopeExact(Builder $builder): Builder
    {
        return $builder->where('is_regex', false)->where('old_url', 'not like', '%*%');
    }

    public function scopePattern(Builder $builder): Builder
    {
        return $builder->where(function (Builder $query): void {
            $query->where('is_regex', true)->orWhere('old_url', 'like', '%*%');
        });
    }

    public static function normalizePath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $path = '/'.trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    protected function shouldNormalizeOldUrl(): bool
    {
        return ! $this->is_regex && ! str_contains($this->old_url, '*');
    }
}
