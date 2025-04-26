<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentUserView extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'user_id',
        'viewed_at',
        'ip_address',
        'user_agent',
        'time_spent',
        'scroll_depth',
        'interacted'
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'interacted' => 'boolean'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
