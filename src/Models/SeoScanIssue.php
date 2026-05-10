<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $seo_scan_id
 * @property string|null $url
 * @property string $type
 * @property string $severity
 * @property string $status
 * @property string $message
 * @property string|null $fingerprint
 * @property array<string, mixed>|null $context
 * @property Carbon|null $resolved_at
 * @property Carbon|null $ignored_at
 * @property string|null $note
 * @property int|string|null $aggregate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SeoScan $scan
 */
class SeoScanIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_scan_id',
        'url',
        'type',
        'severity',
        'status',
        'message',
        'fingerprint',
        'context',
        'resolved_at',
        'ignored_at',
        'note',
    ];

    protected $casts = [
        'context' => 'array',
        'resolved_at' => 'datetime',
        'ignored_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues');
    }

    public function scopeOpen(Builder $builder): Builder
    {
        return $builder->where('status', 'open');
    }

    public function scopeResolved(Builder $builder): Builder
    {
        return $builder->where('status', 'resolved');
    }

    public function scopeIgnored(Builder $builder): Builder
    {
        return $builder->where('status', 'ignored');
    }

    public function markResolved(?string $note = null): bool
    {
        return $this->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
            'ignored_at' => null,
            'note' => $note ?? $this->note,
        ])->save();
    }

    public function markIgnored(?string $note = null): bool
    {
        return $this->forceFill([
            'status' => 'ignored',
            'ignored_at' => now(),
            'resolved_at' => null,
            'note' => $note ?? $this->note,
        ])->save();
    }

    public function reopen(): bool
    {
        return $this->forceFill([
            'status' => 'open',
            'resolved_at' => null,
            'ignored_at' => null,
        ])->save();
    }

    /** @return BelongsTo<SeoScan, $this> */
    public function scan(): BelongsTo
    {
        return $this->belongsTo(SeoScan::class, 'seo_scan_id');
    }
}
