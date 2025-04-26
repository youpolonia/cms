<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBuilderComponent extends Model
{
    protected $fillable = [
        'type',
        'content',
        'page_id',
        'position',
        'styles',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'content' => 'array',
        'styles' => 'array',
        'settings' => 'array'
    ];

    public function page()
    {
        return $this->belongsTo(Content::class, 'page_id');
    }
}
