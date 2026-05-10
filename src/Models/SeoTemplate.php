<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $name
 * @property array<string, string>|null $title
 * @property array<string, string>|null $description
 * @property array<string, string>|null $keywords
 * @property array<string, mixed>|null $payload
 * @property bool $enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
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
