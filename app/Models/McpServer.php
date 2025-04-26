<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McpServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'url',
        'status',
        'config',
        'last_ping_at'
    ];

    protected $casts = [
        'config' => 'array',
        'last_ping_at' => 'datetime'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_INSTALLED = 'installed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ACTIVE = 'active';
}
