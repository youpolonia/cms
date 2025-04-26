<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'metadata',
        'user_id'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function blocks()
    {
        return $this->hasMany(Block::class)->orderBy('order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}