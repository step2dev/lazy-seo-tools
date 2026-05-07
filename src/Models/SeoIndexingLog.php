<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SeoIndexingLog extends Model
{
    protected $fillable = [
        'engine',
        'host',
        'urls',
        'status',
        'successful',
        'response_body',
        'payload',
        'meta',
        'submitted_at',
    ];

    protected $casts = [
        'urls' => 'array',
        'payload' => 'array',
        'meta' => 'array',
        'status' => 'int',
        'successful' => 'bool',
        'submitted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_indexing_logs', 'seo_indexing_logs');
    }

    public function scopeSuccessful(Builder $builder): Builder
    {
        return $builder->where('successful', true);
    }

    public function scopeFailed(Builder $builder): Builder
    {
        return $builder->where('successful', false);
    }
}
