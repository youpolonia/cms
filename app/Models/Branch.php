<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'root_version_id',
        'current_head_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function rootVersion(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'root_version_id');
    }

    public function currentHead(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'current_head_id');
    }

    public function content()
    {
        return $this->rootVersion->content();
    }
}