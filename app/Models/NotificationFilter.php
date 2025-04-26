<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationFilter extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'value',
        'is_active'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function defaultFilters(): array
    {
        return [
            [
                'type' => 'priority',
                'value' => 'high',
                'is_active' => true
            ],
            [
                'type' => 'category',
                'value' => 'system',
                'is_active' => true
            ],
            [
                'type' => 'read_status',
                'value' => 'unread',
                'is_active' => true
            ]
        ];
    }

    public static function availableFilterTypes(): array
    {
        return [
            'priority' => ['high', 'medium', 'low'],
            'category' => ['system', 'content', 'user', 'approval'],
            'read_status' => ['read', 'unread'],
            'date_range' => ['today', 'this_week', 'this_month']
        ];
    }
}