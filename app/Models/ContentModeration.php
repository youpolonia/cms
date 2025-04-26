<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentModeration extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_version_id',
        'moderator_id',
        'action',
        'notes',
        'changes_requested'
    ];

    protected $casts = [
        'changes_requested' => 'array'
    ];

    public function version()
    {
        return $this->belongsTo(ContentVersion::class, 'content_version_id');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
