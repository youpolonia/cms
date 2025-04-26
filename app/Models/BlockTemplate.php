<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'blocks'
    ];

    protected $casts = [
        'blocks' => 'array'
    ];
}
