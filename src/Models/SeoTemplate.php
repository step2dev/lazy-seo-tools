<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SeoTemplate extends Model
{
    use HasTranslations;

    public array $translatable = [
        'title',
        'description',
        'keywords',
    ];

    protected $fillable = [
        'name',
        'title',
        'description',
        'keywords',
        'payload',
        'enabled',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'keywords' => 'array',
        'payload' => 'array',
        'enabled' => 'bool',
    ];

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_templates', 'seo_templates');
    }

    public function scopeEnabled(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }
}
