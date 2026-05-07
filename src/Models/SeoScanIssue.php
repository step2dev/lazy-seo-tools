<?php

namespace Step2dev\LazySeoTools\Models;

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
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function getTable(): string
    {
        return config('lazy-seo.tables.seo_scan_issues', 'seo_scan_issues');
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(SeoScan::class, 'seo_scan_id');
    }
}
