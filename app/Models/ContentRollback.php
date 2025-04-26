<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentRollback extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'version_id', 
        'user_id',
        'version_data_before',
        'version_data_after',
        'reason',
        'confirmed',
        'confirmed_at',
        'comparison_data'
    ];

    protected $casts = [
        'version_data_before' => 'array',
        'version_data_after' => 'array',
        'confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'comparison_data' => 'array'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function version()
    {
        return $this->belongsTo(ContentVersion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}