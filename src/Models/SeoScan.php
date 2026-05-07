<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_url',
        'score',
        'pages_count',
        'issues_count',
        'broken_links_count',
        'redirect_chains_count',
        'duplicate_titles_count',
        'duplicate_descriptions_count',
        'canonical_conflicts_count',
        'summary',
        'options',
        'finished_at',
    ];

    protected $casts = [
        'score' => 'int',
        'pages_count' => 'int',
        'issues_count' => 'int',
        'broken_links_count' => 'int',
        'redirect_chains_count' => 'int',
        'duplicate_titles_count' => 'int',
        'duplicate_descriptions_count' => 'int',
        'canonical_conflicts_count' => 'int',
        'summary' => 'array',
        'options' => 'array',
        'finished_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_scans', 'seo_scans');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(SeoScanIssue::class, 'seo_scan_id');
    }

    public function scopeLatestFirst(Builder $builder): Builder
    {
        return $builder->latest('created_at');
    }

    public function passed(): bool
    {
        return $this->score >= (int) config('lazy-seo.monitoring.pass_score', 75);
    }
}
