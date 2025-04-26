<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentVersionComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'base_version_id',
        'compare_version_id',
        'metrics',
        'change_categories',
        'significance',
        'user_id'
    ];

    protected $casts = [
        'metrics' => 'array',
        'change_categories' => 'array'
    ];

    public function baseVersion()
    {
        return $this->belongsTo(ContentVersion::class, 'base_version_id');
    }

    public function compareVersion()
    {
        return $this->belongsTo(ContentVersion::class, 'compare_version_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}