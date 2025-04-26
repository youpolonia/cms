<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErrorCategory extends Model
{
    protected $fillable = [
        'name',
        'slug', 
        'description',
        'severity',
        'color',
        'is_system'
    ];

    protected $casts = [
        'is_system' => 'boolean'
    ];

    public function classifications(): HasMany
    {
        return $this->hasMany(ErrorClassification::class);
    }

    public static function systemCategories(): array
    {
        return [
            [
                'name' => 'Connection Error',
                'slug' => 'connection-error',
                'severity' => 'high',
                'color' => '#ef4444', // red-500
                'is_system' => true,
                'description' => 'Network or connection related failures'
            ],
            [
                'name' => 'Authentication Error',
                'slug' => 'auth-error',
                'severity' => 'high',
                'color' => '#f97316', // orange-500
                'is_system' => true,
                'description' => 'Authentication or permission issues'
            ],
            [
                'name' => 'Data Validation',
                'slug' => 'data-validation',
                'severity' => 'medium',
                'color' => '#eab308', // yellow-500
                'is_system' => true,
                'description' => 'Invalid or malformed data'
            ],
            [
                'name' => 'Timeout',
                'slug' => 'timeout',
                'severity' => 'medium',
                'color' => '#8b5cf6', // violet-500
                'is_system' => true,
                'description' => 'Operation timed out'
            ],
            [
                'name' => 'Resource Limit',
                'slug' => 'resource-limit',
                'severity' => 'high',
                'color' => '#ec4899', // pink-500
                'is_system' => true,
                'description' => 'Exceeded resource limits'
            ]
        ];
    }
}