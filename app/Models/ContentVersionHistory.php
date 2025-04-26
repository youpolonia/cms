<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentVersionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_version_id',
        'user_id',
        'action',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function version()
    {
        return $this->belongsTo(ContentVersion::class, 'content_version_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}