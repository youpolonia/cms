<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'version1_id',
        'version2_id',
        'change_count',
        'additions',
        'deletions',
        'changed_lines',
        'similarity',
        'version1_views',
        'version2_views',
        'expires_at'
    ];

    protected $dates = [
        'expires_at'
    ];

    public function version1()
    {
        return $this->belongsTo(ContentVersion::class, 'version1_id');
    }

    public function version2()
    {
        return $this->belongsTo(ContentVersion::class, 'version2_id');
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }
}