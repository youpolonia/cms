<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'notification_data',
        'read_at',
        'archived_at'
    ];

    protected $casts = [
        'notification_data' => 'array',
        'read_at' => 'datetime',
        'archived_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}