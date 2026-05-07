<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $old_url
 * @property string|null $new_url
 * @property int $status_code
 * @property bool $enabled
 * @property int $hits
 */
class SeoRedirect extends Model
{
    protected $fillable = [
        'old_url',
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

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_redirects', 'seo_redirects');
    }

    public function scopeEnabled(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }
}
