<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentRestoration extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'version_id', 
        'restored_by',
        'restored_at'
    ];

    protected $casts = [
        'restored_at' => 'datetime'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function version()
    {
        return $this->belongsTo(ContentVersion::class);
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}