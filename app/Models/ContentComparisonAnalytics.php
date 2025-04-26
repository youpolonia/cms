<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentComparisonAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'version1_id',
        'version2_id',
        'granularity',
        'user_id',
        'compared_at'
    ];

    protected $casts = [
        'compared_at' => 'datetime'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function version1()
    {
        return $this->belongsTo(ContentVersion::class, 'version1_id');
    }

    public function version2()
    {
        return $this->belongsTo(ContentVersion::class, 'version2_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}