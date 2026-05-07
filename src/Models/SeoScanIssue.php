<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
