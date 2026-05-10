<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $start_url
 * @property int|null $previous_scan_id
 * @property string $status
 * @property int $score
 * @property int $score_delta
 * @property int $pages_count
 * @property int $issues_count
 * @property int $new_issues_count
 * @property int $resolved_issues_count
 * @property int $broken_links_count
 * @property int $external_broken_links_count
 * @property int $redirect_chains_count
 * @property int $duplicate_titles_count
 * @property int $duplicate_descriptions_count
 * @property int $canonical_conflicts_count
 * @property array<string, mixed>|null $summary
 * @property array<int, array<string, mixed>>|null $regressions
 * @property array<int, array<string, mixed>>|null $resolved_issues
 * @property array<string, mixed>|null $options
 * @property string|null $failure_reason
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SeoScan|null $previousScan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SeoScanIssue> $issues
 */
class SeoScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_url',
        'previous_scan_id',
        'status',
        'score',
        'score_delta',
        'pages_count',
        'issues_count',
        'new_issues_count',
        'resolved_issues_count',
        'broken_links_count',
        'external_broken_links_count',
        'redirect_chains_count',
        'duplicate_titles_count',
        'duplicate_descriptions_count',
        'canonical_conflicts_count',
        'summary',
        'regressions',
        'resolved_issues',
        'options',
        'failure_reason',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'previous_scan_id' => 'int',
        'score' => 'int',
        'score_delta' => 'int',
        'pages_count' => 'int',
        'issues_count' => 'int',
        'new_issues_count' => 'int',
        'resolved_issues_count' => 'int',
        'broken_links_count' => 'int',
        'external_broken_links_count' => 'int',
        'redirect_chains_count' => 'int',
        'duplicate_titles_count' => 'int',
        'duplicate_descriptions_count' => 'int',
        'canonical_conflicts_count' => 'int',
        'summary' => 'array',
        'regressions' => 'array',
        'resolved_issues' => 'array',
        'options' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_scans', 'seo_scans');
    }

    /** @return BelongsTo<SeoScan, $this> */
    public function previousScan(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_scan_id');
    }

    /** @return HasMany<SeoScanIssue, $this> */
    public function issues(): HasMany
    {
        return $this->hasMany(SeoScanIssue::class, 'seo_scan_id');
    }

    public function scopeLatestFirst(Builder $builder): Builder
    {
        return $builder->latest('created_at');
    }

    public function scopePending(Builder $builder): Builder
    {
        return $builder->where('status', 'pending');
    }

    public function scopeRunning(Builder $builder): Builder
    {
        return $builder->where('status', 'running');
    }

    public function scopeCompleted(Builder $builder): Builder
    {
        return $builder->where('status', 'completed');
    }

    public function scopeFailed(Builder $builder): Builder
    {
        return $builder->where('status', 'failed');
    }

    public function markRunning(): bool
    {
        return $this->forceFill([
            'status' => 'running',
            'failure_reason' => null,
            'started_at' => now(),
        ])->save();
    }

    public function markFailed(string $reason): bool
    {
        return $this->forceFill([
            'status' => 'failed',
            'failure_reason' => mb_substr($reason, 0, 255),
            'finished_at' => now(),
        ])->save();
    }

    public function markCompleted(): bool
    {
        return $this->forceFill([
            'status' => 'completed',
            'failure_reason' => null,
            'finished_at' => now(),
        ])->save();
    }

    public function passed(): bool
    {
        return $this->score >= (int) config('lazy-seo.monitoring.pass_score', 75);
    }
}
