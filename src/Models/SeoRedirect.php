<?php

namespace Step2dev\LazySeoTools\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $old_url
 * @property string|null $new_url
 * @property int $status_code
 * @property bool $enabled
 */
class SeoRedirect extends Model
{
    protected $fillable = [
        'old_url',
        'new_url',
        'status_code',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'status_code' => 'int',
    ];

    public function scopeEnabled(Builder $builder): Builder
    {
        return $builder->where('enabled', true);
    }
}
