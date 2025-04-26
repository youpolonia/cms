<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSound extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'duration',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public static function getDefaultSound()
    {
        return self::where('is_default', true)->first();
    }
}