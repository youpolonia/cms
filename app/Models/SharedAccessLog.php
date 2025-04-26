<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_comparison_stat_id',
        'ip_address',
        'country_code',
        'city',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'referrer',
        'metadata',
        'duration_seconds',
        'scroll_depth',
        'interactions'
    ];

    protected $casts = [
        'metadata' => 'array',
        'interactions' => 'array'
    ];

    public function comparisonStat()
    {
        return $this->belongsTo(VersionComparisonStat::class, 'version_comparison_stat_id');
    }
}
