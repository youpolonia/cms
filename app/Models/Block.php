<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Block extends Model
{
    use HasFactory;
    use HasRoles;

    protected $fillable = [
        'user_id',
        'name',
        'content',
        'is_template',
        'meta'
    ];

    protected $casts = [
        'content' => 'array',
        'is_template' => 'boolean',
        'meta' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isLocked()
    {
        return $this->meta['locked'] ?? false;
    }

    public function scopeUnlocked($query)
    {
        return $query->where(function($q) {
            $q->whereNull('meta->locked')
              ->orWhere('meta->locked', false);
        });
    }
}